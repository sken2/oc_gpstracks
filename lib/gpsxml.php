<?php

namespace OCA\GpsTracks\Lib;

use \OC\Files\Node;
use \OC\Files\View;
use \OC\Files\Filesystem;
use \OC\Files\Fileinfo;

use \OCP\IDb;
use \OCP\IDbConnection;

use \OCA\GpsTracks\Lib\GpxDOM;

class GpsXML {

	private $appname;
	private $userId;
	private $db;
	private $storage;

	public function __construct(
		$AppName,
		$UserId,
//		View $view,
//		IDb $db
		IDbConnection $db
	) {
		$this->appname = $AppName;
		$this->userId = $UserId;
		$this->db = $db;

	}

	public function refresh() {
	
		$view = new \OC\Files\View('');	//! need root ?
		$gpx_files = $view->searchByMime('application/gpx');
		$fileids = array();
		foreach ($gpx_files as $fileinfo) {
			$fileid = $fileinfo->getId();
			$fileids[$fileid] = false;
		}

		$sql = 'select distinct fileid from oc_gpx_tracks;';
		$r = $this->db->query($sql);
		$filesondb = $r->fetchAll(\PDO::FETCH_ASSOC);
//return $filesondb;
		$lost = array() ;
		foreach ($filesondb as $t) {
			$id = $t['fileid'];
			if(isset ($fileids[$id])) {
				$fileids[$id] = true;
			} else {
				$lost[] = $id; //on the db but not in filecache
			}
		}

		$done = array();
		foreach ($fileids as $fileid => $status) {
			if (!$status) {
				$this->put_all_tracks($fileid);
				$done[] = $fileid;
			}
		}
		return $done;
		//! TODO remove lost files records
	}
	protected function put_all_tracks($id) {

		$DOM = $this->idtoDom($id);
		$tracks = $DOM->get_track_number();
		$putted = false ;
		for ($i = 0; $i<$tracks ; $i++) {
			$points = $DOM->get_tracks($i);
			if ($points !== 0) {
				$this->put_track_from_dom($DOM, $i);
				$putted = true;
			}
		}
		return $putted;
	}

	public function getTrackInfo($id){
		$DOM = $this->idtoDom($id);
		$tracks = $DOM->get_track_number();
		$t = array();
		for($i = 0; $i < $tracks ; $i++) {
			$points = $DOM->get_tracks($i) ;
			if ($points === 0) {
				continue;
			}
			$end = end($points)['time'];
			$start = reset($points)['time'];
			$t[] = array($i, $start, $end) ;
		}
		return array($t);
	}

	public function getSegment($id, $seg) {
		$DOM = $this->idtoDom($id);
		return $DOM->get_tracks($seg);
	}

	public function put_track($id, $seg = 0) {

		$DOM = $this->idtoDom($id);
		$tracks = $DOM->get_track_number();
		return $this->put_tracks_from_dom($DOM, $seg);
	}
	protected function put_track_from_dom($DOM, $seg = 0){
		try{
			$this->db->beginTransaction();
			$sql = 'insert into oc_gpx_tracks'
				.' (name, ctime, user_id, fileid)'
//				.' values(:name, :time, :uid, :fileid)'
				.' values(?, ?, ?, ?)'
				.' returning *';
			$trk_st=$this->db->prepare($sql);
			$name = $DOM->get_trackname($trkno);
			$now=date('U');
			$trk_st->bindValue(1, $name, \PDO::PARAM_STR);
			$trk_st->bindValue(2, $now, \PDO::PARAM_INT);
			$trk_st->bindValue(3, $this->userId, \PDO::PARAM_STR);
			$trk_st->bindValue(4, $id, \PDO::PARAM_INT);
			$trk_st->execute();
			$res=$trk_st->fetch(\PDO::FETCH_OBJ);

//			$track_id = $this->dbo->lastInsertId('oc_gpstrack_id_seq');
			$track_id = $res->id;
			$sql = 'insert into oc_gpx_points'
				.' (lat, lon, time, ele, speed, track_id)'
//				.' values (:lat, :lon, :time, :ele, :speed, :track_id);';
				.' values (?, ?, ?, ?, ?, ?);';
                        $pos_st=$this->db->prepare($sql);
                        foreach($DOM->get_tracks($seg) as $pos){
				$pos_st->bindValue(1, $pos['lat']);
				$pos_st->bindValue(2, $pos['lon']);
				$pos_st->bindValue(3, $pos['time'], \PDO::PARAM_INT);
				$pos_st->bindValue(4, $pos['ele']);
				$pos_st->bindValue(5, $pos['speed']);
				$pos_st->bindValue(6, $track_id, \PDO::PARAM_INT);
				$pos_st->execute();
			}
			$this->db->commit();
		} catch (\PDOException $e){
			$this->db->rollback();
			throw $e;
		}
		return true;
	}

	public function delete_track($track_id) {

		$this->db->beginTransaction();
		try {
			$sql = "SELECT * from oc_gpx_tracks"
				." where id = ?"
				." and user_id = ? for update";
			$prep = $this->db->prepare($sql);
			$prep->bindValue(1, $track_id, \PDO::PARAM_INT);
			$prep->bindValue(2, $this->userId);
			$prep->execute();
			$r = $prep->fetch(\PDO::FETCH_OBJ);
			if (!$r) {
				throw new \Exception('oops');
			}
			$sql = "delete from oc_gpx_points where track_id = $r->id;";
			$this->db->query($sql);
			$sql = "delete from oc_gpx_tracks where id = $r->id;";
			$this->db->query($sql);
			$this->db->commit();
			
		} catch (\PDOException $e) {//!
			$this->db->rollback();
			throw $e;
//return 'oops';
		}
		return true;
	}

	protected function idtoDom ($id){
		$id=(int)$id;
		$view = new \OC\Files\View('');	//! need root ?
		$path = $view->getPath($id);
		$DOM = new GpxDOM();
		$DOM->loadXML($view->file_get_contents($path));
		return $DOM;
	}

	protected function is_identical_track($points) {


	}
	public function find_point_from_time($time, $mins = 1) {

		if(!is_Numeric($mins) or $mins < 1){
			$mins = 1;
		}
		$intspec = $this->mintointerval($mins);
		$sql = "with t as (SELECT to_timestamp(?) as t),"
			."be as (SELECT t.t - interval $intspec as b, t.t + interval $intspec as e"
			." from t)"
			."SELECT p.* FROM oc_gpx_points p, oc_gpx_tracks tr, be"
			." WHERE to_timestamp(p.time) BETWEEN be.b AND be.e"
			." AND tr.user_id = ?"
			." AND tr.id = p.track_id";
		$prep = $this->db->prepare($sql);
		$prep->bindValue(1, $time, \PDO::PARAM_INT);
		$prep->bindValue(2, $this->userId);
		$prep->execute();
		$r = $prep->fetchAll(\PDO::FETCH_OBJ);

		return $r;
	}

	protected function mintointerval($min) {
		return "'".$min." mins'";
	}
	/**
	 * stubb for UI debugging
	 */
//	public function test(){
//		return array('hoge');
//	}

}

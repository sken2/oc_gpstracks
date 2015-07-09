<?php

namespace OCA\GpsTracks\Lib;

use OC\Files\Node;
use OC\Files\View;
use OC\Files\Filesystem;
use OC\Files\Fileinfo;

use OCP\IDb;
use OCP\IDbConnection;

use OCA\OwnLayer\Lib\GeoJSON;
use OCA\GpsTracks\Lib\GpxDOM;

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

	public function index($order = 'asc') {
		if($order !== 'asc') {
			$order = 'desc';
		}
//		$this->refresh();

		$sql = "SELECT * from *PREFIX*gpx_tracks"
			." WHERE user_id = ?"
			." order by ctime $order;";
		$prep = $this->db->prepare($sql);
		$prep->bindValue(1, $this->userId);
		$prep->execute();
		$res = array();
		while (($r = $prep->fetch(\PDO::FETCH_OBJ)) !== false) {
			$res[] = $r;
		}
		return $res;
	}

	public function refresh() {
	
		$view = new \OC\Files\View('');	//! need root ?
		$gpx_files = $view->searchByMime('application/gpx');
		$fileids = array();
		foreach ($gpx_files as $fileinfo) {
			$fileid = $fileinfo->getId();
			$fileids[$fileid] = false;
		}

		$sql = "select distinct fileid"
			." from *PREFIX*gpx_tracks"
			." WHERE user_id = ?";
		$prep = $this->db->prepare($sql);
		$prep->bindValue(1, $this->userId);
		$prep->execute($sql);
		$filesondb = $prep->fetchAll(\PDO::FETCH_ASSOC);
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
				$this->putAllTracks($fileid);
				$done[] = $fileid;
			}
		}
		return $done;
		//! TODO remove lost files records
	}
	protected function putAllTracks($id) {

		$DOM = $this->idtoDom($id);
		$tracks = $DOM->get_track_number();
		$putted = false ;
		for ($i = 0; $i<$tracks ; $i++) {
			$points = $DOM->get_tracks($i);
			if (count($points) > 0 and $this->isIdentical($DOM->get_trackname($i))) {
				$this->putTrackFromDom($DOM, $id , $i);
				$putted = true;
			}
		}
		return $putted;
	}

public function getTrackInfo($id) {

		$sql = "SELECT po.*"
			." FROM *PREFIX*gpx_points as po, *PREFIX*gpx_tracks as tr"
			." WHERE po.track_id = ?"
			." AND tr.id = po.track_id"
			." AND tr.user_id = ?"
			." ORDER by time asc";
		$prep = $this->db->prepare($sql);
		$prep->bindValue(1, $id);
		$prep->bindValue(2, $this->userId);
		$prep->execute();
		$points = $prep->fetchAll(\PDO::FETCH_OBJ);
		return $this->pointsToGeoJSON($points);
	}

	public function getSegment($id, $seg) {
		$DOM = $this->idtoDom($id);
		return $DOM->get_tracks($seg);
	}

	public function putTrack($id, $seg = 0) {

		$DOM = $this->idtoDom($id);
		$tracks = $DOM->get_track_number();
		return $this->putTrackFromDom($DOM, $id, $seg);
	}

	protected function putTrackFromDom($DOM, $fileid, $seg = 0){
		try{
			$this->db->beginTransaction();
			$sql = 'insert into *PREFIX*gpx_tracks'
				.' (name, ctime, user_id, fileid)'
//				.' values(:name, :time, :uid, :fileid)'
				.' values(?, ?, ?, ?)'
				.' returning *';
			$trk_st=$this->db->prepare($sql);
			$name = $DOM->get_trackname($seg);
//			$now=date('U');
			$now = new \DateTime();
			$now = $now->format(\DateTime::ISO8601);
			$trk_st->bindValue(1, $name, \PDO::PARAM_STR);
//			$trk_st->bindValue(2, $now, \PDO::PARAM_INT);
			$trk_st->bindValue(2, $now);
			$trk_st->bindValue(3, $this->userId, \PDO::PARAM_STR);
			$trk_st->bindValue(4, $fileid, \PDO::PARAM_INT);
			$trk_st->execute();
			$res=$trk_st->fetch(\PDO::FETCH_OBJ);

//			$track_id = $this->dbo->lastInsertId('*PREFIX*gpstrack_id_seq');
			$track_id = $res->id;
			$sql = 'insert into *PREFIX*gpx_points'
				.' (lat, lon, time, ele, speed, track_id)'
//				.' values (:lat, :lon, :time, :ele, :speed, :track_id);';
				.' values (?, ?, ?, ?, ?, ?);';
                        $pos_st=$this->db->prepare($sql);
                        foreach($DOM->get_tracks($seg) as $pos){
				$time = \DateTime::createFromFormat('U', $pos['time']);
				$time = $time->format(\DateTime::ISO8601);
				$pos_st->bindValue(1, $pos['lat']);
				$pos_st->bindValue(2, $pos['lon']);
//				$pos_st->bindValue(3, $pos['time'], \PDO::PARAM_INT);
				$pos_st->bindValue(3, $time);
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
			$sql = "SELECT * from *PREFIX*gpx_tracks"
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
			$sql = "delete from *PREFIX*gpx_points where track_id = $r->id;";
			$this->db->query($sql);
			$sql = "delete from *PREFIX*gpx_tracks where id = $r->id;";
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

	protected function isIdentical($name) {
//		$name = $DOM->get_trackname($seg);
		$sql = "SELECT count(id) from *PREFIX*gpx_tracks"
			." WHERE name = ?"
			." AND user_id = ?";
		$prep = $this->db->prepare($sql);
		$prep->bindValue(1, $name);
		$prep->bindValue(2, $this->userId);
		$prep->execute();
		$r = $prep->fetch(\PDO::FETCH_OBJ);
		return (bool)!$r->count;
	}

	public function findPointFromTime($time, $mins = 1) {

		if(!is_Numeric($mins) or $mins < 1){
			$mins = 3;
		}
		$intspec = $this->mintointerval($mins);
		$sql = "with t as (SELECT to_timestamp(?) as t),"
			."be as (SELECT t.t - interval $intspec as b, t.t + interval $intspec as e"
			." from t)"
			."SELECT p.* FROM *PREFIX*gpx_points p, *PREFIX*gpx_tracks tr, be"
			." WHERE p.time BETWEEN be.b AND be.e"
			." AND tr.user_id = ?"
			." AND tr.id = p.track_id"
			." ORDER by p.time asc";
		$prep = $this->db->prepare($sql);
		$prep->bindValue(1, $time, \PDO::PARAM_INT);
		$prep->bindValue(2, $this->userId);
		$prep->execute();
		$r = $prep->fetchAll(\PDO::FETCH_OBJ);
		return $this->pointsToGeoJSON($r);
	}
	protected function mintointerval($min) {
		return "'".$min." mins'";
	}

	protected function pointsToGeoJSON($points) {
		$pts = array();
		foreach ($points as $point) {
			$pts[] = array($point->lon, $point->lat);
		}
		$mpt = new GeoJSON('MultiPoint', $pts);
		return $mpt;
	}

	public function getXml($id) {
		$sql = "select * from *PREFIX*gpx_tracks"
			." WHERE id = ?"
			." AND user_id = ?";
		$prep = $this->db->prepare($sql);
		$prep->bindValue(1, $id, \PDO::PARAM_INT);
		$prep->bindValue(2, $this->userId);
		$prep->execute();
		$trk = $prep->fetch(\PDO::FETCH_OBJ);
		if(!$trk) {
			throw new \Exception('UUPS');
		}
		$sql = "select * from *PREFIX*gpx_points"
			." WHERE track_id = ?"
			." ORDER by time";
		$prep = $this->db->prepare($sql);
		$prep->bindValue(1, $id, \PDO::PARAM_INT);
		$prep->execute();
		$points = $prep->fetchAll(\PDO::FETCH_OBJ);
		$X = new gpxDOM();
		$X->create_track($trk, $points);
		return $X->saveXML();
	}

	public function getMovingAmount($id, $span = 600) {
		$int = '1 min';//!
		$sql =" with be as (select date_trunc('min', min(time)) as b, max(time) as e"
			." from *PREFIX*gpx_points where track_id =?),"

			."grid as (select generate_series(be.b, be.e, '$int')as st from be)"
			."select grid.st as time, avg(p.lat) as lat, avg(p.lon) as lon,"
			."avg(p.ele) as ele, avg(p.speed) as speed"
			." from *PREFIX*gpx_points p, *PREFIX*gpx_tracks tr, grid "
			." where p.track_id=12"
			." and tr.user_id = ? and p.track_id = tr.id"
			." and p.time between grid.st and grid.st + interval '$int'"
			." group by grid.st order by grid.st asc";
		$prep = $this->db->prepare($sql);
		$prep->bindValue(1, $id, \PDO::PARAM_INT);
		$prep->bindValue(2, $this->userId);
		$prep->execute();
		$r = $prep->fetchAll(\PDO::FETCH_OBJ);

//		$prev = null;
//		foreach($r as $point) {
//			if(!$prev) {
//				$prev = $point;
//				$point->dist = 0.0;
//			} else {
//				$oo = ((float)$prev.lon - (float)$point.lon);
//				$oa = ((float)$prev.lat - (float)$point.lat);
//				$point->dist = sqrt($oo*$oo + $oa*$oa);
////				$point->dist = $prev->lat - $point->lat;
//				$prev = $point;
//			}
//		}
		return $this->pointsToGeoJSON($r);
	}
	public function getPointsGsoJSON($id) {

	}
	/**
	 * stubb for UI debugging
	 */
	public function test($id){
		return $this->getMovingAmount(12);
//		return $this->getXml($id);	
//		return $this->isIdentical($name);
//		return array('hoge');
	}

}

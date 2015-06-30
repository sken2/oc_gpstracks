<?php
namespace OCA\GpsTracks\Lib;

use OC\Files\Fileinfo;

class GpxDOM extends \DOMDocument{

	protected $gpx_version;
	protected $creator;
	protected $track_name;

	public function __construct($ver='1.0', $encoding='utf-8'){
		parent::__construct($ver, $encoding);
	}

	/**
	 * @ param $xml : stirng
	 */
	public function loadXML($xml){
		$r=parent::loadXML($xml);
		$root=$this->firstChild;
		$this->gpx_version=$root->getAttribute('version');
//print $this->gpx_version;	// it says 1.1
		$this->creator=$root->getAttribute('creator');
		$this->trim_crlf();
		return $r;
	}

	public function get_track_number(){
		$root=$this->firstChild;
		for($i = 0; $this->lookup_node($root, "trk", $i); $i++){

		}
		return $i;
	}

	public function get_trackname($nth = 0){

		$trk=$this->lookup_node($this->firstChild, "trk", $nth);
		$name=$this->lookup_node($trk, "name");
		return $name->nodeValue;
	}

	public function get_tracks($nth = 0){

		$points = array();
		$trk=$this->lookup_node($this->firstChild, "trk", $nth);
		$trkseg=$this->lookup_node($trk, "trkseg");
		
		for($i=0; $i<$trkseg->childNodes->length; $i++){
			$trkpt=$trkseg->childNodes->item($i);
			$points[]=$this->decode_point($trkpt);
		}
		return $points;
	}

	protected function decode_point($point_node){
		$t=array();
		$t['lat']=(float)$point_node->getAttribute('lat');
		$t['lon']=(float)$point_node->getAttribute('lon');
		$timestr=$this->lookup_node($point_node, 'time')->nodeValue;
		$D=new \DateTime($timestr);
		$t['time']=(int) $D->format('U');
		$t['ele']=(float)$this->lookup_node($point_node, 'ele')->nodeValue;
		$t['speed']=(float)$this->lookup_node($point_node, 'speed')->nodeValue;
		return $t;
	}

	protected function lookup_node($here, $name, $nth=0){
		$nth = (int)$nth;

		if(!$here->hasChildNodes()){
			return false;
		}
		for($i=0; $i<$here->childNodes->length; $i++){
			if($here->childNodes->item($i)->nodeName === $name){
				if($nth === 0){
					return $here->childNodes->item($i);
				} else {
					$nth--;
				}
			}
		}
		return false;
	}

	protected function trim_crlf($here=null){
		$to_remove=array();
		if(!$here){
			$here=$this;
		}
		if(!$here->hasChildNodes()){
			return;
		}
		for($i=0; $i<$here->childNodes->length; $i++){
			if($here->childNodes->item($i)->nodeType===XML_ELEMENT_NODE){
				$this->trim_crlf($here->childNodes->item($i));
			}
			if($here->childNodes->item($i)->nodeType===XML_TEXT_NODE
			&& !rtrim($here->childNodes->item($i)->nodeValue)) {
				$to_remove[]=$here->childNodes->item($i);
			}
		}
		if(!empty($to_remove)){
			foreach ($to_remove as $e){
				$here->removeChild($e);
			}
		}
		return;
	}
}

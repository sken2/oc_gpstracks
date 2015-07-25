<?php

namespace OCA\GpsTracks\Controller;

use Exception;

use OCP\IRequest;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JsonResponse;
use OCP\AppFramework\Controller;

use OCA\GpsTracks\Lib\GpsXML;

class JsonController extends Controller {

	private $lib;

	public function __construct($AppName, $UserId, IRequest $request, GpsXML $lib){
		parent::__construct($AppName, $request);
		$this->lib = $lib;
	}

	public function index() {
		return new JsonResponse($this->lib->index());
	}
	public function refresh() {
		return new JsonResponse($this->lib->refresh());
	}
	public function trkinfo($trkid){
//		return new DataResponse($this->lib->getTrackInfo($trkid));
		return new DataResponse($this->lib->getMovingAmount($trkid));
	}
	public function segment($fileid, $segno) {
		return new JsonResponse($this->lib->getSegment($fileid, $segno));
	}

	public function writedb($fileid, $segno) {
		return new JsonResponse($this->lib->putTrack($fileid, $segno));
	}
//	public function writeall() {
//		return new JsonResponse($this->lib->refresh());
//	}
	public function findpoint($time){
		/** @var epoch $time */
		return new JsonResponse($this->lib->findPointFromTime($time));
	}
	public function test($foo){
//		return new JsonResponse($this->lib->refresh($foo));
		return new JsonResponse($this->lib->test($foo));
//		return new JsonResponse(['test']);
	}
}

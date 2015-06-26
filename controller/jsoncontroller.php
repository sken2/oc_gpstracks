<?php

namespace OCA\GpsTracks\Controller;

use \Exception;

use \OCP\IRequest;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\DataResponse;
use \OCP\AppFramework\Http\JsonResponse;
use \OCP\AppFramework\Controller;

use \OCA\GpsTracks\Lib\GpsXML;

class JsonController extends Controller {

	private $lib;

	public function __construct($AppName, $UserId, IRequest $request, GpsXML $lib){
		parent::__construct($AppName, $request);
		$this->lib = $lib;
	}

	public function index($fileid){
		return new JsonResponse($this->lib->getTrackInfo($fileid));
	}

	public function segment($fileid, $segno) {
		return new JsonResponse($this->lib->getSegment($fileid, $segno));
	}

	public function writedb($fileid, $segno) {
		return new JsonResponse($this->lib->put_track($fileid, $segno));
	}

	public function test($foo){
		return new JsonResponse($this->lib->test($foo));
//		return new JsonResponse(['test']);
	}
}

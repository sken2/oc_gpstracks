<?php

namespace OCA\GpsTracks\Controller;

use \Exception;

use \OCP\IRequest;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\DataResponse;
use \OCP\AppFramework\Http\JsonResponse;
use \OCP\AppFramework\Controller;

use \OCA\GpsTracks\Lib\GpsXML;
use \OCA\GpsTracks\Lib\TestService;

class TestController extends Controller {

	private $lib;

	public function __construct($AppName, $UserId, IRequest $request, TestService $lib){
		parent::__construct($AppName, $request);
		$this->lib = $lib;
	}

	public function test($foo) {
		$this->lib->test1();
		return new JsonResponse(['hoge']);
//		return new JsonResponse(['test']);
	}
}

<?php

namespace OCA\GpsTracks\AppInfo;

use \OCP\AppFramework\App;
//use \OCP\Files\Folder;

use \OCA\GpsTracks\Controller\PageController;
use \OCA\GpsTracks\Controller\JsonController;
use \OCA\GpsTracks\Controller\TestController;//!

use \OCA\GpsTracks\Lib\GpsXML;

class Applicaton extends App {

	public function __construct(array $urlParams=array()){

		parent::__construct('exifview', $urlParams);

		$container = $this->getContainer();

		$container->registerService('RootView', function ($c) {
			return \OC\files::getView();
		});

//		$container->registerService('Dbo', function ($c) {
//			return $c->query('ServerContainer')->getDb();
//		});
//
		$container->registerService('GpsXML', function ($c) {
			return new GpsXML(
				$c->query('AppName'),
				$c->query('UserId'),
				$c->query('ServerContainer')->getDbConnection()
//				$c->query('ServerContainer')->getDb()
//				$c->query('RootView')
			);
		});

		$container->registerService('UserStorage', function ($c){
			return $c->query('ServerContainer')->getUserFolder();
		});

		$container->registerService('JsonController', function($c){
			return new JsonController(
				$c->query('AppName'),
				$c->query('UserId'),
				$c->query('Request'),
				$c->query('GpsXML')
			);
		});

		$container->registerService('TestService', function($c) {
			return new TestService(
				$c->query('ServerContainer')->getDbConnection()
			);
		});
		$container->registerService('TestController', function($c){
			return new TestController(
				$c->query('AppName'),
				$c->query('UserId'),
				$c->query('Request'),
				$c->query('TestService')
			);
		});
	}
}

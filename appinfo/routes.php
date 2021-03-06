<?php
/**
 * ownCloud - gpstracks
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author shi <shi@example.com>
 * @copyright shi 2015
 */

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\GpsTracks\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return array(
	'routes' => array(
		array(
			'name' => 'page#index',
			'url' => '/',
			'verb' => 'GET',
		),
		array(
			'name' => 'json#index',
			'url' => '/gpx',
			'verb' => 'GET',
		),
		array(
			'name' => 'json#refresh',
			'url' => '/gpx',
			'verb' => 'POST',
		),
		array(
			'name' => 'json#trkinfo',
			'url' => '/gpx/{trkid}',
			'verb' => 'GET',
		),
//		array(
//			'name' => 'json#segment',
//			'url' => '/gpx/{fileid}/{segno}',
//			'verb' => 'GET',
//		),
//		array(
//			'name' => 'json#writedb',
//			'url' => '/gpx/{fileid}/{segno}',
//			'verb' => 'POST',
//		),
		array(
			'name' => 'json#findpoint',
			'url' => '/gpxmatch/{time}',
			'verb' => 'GET',
		),
		array(
			'name' => 'json#test',
			'url' => '/test/{foo}',
			'verb' => 'GET',
		),
	),
);

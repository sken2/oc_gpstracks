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

//namespace OCA\GpsTracks\AppInfo;

//use OCP\AppFramework\App;
//
//$app = new App('gpstracks');
//$container = $app->getContainer();
//
//$container->query('OCP\INavigationManager')->add(function () use ($container) {
//	$urlGenerator = $container->query('OCP\IURLGenerator');
//	$l10n = $container->query('OCP\IL10N');
//	return [
//		// the string under which your app will be referenced in owncloud
//		'id' => 'gpstracks',
//
//		// sorting weight for the navigation. The higher the number, the higher
//		// will it be listed in the navigation
//		'order' => 10,
//
//		// the route that will be shown on startup
//		'href' => $urlGenerator->linkToRoute('gpstracks.page.index'),
//
//		// the icon that will be shown in the navigation
//		// this file needs to exist in img/
//		'icon' => $urlGenerator->imagePath('gpstracks', 'app.svg'),
//
//		// the title of your application. This will be used in the
//		// navigation or on the settings page of your app
//		'name' => $l10n->t('Gps Tracks'),
//	];
//});
\OCP\App::addNavigationEntry([
		'id' => 'gpstracks',
		'order' => 10,
//		'name' => $l10n->t('Gps Tracks'),
//		'icon' => $urlGenerator->imagePath('gpstracks', 'app.svg')
        'href' => \OCP\Util::linkToRoute('gpstracks.page.index'),

        // the icon that will be shown in the navigation
        // this file needs to exist in img/
        'icon' => \OCP\Util::imagePath('gpstracks', 'app.svg'),

        // the title of your application. This will be used in the
        // navigation or on the settings page of your app
        'name' => \OC_L10N::get('gpstracks')->t('Gps Tracks')

]);

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

namespace OCA\GpsTracks\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OC\Files\Filesystem;

use OCA\GpsTracks\Lib\GpsXML;

class PageController extends Controller {

	private $userId;
	private $storage;

	public function __construct($AppName, $UserId, IRequest $request, Filesystem $Storage){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->storage = $Storage;
	}

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		$params = ['user' => $this->userId];
		return new TemplateResponse('gpstracks', 'main', $params);  // templates/main.php
	}
}

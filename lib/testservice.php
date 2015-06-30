<?php

namespace OCA\GpsTracks\Lib;

use OCP\IDb;
use OCP\IDbConnection;

class TestService {

	private $db;

	public function __construct(IDbConnection $db) {
		$this->db = $db;
	}

	public function test1() {
		$this->db->beginTransaction();
		$this->db->commit();
	}
}

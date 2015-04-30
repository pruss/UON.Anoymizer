<?php
/**
 * Mysql $COMMENT$
 *
 * @author      Peter Russ<peter.russ@uon.li>
 * @package     UON.DBAnonymizer
 * @date        20150325-1054
 * @link 		https://github.com/t.b.d
 * @copyright	Copyright 2015 Peter Russ
 * @license		http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace UON\DBAnonymizer\Database;


class Mysql {

	/**
	 * @var array
	 */
	protected $database = array();

	/**
	 * @param string $dbHost IP or URL to dataserver
	 * @param int $dbPort	Port to connect to database
	 * @param string $dbUser	User to access database
	 * @param string $dbPassword	Password to access database
	 * @param string $dbDatabase	database to be manipulated
	 */
	public function setDatabase($dbHost, $dbPort, $dbUser, $dbPassword, $dbDatabase){
		$this->database = array(
				'host' => $dbHost,
				'port' => (int)$dbPort,
				'user' => $dbUser,
				'password' => $dbPassword,
				'database' => $dbDatabase
		);
	}

	/**
	 * @return string
	 */
	protected function getDatabaseHost() {
		return $this->database['host'];
	}

	/**
	 * @return int
	 */
	protected function getDatabasePort() {
		return $this->database['port'];
	}

	/**
	 * @return string
	 */
	protected function getDatabaseUser() {
		return $this->database['user'];
	}

	/**
	 * @return string
	 */
	protected function getDatabasePassword() {
		return $this->database['password'];
	}

	/**
	 * @return mixed
	 */
	protected function getDatabase() {
		return $this->database['database'];
	}

	/**
	 * @return resource
	 */
	public function openDatabase() {
		$link = mysql_connect(
				$this->getDatabaseHost() . ':' . $this->getDatabasePort(),
				$this->getDatabaseUser(),
				$this->getDatabasePassword(),
				true
		);
		if (!$link) {
			die ('Problems connecting to database:' . mysql_error());
		}
		$select = mysql_select_db($this->getDatabase(), $link);
		if ($select === false) {
			mysql_close($link);
			die('Can not use database ' . mysql_error($link));
		}

		return $link;
	}

	/**
	 * @param null|resource $link
	 */
	public function closeDatabase($link = null) {
		mysql_close($link);
	}

} 
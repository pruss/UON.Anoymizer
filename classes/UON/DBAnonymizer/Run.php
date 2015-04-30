<?php
/**
 * Run
 *
 * @author      Peter Russ<peter.russ@uon.li>
 * @package     UON.DBAnonymizer
 * @date        20150324-1014
 * @link 		https://github.com/t.b.d.
 * @copyright	Copyright 2015 Peter Russ
 * @license		http://www.opensource.org/licenses/mit-license.php MIT License
 * 
 */

namespace UON\DBAnonymizer;

include 'Bootstrap.php';


class Run {

	/**
	 * @var string
	 */
	protected $configurationFile = '';

	/**
	 * @var array
	 */
	protected $configuration = null;

	/**
	 * @var string
	 */
	protected $tempSqlFile = '';

	/**
	 * @var bool
	 */
	protected $runScript = false;

	/**
	 * @param string $configFile path and filename with configuration
	 * @param string $run
	 */
	public function __construct($configFile, $run = '') {
		$this->init($configFile, $run);
	}

	/**
	 * @param string $configFile	path and filename with configuration
	 */
	public function init($configFile, $run = '') {
		$this->setConfigurationFile($configFile);
		$this->setRun($run);
	}

	/**
	 * @param string $configFile
	 */
	public function setConfigurationFile($configFile) {
		if (file_exists($configFile)) {
			$this->configurationFile = $configFile;
		} else {
			die('Can not access configuration file ' . $configFile);
		}
	}

	/**
	 * @param string $run
	 */
	public function setRun($run = '') {
		if ($run === 'run') {
			echo 'Option run not implemented yet' . PHP_EOL;
		}
	}

	public function dispatch() {
		$this->loadConfiguration();
		$this->prepareStatements();
		$this->run();
	}

	protected function loadConfiguration() {
		$this->configuration = \Spyc::YAMLLoad($this->configurationFile);
	}

	/**
	 * @param mixed $message
	 * @return string
	 */
	protected function getComment($message) {
		if (is_array($message)) {
			$message = join(PHP_EOL . '-- ', $message);
		}
		return PHP_EOL . '-- ' . PHP_EOL .'-- ' . $message . PHP_EOL . '-- ';
	}

	protected function prepareStatements() {
		if (isset($this->configuration['ACTIONS'])) {
			$this->setTempSqlFileName();

			$this->addLibraries();

			foreach($this->configuration['ACTIONS'] as $action => $configuration) {
				$this->writeTempSqlFile($this->getComment($action), '');
				switch($action) {
					case 'DROP':
						$this->drop($configuration);
						break;
					case 'TRUNCATE':
						$this->truncate($configuration);
						break;
					case 'DELETE':
						$this->delete($configuration);
						break;
					case 'INSERT':
						$this->insert($configuration);
						break;
					case 'UPDATE':
						$this->update($configuration);
						break;
				}
			}
			$this->finalizeStatements();
		}
	}

	protected function run() {
		if ($this->runScript === true) {
			echo 'Not implemented yet' . PHP_EOL;
		} else {
			readfile($this->tempSqlFile);
			unlink($this->tempSqlFile);
		}
	}

	/**
	 * @param array $configuration
	 */
	protected function drop(array &$configuration) {
		if (isset($configuration['TABLES'])) {
			foreach($configuration['TABLES'] as $table) {
				$this->writeTempSqlFile('DROP TABLE IF EXISTS ' . $table);
			}
		}
	}

	/**
	 * @param array $configuration
	 */
	protected function truncate(array &$configuration) {
		if (isset($configuration['TABLES'])) {
			foreach($configuration['TABLES'] as $table) {
				$this->writeTempSqlFile('TRUNCATE TABLE ' . $table);
			}
		}
	}

	/**
	 * @param array $configuration
	 */
	protected function delete(array &$configuration) {
		if (isset($configuration['QUERIES'])) {
			foreach($configuration['QUERIES'] as $queryItem) {
				if (isset($queryItem['TABLES'])) {
					if (isset($queryItem['WHERE'])) {
						$where = $queryItem['WHERE'];
						$item = isset($queryItem['ITEM'])? $queryItem['ITEM'] . ' ' : '';
						foreach($queryItem['TABLES'] as $table) {
							$statement = 'DELETE ' . $item . 'FROM ' . $table . ' ' . $where;
							$this->writeTempSqlFile($statement);
						}
					}
				}
			}
		}
	}

	/**
	 * @param array $configuration
	 */
	protected function insert(array &$configuration) {
		if (isset($configuration['TABLES'])) {
			foreach($configuration['TABLES'] as $table => $values) {
				if (isset($values['VALUES'])) {
					foreach($values['VALUES'] as $items) {
						$statement = 'INSERT INTO ' . $table .' (' . join(',', array_keys($items)) . ') VALUES (';
						$glue = '';
						foreach(array_values($items) as $value) {
							if (is_string($value)) {
								if (strpos($value, 'f:') ===  FALSE) {
									$value = '"' . addslashes($value) . '"';
								} else {
									$value = substr($value,2);
								}
							}
							$statement .= $glue . $value;
							$glue = ',';
						}
						$statement .= ')';
						$this->writeTempSqlFile($statement);
					}
				}
			}
		}
	}

	/**
	 * @param array $configuration
	 */
	protected function update(array &$configuration) {
		if (isset($configuration['SQL'])) {
			foreach($configuration['SQL'] as $table => $values) {
				if (isset($values['FIELDS'])) {
					$statement = 'UPDATE ' . $table . ' SET ';

					$where = (isset($values['WHERE']))? $values['WHERE'] : "";

					foreach($values['FIELDS'] as $field => $value) {
						if (is_string($value)) {
							if (strpos($value, 'f:') ===  FALSE) {
								$value = '"' . addslashes($value) . '"';
							} else {
								$value = substr($value,2);
							}
						}

						$statement .= $field . ' = ' . $value . ',' . PHP_EOL;
					}
					if (substr($statement, -2) === ',' . PHP_EOL) {
						$statement = substr($statement, 0, -2);
					}
					$statement = trim($statement . ' ' . $where);
					$this->writeTempSqlFile($statement);
				}
			}
		}
	}

	/**
	 * @param string $statement text to be writen to temporary file i.e. a sql query or command
	 */
	protected function writeTempSqlFile($statement, $closure = ';') {
		file_put_contents($this->tempSqlFile, $statement . $closure . PHP_EOL, FILE_APPEND);
	}

	protected function setTempSqlFileName () {
		$prefix = $this->configuration['FILENAME']? : 'uon-anon';
		$path = $this->configuration['DIRECTORY']? : '/tmp/';
		if (substr($path,-1,1) !== '/') {
			$path .= '/';
		}
		$this->tempSqlFile = tempnam($path, $prefix);
	}

	protected function addLibraries() {
		if (isset($this->configuration['OPENINGS'])) {
			foreach($this->configuration['OPENINGS'] as $library) {
				$this->addLibrary($library);
			}
		}
	}

	/**
	 * @param string $library relative or absolute path and file to the sql file to be included
	 */
	protected function addLibrary($library) {
		if ($this->tempSqlFile) {
			if ($library{0} !== '/') {
				$library = PATH_ROOT . $library;
			}
			if (file_exists($library)) {
				file_put_contents(
						$this->tempSqlFile,
						$this->getComment('included from ' . $library) . PHP_EOL.
						file_get_contents($library) . PHP_EOL,
						FILE_APPEND
				);
			}
		}
	}

	protected function finalizeStatements() {
		if (isset($this->configuration['CLOSURES'])) {
			foreach($this->configuration['CLOSURES'] as $library) {
				$this->addLibrary($library);
			}
		}
	}

	/**
	 * @return array
	 */
	protected function getConfiguration() {
		return $this->configuration;
	}



} 
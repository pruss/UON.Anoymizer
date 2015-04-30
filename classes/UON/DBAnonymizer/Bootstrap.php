<?php
/**
 * Bootstrap
 *
 * @author      Peter Russ<peter.russ@uon.li>
 * @package     UON.DBAnonymizer
 * @date        20150324-1133
 * @link 		https://github.com/t.b.d
 * @copyright	Copyright 2015 Peter Russ
 * @license		http://www.opensource.org/licenses/mit-license.php MIT License
 *
 */

if (defined('BOOT_STRAP_INCLUDED') === FALSE) {
	define ('PATH_ROOT', realpath(__DIR__ . '/../../../') . '/');

	require PATH_ROOT . '/contrib/spyc-master/Spyc.php';

	define('BOOT_STRAP_INCLUDED', TRUE);
}

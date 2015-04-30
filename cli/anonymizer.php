#!/usr/bin/env php
<?php
/**
 * ${NAME} $COMMENT$
 *
 * @author      Peter Russ<peter.russ@uon.li>
 * @package     $PACKAGE$
 * @date        20150324-1146
 * @subpackage  $SUB_PACKAGE$
 * 
 */

function usage() {
	$usage = array(
			'anonymizer <configFile> [<run>]'
	);

	echo join(PHP_EOL, $usage) . PHP_EOL;
}

if ($argc < 2) {
	usage();
} else {
	include __DIR__ . '/../classes/UON/DBAnonymizer/Run.php';
	$configFile = $argv[1];
	$run = ($argc === 3) ? ($argv[2] === 'run' ? 'run' : '') : '';
	$run = new UON\DBAnonymizer\Run($configFile, $run);
	$run->dispatch();
}


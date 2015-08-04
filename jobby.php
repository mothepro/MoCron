<?php
/**
 * @todo Jobby cache object
 */
require 'vendor/autoload.php';
set_time_limit(0);
error_reporting(-1);

/*
 * The MIT License
 *
 * Copyright 2015 Maurice Prosper <maurice.prosper@ttu.edu>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


/**
 * Giv'em some help
 */
$showHelp = function() {
	echo <<<HELP
* * * * * php vendor/bin/jobby.php -s /config/cron.neon -d /path/to/project 1> /dev/null 2>&1

1> /dev/null	This redirects stdout to blackhole
2>&1			This redirects stderr to stdout

Options
	-h	--help	Print this help message.
	-s			Cron Configuration file to use
	-d			Working Directory
HELP;
};

$neon = null;
$opt = getopt('s:d:h', ['help']);
	
// not missing neon or config
if(isset($opt['s'])) {

	$data = file_get_contents($opt['s']);
	$neon = \Nette\Neon\Neon::decode($data);
	
	if(isset($neon['cron']))
		$neon = $neon['cron'];

	//  check errors
	$error = empty($neon);
}

// change working dir
if(isset($opt['d']))
	chdir (realpath($opt['d']));
elseif(isset($neon['d']))
	chdir (realpath ($neon['d']));

unset($neon['d']);

// error OR just needs a little help
if($error || isset($opt['h']) || isset($opt['help'])) {
	if($error)
		echo 'Error, missing required option.', PHP_EOL, PHP_EOL;
	$showHelp();
	exit($error);
}

/////////////
// LETS GO //
/////////////

$jobby = new \Jobby\Jobby;

foreach($neon as $name => $cfg)
	$jobby->add ($name, $cfg);

$jobby->run();
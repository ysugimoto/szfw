<?php

/**
 * ====================================================================
 * 
 * Seezoo-Framework bootstrap file
 * 
 * Define Path constants and load Core files.
 * 
 * @author Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * 
 * ====================================================================
 */

// System always handles the UTF-8 encoding.
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');

// Framework version
define('SZFW_VERSION', '0.6');

// System path constants difinition
define('SZ_EXEC',    TRUE);
define('DISPATCHER', basename($_SERVER['SCRIPT_FILENAME']));
define('ROOTPATH',   realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '/');
define('SZPATH',     dirname(__FILE__) . '/');

// Autoloader register
require_once(SZPATH . 'core/system/Autoloader.php');
Autoloader::init();

// System startup!
Seezoo::startup();

// Did you request from CLI?
if ( PHP_SAPI === 'cli' )
{
	// Command line tools ignittion
	if ( defined('SZ_COMMANDLINE_WORKER') )
	{
		$dog = Seezoo::$Importer->classes('Dog');
		$dog->executeCommandLine();
	}
	// PHPUnit test mode ignittion
	else if ( strpos($_SERVER['argv'][0], 'phpunit') !== FALSE )
	{
		define('SZ_COMMANDLINE_WORKER', 1);
		spl_autoload_register(array('Autoloader', 'loadTestModule'));
	}
	// Else, single CLI request
	else
	{
		define('SZ_COMMANDLINE_WORKER', 1);
		chdir(SZPATH);
		Seezoo::init(SZ_MODE_CLI, ( isset($_SERVER['argv'][1]) ) ? $_SERVER['argv'][1] : '');
	}
}
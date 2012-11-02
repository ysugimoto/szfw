<?php if ( ! defined('SZ_EXEC') OR  PHP_SAPI !== 'cli' ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Command line action dispatcher class
 * 
 * @package  Seezoo-Framework
 * @category Classes
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */
 
class SZ_Dog extends SZ_Driver
{
	/**
	 * console arguments
	 * @var array
	 */
	protected $_argv;
	
	
	public function __construct()
	{
		$this->_argv  = $_SERVER['argv'];
		$this->driver = $this->_loadDriver('command', 'Console_command', TRUE);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Execute command line action
	 * 
	 * @access public
	 */
	public function executeCommandLine()
	{
		if ( ! isset($this->_argv[1]) )
		{
			$this->_showUsage();
			exit;
		}
		
		$exec = ltrim($this->_argv[1], '-');
		if ( $exec === 'bite' )
		{
			echo $this->driver->easterEgg();
		}
		else if ( method_exists($this->driver, 'command' . strtolower($exec)) )
		{
			$args = array_slice($this->_argv, 2);
			$this->driver->{'command' . strtolower($exec)}($args);
		}
		else
		{
			$this->_showUsage();
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Show tool's usage
	 * 
	 * @access protected
	 */
	protected function _showUsage()
	{
		echo '============================================' . PHP_EOL;
		echo '  SZFW Commnad Line Tool ver ' . SZFW_VERSION . PHP_EOL;
		echo '============================================' . PHP_EOL;
		echo 'usage: ' . PHP_EOL;
		echo '  Testing Model  : ./dog modeltest [model-name]' . PHP_EOL;
		echo '  Class generate : ./dog generate' . PHP_EOL . PHP_EOL;
		exit;
	}
}

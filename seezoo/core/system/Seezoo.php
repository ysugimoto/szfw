<?php if ( ! defined('SZ_EXEC') ) exit('access denied.');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * ------------------------------------------------------------------
 * 
 * Main class file
 * 
 * @package  Seezoo-Framework
 * @category System
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class Seezoo
{
	/**
	 * system configuration 
	 * @var array
	 */
	public static $config;
	
	
	/**
	 * Public class importer
	 * @var Importer class instance
	 */
	public static $Importer;
	
	
	/**
	 * Public class response
	 * @var Response class instance
	 */
	public static $Response;
	
	
	/**
	 * Public class Cache
	 * @var Cache class imstance
	 */
	public static $Cache;
	
	
	/**
	 * loaded Classes strings (extension included)
	 * @var array
	 */
	public static $Classes = array();
	
	
	/**
	 * Stack of system environments
	 * @var Environment class instance
	 */
	protected static $_stackENV;
	
	
	/**
	 * Stack of Request instance
	 * @var Request class instance
	 */
	protected static $_stackRequest;
	
	
	/**
	 * System statup flagment
	 * @var bool
	 */
	private static $startUpExecuted;
	
	
	/**
	 * Propery alias stacks
	 * @var array
	 */
	private static $_propertyAliases = array();
	
	
	/**
	 * Output buffer mode flag
	 * @var bool
	 */
	public static $outpuBufferMode = TRUE;
	
	
	/**
	 * Active packages list
	 * @var array
	 */
	private static $packages = array();
	
	
	/**
	 * Prefix list
	 * @var array
	 */
	private static $prefixes = array(
	                             SZ_PREFIX_PKG,
	                             SZ_PREFIX_EXT,
	                             SZ_PREFIX_APP,
	                             SZ_PREFIX_CORE
	                           );
	
	/**
	 * Default suffix list
	 * @var array
	 */
	public static $suffixes = array(
	                           'classes'      => array(''),
	                           'helper'       => array('Helper'),
	                           'library'      => array(''),
	                           'model'        => array(''),
	                           'activerecord' => array('Activerecord')
	                         );
	
	// ---------------------------------------------------------------
	
	
	/**
	 * constructor
	 * @access private
	 * @param  string $mode
	 * @param  string $pathinfo
	 */
	private function __construct($mode, $pathinfo)
	{
		$this->mode     = $mode;
		$this->level    = SeezooFactory::addProcess($this);
		$this->pathinfo = self::$_stackRequest->setRequest($pathinfo, $mode, $this->level);
		$this->router   = new self::$Classes['Router']();
		
		$this->router->setPathInfo($this->pathinfo);
		$this->router->setMode($mode);
		$this->router->setLevel($this->level);
	}


	// ---------------------------------------------------------------


	/**
	 *
	 * Register regex GET request
	 * @access public static
	 * @param  string $pathRegex
	 * @param  callable $callback
	 * @param  bool $forceQuit
	*/
	public static function get($pathRegex, $callback, $forceQuit = FALSE)
	{
		self::_handleRegexRequest('GET', $pathRegex, $callback, $forceQuit);
	}


	// ---------------------------------------------------------------


	/**
	 *
	 * Register regex POST request
	 * @access public static
	 * @param  string $pathRegex
	 * @param  callable $callback
	 * @param  bool $forceQuit
	*/
	public static function post($pathRegex, $callback, $forceQuit = FALSE)
	{
		self::_handleRegexRequest('POST', $pathRegex, $callback, $forceQuit);
	}


	// ---------------------------------------------------------------


	/**
	 *
	 * Fire the regex request if path matched
	 * @access private static
	 * @param  string $method
	 * @param  string $pathRegex
	 * @param  callable $callback
	 * @param  bool $forceQuit
	*/
	private static function _handleRegexRequest($method, $pathRegex, $callback, $forceQuit)
	{
		if ( ! is_callable($callback) )
		{
			throw new InvalidArgumentException('Second argument must be callable.');
		}
		$request  = self::$_stackRequest;
		$path     = ltrim($request->getAccessPathInfo(), '/');
		$regex    = '^' . trim(str_replace('|', '\|', $pathRegex), '^$') . '$';
		if ( $request->server('query_string') )
		{
			$path .= '?' . $request->server('query_string');
		}

		if ( $request->requestMethod === $method && preg_match('|' . $regex . '|', $path, $match) )
		{
			array_shift($match);
			call_user_func_array($callback, $match);
			if ( $forceQuit )
			{
				exit;
			}
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Set property alias
	 * @access public static
	 * @param  mixed  $prop
	 * @param  string $aliasName
	 */
	public static function setAlias($prop, $aliasName = '')
	{
		if ( is_array($prop) )
		{
			foreach ( $prop as $p => $name )
			{
				self::$_propertyAliases[$p] = $name;
			}
		}
		else
		{
			self::$_propertyAliases[$prop] = $aliasName;
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get property alias
	 * @access public static
	 * @return array
	 */
	public static function getAliases()
	{
		return self::$_propertyAliases;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Check class has prefix
	 * 
	 * @access public static
	 * @param  string $className
	 * @return bool
	 */
	public static function hasPrefix($className)
	{
		$regex = '/^' . implode('|', self::$prefixes) . '/';
		return ( preg_match($regex, $className) ) ? TRUE : FALSE;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Remove or split prefix
	 * 
	 * @access public static
	 * @param  string $className
	 * @param  bool $returnPrefix
	 */
	public static function removePrefix($className, $returnPrefix = FALSE)
	{
		$regex = '/^(' . implode('|', self::$prefixes) . ')(.+)$/';
		if ( preg_match($regex, $className, $matches) )
		{
			return ( $returnPrefix )
			         ? array($matches[1], $matches[2])
			         : $matches[1];
		}
		return ( $returnPrefix ) ? array($className, '') : $className;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Add suffix format
	 * 
	 * @access public static
	 * @param  string $type
	 * @param  string $suffix
	 */
	public static function addSuffix($type, $suffix)
	{
		if ( ! isset(self::$suffixes[$type]) )
		{
			throw new LogicException($type . ' suffix is not defined.');
		}
		array_unshift(self::$suffixes[$type], $suffix);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get suffix
	 * 
	 * @access public static
	 * @param  string $type
	 * @return array
	 */
	public static function getSuffix($type)
	{
		return ( isset(self::$suffixes[$type]) )
		         ? self::$suffixes[$type]
		         : array('');
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Write log file
	 * 
	 * @access public static
	 * @param string $msg
	 * @param int $level
	 */
	public static function log($msg, $level = FALSE)
	{
		$LOG = self::$Importer->classes('Logger');
		$LOG->write($msg, $level);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * getInstance
	 * 
	 * get a "leveled" process instance
	 * @access public static
	 * @return object
	 */
	public static function getInstance()
	{
		return SeezooFactory::getInstance();
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Add active package handle on this process
	 * 
	 * @access public static
	 * @param  string $package
	 */
	public static function addPackage($package)
	{
		if ( ! in_array($package, self::$packages) )
		{
			self::$packages[] = $package;
			foreach ( Autoloader::$loadTargets as $path => $suffix )
			{
				Autoloader::register(PKGPATH . $package . '/' . $path, $suffix, SZ_PREFIX_PKG);
			}
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Remove active package
	 * 
	 * @access public static
	 * @param  string $package
	 */
	public static function removePackage($package)
	{
		if ( FALSE !== ($key = array_search($package, self::$packages)) )
		{
			array_splice(self::$packages, $key, 1);
			foreach ( Autoloader::$loadTargets as $path => $suffix )
			{
				Autoloader::unregister(PKGPATH . $package . '/' . $path);
			}
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get boot package or active packages
	 * 
	 * @access public static
	 */
	public static function getPackage()
	{
		return self::$packages;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get stacked Environment instance
	 * 
	 * @access public static
	 * @return object
	 */
	public static function getENV()
	{
		return self::$_stackENV;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get stacked Request instance
	 * 
	 * @access public static
	 * @return object
	 */
	public static function getRequest()
	{
		return self::$_stackRequest;
	}
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Main method
	 * 
	 * @access public static
	 * @param string $mode
	 * @param string $overridePathInfo
	 */
	public static function init($mode = FALSE, $overridePathInfo = '', $extraArgs = FALSE)
	{
		// Benchmark start
		$Mark = self::$Importer->classes('Benchmark');
		
		if ( $mode === FALSE )
		{
			$mode = self::$config['default_process'];
		}
		
		// create process instance
		$process = new self($mode, $overridePathInfo);
		$level   = $process->level;
		$Mark->start('baseProcess:'. $process->level);
		
		Event::fire('process_start', $process);
		
		// Priority process MVC/CLI.
		if ( $mode === SZ_MODE_MVC || $mode === SZ_MODE_CLI )
		{
			// Is really CLI access?
			if ( $mode === SZ_MODE_CLI && PHP_SAPI !== 'cli' )
			{
				show_404();
			}
			
			if ( $process->router->routing($level) === FALSE )
			{
				show_404();
			}
			$Mark->end('process:' . $process->level . ':MVC:Routed', 'baseProcess:'. $process->level);
			// Load Controller and execute method
			$exec = $process->router->bootController($extraArgs);
			if ( ! is_array($exec) )
			{
				show_404();
			}
			
			// extract instance/returnvalue
			list($SZ, $rv) = $exec;
			
			$Mark->end('process:' . $process->level . ':MVC:ControllerExecuted', 'baseProcess:'. $process->level);
			Event::fire('controller_execute');
			
			// Does output hook method exists?
			if ( method_exists($SZ, '_output') )
			{
				$output = $SZ->_output($SZ->view->getDisplayBuffer());
				$SZ->view->replaceBuffer($output);
			}
			$Mark->end('process:' . $process->level . ':MVC:MethodExecuted', 'baseProcess:'. $process->level);
		}
		else
		{
			$SZ = new Process();
			
			switch ( $mode )
			{
				// Case : default mode execute
				// simple returns process instance.
				case SZ_MODE_DEFAULT:
					
					return $SZ;
					
				// Case : action mode execute
				// process execute from simple file, and get a output buffer
				case SZ_MODE_ACTION:
					
					$SZ->view->bufferStart();
					$SZ->router->bootAction();
					$SZ->view->getBufferEnd(TRUE);
					$Mark->end('process:' . $process->level . ':API:executed', 'baseProcess:'. $process->level);
					
				break;
				
				// Case : process mode execute
				// process execute from simple file, and get a return-value
				case SZ_MODE_PROC:
					
					$result = $SZ->router->bootProcess();
					self::releaseInstance($SZ);
					$Mark->end('process:' . $process->level . ':end', 'baseProcess:'. $process->level);
					return $result;
					
				break;

				// Case : not found...
				default:
					
					show_404();
					
				break;
			}
		}
		
		// process executed. release process instance.
		$Mark->end('process:' . $process->level . ':end', 'baseProcess:'. $process->level);
		Event::fire('process_end');
		self::releaseInstance($SZ);
		
		// Is this process in a sub process?
		if ( $level > 1 )
		{
			// returns process result if buffermode is FALSE
			if ( self::$outpuBufferMode === FALSE )
			{
				$returnValue = ( isset($rv) ) ? $rv : $SZ->view->getDisplayBuffer();
			}
			else
			{
				// returns output buffer
				$returnValue = $SZ->view->getDisplayBuffer();
			}
			self::$outpuBufferMode = TRUE;
			return $returnValue;
		}
		else
		{
			$Mark->end('final', 'baseProcess:'. $process->level);
			Event::fire('session_update');
			
			// returns process result if buffermode is FALSE
			if ( self::$outpuBufferMode === FALSE )
			{
				$output = ( isset($rv) ) ? $rv : $SZ->view->getDisplayBuffer();
				return $output;
			}
			else
			{
				// final output!
				$output = $SZ->view->getDisplayBuffer();
				self::$Response->display($output);
			}
			self::$outpuBufferMode = TRUE;
			
			//SeezooFactory::killAll();
		}
		
		// -- complete!
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Release a process instance
	 * 
	 * @access private static
	 * @param  Seezoo $SZ
	 */
	private static function releaseInstance($SZ)
	{
		SeezooFactory::endSub($SZ->level);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * System startup and load Core classes
	 * 
	 * @access private static
	 * @throws Exception
	 */
	public static function startup()
	{
		// Guard multiple execute
		if ( self::$startUpExecuted )
		{
			return;
		}
		
		// Cofguration ----------------------------------------------
		
		if ( ! file_exists(APPPATH . 'config/config.php') )
		{
			throw new RuntimeException('Configuration file is not exists!');
			exit;
		}
		include(APPPATH . 'config/config.php');
		self::$config = $config;
		
		
		// Init packages ----------------------------------------------
		
		if ( file_exists(APPPATH . 'config/package.php') )
		{
			include(APPPATH . 'config/package.php');
			if ( isset($pakcage) )
			{
				foreach ( (array)$package as $pkg )
				{
					self::addPackage($pkg);
				}
			}
		}
		
		
		// Application settings --------------------------------------
		
		date_default_timezone_set(self::$config['date_timezone']);
		error_reporting(self::$config['error_reporting']);
		
		
		// Event startup ---------------------------------------------
		
		Event::addListenerFromFile(APPPATH . 'config/event.php');
		Event::addListenerFromFile(EXTPATH . 'config/event.php');
		
		
		// Depend system modules include -----------------------------
		
		$importer = new SZ_Importer(array('attachMode' => FALSE));
		self::$Classes['Importer'] = $importer->classes('Importer', FALSE);
		self::$Importer = new self::$Classes['Importer'](array('attachMode' => FALSE));
		
		// Exception setting -----------------------------------------
		
		self::$Classes['Exception'] = self::$Importer->classes('Exception', FALSE);
		$Exception = new self::$Classes['Exception']();
		set_exception_handler(array($Exception, 'handleException'));
		set_error_handler(array($Exception, 'handleError'));
		register_shutdown_function(array($Exception, 'handleShutdown'));
		
		// Preprocess event fire
		Event::fire('preprocess');
		
		// Load core classes to stack property -----------------------
		
		self::$_stackENV         = self::$Importer->classes('Environment');
		self::$_stackRequest     = self::$Importer->classes('Request');
		self::$Response          = self::$Importer->classes('Response');
		self::$Cache             = self::$Importer->classes('Cache');
		self::$Classes['View']   = self::$Importer->classes('View',   FALSE);
		self::$Classes['Router'] = self::$Importer->classes('Router', FALSE);
		
		// Only once!
		self::$startUpExecuted = TRUE;
		
		foreach ( self::$packages as $pkg )
		{
			self::initPackage($pkg);
		}
		
		Event::fire('startup');
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Registers packages init
	 * 
	 * @access public
	 * @param  string $pkg
	 */
	public static function initPackage($pkg)
	{
		if ( file_exists(PKGPATH . $pkg . '/init.php') )
		{
			require(PKGPATH . $pkg . '/init.php');
		}
	}
}


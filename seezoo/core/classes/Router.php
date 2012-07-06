<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Access Router
 * 
 * @package  Seezoo-Framework
 * @category classes
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Router
{
	protected $_level;
	
	protected $_mode;
	
	/**
	 * Environment
	 * @var Environment
	 */
	protected $env;
	
	
	/**
	 * Controller suffix
	 * @var string
	 */
	protected $controllerSuffix;
	
	
	/**
	 * Method prefix
	 * @var string
	 */
	protected $methodPrefix;
	
	
	/**
	 * Request method
	 * @var string
	 */
	protected $requestMethod;
	
	
	/**
	 * Requested pathinfo
	 * @var string
	 */
	protected $_pathinfo = '';
	
	
	/**
	 * Default controller
	 * @var string
	 */
	protected $defaultController;
	
	
	/**
	 * Detection directory
	 * @var string
	 */
	protected $detectDir;
	
	
	/**
	 * Routed informations
	 * @var string /array
	 */
	protected $_package   = '';
	protected $_directory = '';
	protected $_class     = '';
	protected $_method    = '';
	protected $_arguments = array();
	
	
	public function __construct()
	{
		$this->env = Seezoo::getENV();
		
		$this->controllerSuffix  = $this->env->getConfig('controller_suffix');
		$this->methodPrefix      = $this->env->getConfig('method_prefix');
		$this->defaultController = $this->env->getConfig('default_controller');
		
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Set boot pathinfo
	 * 
	 * @access public
	 * @param  string $pathinfo
	 */
	public function setPathInfo($pathinfo)
	{
		$this->_pathinfo = $pathinfo;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Set current process mode
	 * 
	 * @access public
	 * @param  string $mode
	 */
	public function setMode($mode)
	{
		$this->_mode = $mode;
		switch ( $mode )
		{
			case SZ_MODE_CLI:
				$this->detectDir = 'classes/cli/';
				break;
			case SZ_MODE_ACTION:
				$this->detectDir = 'scripts/actions/';
				break;
			case SZ_MODE_PROC:
				$this->detectDir = 'scripts/processes/';
				break;
			default:
				$this->detectDir = ( is_ajax_request() )
				                    ? 'classes/ajax/'
				                    : 'classes/controllers/';
				break;
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Set process level
	 * 
	 * @access public
	 * @param  int $level
	 */
	public function setLevel($level)
	{
		$this->_level = $level;
	}
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Boot action process
	 * 
	 * @access public
	 */
	public function bootAction()
	{
		$path     = str_replace(array('.', '/'), '', $this->_pathinfo);
		$dir      = $this->detectDir;
		$pacakges = Seezoo::getPackage();
		
		foreach ( $pacakges as $pkg )
		{
			if ( file_exists(PKGPATH . $pkg . '/' . $dir . $path . '.php') )
			{
				$this->bootPackage(PKGPATH . $pkg . '/');
				require(PKGPATH . $pkg . '/' . $dir . $path . '.php');
				return;
			}
		}
		
		if ( file_exists(EXTPATH . $dir . $path . '.php') )
		{
			require(EXTPATH . $dir . $path . '.php');
		}
		else if ( file_exists(APPPATH . $dir . $path . '.php') )
		{
			require(APPPATH . $dir . $path . '.php');
		}
		else
		{
			show_404();
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Boot process process
	 * 
	 * @access public
	 */
	public function bootProcess()
	{
		$path     = str_replace(array('.', '/'), '', $this->_pathinfo);
		$packages = Seezoo::getPackage();
		$proc     = FALSE;
		$dir      = $this->detectDir;
		
		// package detection
		foreach ( $packages as $pkg )
		{
			if ( file_exists(PKGPATH . $pkg . '/' . $dir . $path . '.php') )
			{
				$this->bootPackage(PKGPATH . $pkg . '/');
				require(PKGPATH . $pkg . '/' . $dir . $path . '.php');
				$proc = TRUE;
			}
		}
		
		// Base application detection if package not exists
		if ( $proc === FALSE )
		{
			if ( file_exists(EXTPATH . $dir . $path . '.php') )
			{
				require(EXTPATH . $dir . $path . '.php');
			}
			else if ( file_exists(APPPATH . $dir . $path . '.php') )
			{
				require(APPPATH . $dir . $path . '.php');
			}
			else
			{
				show_404();
			}
		}
		
		// has a returnValue?
		return ( isset($returnValue) ) ? $returnValue : null;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Boot MVC/CLI process
	 * 
	 * @access public
	 */
	public function bootController()
	{
		Seezoo::$Importer->classes('Breeder', FALSE);
		$dir    = $this->detectDir;
		$path   = $this->_package . $dir . $this->_directory . $this->_class . '.php';
		
		if ( ! file_exists($path) )
		{
			return FALSE;
		}
		
		require_once($path);
		$class = ucfirst($this->_class);
		
		if ( ! class_exists($class) )
		{
			$class .= $this->controllerSuffix;
		}
		if ( ! class_exists($class) )
		{
			return FALSE;
		}
		
		$Controller = new $class();
		$Controller->lead->prepare();
		
		// under-score prefixed method cannot execute.
		if ( preg_match('/^_.+$/u', $this->_method) )
		{
			throw new Exception('Cannot call private method!');
			return;
		}
		else
		{
			Event::fire('controller_load');
			
			// Does mapping method exists?
			if ( method_exists($Controller, '_mapping') )
			{
				// execute mapping method
				$Controller->_mapping($this->_method);
			}
			else
			{
				// request method suffix
				$methodSuffix = ( $this->requestMethod === 'POST' ) ? '_post' : '';
				
				$callMethod = '';
				// First, call method-suffix ( *_post method ) if exists
				if ( method_exists($Controller, $this->_method . $methodSuffix) )
				{
					$callMethod = $this->_method . $methodSuffix;
				}
				// Second, call prefix-method-suffix ( ex.action_index_post ) if exists
				else if ( ! empty($this->methodPrefix)
				           && method_exists($Controller, $this->methodPrefix . $this->_method . $methodSuffix) )
				{
					$callMethod = $this->methodPrefix . $this->_method . $methodSuffix;
				}
				// Third, call method simply if exists
				else if ( method_exists($Controller, $this->_method) )
				{
					$callMethod = $this->_method;
				}
				// Fourth, call prefix-method if exists
				else if ( ! empty($this->methodPrefix)
				           && method_exists($Controller, $this->methodPrefix . $this->_method) )
				{
					$callMethod = $this->methodPrefix . $this->_method;
				}
				// Method doesn't exists...
				else
				{
					return FALSE;
				}
				$Controller->lead->setExecuteMethod($callMethod);
				call_user_func_array(array($Controller, $callMethod), $this->_arguments);
			}
		}
		$Controller->lead->teardown();
		
		return $Controller;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Package boot
	 * 
	 * @param string $package
	 */
	public function bootPackage($package)
	{
		$packageName = basename($package);
		$action      = $packageName . '_bootstrap';
		
		if ( ! SeezooFactory::isBootedPackage($packageName) )
		{
			// load the bootstrap file if exists
			if ( file_exists($package . 'bootstrap.php') )
			{
				include_once($package . 'bootstrap.php');
				if ( class_exists(ucfirst($action)) )
				{
					new $action();
				}
				else if ( function_exists($action) )
				{
					$action();
				}
			}
			
			// package_boot event fire
			Event::fire('package_boot', $packageName);
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Routing execute
	 * 
	 * @access public
	 * @return bool
	 */
	public function routing()
	{
		$REQ      = Seezoo::getRequest();
		$segments = $REQ->uriSegments($this->_level);
		$packages = Seezoo::getPackage();//$this->env->getConfig('package');
		$isRouted = FALSE;
		
		$this->requestMethod = $REQ->requestMethod;
		$this->directory     = '';
		
		// If URI segments is empty array ( default page ),
		// set default-controller and default method array.
		if ( count($segments) === 0 )
		{
			$segments = array($this->defaultController, 'index');
		}
		
		// loop the package routing
		foreach ( $packages as $pkg )
		{
			$pkg      = PKGPATH . rtrim($pkg, '/') . '/';
			$base     = $pkg . $this->detectDir;
			$detected = $this->_detectController($segments, $base);
			
			if ( is_array($detected) )
			{
				$this->_package   = $pkg;
				$this->_class     = $detected[0];
				$this->_method    = $detected[1];
				$this->_arguments = array_slice($detected, 2);
				$isRouted         = TRUE;
				$this->bootPackage($pkg);
				break;
			}
			$this->directory = '';
		}
		
		// If package controller doesn't exists,
		// routing from extensioned or default application path.
		if ( $isRouted === FALSE )
		{
			$this->directory = '';
			$dir      = $this->detectDir;
			$detected = $this->_detectController($segments, EXTPATH . $dir);
			
			if ( is_array($detected) )
			{
				$this->_package   = EXTPATH;
				$this->_class     = $detected[0];
				$this->_method    = $detected[1];
				$this->_arguments = array_slice($detected, 2);
				$isRouted = TRUE;
			}
			else
			{
				$apppath = APPPATH;
				$detected = $this->_detectController($segments, $apppath . $dir);
				
				if ( ! is_array($detected) )
				{
					if ( count($segments) === 0 )
					{
						$this->_package   = $apppath;
						$this->_direcotry = '';
						$this->_class     = $this->defaultController;
						$this->_method    = 'index';
						$this->_arguments = array();
						$isRouted = TRUE;
					}
				}
				else
				{
					$this->_package   = $apppath;
					$this->_class     = $detected[0];
					$this->_method    = $detected[1];
					$this->_arguments = array_slice($detected, 2);
					$isRouted = TRUE;
				}
			}
		}
		
		if ( $isRouted === TRUE )
		{
			// Routing succeed!
			Event::fire('routed', $this);
		}
		return $isRouted;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get routing info
	 * 
	 * @access public
	 * @param string $prop
	 * @return string
	 */
	public function getInfo($prop)
	{
		return ( isset($this->{'_' . $prop}) ) ? $this->{'_' . $prop} : '';
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Controller detection
	 * 
	 * @access protected
	 * @param  array $segments
	 * @param  string $baseDir
	 * @param  stdClass $routes ( reference )
	 * @return mixed
	 */
	protected function _detectController($segments, $baseDir)
	{
		if ( file_exists($baseDir . $segments[0] . '.php') )
		{
			if ( (! isset($segments[1])) || ( empty ($segments[1] )) )
			{
				$segments[1] = 'index';
			}
			return $segments;
		}
		
		if ( is_dir($baseDir . $segments[0]) )
		{
			$dir      = array_shift($segments);
			$baseDir .= $dir . '/';
			$this->_directory .= $dir . '/';
			
			if ( count($segments) === 0 )
			{
				if ( file_exists($baseDir . $this->defaultController . '.php') )
				{
					return array($this->defaultController, 'index');
				}
				else
				{
					return FALSE;
				}
			}
			return $this->_detectController($segments, $baseDir);
		}
		else
		{
			return FALSE;
		}
	}
}
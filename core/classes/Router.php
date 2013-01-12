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

class SZ_Router implements Growable
{
	protected $_level;
	
	protected $_mode;
	
	/**
	 * Environment
	 * @var Environment
	 */
	protected $env;
	
	
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
	 * Really executed method
	 * @var string
	 */
	protected $_execMethod = '';
	
	
	/**
	 * Routed informations
	 * @var string /array
	 */
	protected $_package    = '';
	protected $_directory  = '';
	protected $_class      = '';
	protected $_method     = '';
	protected $_arguments  = array();
	protected $_loadedFile = '';
	
	
	public function __construct()
	{
		$this->env = Seezoo::getENV();
	}
	
	
	/**
	 * Growable interface implementation
	 * 
	 * @access public static
	 * @return SZ_Router ( extended )
	 */
	public static function grow()
	{
		return Seezoo::$Importer->classes('Router');
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
		if ( $this->routing() === FALSE )
		{
			return FALSE;
		}
		
		$this->_loadedFile = $this->_package
		                     . $this->detectDir
		                     . $this->_directory
		                     . $this->_class
		                     . '.php';
		
		require($this->_loadedFile);
		return TRUE;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Boot process process
	 * 
	 * @access public
	 */
	public function bootProcess()
	{
		if ( $this->routing() === FALSE )
		{
			return FALSE;
		}
		
		$this->_loadedFile = $this->_package
		                     . $this->detectDir
		                     . $this->_directory
		                     . $this->_class
		                     . '.php';
		
		require($this->_loadedFile);
		
		// has a returnValue?
		return ( isset($returnValue) ) ? $returnValue : null;
	}
	
	
	// ---------------------------------------------------------------o


	/**
	 * Boot Lead layer class
	 *
	 * @access public
	 * @return object SZ_Lead
	 */
	public function bootLead()
	{
		$lead = Seezoo::$Importer->lead($this->_directory . $this->_class);
		return Injector::inject($lead);
	}


	// ---------------------------------------------------------------
	
	
	/**
	 * Boot MVC/CLI controller
	 * 
	 * @access public
	 */
	public function bootController($extraArgs = FALSE)
	{
		if ( $this->routing() === FALSE )
		{
			return FALSE;
		}
		
		$this->_loadedFile = $this->_package
		                     . $this->detectDir
		                     . $this->_directory
		                     . $this->_class . '.php';
		
		if ( ! file_exists($this->_loadedFile) )
		{
			return FALSE;
		}
		
		require_once($this->_loadedFile);
		$class = ucfirst($this->_class);
		
		if ( ! class_exists($class, FALSE) )
		{
			$class .= SZ_CONTROLLER_SUFFIX;
		}
		if ( ! class_exists($class, FALSE) )
		{
			return FALSE;
		}
		
		$Controller = new $class();
		$Controller->lead->prepare();
		
		$Controller->view->set(strtolower($this->_class . '/' . $this->_method));

		Event::fire('controller_load');
		
		// Does mapping method exists?
		if ( method_exists($Controller, '_mapping') )
		{
			// execute mapping method
			$rv = $Controller->_mapping($this->_method);
		}
		else
		{
			// request method suffix
			$methodSuffix = ( $this->requestMethod === 'POST' ) ? '_post' : '';
			
			// First, call prefix-method-suffix ( ex.act_index_post ) if exists
			if ( method_exists($Controller, SZ_EXEC_METHOD_PREFIX . $this->_method . $methodSuffix) )
			{
				$this->_execMethod = SZ_EXEC_METHOD_PREFIX . $this->_method . $methodSuffix;
			}
			// Second, call method-suffix ( *_post method ) if exists
			else if ( method_exists($Controller, $this->_method . $methodSuffix) )
			{
				$this->_execMethod = $this->_method . $methodSuffix;
			}
			// Third, call prefix-method if exists
			else if ( method_exists($Controller, SZ_EXEC_METHOD_PREFIX . $this->_method) )
			{
				$this->_execMethod = $this->methodPrefix . $this->_method;
			}
			// Fourth, call method simply if exists
			else if ( method_exists($Controller, $this->_method) )
			{
				$this->_execMethod = $this->_method;
			}
			// Method doesn't exists...
			else
			{
				return FALSE;
			}
			$Controller->lead->setExecuteMethod($this->_execMethod);
			if ( $extraArgs !== FALSE )
			{
				array_push($this->_arguments, $extraArgs);
			}
			Injector::injectByAnnotation($Controller, $this->_execMethod);
			$rv = call_user_func_array(array($Controller, $this->_execMethod), $this->_arguments);
		}
		$Controller->lead->teardown();
		
		return array($Controller, $rv);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Routing execute
	 * 
	 * @access protected
	 * @return bool
	 */
	protected function routing()
	{
		$this->requestMethod     = Seezoo::getRequest()->getRequestMethod();
		$this->defaultController = get_config('default_controller');
		$this->directory         = '';
		
		// If URI segments is empty array ( default page ),
		// set default-controller and default method array.
		$segments = ( empty($this->_pathinfo) )
		              ? array($this->defaultController, 'index')
		              : explode('/', $this->_pathinfo);
		// Mark routing succeed
		$isRouted = FALSE;
		
		foreach ( Seezoo::getApplication() as $app )
		{
			$this->directory = '';
			$dir      = $this->detectDir;
			$detected = $this->_detectController($segments, $app->path . $dir);
			
			if ( is_array($detected) )
			{
				$this->_package   = $app->path;
				$this->_class     = $detected[0];
				$this->_method    = $detected[1];
				$this->_arguments = array_slice($detected, 2);
				$isRouted = TRUE;
				break;
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
			if ( ! isset($segments[1]) || empty($segments[1]) )
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

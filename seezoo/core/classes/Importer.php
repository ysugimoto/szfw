<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Core/Library/Model/Helper/Tools multi importer class
 * 
 * @package  Seezoo-Framework
 * @category Classes
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Importer implements Growable
{	
	/**
	 * database class name
	 * @var string
	 */
	protected $_databaseClass;
	
	protected $_superHelper;
	protected $_helperCount = 0;
	
	
	/**
	 * attach mode flagment
	 * ( if TRUE, attach instance to Controller property )
	 * @var bool
	 */
	protected $attachMode = TRUE;
	
	
	public function __construct($param = array())
	{
		foreach ( $param as $key => $value )
		{
			$this->{$key} = $value;
		}
	}
	
	
	/**
	 * Growable interface implementation
	 * 
	 * @access public static
	 * @return SZ_Importer ( extended )
	 */
	public static function grow()
	{
		return Seezoo::$Importer->classes('Importer');
	}
	
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load a database instance
	 * 
	 * @access public
	 * @param  string $group
	 * @return Database $db
	 */
	public function database($group = '')
	{
		if ( $group === ''
		     && ! ($group = get_config('default_database_connection_handle')) )
		{
			$group = 'default'; 
		}
		
		$db = SeezooFactory::getDB($group);
		if ( $db === FALSE )
		{
			$dbClass = $this->loadModule('Database', '', FALSE)->data;
			$db      = new $dbClass($group);
			SeezooFactory::pushDB($group, $db);
		}
		$this->_attachModule('db', $db);
		
		return $db;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load a database forge library
	 * 
	 * @access public
	 * @return Databaseforge instance
	 */
	public function dbforge()
	{
		return $this->loadModule('databaseforge', '', TRUE, array(), 'dbforge');
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load an activerecord
	 * 
	 * @access public
	 * @return ActiveRecord instance
	 */
	public function activeRecord($arName)
	{
		$module = $this->loadModule($arName, 'activerecords', TRUE);
		return $module->data;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load a core class
	 * 
	 * @access public
	 * @param  string $className
	 * @param  bool $instanciate
	 * @return object
	 */
	public function classes($className, $instanciate = TRUE)
	{
		$module = $this->loadModule($className, '', $instanciate);
		return $module->data;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load a library
	 * 
	 * @access public
	 * @param  mixed $libname
	 * @param  array  $param
	 * @param  string $alias
	 * @return object
	 */
	public function library($libname, $param = array(), $alias = FALSE, $instantiate = TRUE)
	{
		foreach ( (array)$libname as $lib )
		{
			$module = $this->loadModule($lib, 'libraries', $instantiate, $param, $alias);
			$this->_attachModule(( $alias ) ? $alias : lcfirst($module->name), $module->data);
		}
		return $module->data;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load a tool
	 * 
	 * @access public
	 * @param  mixed $tools
	 */
	 /*
	public function tool($tools)
	{
		foreach ( (array)$tools as $tool )
		{
			$name = str_replace('_tool', '', $tool);
			$this->loadModule($name . '_tool', 'tools', FALSE);
		}
	}
	*/
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load a Helper
	 * 
	 * @access public
	 * @param  mixed $helpers
	 * @param  string $alias
	 * @return object
	 */
	public function helper($helpers, $alias = FALSE)
	{
		if ( is_array($helpers) )
		{
			$alias = FALSE;
		}
		$H = $this->classes('Helpers');
		
		foreach ( (array)$helpers as $helper )
		{
			$name   = str_replace(Seezoo::getSuffix('helper'), '', $helper);
			$alias  = ( $alias ) ? $alias : ucfirst($name);
			$module = $this->loadModule($helper, TRUE, array(), $alias);

			$H->{strtolower($name)} = $module->data;
		}
		
		return $module->data;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load a Model
	 * 
	 * @access public
	 * @param  mixed $models
	 * @param  array  $param
	 * @param  string $alias
	 * @return object
	 */
	public function model($models, $param = array(), $alias = FALSE)
	{
		$this->classes('Kennel', FALSE);
		
		if ( is_array($models) )
		{
			$alias = FALSE;
		}
		
		foreach ( (array)$models as $model )
		{
			$module = $this->loadModule($model, 'models', TRUE, $param);
			$this->_attachModule(( $alias ) ? $alias : $module->name, $module->data);
		}
		return $module->data;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load a kennel
	 * 
	 * @access public
	 * @param  mixed  $kennel
	 * @param  array  $param
	 * @param  string $alias
	 * @return object
	 */
	public function kennel($kennel, $param = array(), $alias = FALSE)
	{
		return $this->model($kennel, $param, $alias);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load a vendor library
	 * 
	 * @access public
	 * @param  mixed  $vendors
	 * @param  array  $param
	 * @return object
	 */
	public function vendor($vendors, $param = array())
	{
		$dirBase = 'vendors/';
		
		foreach ( (array)$vendors as $vendor )
		{
			// Does request class in a sub-directory?
			if ( FALSE !== ($point = strrpos($vendor, '/')) )
			{
				$dir    = $dirBase . substr($vendor, 0, ++$point);
				$vendor = substr($vendor, $point);
				$Class  = ucfirst($vendor);
			}
			else
			{
				$Class = ucfirst($vendor);
				$dir   = $dirBase;
			}
			
			$isLoaded = FALSE;
			$filePath = $dir . $Class . '.php';
		
			// Is class already loaded?
			if ( FALSE !== ($stacked = SeezooFactory::get('vendors', $vendor)) )
			{
				if ( is_object($stacked) )
				{
					$this->_attachModule($vendor, $stacked);
				}
				continue;
			}
			
			foreach ( Seezoo::getPackage() as $pkg )
			{
				if ( file_exists(PKGPATH . $pkg . '/' . $filePath) )
				{
					require_once(PKGPATH . $pkg . '/' . $filePath);
					$isLoaded = TRUE;
					break;
				}
			}
			
			if ( $isLoaded === FALSE )
			{
				if ( file_exists(EXTPATH . $filePath) )
				{
					require_once(EXTPATH . $filePath);
				}
				else if ( file_exists(APPPATH . $filePath) )
				{
					require_once(APPPATH . $filePath);
				}
				else
				{
					throw new UndefinedClassException($Class . ' is not specified.');
				}
			}
			
			if ( class_exists($Class, FALSE) )
			{
				$module = new $Class($param);
				$this->_attachModule(strtolower($vendor), $module);
			}
			else
			{
				$module = $Class;
			}
			SeezooFactory::push('vendors', $vendor, $vendor, $module);
			return $module;
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load a Breader's lead
	 * 
	 * @access public
	 * @param  string lead
	 * @return object
	 */
	public function lead($lead)
	{
		$systemLead = $this->classes('Lead');
		$dir        = 'classes/leads/';
		
		// Does request class in a sub-directory?
		if ( FALSE !== ($point = strrpos($lead, '/')) )
		{
			$dir  .= trail_slash(substr($lead, 0, ++$point));
			$lead  = lcfirst(substr($lead, $point));
			$Class = $lead . 'Lead';
			
		}
		else
		{
			$Class = $lead . 'Lead';
			$lead  = lcfirst($lead);
		}
		
		$filePath = $dir . $lead . '.php';
		$isLoaded = FALSE;
		
		foreach ( Seezoo::getPackage() as $pkg )
		{
			if ( file_exists(PKGPATH . $pkg . '/' . $filePath) )
			{
				require_once(PKGPATH . $pkg . '/' . $filePath);
				$isLoaded = TRUE;
				break;
			}
		}
		
		if ( $isLoaded === FALSE )
		{
			if ( file_exists(EXTPATH . $filePath) )
			{
				require_once(EXTPATH . $filePath);
			}
			else if ( file_exists(APPPATH . $filePath) )
			{
				require_once(APPPATH . $filePath);
			}
		}
		
		return ( class_exists($Class, FALSE) )
		         ? new $Class()
		         : $systemLead;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Import confiuration dataset
	 * 
	 * @access public
	 * @param string $configName
	 * @param bool   $isOtherKey
	 * @return array
	 */
	public function config($configName, $isOtherKey = FALSE)
	{
		$dir = 'config/';
		// Does request class in a sub-directory?
		if ( FALSE !== ($point = strrpos($configName, '/')) )
		{
			$dir .= trail_slash(substr($configName, 0, ++$point));
			$configName = substr($configName, $point);
		}
		
		// remove php-extension
		$configName = preg_replace('/\.php\Z/u', '', $configName);
		
		// Is config already loaded?
		if ( FALSE !== ($stacked = SeezooFactory::exists('config', $configName)) )
		{
			return $stacked;
		}
		
		$isLoaded      = FALSE;
		$stackedConfig = array();
		$filePath      = $dir . $configName . '.php';
		
		// Notice:
		// Configure data is merged from base file cascading.
		$detections = array(APPPATH, EXTPATH);
		foreach ( Seezoo::getPackage() as $pkg )
		{
			$detections[] = PKGPATH . $pkg . '/';
		}
		
		foreach ( $detections as $detect )
		{
			if ( file_exists($detect . $filePath) )
			{
				require_once($absPath . $filePath);
				if ( isset($config) )
				{
					$stackedConfig = array_merge($stackedConfig, $config);
				}
				unset($config);
				$isLoaded = TRUE;
			}
		}
		
		if ( $isLoaded === FALSE )
		{
			throw new UndefinedClassException('Configuration file ' . $configName . ' is not exists.');
		}
		
		SeezooFactory::push('config', $configName, $configName, $stackedConfig);
		$env = Seezoo::getENV();
		$env->importConfig($stackedConfig, $configName, $isOtherKey);
		
		return $stackedConfig;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load file by string
	 * 
	 * @access public
	 * @param  string $filePath
	 * @return mixed
	 */
	public function file($filePath)
	{
		if ( preg_match('/\Ahttp/', $filePath) )
		{
			$http = $this->library('Http');
			$resp = $http->request('GET', $filePath);
			if ( $resp->status !== 200 )
			{
				return FALSE;
			}
			return $resp->body;
		}
		else if ( ! is_file($filePath) )
		{
			throw new InvalidArgumentException('import file is not found: '
			                                   . get_class($this) . '::file');
		}
		{
			return file_get_contents($filePath);
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * attach the module
	 * 
	 * @access protected
	 * @param  string $name
	 * @param  object $module
	 */
	protected function _attachModule($name, $module)
	{
		if ( FALSE === ($SZ = Seezoo::getInstance())
		     || $this->attachMode === FALSE )
		{
			return;
		}
		
		if ( ! isset($SZ->{$name}) )
		{
			$SZ->{$name} = $module;
		}
		if ( isset($SZ->lead) )
		{
			$SZ->lead->attachModule($name, $module);
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load the class
	 * 
	 * @access public static
	 * @param  string $class
	 * @param  string $destDir
	 * @param  bool   $instanciate
	 * @param  array  $params
	 * @throws Exception
	 * @return mixed
	 */
	protected function loadModule(
	                               $class,                      // module name
	                               $destDir     = 'libraries',  // load target directory
	                               $instanciate = TRUE,         // If true, create instance
	                               $params      = array(),      // pass parameter to class constructor
	                               $alias       = FALSE         // property alias name
	                              )
	{
		if ( FALSE !== ($point = strrpos($class, '/')) )
		{
			$dir   = trim(substr($class, 0, $point++), '/') . '/';
			$class = ucfirst(substr($class, $point));
		}
		else
		{
			$dir   = '';
			$class = ucfirst($class);
		}
		$dir = 'classes/' . $destDir . '/' . $dir;
		if ( $destDir === '' )
		{
			$destDir = 'classes';
		}
		
		$module = new stdClass;
		$module->name = lcfirst($class);
		
		// Is class already loaded?
		if ( FALSE !== ($stacked = SeezooFactory::get($destDir, $class)) )
		{
			if ( ! $instanciate )
			{
				$module->data = ( is_object($stacked) ) ? sz_get_class($stacked) : $stacked;
			}
			else
			{
				if ( ! is_object($stacked) )
				{
					$instance = new $stacked($params);
					$module->data = ( $instance instanceof Aspect )
					                  ? Aspect::create($instance)
					                  : $instance;
				}
				else
				{
					$module->data = $stacked;
				}
				//$module->data = ( ! is_object($stacked) ) ? new $stacked($params) : $stacked;
			}
			return $module;
		}
		
		// Loop of detections factory
		$detections = array();
		
		foreach ( Seezoo::getPackage() as $pkg )
		{
			$detections[] = array(SZ_PREFIX_PKG, PKGPATH . $pkg . '/' . $dir);
		}
		$detections[] = array(SZ_PREFIX_EXT,  EXTPATH  . $dir);
		$detections[] = array(SZ_PREFIX_APP,  APPPATH  . $dir);
		$detections[] = array(SZ_PREFIX_CORE, COREPATH . $dir);
		
		// Do loop detection
		$classSuffix = ( $destDir === 'libraries' )
		                 ? 'library'
		                 : substr($destDir, 0, -1);
		foreach ( $detections as $detect )
		{
			if ( file_exists($detect[1] . $detect[0] . $class . '.php') )
			{
				require_once($detect[1] . $detect[0] . $class . '.php');
				$loadClass = ( class_exists($detect[0] . $class, FALSE) )
				                ? $detect[0] . $class
				                : $class;
				break;
			}
			else if ( file_exists($detect[1] . $class . '.php') )
			{
				require_once($detect[1] . $class . '.php');
				$loadClass = ( class_exists($detect[0] . $class, FALSE) )
				                ? $detect[0] . $class
				                : $class;
				break;
			}
		}
		
		if ( ! isset($loadClass) )
		{
			throw new UndefinedClassException('Undefined class' . ':' . $class);
		}
		
		if ( $instanciate === TRUE )
		{
			$instance = new $loadClass($params);
			$module->data = ( $instance instanceof Aspectable )
			                  ? Aspect::create($instance)
			                  : $instance;
		}
		else
		{
			$module->data = $loadClass;
		}
		
		SeezooFactory::push($destDir, $class, $alias, $module->data);
		return $module;
	}
}

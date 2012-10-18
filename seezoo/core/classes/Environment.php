<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Application and Server environment management
 * 
 * @package  Seezoo-Framework
 * @category Classes
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Environment
{
	/**
	 * application configure data
	 * @var array
	 */
	protected $_config   = array();
	protected $_loadedConfig = array();
	
	
	/**
	 * application database settings
	 * @var array
	 */
	protected $_database = array();
	
	
	/**
	 * application uri-mapping data
	 * @var array
	 */
	protected $_mapping  = array();
	
	
	/**
	 * application actions data
	 * @var array
	 */
	protected $_action   = array();
	
	
	/**
	 * Framework can treats mimetypes
	 * @var array
	 */
	protected $_mimetypes = array();
	
	
	/**
	 * System mail setting stack
	 * @var array
	 */
	protected $_mail      = array();
	
	
	/**
	 * flagment of magic_quotes_gpc
	 * @var bool
	 */
	public $isMagicQuote;
	
	
	/**
	 * flagment of mb_check_encoding enable
	 * @var bool
	 */
	public $isMBEnc;
	
	
	/**
	 * Does your Server-OS is windows?
	 * @var bool
	 */
	public $isWindows;
	
	
	/**
	 * Does PHP works with safe_mode On?
	 * @var bool
	 */
	public $isSafeMode;
	
	
	/**
	 * Does PHP enable to use iconv function?
	 * @var bool
	 */
	public $isEnableIconv;
	
	
	/**
	 * PHP enable to use memory byte limit
	 * @var int
	 */
	public $memoryLimit;
	
	
	/**
	 * Does access user-agent is Internet explorer?
	 * @var bool
	 */
	public $isIE;
	
	
	/**
	 * Memory digit times
	 * @var array
	 */
	protected $_memMap = array('G' => 3, 'M' => 2, 'K' => 1); 
	
	
	/**
	 * PHP works API
	 * @var string
	 */
	public $api;
	
	
	public function __construct()
	{
		$this->isMagicQuote  = get_magic_quotes_gpc();
		$this->isMBEnc       = function_exists('mb_check_encoding');
		$this->isWindows     = ( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ) ? TRUE : FALSE;
		$this->isSafeMode    = ( ini_get('safe_mode') ) ? TRUE : FALSE;
		$this->isEnableIconv = function_exists('iconv');
		$this->memoryLimit   = $this->_getMemoryLimit();
		$this->isIE          = ( isset($_SERVER['HTTP_USER_AGENT'])  && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) ? TRUE : FALSE;
		
		// load the configurations
		$this->_config =& Seezoo::$config;
		
		// detect PHP API
		$this->_detectAPI();
	}
		
	
	// ---------------------------------------------------------------
	
	
	/**
	 * gettter/setter configure
	 * 
	 * @access public
	 */
	public function config()
	{
		$args = func_get_args();
		$nums = func_num_args();
		
		// If single argment and string, works getter.
		if ( $nums === 1 && is_string($args[0]) )
		{
			return $this->getConfig($args[0]);
		}
		// else. works setter
		else
		{
			if ( $nums === 2 )
			{
				$this->setConfig($args[0], $args[1]);
			}
			else if ( $nums === 1 && is_array($args[0]) )
			{
				$this->setConfig($args[0]);
			}
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * get the current configure
	 * 
	 * @access public
	 * @param  string $key
	 * @param  string $keyName
	 * @return mixed
	 */
	public function getConfig($key, $keyName = '')
	{
		if ( $keyName )
		{
			return ( isset($this->_loadedConfig[$keyName]) && isset($this->_loadedConfig[$keyName][$key]) )
			         ? $this->_loadedConfig[$keyName][$key]
			         : FALSE;
		}
		else
		{
			return ( isset($this->_config[$key]) ) ? $this->_config[$key] : FALSE;
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * get All current configure
	 * 
	 * @access public
	 * @return array
	 */
	public function getAllConfig($keyName = null)
	{
		if ( ! $keyName )
		{
			return $this->_config;
		}
		return ( isset($this->_loadedConfig[$keyName]) )
		        ? $this->_loadedConfig[$keyName]
		        : array();
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * set the configure only this process
	 * 
	 * @access public
	 * @param  string $key
	 * @param  mixed $value
	 * @param  string $keyName
	 */
	public function setConfig($key, $value = '', $keyName = '')
	{
		if ( is_array($key) )
		{
			foreach ( $key as $key2 => $val )
			{
				if ( $keyName && isset($this->_loadedConfig[$keyName]) )
				{
					$this->_loadedConfig[$keyName][$key] = $val;
				}
				else
				{
					$this->_config[$key2] = $val;
				}
			}
		}
		else
		{
			if ( $keyName && isset($this->_loadedConfig[$keyName]) )
			{
				$this->_loadedConfig[$keyName][$key] = $value;
			}
			else
			{
				$this->_config[$key] = $value;
			}
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * enable/disable debugger
	 * 
	 * @access public
	 * @param  bool $flag
	 */
	public function debugger($flag = FALSE)
	{
		$this->setConfig('enable_debug', (bool)$flag);
	}


	// ---------------------------------------------------------------
	

	/**
	 * Get ini settings
	 *
	 * @access public
	 * @param string
	 * @param bool
	 * @return string
	 */
	public function ini($get, $set = FALSE)
	{
		if ( $set === FALSE )
		{
			return ini_get($get);
		}
		else
		{
			ini_set($get, $set);
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * get database settings
	 * 
	 * @access public
	 * @return array
	 */
	public function getDBSettings()
	{
		if ( ! $this->_database )
		{
			$this->_load('database');
		}
		return $this->_database;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * get all mapping settings
	 * 
	 * @access public
	 * @return array
	 */
	public function getMapping()
	{
		if ( ! $this->_mapping )
		{
			$this->_load('mapping');
		}
		return $this->_mapping;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * get all actions settings
	 * 
	 * @access public
	 * @return array
	 */
	public function getActions()
	{
		if ( ! $this->_action )
		{
			$this->_load('actions');
		}
		return $this->_action;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * get mail setting
	 * 
	 * @access public
	 * @return array
	 */
	public function getMailSettings()
	{
		if ( count($this->_mail) === 0 )
		{
			$this->_load('mail');
		}
		
		return $this->_mail;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * package bootstrap
	 * 
	 * @access public
	 * @param  string $packagePath
	 */
	public function bootPackage($packagePath)
	{
//		if ( file_exists(EXTPATH . $packagePath . '/config/config.php') )
//		{
//			include(EXTPATH . $packagePath . '/config/config.php');
//			if ( isset($config) )
//			{
//				$this->_config = array_merge($this->_config, $config);
//				unset($config);
//			}
//		}
		
		if ( file_exists(EXTPATH . $packagePath . '/boot.php') )
		{
			include(EXTPATH . $packagePath . '/boot.php');
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * import configuration dataset
	 * 
	 * @access public
	 * @param  array  $config
	 * @param  string $keyName
	 * @param  bool   $isOtherKey
	 */
	public function importConfig($config, $keyName, $isOtherKey = FALSE)
	{
		if ( $isOtherKey === FALSE )
		{
			$this->_config = array_merge($this->_config, $config);
			return;
		}
		
		$this->_loadedConfig[$keyName] = $config;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * load the setting
	 * 
	 * @access protected
	 */
	protected function _load($setting)
	{
		// Load the application settings if exists
		if ( file_exists(APPPATH . 'config/' . $setting . '.php') )
		{
			include(APPPATH . 'config/' . $setting . '.php');
			$this->{'_' . $setting} = ( isset($$setting) ) ? $$setting : array(); // not typo.
		}
		else
		{
			$this->{'_' . $setting} = array();
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Override setting
	 * 
	 * @access protected
	 * @param  string $path
	 * @param  string $name
	 */
	protected function _overrideSetting($path, $name)
	{
		if ( isset($this->{'_' . $name}) && file_exists($path) )
		{
			include($path);
			if ( isset($$name) )
			{
				$this->{'_' . $name} = array_merge($this->{'_' . $name}, $$name);
			}
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get memory_limit settings to byte digit
	 * 
	 * @access protected
	 * @return int $limit
	 */
	protected function _getMemoryLimit()
	{
		$limit = $this->ini('memory_limit');
		$digit = strtoupper(substr($limit, -1, 1));
		$times = isset($this->_memMap[$digit]) ? $this->_memMap[$digit] : 0;
		while ( $times > 0 )
		{
			$limit = (int)$limit * 1024;
			--$times;
		}
		return $limit;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * PHP worler API detection
	 * 
	 * @access portected
	 * @return string
	 */
	protected function _detectAPI()
	{
		if ( PHP_SAPI === 'cli' )
		{
			$this->api = 'cli';
		}
		else if ( strpos(PHP_SAPI, 'cgi') !== FALSE )
		{
			$this->api = 'cgi';
		}
		else
		{
			// temporary mod ( includes apache2hanler etc... )
			$this->api = 'mod';
		}
	}
	
}
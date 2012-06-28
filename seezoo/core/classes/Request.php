<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * User Request parameters as $_POST, $_GET, $_SERVER, $_COOKIE management
 * 
 * @package  Seezoo-Framework
 * @category Classes
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Request
{
	/**
	 * request method
	 * @var string ( always uppercase )
	 */
	public $requestMethod;
	
	
	/**
	 * request pathinfo
	 * @var string
	 */
	protected $_pathinfo;
	
	
	/**
	 * $_COOKIE stack
	 * @var array
	 */
	protected $_cookie;
	
	
	/**
	 * $_SERVER stack
	 * @var array
	 */
	protected $_server;
	
	
	/**
	 * $_POST stack
	 * @var array
	 */
	protected $_post;
	
	
	/**
	 * $_GET stack
	 * @var array
	 */
	protected $_get;


	/**
	 * INPUT stack
	 * @var array
	 */
	protected $_input;
	
	
	/**
	 * URI info
	 * @var string
	 */
	protected $_uri;
	
	
	/**
	 * segment info
	 * @var array
	 */
	protected $_uriArray = array();
	
	
	/**
	 * Accessed passinfo ( not overrided )
	 * @var string
	 */
	protected $_accessPathInfo;
	
	
	
	/**
	 * Your server encoding
	 * @var string
	 */
	protected $_serverEncoding;
	
	
	public function __construct()
	{
		$this->env             = Seezoo::getENV();
		$this->requestMethod   = $this->_detectRequestMethod();
		$this->_serverEncoding = $this->env->getConfig('server_encoding');
		$this->_appCharset     = $this->env->getConfig('charset');
		$this->_cookie         = $this->_cleanFilter($_COOKIE);
		$this->_server         = $_SERVER;//( $this->env->api === 'cli' ) ? $_SERVER : $this->_cleanFilter($_SERVER);
		$this->_post           = $this->_cleanFilter($_POST);
		$this->_get            = $this->_cleanFilter($_GET);
		$this->_input          = $this->_parseInput();
		$this->_uri            = trim((string)$this->server('REQUEST_URI'), '/');
		$this->_accessPathInfo = (string)$this->server('PATH_INFO');
	}


	// ---------------------------------------------------------------


	/**
	 * Request method detection
	 @access protected
	 @return string
	 */
	protected function _detectRequestMethod()
	{
		$method = ( isset($_SERVER['REQUEST_METHOD']) ) ? $_SERVER['REQUEST_METHOD'] : 'GET';
		switch ( $method )
		{
			case 'GET':
			case 'HEAD':
				$method ='GET';
				break;
			default:
				break;
		}
		return $method;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get the server parameter
	 * 
	 * @access public
	 * @param  string $key
	 * @return mixed
	 */
	public function server($key)
	{
		$key = strtoupper($key);
		return ( isset($this->_server[$key]) ) ? $this->_server[$key] : FALSE;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get the POST parameter
	 * 
	 * @access public
	 * @param  string $key
	 * @return mixed
	 */
	public function post($key)
	{
		return ( isset($this->_post[$key]) ) ? $this->_post[$key] : FALSE;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get the GET parameter
	 * 
	 * @access public
	 * @param  string $key
	 * @return mixed
	 */
	public function get($key)
	{
		return ( isset($this->_get[$key]) ) ? $this->_get[$key] : FALSE;
	}
	
	
	// ---------------------------------------------------------------


	/**
	 * Get the PHP input
	 *
	 * @access public
	 * @param  string $key
	 * @return mixed
	 */
	public function input($key)
	{
		return ( isset($this->_input[$key]) ) ? $this->_input[$key] : FALSE;
	}


	// ---------------------------------------------------------------
	
	
	/**
	 * Get the COOKIE parameter
	 * 
	 * @access public
	 * @param  string $key
	 * @return mixed
	 */
	public function cookie($key)
	{
		return ( isset($this->_cookie[$key]) ) ? $this->_cookie[$key] : FALSE;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Set a process request
	 * 
	 * @access public
	 * @param  string $pathinfo
	 * @param  string $mode
	 * @param  int    $level
	 */
	public function setRequest($pathinfo, $mode, $level)
	{
		// If pathinfo is empty, use server-pathinfo.
		if ( empty($pathinfo) )
		{
			$pathinfo = (string)$this->server('PATH_INFO');
		}
		$pathinfo = kill_traversal(trim($pathinfo, '/'));
		if ( $pathinfo !== '' )
		{
			$segments = explode('/', $pathinfo);
			//array_unshift($segments, '');
		}
		else
		{
			$segments = array();
		}
		
		$this->_uriArray[$level] = $segments;
		
		// method mapping
		$mapping = $this->env->getMapping();
		if ( $mapping && isset($mapping[$mode]) && is_array($mapping[$mode]) )
		{
			foreach ( $mapping[$mode] as $regex => $map )
			{
				if ( $regex === $pathinfo )
				{
					$pathinfo = $map;
					break;
				}
				else if ( preg_match('|^' . $regex . '$|u', $pathinfo, $matches) )
				{
					$pathinfo = ( isset($matches[1]) )
					              ? preg_replace('|^' . $regex . '$|u', $map, $pathinfo)
					              : $val;
					break;
					
				}
			}
		}
		
		return $pathinfo;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get access URI-segment
	 * 
	 * @access public
	 * @param  int $index
	 * @param  mixed $default
	 * @return mixed
	 */
	public function segment($index, $default = FALSE)
	{
		$level = SeezooFactory::getLevel();
		return ( isset($this->_uriArray[$level]) && isset($this->_uriArray[$level][$index - 1]) )
		         ? $this->_uriArray[$level][$index - 1]
		         : $default;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get all access URI-segment array
	 * 
	 * @access public
	 * @return array
	 */
	public function uriSegments($level = FALSE)
	{
		if ( ! $level )
		{
			$level = SeezooFactory::getLevel();
		}
		return ( isset($this->_uriArray[$level]) ) ? $this->_uriArray[$level] : array();
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get HTTP requested PATH_INFO
	 * 
	 * @access public
	 * @return string
	 */
	public function getAccessPathInfo()
	{
		return $this->_accessPathInfo;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * clean up parameters
	 * 
	 * @access protected
	 * @param  array $data
	 * @return mixed
	 */
	protected function _cleanFilter($data)
	{
		$filtered = array();
		foreach ( $data as $key => $value )
		{
			if ( is_array($value) )
			{
				$filtered[$key] = $this->_cleanFilter($value);
			}
			else
			{
				// remove magic_quote
				if ( $this->env->isMagicQuote )
				{
					$value = stripslashes($value);
				}
				
				// check encoding
				if ( $this->env->isMBEnc && mb_check_encoding($value, $this->_serverEncoding) === TRUE )
				{
					$value = $this->_convertUTF8($value, $this->_serverEncoding);
				}
				else
				{
					$value = $this->_convertUTF8($value);
				}
				
				// kill invisible character
				do
				{
					preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $value, -1, $count);
				}
				while( $count );
				
				// to strict linefeed
				if ( strpos($value, "\r") !== FALSE )
				{
					$value = str_replace(array("\r\n", "\r"), "\n", $value);
				}
				
				// kill nullbyte
				$value = str_replace('\0', '', $value);
				//$key   = $this->_convertUTF8($key);
				
				$filtered[$key] = $value;
			}
		}
		
		// TODO: some security process
		
		return $filtered;
	}


	// ---------------------------------------------------------------


	/**
	 * Parse PHP Input ( when requested with PUT/DELETE method )
	 * @access protected
	 * @return array
	 */
	protected function _parseInput()
	{
		if ( $this->requestMethod === 'GET' || $this->requestMethod === 'POST' )
		{
			return array();
		}

		$input = explode('&', file_get_contents('php://input'));
		$data  = array();
		foreach ( $input as $keyValue )
		{
			list($key, $value) = explode('=', $keyValue);
			// Raw input should have been encoded
			$data[$key] = rawurldecode($value);
		}
		return $this->_cleanFilter($data);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * String convert to UTF-8 encoding
	 * @param string $str
	 * @param string $encoding
	 */
	protected function _convertUTF8($str, $encoding = 'UTF-8')
	{
		if ( $this->env->isEnableIconv && ! preg_match('/[^\x00-\x7F]/S', $str) )
		{
			return @iconv($encoding, 'UTF-8//IGNORE', $str);
		}
		else if ( $this->env->isMBEnc && mb_check_encoding($str, $encoding) )
		{
			return mb_convert_encoding($str, $encoding, mb_internal_encoding());
		}
		return str;
	}
}

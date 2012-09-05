<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * XML-RPC Client library
 * 
 * @package  Seezoo-Framework
 * @category Libraries
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */
class SZ_Xmlrpc extends SZ_Driver
{
	/**
	 * Request options
	 * @var array
	 */
	protected $_options    = array();
	
	/**
	 * Request parameers
	 * @var array
	 */
	protected $_params     = array();
	
	/**
	 * Rquest URI info
	 * @var array ( parsed )
	 */
	protected $_serverInfo = array();
	
	/*
	 * Carridge return
	 */
	protected $CRLF        = "\r\n";
	
	
	
	public function __construct($params = array())
	{
		$this->_options = $params;
		$this->_loadDriver('xmlrpc', 'Xmlrpc_value', FALSE, FALSE);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Ser request server URI
	 * 
	 * @access public
	 * @param  string $uri
	 * @throws InvalidArgumentException
	 */
	public function setServer($uri)
	{
		// Does URI contains protocol string?
		if ( ! preg_match('/^http/', $uri) )
		{
			$protocol = ( $this->getOption('ssl') ) ? 'https://' : 'http://';
			$uri      = $protocol . $uri;
		}
		
		if ( FALSE === ($parsed = parse_url($uri)) )
		{
			throw new InvalidArgumentException('Invalid URI format! ' . get_class($this) .'::' . __METHOD__);
		}
		$this->_serverInfo = $parsed;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Set request parameters
	 * 
	 * @access public
	 * @param  mixed $param
	 * @param  bool $expType
	 */
	public function setParam($param, $expType = FALSE)
	{
		if ( call_user_func(array($this->driver, 'isOrderedArray'), $param) )
		{
			foreach ( $param as $val )
			{
				$this->_params[] = $val;
			}
		}
		else
		{
			$this->param[] = ( $expType )
			                   ? new $this->driver($param, $expType)
			                   : $param;
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Set request options
	 * 
	 * @access public
	 * @param  mixed $options
	 */
	public function setOption($options = array())
	{
		foreach ( (array)$options as $key => $val )
		{
			$this->_options[$key] = $val;
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get option
	 * 
	 * @access public
	 * @param  string $key
	 * @return mixed
	 */
	public function getOption($key)
	{
		return ( isset($this->_options[$key]) ) ? $this->_options[$key] : FALSE;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get server info
	 * 
	 * @access public
	 * @param  string $key
	 * @return mixed
	 */
	public function getServerInfo($key)
	{
		return ( isset($this->_serverInfo[$key]) ) ? $this->_serverInfo[$key] : FALSE;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Send XML-RPC request
	 * 
	 * @access public
	 * @param  string $method
	 * @return bool
	 * @throws InvalidArgumentException, RuntimeException
	 */
	public function sendRequest($method = '')
	{
		if ( empty($method) )
		{
			throw new InvalidArgumentException('Method name must not be empty! ' . get_class($this) .'::' . __METHOD__);
		}
		
		// Parse request info
		list($host, $path, $port) = $this->_parseURI();
		
		// Open connection with fsockopen
		$stream = @fsockopen($host, $port, $errno, $errstr);
		
		if ( ! is_resource($stream) )
		{
			throw new RuntimeException('Couldn\'t open XML-RPC request socket!');
		}
		
		$requestBody = $this->_buildRequestBody($method);
		$request     = $this->_buildRequestHeader($host, $path, strlen($requestBody));
		$request    .= $this->CRLF . $this->CRLF;
		$request    .= $requestBody;
		
		if ( ! fputs($stream, $request, strlen($request)) )
		{
			throw new RuntimeException('Stream can\'t write to host!');
		}
		
		
		// Get response
		$response = '';
		while ( ! feof($stream) )
		{
			$response .= fgets($stream, 4096);
		}
		fclose($stream);
		
		// Parse and check parameter
		return $this->_parseResponse($response);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Build request header string
	 * 
	 * @access protected
	 * @param  string $host
	 * @param  string $path
	 * @param  int $contentLength
	 * @return string
	 */
	protected function _buildRequestHeader($host, $path, $contentLength)
	{
		if ( $this->getOption('userAgent') )
		{
			$ua = $this->getOption('userAgent');
		}
		else
		{
			$req = Seezoo::getRequest();
			$ua  = $req->server('HTTP_USER_AGENT');
		}
		$headers   = array();
		$headers[] = 'POST ' . $path . ' HTTP/1.0';
		$headers[] = 'Host: ' . $host;
		$headers[] = 'Content-Type: text/xml';
		$headers[] = 'User-Agent: ' . $ua;
		$headers[] = 'Content-Length: ' . $contentLength;
		
		return implode($this->CRLF, $headers);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Parse URI info
	 * 
	 * @access protected
	 * @return array
	 */
	protected function _parseURI()
	{
		$host  = $this->getServerInfo('host');
		$port  = $this->getServerInfo('port');
		$path  = ( $this->getServerInfo('path') ) ? $this->getServerInfo('path') : '/';
		$query = $this->getServerInfo('query');
		if ( $query && ! empty($query) )
		{
			$path .= '?' . $query;
		}
		if ( $port === FALSE )
		{
			$port = ( $this->getOption('ssl') ) ? 443 : 80;
		}
		return array($host, $path, $port);
		
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Build request body ( XML formatted string )
	 * 
	 * @access protected
	 * @param  string $method
	 * @return string
	 */
	protected function _buildRequestBody($method)
	{
		$body  = '<?xml version="1.0" encoding="UTF-8"?>'; // UTF-8 only
		$body .= '<methodCall>';
		$body .= '<methodName>' . $method . '</methodName>';
		$body .= '<params>';
		$body .= $this->_encodeParameters();
		$body .= '</params>';
		$body .= '</methodCall>';
		
		return $body;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Encode request parameters
	 * 
	 * @access protected
	 * @return string
	 */
	protected function _encodeParameters()
	{
		$encoded = array();
		foreach ( $this->_params as $param )
		{
			if ( $param instanceof $this->driver )
			{
				$encoded[] = $param->getValue();
				continue;
			}
			
			$type      = call_user_func(array($this->driver, 'detectType'), $param);
			$value     = new $this->driver($param, $type);
			$encoded[] = $value->getValue();
		}
		return implode("\n", $encoded);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Parse response
	 * 
	 * @access protected
	 * @param  string $resp
	 * @return bool
	 */
	protected function _parseResponse($resp)
	{
		$split = explode($this->CRLF . $this->CRLF, $resp, 2);
		// Bad request
		if ( count($split) < 2 )
		{
			return FALSE;
		}
		
		$header = trim($split[0]);
		$body   = trim($split[1]);
		
		// Check response code
		if ( ! preg_replace('#HTTP/[0-9\.]+\s200\s#u', '$1', $header) )
		{
			return FALSE;
		}
		
		// Check response body
		try
		{
			$XML = simplexml_load_string($body);
			return ( isset($XML->params) ) ? TRUE : FALSE;
		}
		catch ( Exception $e )
		{
			throw $e;
		}
		
	}
}

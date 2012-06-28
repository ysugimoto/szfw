<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Application response Management class
 * 
 * @package  Seezoo-Framework
 * @category Classes
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Response
{
	/**
	 * output headers stack
	 * @var array
	 */
	protected $_headers = array();
	
	
	/**
	 * Environment class instance
	 * @var Environment
	 */
	protected $env;
	
	
	/**
	 * Request class instance
	 * @var Request
	 */
	protected $req;
	
	public function __construct()
	{
		$this->env = Seezoo::getENV();
		$this->req = Seezoo::getRequest();
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Force redirect
	 * 
	 * @access public
	 * @param  string uri
	 * @param  int    $code
	 */
	public function redirect($uri, $code = 302)
	{
		if ( ! preg_match('/^https?:/', $uri ) )
		{
			$rewrite = Seezoo::$config['enable_mod_rewrite'];
			$uri     = Seezoo::$config['base_url'] . (( $rewrite ) ? '' : DISPATCHER . '/') . $uri;
		}
		header("Location: " . $uri, TRUE, $code);
		exit;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * add Output header
	 * 
	 * @access public
	 * @param  string $key
	 * @param  string $value
	 * @param  bool $replace
	 */
	public function setHeader($key, $value, $replace = TRUE)
	{
		$this->_headers[] = array($key . ': ' . $value, $replace);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * send browser buffer
	 * 
	 * @access public
	 * @param  string $output
	 */
	public function display($output)
	{
		// Is it possible to transfer compressed gzip?
		$this->setGzipHandler();
		
		header('HTTP/1.1 200 OK');
		foreach ( $this->_headers as $header )
		{
			@header($header[0], $header[1]);
		}
		
		Event::fire('final_output', $output);
		
		if ( $this->env->getConfig('enable_debug') === TRUE )
		{
			$memory   = memory_get_usage();
			$debugger = Seezoo::$Importer->classes('Debugger');
			$output   = str_replace('</body>', $debugger->execute($memory) . "\n</body>", $output);
		}
		
		echo $output;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * send json formatted string
	 * 
	 * @access public
	 * @param  string $json
	 */
	public function displayJSON($json)
	{
		// Is it possible to transfer compressed gzip?
		$this->setGzipHandler();
		
		header('HTTP/1.1 200 OK');
		header('Content-Type: application/json', TRUE);
		
		echo ( is_string($json) ) ? trim($json) : json_encode($json);
		// no more output...
		exit;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Force download and exit
	 * 
	 * @access public
	 * @param string $filepath
	 * @param string $filename
	 * @param bool   $isData
	 * @throws Exception
	 */
	public function download($filepath, $filename = '', $isData = FALSE)
	{
		if ( empty($filename) )
		{
			if ( $isData ==- TRUE )
			{
				throw new Exception('Download filename is not empty when direct data download.');
				return FALSE;
			}
			$filename = basename($filepath);
		}
		
		// get extension
		$exp         = explode('.', $filepath);
		$extention   = end($exp);
		$fileSize    = ( isData === TRUE ) ? strlen($filepath) : filesize($filepath);
		$memoryLimit = $this->env->memoryLimit;
		
		// set mimetype
		if ( $isData === TRUE )
		{
			$mimetype = 'application/octet-stream';
		}
		else
		{
			$Mime = Seezoo::$Importer->classes('Mimetype');
			$mimetype = $Mime->detect($filepath);
		}
		
		// send headers
		$headers = array(
			'Content-Type: "' . $mimetype . '"',
			'Content-Disposition: attachment; filename="' . $filename . '"'
		);
		if ( $this->env->isIE )
		{
			$filename = mb_convert_encoding($filename, 'SHIFT_JIS', 'UTF-8');
			$headers[] = 'Expires: 0';
			$headers[] = 'Cache-Control: must-revalidate, post-check=0, pre-check=0';
			$headers[] = 'Content-Transfar-Encoding: binary';
			$headers[] = 'Pragma: public';
			$headers[] = 'Content-Length: ' . $fileSize;
		}
		else
		{
			$headers[] = 'Content-Transfar-Encoding: binary';
			$headers[] = 'Expires: 0';
			$headers[] = 'Pragma: no-cahce';
			$headers[] = 'Content-Length: ' . $fileSize;
		}
		
		foreach ( $headers as $headerLine )
		{
			header($headerLine);
		}
		
		// If download filesize over our PHP memory_limit,
		// we try to split download
		if ( ! $isData && $memoryLimit < $fileSize )
		{
			flush();
			$fp = fopen($filepath, 'rb');
			do
			{
				echo fread($fp, 4096);
				flush();
			}
			while ( ! feof($fp) );
			fclose($fp);
		}
		else
		{
			echo ( $isData ) ? $filepath : file_get_contents($filepath);
		}
		exit;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Start gzip compressed output if enable
	 * @access protected
	 */
	protected function setGzipHandler()
	{
		if ( $this->env->getConfig('gzip_compress_output') === TRUE 
		     && extension_loaded('zlib')
		     && strpos((string)$this->req->server('HTTP_ACCEPT_ENCODING'), 'gzip') !== FALSE )
		{
			ob_start('ob_gzhandler');
		}
	}
}
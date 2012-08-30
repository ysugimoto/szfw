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
			$uri     = Seezoo::$config['base_url']
			           . (( $rewrite ) ? '' : DISPATCHER . '/') . ltrim($uri, '/');
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
	 * @param string $filePath
	 * @param string $fileName
	 * @param bool   $isData
	 * @throws Exception
	 */
	public function download($filePath, $fileName = '', $isData = FALSE)
	{
		// Is Download data real data string?
		if ( $isData === TRUE )
		{
			if ( empty($fileName) )
			{
				throw new Exception('Download filename is not empty when direct data download.');
			}
			$fileSize = strlen($filePath);
			$mimeType = 'application/octet-stream';
		}
		// Else, download file
		else
		{
			if ( ! file_exists($filePath) )
			{
				throw new InvalidArgumentException('Download file is not exists! file: ' . $filePath);
			}
			
			if ( empty($fileName) )
			{
				$fileName = basename($filePath);
			}
			$fileSize = filesize($filePath);
			$Mime     = Seezoo::$Importer->classes('Mimetype');
			$mimeType = $Mime->detect($filePath);
			
		}
		
		// send headers
		$headers = array('Content-Type: "' . $mimeType . '"');
		
		if ( $this->env->isIE )
		{
			$fileName = mb_convert_encoding($fileName, 'SHIFT_JIS', 'UTF-8');
			$headers[] = 'Content-Disposition: attachment; filename="' . $fileName . '"';
			$headers[] = 'Expires: 0';
			$headers[] = 'Cache-Control: must-revalidate, post-check=0, pre-check=0';
			$headers[] = 'Content-Transfar-Encoding: binary';
			$headers[] = 'Pragma: public';
			$headers[] = 'Content-Length: ' . $fileSize;
		}
		else
		{
			$headers[] = 'Content-Disposition: attachment; filename="' . $fileName . '"';
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
		if ( $this->env->memoryLimit < $fileSize )
		{
			flush();
			if ( ! $isData )
			{
				$fp = fopen($filePath, 'rb');
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
				$point = 0;
				do
				{
					echo substr($filePath, $point, 4096);
					flush();
					$point += 4096;
				} while ( $point < $fileSize );
			}
		}
		else
		{
			echo ( $isData ) ? $filePath : file_get_contents($filePath);
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
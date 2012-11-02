<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Simple access utility functions
 * 
 * @package  Seezoo-Framework
 * @category System
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */


if ( ! function_exists('show_404') )
{
	/**
	 * show 404 error
	 * 
	 * @param string $message
	 * @param string $backLinkPath
	 */
	function show_404($message = '', $backLinkPath = '')
	{
		$e = Seezoo::$Importer->classes('Exception');
		$e->error404($message, $backLinkPath);
		exit;
	}
}


// ---------------------------------------------------------------


if ( ! function_exists('show_error') )
{
	/**
	 * show generic error
	 * 
	 * @param string $message
	 * @param int $code
	 */
	function show_error($message = '', $code = 0)
	{
		$e = Seezoo::$Importer->classes('Exception', FALSE);
		throw new $e($message, $code);
	}
}


// ---------------------------------------------------------------


if ( ! function_exists('get_env') )
{
	/**
	 * get Enviroment instance shortcut
	 * 
	 * @return object
	 */
	function get_env()
	{
		return Seezoo::getENV();
	}
}


// ---------------------------------------------------------------


if ( ! function_exists('get_request') )
{
	/**
	 * get Request instance shortcut
	 * 
	 * @return object
	 */
	function get_request()
	{
		return Seezoo::getRequest();
	}
}


// ---------------------------------------------------------------


if ( ! function_exists('get_config') )
{
	/**
	 * get configure data
	 * 
	 * @param  string $key
	 * @return mixed
	 */
	function get_config($key)
	{
		$env = Seezoo::getENV();
		return $env->getConfig($key);
	}
}


// ---------------------------------------------------------------


if ( ! function_exists('page_link') )
{
	/**
	 * return a http-based link
	 * 
	 * @param sring $path
	 * @return string $link
	 */
	function page_link($path = '', $querySuffix = TRUE)
	{
		$env        = Seezoo::getENV();
		$dispatcher = ( ! $env->getConfig('enable_mod_rewrite') ) ? DISPATCHER . '/' : '';
		if ( preg_match('/\Ahttps?:\/\//u', $path) )
		{
			$uri = $path;
		}
		else
		{
			$uri = $env->getConfig('base_url') . $dispatcher . trim($path, '/');
		}
		
		if ( $querySuffix === TRUE )
		{
			if ( '' !== ( $q = SeezooFactory::getQueryStringSuffix()) )
			{
				$char = ( strpos($uri, '?') !== FALSE ) ? '&amp;' : '?';
				$uri .= $char . $q;
			}
		}
		return prep_str($uri);
	}
}


// ---------------------------------------------------------------


if ( ! function_exists('base_link') )
{
	/**
	 * return a base-link
	 * 
	 * @return string $link
	 */
	function base_link($path = '')
	{
		$env = get_env();
		return prep_str($env->getConfig('base_url') . $path);
	}
}


// ---------------------------------------------------------------


if ( ! function_exists('debug_var') )
{
	/**
	 * variable add to debugger class stack
	 * 
	 * @param argments
	 */
	function debugVar()
	{
		$debug = Seezoo::$Importer->classes('Debugger');
		foreach ( func_get_args() as $value )
		{
			$debug->store($value);
		}
	}
}


// ---------------------------------------------------------------


if ( ! function_exists('prep_str') )
{
	/**
	 * simple shortcut function of htmlspecialchars
	 * 
	 * @param $str
	 * @param $charset
	 * @return $str
	 */
	function prep_str($str, $charset = 'UTF-8')
	{
		return ( is_array($str) )
		        ? array_map('prep_str', $str, $charset)
		        : htmlspecialchars($str, ENT_QUOTES, $charset);
	}
}


// ---------------------------------------------------------------


if ( ! function_exists('is_ajax_request') )
{
	/**
	 * check request with Ajax
	 * 
	 * @return bool
	 */
	function is_ajax_request()
	{
		return ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) ) ? TRUE : FALSE;
	}
}


// ---------------------------------------------------------------


if ( ! function_exists('kill_nullbyte') )
{
	/**
	 * kill nullbyte string
	 * 
	 * @param mixed $str
	 * @return mixed
	 */
	function kill_nullbyte($str)
	{
		return ( is_array($str) ) 
		        ? array_map('kill_nullbyte', $str)
		        : str_replace('\0', '', $str);
	}
}


// ---------------------------------------------------------------


if ( ! function_exists('kill_traversal'))
{
	/**
	 * kill traversal string ( relative/absolute path )
	 * 
	 * @param mixed $str
	 * @return mixed
	 */
	function kill_traversal($str)
	{
		if (is_array($str))
		{
			return array_map('kill_traversal', $str);
		}
		$str   = str_replace('../', '', $str);
		$paths = explode('/', ltrim($str, '/'));
		$ret   = array();
		
		foreach ( $paths as $path )
		{
			$ret[] = basename(kill_nullbyte($path));
		}
		return implode('/', $ret);
	}
}


// ---------------------------------------------------------------


if ( ! function_exists('really_writable') )
{
	/**
	 * Check path is really writable permission
	 * @param $path
	 * @return bool
	 */
	function really_writable($path)
	{
		$env = Seezoo::getENV();
		if ( ! $env->isWindows )
		{
			if ( ! file_exists($path) )
			{
				$path = dirname($path);
			}
			return is_writable($path);
		}
		
		if ( is_dir($path) )
		{
			// make tmp filename
			$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			$path .= uniqid(mt_rand(), TRUE) . '.tmp';
			if ( ! @touch($path) )
			{
				return FALSE;
			}
			unlink($path);
			return TRUE;
		}
		else
		{
			$fp = @fopen($path, 'wb');
			if ( ! $fp )
			{
				return FALSE;
			}
			fclose($fp);
			return TRUE;
		}
	}
}


// ---------------------------------------------------------------


if ( ! function_exists('trail_slash') )
{
	/**
	 * always trails slash rightside
	 * @param  $path
	 * @return string
	 */
	function trail_slash($path)
	{
		return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	}
}


// ---------------------------------------------------------------


if ( ! function_exists('json_encode') )
{
	/**
	 * Compatible json_encode function on PHP 5.2.0 or lower
	 * 
	 * @param mixed $json
	 */
	function json_encode($json)
	{
		return JSON::encode($json);
	}
}


// ---------------------------------------------------------------


if ( ! function_exists('json_decode') )
{
	/**
	 * Compatible json_decode function on PHP 5.2.0 or lower
	 * 
	 * @param mixed $json
	 */
	function json_decode($jsonStr, $assoc = FALSE)
	{
		return JSON::decode($jsonStr, $assoc);
	}
}


// ---------------------------------------------------------------


// Framework uses PHP5.3 or newer function
if ( ! function_exists('lcfirst') )
{
	/**
	 * Compatible less than PHP5.3
	 * 
	 * @param string $str
	 */
	function lcfirst($str)
	{
		return strtolower($str[0]) . substr($str, 1);
	}
}

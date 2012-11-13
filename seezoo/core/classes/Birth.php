<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Birth singleton / re-create instance
 * 
 * @package  Seezoo-Framework
 * @category Classes
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Birth
{
	/**
	 * Stack singleton instance
	 * @var object
	 */
	private static $_birthClassInstance;
	
	
	/**
	 * Stack Facke Late Static Binding class name
	 * @var string
	 */
	private static $_birthClassName;
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Create singleton instance
	 * 
	 * @access public static
	 * @return object
	 */
	public static function birth()
	{
		if ( ! self::$_birthClassInstance )
		{
			$isEnableLSB = version_compare(PHP_VERSION, '5.3.0', '>=');
			self::$_birthClassInstance = ( $isEnableLSB )
			                               ? new static()
			                               : new self::$_birthClassName();
		}
		
		return self::$_birthClassInstance;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Re-create class instance
	 * 
	 * @access public static
	 * @param  bool $replace
	 * @return object
	 */
	public static function rebirth($replace = FALSE)
	{
		$isEnableLSB = version_compare(PHP_VERSION, '5.3.0', '>=');
		$instance = ( $isEnableLSB )
		              ? new static()
		              : new self::$_birthClassName();
		
		if ( $replace )
		{
			self::$_birthClassInstance = $instance;
		}
		return $instance;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Set fake Late Static Binding class name
	 * 
	 * @access public static
	 * @param  string $className
	 */
	public static function setBirthClass($className)
	{
		self::$_birthClassName = $className;
	}
}

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

class SeezooBase
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
	
	
	public function __destruct()
	{
		$this->heaven();
	}
	
	
	// ---------------------------------------------------------------
	
	
	
	/**
	 * Dog is trying to go to heaven...
	 * 
	 * @access public
	 * 
	 */
	public function heaven()
	{
		// Have you ever done anything left?
	}

	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Create singleton instance
	 * 
	 * @access public static
	 * @return object
	 */
	public static function grow()
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
	public static function birth($isClone = FALSE)
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
	public static function birthOf($className)
	{
		self::$_birthClassName = $className;
	}
}

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
	 * Create new class instance
	 * 
	 * @access public static
	 * @return object
	 */
	public static function birth()
	{
		$isEnableLSB = version_compare(PHP_VERSION, '5.3.0', '>=');
		return ( $isEnableLSB )
		         ? new static()
		         : new self::$_birthClassName();
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Re-create class instance ( cloned )
	 * 
	 * @access public static
	 * @return object
	 */
	public static function rebirth()
	{
		return ( self::$_birthClassInstance )
		         ? clone self::$_birthClassInstance
		         : self::birth();
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

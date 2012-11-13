<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Dependecy injection management class
 * 
 * @package  Seezoo-Framework
 * @category System
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */
 
class DI
{
	/**
	 * Stack instance
	 * @var object
	 */
	private $instance;
	
	/**
	 * Stack class name
	 * @var string
	 */
	private $className;
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * create DI wrapper
	 * 
	 * @access pubic static
	 * @param  object $instance
	 * @return DI
	 */
	public static function make($instance)
	{
		return new DI($instance);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * constructor
	 * 
	 * @access private
	 * @param  object $instance
	 */
	private function __construct($instance)
	{
		$this->instance = $instance;
		$this->className = get_class($instance);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Inject class
	 * 
	 * @access public
	 * @param  mixed $methodName
	 * @param  mixed $diInstance
	 * @return $this
	 */
	public function inject($methodName = '', $diInstance = NULL)
	{
		// Does first argument an array?
		if ( is_array($methodName) )
		{
			// Inject key:value
			$key = key($methodName);
			if ( ! property_exists($this->instance, $key) )
			{
				$this->instance->{$key} = $methodName[$key];
			}
			return $this;
		}
		// Does second argument is instance?
		else if ( is_object($diInstance) )
		{
			// Inject firstarg:secondarg
			if ( ! property_exists($this->instance, $methodName) )
			{
				$this->instance->{$methodName} = $diInstance;
			}
			return $this;
		}
		
		// If no argument supplied, inject from class annotation.
		if ( $methodName === '' )
		{
			$ref = new ReflectionClass($this->instance);
			$docc = $ref->getDocComment();
		}
		// Else, inject from class-method annotation.
		else
		{
			if ( ! method_exists($this->instance, $methodName) )
			{
				throw new LogicException($this->className . ' class doesn\'t have method:' . $methodName);
			}
			$ref = new ReflectionMethod($this->instance, $methodName);
			$docc = $ref->getDocComment();
		}
		
		$docs = $this->_parseAnnotation($docc);
		
		foreach ( array('model', 'library', 'helper', 'class') as $di )
		{
			if ( ! isset($docs['di:' . $di]) )
			{
				continue;
			}
			foreach ( explode(',', $docs['di:' . $di]) as $module )
			{
				$mod = lcfirst($module);
				if ( ! property_exists($this->instance, $mod) )
				{
					$this->instance->{$mod} = Seezoo::$Importer->{$di}($module);
				}
			}
		}
		
		return $this;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get wrapper instance
	 * 
	 * @access public
	 * @return $instance
	 */
	public function getInstance()
	{
		return $this->instance;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Parse annotation comment
	 * 
	 * @access private
	 * @param  string $docc
	 * @return array
	 */
	private function _parseAnnotation($docc)
	{
		$ret  = array();
		if ( preg_match_all('/@(.+)/u', $docc, $matches) )
		{
			foreach ( $matches[1] as $line )
			{
				list($key, $value) = explode(' ', $line, 2);
				$ret[$key] = $value;
			}
		}
		return array_change_key_case($ret, CASE_LOWER);
		
	}
}

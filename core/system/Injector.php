<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Dependency injection Management Class
 * 
 * @package  Seezoo-Framework
 * @category System
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */
 
class Injector
{
	
	/**
	 * Inject method detect and execute
	 * 
	 * @access public static
	 * @param  object $instance
	 * @return object $instance
	 */
	public static function inject(&$instance)
	{
		$ref = new ReflectionClass($instance);
		foreach ( $ref->getMethods() as $method )
		{
			// Process "inject" prefixed method only
			if ( strpos($method->name, 'inject') !== 0 )
			{
				continue;
			}
			
			$injections = array();
			foreach ( $method->getParameters() as $param )
			{
				// Get type-hinting classname
				$className = $param->getClass()->getName();
				if ( empty($className) )
				{
					continue;
				}
				
				// Remove prefix
				$npClass = Seezoo::removePrefix($className);
				
				// Does cache exists?
				if ( FALSE === ($inject = Seezoo::getSingleton($npClass)) )
				{
					if ( ! class_exists($className) )
					{
						throw new UndefinedClassException('Class ' . $className . ' is undefined.');
					}
					
					$inject = new $className();
					// If inject object implments Growable interface, extend it
					if ( $inject instanceof Growable )
					{
						$inject = call_user_func(array($inject, 'grow'));
					}
					
					if ( $inject instanceof Singleton )
					{
						Seezoo::addSingleton($npClass, $inject);
					}
				}
				$injections[] = $inject;
			}
			// Execute injection
			$method->invokeArgs($instance, $injections);
		}
		return $instance;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Inject class
	 * 
	 * @access public static
	 * @param  object $instance
	 * @param  mixed $methodName
	 * @return $instance
	 */
	public static function injectByAnnotation(&$instance, $methodName = '')
	{
		// If no argument supplied, inject from class annotation.
		if ( $methodName === '' )
		{
			$ref = new ReflectionClass($instance);
			$docc = $ref->getDocComment();
		}
		// Else, inject from classmethod annotation.
		else
		{
			if ( ! method_exists($instance, $methodName) )
			{
				throw new LogicException(get_class($instance) . ' class doesn\'t have method:' . $methodName);
			}
			$ref = new ReflectionMethod($instance, $methodName);
			$docc = $ref->getDocComment();
		}
		
		$docs = self::parseAnnotation($docc);
		
		foreach ( array('model', 'library', 'helper', 'class') as $di )
		{
			if ( ! isset($docs['di:' . $di]) )
			{
				continue;
			}
			foreach ( explode(',', $docs['di:' . $di]) as $module )
			{
				$module = trim($module);
				$mod = lcfirst($module);
				if ( ! property_exists($instance, $mod) )
				{
					$instance->{$mod} = Seezoo::$Importer->{$di}($module);
				}
			}
		}
		return $instance;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Parse annotation comment
	 * 
	 * @access private static
	 * @param  string $docc
	 * @return array
	 */
	private static function parseAnnotation($docc)
	{
		$ret  = array();
		if ( preg_match_all('/@(.+)/u', $docc, $matches, PREG_PATTERN_ORDER) )
		{
			foreach ( $matches[1] as $line )
			{
				list($key, $value) = explode(' ', trim($line), 2);
				$ret[$key] = $value;
			}
		}
		return array_change_key_case($ret, CASE_LOWER);
		
	}
}

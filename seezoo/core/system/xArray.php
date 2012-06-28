<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Array supplies object syntax
 * 
 * @package  Seezoo-Framework
 * @category System
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class xArray implements ArrayAccess
{
	/**
	 * Array stack
	 * @var array
	 */
	private $_ary;
	
	
	/**
	 * processed last value
	 * @var mixed
	 */
	private $_lastValue;
	
	
	public function __construct()
	{
		$args = func_get_args();
		$nums = func_num_args();
		if ( $nums === 1 && is_array($args[0]) )
		{
			$this->_ary = $ary;
		}
		else
		{
			$ary = array();
			foreach ( $args as $arg )
			{
				$ary[] = $arg;
			}
			$this->_ary = $ary;
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get the array
	 * 
	 * @access public
	 * @param int $num
	 */
	public function get($num = FALSE)
	{
		if ( is_int($num) || is_string($num) )
		{
			return ( isset($this->_ary[$num]) ) ? $this->_ary[$num] : null;
		}
		return $this->_ary;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * get the last return value
	 * 
	 * @access public
	 * @return mixed
	 */
	public function value()
	{
		return $this->_lastValue;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Abstract declare offsetSet
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value)
	{
		if ( is_null($offset) )
		{
			$this->_ary[] = $value;
		}
		else
		{
			$this->_ary[$offset] = $value;
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Abstract declare offsetExists
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset)
	{
		return isset($this->_ary[$offset]);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Abstract declare offsetUnset
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset)
	{
		unset($this->_ary[$offset]);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Abstract declare offsetGet
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset)
	{
		return ( isset($this->_ary[$offset]) ) ? $this->_ary[$offset] : null;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Method overload
	 * 
	 * mapping call method name to "array_" prefixed function
	 */
	public function __call($method, $argments = array())
	{
		if ( method_exists($this, $method) )
		{
			$this->_lastValue = call_user_func_array(array($this, $method), $argments);
			return $this;
		}
		else if ( function_exists('array_' . $method) )
		{
			$function   = 'array_' . $method;
			$reflection = new ReflectionFunction($function);
			$arg_nums   = $reflection->getParameters();
			
			// Does function need reference array?
			if ( isset($arg_nums[0]) && $arg_nums[0]->isPassedByReference() === TRUE )
			{
				$arg[] =& $this->_ary;
			}
			else
			{
				$arg[] = $this->_ary;
			}
			$cnt = 1;
			foreach ( $argments as $a )
			{
				if ( $cnt === $arg_nums )
				{
					break;
				}
				$arg[] = $a;
			}
			$this->_lastValue = call_user_func_array($function, $arg);
			if ( is_array($this->_lastValue) )
			{
				$this->_ary = $this->_lastValue;
			}
			return $this;
			
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * toString
	 */
	public function __toString()
	{
		return $this->_ary;
	}
}
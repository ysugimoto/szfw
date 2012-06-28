<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Reguler Exception wrapper like JavaScript
 * 
 * @package  Seezoo-Framework
 * @category System
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class RegExp
{
	/**
	 * Matching flag
	 * @var string
	 */
	protected $flag;
	
	/**
	 * Matching pattern
	 * @var string
	 */
	protected $patttern;
	
	/**
	 * Pattern format template
	 * @var string
	 */
	private $patternFormat = '/%s/%s';
	
	
	public function __construct($pattern = '', $flag = '')
	{
		if ( $pattern === '' )
		{
			throw new InvalidArgumentException('Pattern must not be empty.');
		}

		$this->pattern = $pattern;
		$this->flag	= $flag;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Matching test
	 * 
	 * @access public
	 * @param string
	 * @return bool
	 */
	public function test($str)
	{
		$pattern = str_replace('/', '\/', $this->pattern);
		return (bool)preg_match(sprintf($this->patternFormat, $pattern, $this->flag), $str);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Matching execute
	 * 
	 * @access public
	 * @param string
	 * @return mixed
	 */
	public function exec($str)
	{
		$pattern = str_replace('/', '\/', $this->pattern);
		preg_match_all(
			sprintf($this->patternFormat, $pattern, $this->flag),
			$str,
			$matches,
			PREG_SET_ORDER
		);

		if ( ! $maches )
		{
			return FALSE;
		}
		return ( strpos($this->flag, 'g') !== FALSE ) ? new _RegExpGlobals($matches) : NULL;
	}
}

/**
 * ---------------------------------------------------------------
 * Global Regex handler class
 * ---------------------------------------------------------------
 * */
class _RegExpGlobals implements ArrayAccess
{
	/**
	 * Matching result stack
	 * @var array
	 */
	private $_match;
	
	/**
	 * Matchin index
	 * @var int
	 */
	public $lastIndex = 0;
	
	
	public function __construct($matches)
	{
		$this->_match = $matches;
		$this->lastIndex = 0;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * ArrayAccess abstract
	 * @see ArrayAccess::offsetySet
	 */
	public function offsetSet($offset, $value) {}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * ArrayAccess abstract
	 * @see ArrayAccess::offsetGet
	 */
	public function offsetGet($index)
	{
		$ary = $this->_match($this->lastIndex);
		return ( isset($ary[$index]) ) ? $ary[$index] : NULL;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * ArrayAccess abstruct
	 * @see ArrayAccess::offsetExists
	 */
	public function offsetExists($index)
	{
		$ary = $this->_match[$this->lastIndex];
		return isset($ary[$index]);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * ArrayAccess abstruct
	 * @see ArrayAccess::offsetUnset
	 */
	public function offsetUnset($index) {}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Continue execute
	 * 
	 * @access public
	 * @return this
	 */
	public function exec()
	{
		++$this->lastIndex;
		return $this;
	}
}

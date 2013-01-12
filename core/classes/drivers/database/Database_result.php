<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * Database Result wrapper class ( PDO driver )
 * 
 * @package  Seezoo-Framework
 * @category Drivers
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Database_result implements Iterator, ArrayAccess
{
	/**
	 * PDOStatement
	 * @var PDOStatement
	 */
	protected $_stmt;
	
	protected $_pointer;
	protected $_fetchMode;
	protected $_currentResult;
	protected $_resultCache = array();
	
	protected $_resultArray;
	protected $_resultObject;
	
	
	public function __construct($statement)
	{
		$this->_stmt      = $statement;
		$this->_pointer   = 0;
		$this->_fetchMode = PDO::FETCH_OBJ;
		$this->_resultCache = array(PDO::FETCH_OBJ => array(), PDO::FETCH_ASSOC => array());
	}
	
	
	// Iterator need implement methods ===========
	
	public function rewind()
	{
		$this->_pointer = 0;
	}
	
	public function current()
	{
		return $this->_resultCache[$this->_fetchMode][$this->_pointer];
	}
	
	public function key()
	{
		return $this->_pointer;
	}
	
	public function next()
	{
		++$this->_pointer;
	}
	
	public function valid()
	{
		if ( ! isset($this->_resultCache[$this->_fetchMode][$this->_pointer]) )
		{
			if ( FALSE === ($row = $this->_stmt->fetch($this->_fetchMode, PDO::FETCH_ORI_ABS, $this->_pointer)) )
			{
				return FALSE;
			}
			$this->_resultCache[$this->_fetchMode][$this->_pointer] = $row;
		}
		return TRUE;
	}
	
	// ArrayAccess need implement methods ========
	
	public function offsetExists($offset)
	{
		if ( ! isset($this->_resultCache[$this->_fetchMode][$offset]) )
		{
			$this->_resultCache[$this->_fetchMode][$offset] = $this->_stmt->fetch($this->_fetchMode, PDO::FETCH_ORI_ABS, $offset);
		}
		return (bool)$this->_resultCache[$this->_fetchMode][$offset];
	}
	
	public function offsetGet($offset)
	{
		if ( ! isset($this->_resultCache[$this->_fetchMode][$offset]) )
		{
			$this->_resultCache[$this->_fetchMode][$offset] = $this->_stmt->fetch($this->_fetchMode, PDO::FETCH_ORI_ABS, $offset);
		}
		return $this->_resultCache[$this->_fetchMode][$offset];
	}
	
	public function offsetSet($offset, $value)
	{
		// nothing to do.
	}
	
	public function offsetUnset($offset)
	{
		// nothing to do.
	}
	
	public function fetchArray()
	{
		$this->_fetchMode = PDO::FETCH_ASSOC;
		return $this;
	}
	
	public function fetchObject()
	{
		$this->_fetchMode = PDO::FETCH_OBJ;
		return $this;
	}
	
	public function fetchNum()
	{
		$this->_fetchMode = PDO::FETCH_NUM;
		return $this;
	}
	
	// --------------------------------------------------
	
	
	/**
	 * Get native PDOStatement object
	 * 
	 * @access public
	 * @return object PDOStatement
	 */
	public function get()
	{
		return $this->_stmt;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * get result objects
	 * 
	 * @access public
	 * @return array
	 */
	public function result()
	{
		if ( ! $this->_resultObject )
		{
			// rewind cursor
			if ( FALSE != ($first = $this->_stmt->fetch(PDO::FETCH_OBJ, PDO::FETCH_ORI_ABS, 0)) )
			{
				$this->_resultObject = $this->_stmt->fetchAll(PDO::FETCH_OBJ);
				array_unshift($this->_resultObject, $first);
			}
			else
			{
				$this->_resultObject = array();
			}
			$this->_resultCache[PDO::FETCH_OBJ] = $this->_resultObject;
		}
		return $this->_resultObject;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * get result array
	 * 
	 * @access public
	 * @return array
	 */
	public function resultArray()
	{
		if ( ! $this->_resultArray )
		{
			// rewind cursor
			if ( FALSE != ($first = $this->_stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, 0)) )
			{
				$this->_resultArray = $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
				array_unshift($this->_resultArray, $first);
			}
			else
			{
				$this->_resultArray = array();
			}
			$this->_resultCache[PDO::FETCH_ASSOC] = $this->_resultArray;
		}
		return $this->_resultArray;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * get result row count
	 * 
	 * @access public
	 * @return int
	 */
	public function numRows()
	{
		return count($this->result());
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * get single result object
	 * 
	 * @access public
	 * @param  int $index
	 * @return object
	 */
	public function row($index = 0)
	{
		$defMode = $this->_fetchMode;
		$this->fetchObject();
		$dat = $this[$index];
		$this->_fetchMode = $defMode;
		return $dat;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * get single result array
	 * 
	 * @access public
	 * @param  int $index
	 * @return array
	 */
	public function rowArray($index = 0)
	{
		$defMode = $this->_fetchMode;
		$this->fetchArray();
		$dat = $this[$index];
		$this->_fetchMode = $defMode;
		return $dat;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * get SQL affected rows
	 * 
	 * @access public
	 * @return int
	 */
	public function affectedRows()
	{
		return $this->_stmt->rowCount();
	}
}
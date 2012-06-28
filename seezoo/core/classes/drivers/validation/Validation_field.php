<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Validation Field Driver
 * 
 * @required seezoo/core/classes/Verify or extended
 * 
 * @package  Seezoo-Framework
 * @category Drivers
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Validation_Field
{
	/**
	 * Field name
	 * @var string
	 */
	protected $_name;
	
	
	/**
	 * Field label
	 * @var string
	 */
	protected $_label;
	
	
	/**
	 * Field value
	 * @var string
	 */
	protected $_value = FALSE;
	
	
	/**
	 * "Validated" Field value
	 * @var string
	 */
	protected $_validatedValue = '';
	
	/**
	 * Validate Rules set
	 * @var array
	 */
	protected $_rules    = array();
	
	
	/**
	 * Error messages
	 * @var array
	 */
	protected $_messages = array();
	
	
	
	/**
	 * Rule reguler exception
	 * @var string
	 */
	protected $_paramRegex = '/\A(.+)\[([^\]]+)\]\Z/u';
	protected $_origRegex  = '/\Aorig:([^\[]+)\[?(.+)?\]?\Z/u';
	
	
	
	public function __construct($fieldName, $label)
	{
		$this->_name  = $fieldName;
		$this->_label = $label;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Filed name getter
	 * 
	 * @access public
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Filed label name getter
	 * 
	 * @access public
	 * @return string
	 */
	public function getLabel()
	{
		return $this->_label;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Filed value setter
	 * 
	 * @access public
	 * @param  mixed $value
	 * @return string
	 */
	public function setValue($value)
	{
		$this->_value = $value;
		return $this;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Validated value setter
	 * 
	 * @access public
	 * @param  mixed $value
	 * @return string
	 */
	public function setValidatedValue($value)
	{
		$this->_validatedValue = $value;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Filed value getter
	 * 
	 * @access public
	 * @param  bool $escape
	 * @return string
	 */
	public function getValue($escape = FALSE)
	{
		return ( $escape ) ? prep_str($this->_value) : $this->_value;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Validated value getter
	 * 
	 * @access public
	 * @param  bool $escape
	 * @return string
	 */
	public function getValidatedValue($escape = FALSE)
	{
		return ( $escape ) ? prep_str($this->_validatedValue) : $this->_validatedValue;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Set validate rule
	 * 
	 * @access public
	 * @param  mixed $rules
	 * @return $this
	 */
	public function setRules($rules = '')
	{
		if ( is_array($rules) )
		{
			$this->_rules = $rules;
		}
		else
		{
			foreach ( explode('|', $rules) as $rule )
			{
				if ( ! in_array($rule, $this->_rules) )
				{
					$this->_rules[] = $rule;
				}
			}
		}
		return $this;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Validate rules getter
	 * 
	 * @access public
	 * @return array
	 */
	public function getRules()
	{
		return $this->_rules;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Validate Error message setter
	 * 
	 * @access public
	 * @param  string $msg
	 */
	public function setMessage($msg)
	{
		$this->_messages[] = $msg;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Message getter
	 * 
	 * @access public
	 * @param  bool $all
	 * @param  string $leftDelimiter
	 * @param  string $rightDelimiter
	 * @return string
	 */
	public function getMessage($all = TRUE, $leftDelimiter = '', $rightDelimiter = '')
	{
		if ( count($this->_messages) === 0 )
		{
			return '';
		}
		$ret = '';
		foreach ( $this->_messages as $msg )
		{
			$ret .= $leftDelimiter . $msg . $rightDelimiter;
			if ( ! $all )
			{
				break;
			}
		}
		
		return $ret;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Single validate execute
	 * 
	 * @access public
	 * @param  mixed $value
	 * @return bool
	 */
	public function exec($value)
	{
		// validate value is array?
		$is_array = ( is_array($value) ) ? TRUE : FALSE;
		$value    = ( $is_array ) ? $value : array($value);
		// load the Verication library
		$verify   = Seezoo::$Importer->library('Verify');
		$success  = TRUE;
		
		$this->_value = $value;
		
		// loop and validate
		foreach ( $this->_rules as $rule )
		{
			if ( $rule === '' )
			{
				continue;
			}
			$class = $verify;
			// rule has condition parameter?
			if ( preg_match($this->_paramRegex, $rule, $matches) )
			{
				list(, $rule, $condition) = $matches;
			}
			// elseif, rule-method declared by Controller or Process instance?
			else if ( preg_match($this->_origRegex, $rule, $matches) )
			{
				$class     = Seezoo::getInstance();
				$rule      = $matches[1];
				$condition = ( isset($matches[2]) ) ? $matches[2] : FALSE;
			}
			else
			{
				// rule have not condition
				$condition = FALSE;
			}
			
			// rule-method really exists?
			if ( ! method_exists($class, $rule) )
			{
				throw new Exception('Undefined ' . $rule . ' rules method in ' . get_class($class) . '!');
				return FALSE;
			}
			
			// value loop and rule-method execute!
			foreach ( $value as $key => $val )
			{
				$result = $class->{$rule}($value, $condition);
				// If method returns boolean (TRUE/FALSE), validate error/success.
				if ( is_bool($result) )
				{
					if ( $result === FALSE )
					{
						if ( ! isset($this->_messages[$rule]) )
						{
							throw new Exception('Undefined Validation message of ' . $rule);
							return FALSE;
						}
						$msg = ( $condition !== FALSE )
						         ? sprintf($verify->_messages[$rule], $this->_label)
						         : sprintf($verify->_messages[$rule], $this->_label, $condition);
						$this->setMessage($msg);
						// switch down flag
						$success = FALSE;
					}
				}
				// else, method returns processed value
				else
				{
					$value[$key] = $result;
				}
			}
		}
		
		// set "validated" value ( maybe same value... )
		$this->setValidatedValue(( $is_array ) ? $value : $value[0]);
		
		// return TRUE(success) / FALSE(failed)
		return $success;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * get Hidden input formatted string
	 * 
	 * @access public
	 * @return string
	 */
	public function getHidden()
	{
		return '<input type="hidden" name="' . $this->getName() . '" value="' . $this->getValue(TRUE) . '" />' . "\n";
	}
}
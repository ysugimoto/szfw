<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Command line action utility driver
 * 
 * @package  Seezoo-Framework
 * @category Classes
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Command_driver
{
	/**
	 * Make Controller template
	 * 
	 * @access public
	 * @param  string $controller
	 * @return string $template
	 */
	public function getControllerTemplate($controller)
	{
		$template = <<<END
<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

class {$controller}Controller extends SZ_Breeder
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		// write some logic.
	}
}
END;
		return $template;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Make Model template
	 * 
	 * @access public
	 * @param  string $model
	 * @return string $template
	 */
	public function getModelTemplate($model)
	{
		$template = <<<END
<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

class {$model}Model extends SZ_Kennel
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function foo()
	{
		// Make some method.
	}
}
		
END;
		return $template;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Make ActiveRecord class definition
	 * 
	 * @access public
	 * @param  string $table
	 * @param  array $fields
	 * @return string $template
	 */
	public function getActiveRecordTemplate($table, $fields)
	{
		$class = ucfirst($table);
		$template = <<<END
<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

class {$class}ActiveRecord extends SZ_ActiveRecord
{
	protected \$_table   = '{$table}';
	protected \$_primary = 'PRIMARY_FIELD';
	protected \$_schemas = array(
SCHEMAS_DIFINITION
	); 
	
VALIDATE_METHODS
}

END;
		$schemas = array();
		$methods = array();
		$primary = '';
		$maxLen  = 0;
		foreach ( $fields as $field )
		{
			$line = "\t\t'{$field->field}' => array('type' => '{$field->type}')";
			$schemas[] = $line;
			$f = $this->_toCamelCase($field->field);
			$methods[] = "\tpublic function isValid{$f}(\$value) {\n\t\treturn TRUE;\n\t}\n";
			if ( $field->key === TRUE )
			{
				$primary = $field->field;
			}
		}
		
		return str_replace(
			array('PRIMARY_FIELD', 'SCHEMAS_DIFINITION', 'VALIDATE_METHODS'),
			array($primary, implode(",\n", $schemas), implode("\n\n", $methods)),
			$template
		);
	}
	
	
	protected function _toCamelCase($field)
	{
		$field = preg_replace_callback(
								'/_([a-zA-Z])/',
								create_function('$m', 'return strtoupper($m[1]);'),
								$field
							);
		return ucfirst($field);
	}
	
	// --------------------------------------------------
	
	
	/**
	 * Easter egg
	 * 
	 * @access public
	 * @return AA
	 */
	public function easterEgg()
	{
		$aa = <<<END
  ＿＿＿
 u|'A `|u ようかんワンワンだー
　|＿＿|
　|＿＿|
　|＿＿|
　|  ￤|﻿
END;
		return $aa . PHP_EOL . PHP_EOL;
	}
}

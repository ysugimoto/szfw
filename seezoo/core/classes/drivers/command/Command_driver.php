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
}

END;
		$schemas = array();
		$primary = '';
		$maxLen  = 0;
		foreach ( $fields as $field )
		{
			if ( FALSE !== ($point = strpos($field->Type, '(')) )
			{
				$type = strtoupper(substr($field->Type, 0, $point));
				$size = (int)rtrim(substr($field->Type, ++$point), ')');
			}
			else
			{
				$type = strtoupper($field->Type);
				$size = NULL;
			}
			if ( $field->Key === 'PRI' && empty($primary) )
			{
				$primary = $field->Field;
			}
			$line = "\t\t'{$field->Field}' => array('type' => '{$type}'";
			if ( $size )
			{
				$line .= ", 'size' => {$size}";
			}
			$line .= ')';
			$schemas[] = $line;
		}
		
		return str_replace(
			array('PRIMARY_FIELD', 'SCHEMAS_DIFINITION'),
			array($primary, implode(",\n", $schemas)),
			$template
		);
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

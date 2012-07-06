<?php if ( ! defined('SZ_EXEC') OR  PHP_SAPI !== 'cli' ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Command line action dispatcher class
 * 
 * @package  Seezoo-Framework
 * @category Classes
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */
 
class SZ_Dog extends SZ_Driver
{
	/**
	 * console arguments
	 * @var array
	 */
	protected $_argv;
	
	
	public function __construct()
	{
		$this->_argv  = $_SERVER['argv'];
		$this->driver = $this->_loadDriver('command', '', TRUE);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Execute command line action
	 * 
	 * @access public
	 */
	public function executeCommandLine()
	{
		if ( ! isset($this->_argv[1]) )
		{
			$this->_showUsage();
			exit;
		}
		
		switch ( $this->_argv[1] )
		{
			// easter egg :-)
			case 'bite':
				echo $this->driver->easterEgg();
				break;
			case 'modeltest':
				$this->_modelTest();
				break;
			case 'libtest':
				$this->_libTest();
				break;
			case 'generate':
				$this->_generate();
				break;
			default:
				$this->_showUsage();
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Show tool's usage
	 * 
	 * @access protected
	 */
	protected function _showUsage()
	{
		echo '============================================' . PHP_EOL;
		echo '  SZFW Commnad Line Tool ver ' . SZFW_VERSION . PHP_EOL;
		echo '============================================' . PHP_EOL;
		echo 'usage: ' . PHP_EOL;
		echo '  Testing Model  : ./dog modeltest [model-name]' . PHP_EOL;
		echo '  Class generate : ./dog generate' . PHP_EOL . PHP_EOL;
		exit;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Testing model
	 * 
	 * @access protected
	 */
	protected function _modelTest()
	{
		echo 'SZFW Model Testing...' . PHP_EOL;
		// change current directory
		chdir(SZPATH);
		if ( isset($this->_argv[2]) )
		{
			$module = preg_replace('/Test$/', '', $this->_argv[2]) . 'Test';
			echo shell_exec('phpunit ' . $module . ' ' . SZPATH . 'tests/models/' . $module . '.php');
		}
		else
		{
			echo shell_exec('phpunit ' . SZPATH . 'tests/models/');
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Generate some classes
	 * 
	 * @access protected
	 */
	protected function _generate()
	{
		// stop output buffering in PHP5.2.x or lower
		if ( version_compare(PHP_VERSION, '5.3', '<') )
		{
			@ob_end_clean();
		}
		echo 'Generate file tool.' . PHP_EOL;
		echo 'Type genetate program:' . PHP_EOL . PHP_EOL;
		echo '[1] Controller' . PHP_EOL;
		echo '[2] Model' . PHP_EOL;
		echo '[3] Both (Controller and Model)' . PHP_EOL;
		echo '[4] ActiveRecords' . PHP_EOL . PHP_EOL;
		echo '[0] Exit' . PHP_EOL . PHP_EOL;
		
		// Create file choose input
		do
		{
			echo ':';
			$type = fgets(STDIN, 10);
			$type = trim($type, "\n");
			if ( ctype_digit($type) )
			{
				if ( $type >= 0 && 5 > $type )
				{
					break;
				}
				else
				{
					echo 'Please Type displayed numbers.' . PHP_EOL;
				}
			}
			else
			{
				echo 'Invalid input. Please Type displayed numbers.' . PHP_EOL;
			}
		}
		while ( 1 );
		
		if ( $type == 0 )
		{
			echo 'Aborted.' . PHP_EOL;
			return;
		}
		
		if ( $type < 4 )
		{
			echo 'Type a Class name:' . PHP_EOL;
			
			// Input class name
			do
			{
				echo ':';
				$name = fgets(STDIN, 20);
				$name = trim($name, "\n");
				if ( preg_match('/^[a-zA-Z][a-zA-Z0-9_]+$/', $name) )
				{
					break;
				}
				else
				{
					echo 'Invalid input. Class name must be alphabet/number/underscore chars only.' . PHP_EOL;
				}
			}
			while ( 1 );
		}
		
		$createFiles = array();
		// switch file types
		switch ( $type )
		{
			// Create Controller only
			case 1:
				$createFiles[] = array(
									APPPATH . 'classes/controllers/' . lcfirst($name) . '.php',
									$this->driver->getControllerTemplate(ucfirst($name)),
									'Controller'
								);
				break;
			// Create Model only
			case 2:
				$createFiles[] = array(
									APPPATH . 'classes/models/' . ucfirst($name) . 'Model.php',
									$this->driver->getModelTemplate(ucfirst($name)),
									'Model'
								);
				break;
			// Create Controller and Model
			case 3:
				$createFiles[] = array(
									APPPATH . 'classes/controllers/' . $name . '.php',
									$this->driver->getControllerTemplate(ucfirst($name)),
									'Controller'
								);
				$createFiles[] = array(
									APPPATH . 'classes/models/' . ucfirst($name) . 'Model.php',
									$this->driver->getModelTemplate(ucfirst($name)),
									'Model'
								);
				break;
			// Create ActiveRecord classes
			case 4:
				$createFiles = $this->_getActiveRecordTargetFiles();
		}
		
		if ( count($createFiles) > 0 )
		{
			foreach ( $createFiles as $files )
			{
				list($path, $template, $class) = $files;
				$isWrite = TRUE;
				echo 'Create: ' . $path . PHP_EOL;
				if ( file_exists($path) )
				{
					// confirm overwrite
					echo $path . ' is already exists.' . PHP_EOL;
					echo 'Are you sure you want to overwrite it? [y/n]';
					do
					{
						echo ':';
						$input = fgets(STDIN, 10);
						$input = trim($input, "\n");
						if ( $input === 'y' || $input === 'yes' )
						{
							echo 'Overwriting.' . PHP_EOL;
							$isWrite = TRUE;
							break;
						}
						else if ( $input === 'n' || $input === 'no' )
						{
							echo 'Skipped.' . PHP_EOL;
							$isWrite = FALSE;
							break;
						}
						else
						{
							echo 'Please type y or n.' . PHP_EOL;
						}
					}
					while ( 1 );
				}
				if ( $isWrite === TRUE )
				{
					$fp = fopen($path, 'wb');
					flock($fp, LOCK_EX);
					fwrite($fp, $template);
					flock($fp, LOCK_UN);
					fclose($fp);
				}
			}
			echo 'Finished!' . PHP_EOL;
			return;
		}
		echo 'Nothing to do.' . PHP_EOL;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get ActiveRecord enables table from DB
	 * 
	 * @access protected
	 * @return array
	 */
	protected function _getActiveRecordTargetFiles()
	{
		$db      = Seezoo::$Importer->database();
		$tables  = $db->tables();
		$schemas = array();
		foreach ( $tables as $table )
		{
			$fields    = $db->fields($table);
			$schemas[] = array(
				APPPATH . 'classes/activerecords/' . $this->driver->toCamelCase($table) . '.php',
				$this->driver->getActiveRecordTemplate($table, $fields),
				ucfirst($table) . 'ActiveRecord'
			);
		}
		return $schemas;
	}
}

<?php if ( ! defined('SZ_EXEC') ) exit('access denied.');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * ------------------------------------------------------------------
 * 
 * Process Base Class
 * 
 * @package  Seezoo-Framework
 * @category system
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class Process
{
	public $mode;
	public $request;
	public $env;
	public $import;
	public $response;
	public $view;
	public $level;
	public $router;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$process     = SeezooFactory::getProcess();
		$this->level = SeezooFactory::sub($this);
		
		// Process level matching
		if ( $this->level !== $process->level )
		{
			throw new RuntimeException('Illigal process number! Direct instantiate is disabled.');
		}
		
		$this->mode     =  $process->mode;
		$this->request  =  Seezoo::getRequest();
		$this->env      =  Seezoo::getENV();
		$this->import   =  Seezoo::$Importer->classes('Importer');
		$this->response =& Seezoo::$Response;
		$this->view     =  new Seezoo::$Classes['View']();
		$this->router   =  $process->router;
		
		$this->_extractAlias();
		
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Extract stacked property-alias
	 * 
	 * @access private
	 */
	private function _extractAlias()
	{
		$aliases = Seezoo::getAliases();
		foreach ( $aliases as $alias => $prop )
		{
			if ( is_string($prop) )
			{
				if ( isset($this->{$prop}) )
				{
					$this->{$alias} = $this->{$prop};
				}
			}
			else
			{
				$this->{$alias} = $prop;
			}
		}
	}
}

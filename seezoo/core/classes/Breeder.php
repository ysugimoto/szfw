<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * MVC base Controller class
 * 
 * @package  Seezoo-Framework
 * @category Classes
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Breeder extends Process
{
	/**
	 * Request instance
	 * @var Request
	 */
	public $req;
	
	
	/**
	 * Environment instance
	 * @var Environment
	 */
	public $env;
	
	
	/**
	 * View class instance
	 * @var View
	 */
	public $view;
	
	
	/**
	 * Flow methods
	 * @var Flow
	 */
	protected $flows = array();
	
	/**
	 * Routed info object
	 * @var object
	 */
	public $router;
	
	
	/**
	 * Importer class instance
	 * @var Import
	 */
	public $import;
	
	
	/**
	 * Lead instance
	 * @var Lead
	 */
	public $lead;
	
	
	public function __construct()
	{
		parent::__construct();
		$this->_execAutoImport();
		$this->_attachLead();
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * auto import the libraries from config settings or autoImport property
	 * 
	 * @access protected
	 * @return void
	 */
	protected function _execAutoImport()
	{
		$autoloadDatabase = $this->env->getConfig('autoload_database');
		$autoloadLibrary  = $this->env->getConfig('autoload_library');
		$autoloadModel    = $this->env->getConfig('autoload_model');
		$autoloadHelper   = $this->env->getConfig('autoload_helper');
		
		if ( isset($this->autoImport) )
		{
			$autoImport   = ( is_array($this->autoImport) ) ? $this->autoImport : array($this->autoImport);
			$modelSuffix  = $this->env->getConfig('model_suffix');
			$helperSuffix = $this->env->getConfig('helper_suffix');
			$regex        = '#(.+)(' . preg_quote($modelSuffix) . '|' . preg_quote($helperSuffix) . ')$#u';
			
			foreach ( $autoImport as $module )
			{
				if ( $module === 'database' )
				{
					$autoloadDatabase = TRUE;
					continue;
				}
				$prop = strtolower($module);
				if ( preg_match($regex, $prop, $match) )
				{
					if ( $match[2] === $modelSuffix )
					{
						$autoloadModel[] = $match[0];
					}
					else if ( $match[2] === $helperSuffix ) 
					{
						$autoloadHelper[] = $match[0];
					}
				}
				else
				{
					$autoloadLibrary[] = $module;
				}
			}
		}
		
		if ( $autoloadDatabase === TRUE )
		{
			$this->import->database();
		}
		
		if ( count($autoloadLibrary) > 0 )
		{
			$this->import->library($autoloadLibrary);
		}
		if ( count($autoloadModel) > 0 )
		{
			$this->import->model($autoloadModel);
		}
		if ( count($autoloadHelper) > 0 )
		{
			$this->import->helper($autoloadHelper);
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load and attach dogs-lead
	 * 
	 * @access protected
	 * @return void
	 */
	protected function _attachLead()
	{
		$leadName = str_replace($this->env->getConfig('controller_suffix'), '', get_class($this));
		$this->lead = Seezoo::$Importer->lead($leadName);
	}
}
	

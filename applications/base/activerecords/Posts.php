<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

class PostsActiveRecord extends SZ_ActiveRecord
{
	protected $_table   = 'posts';
	protected $_primary = 'id';
	protected $_schemas = array(
		'id' => array('type' => 'INT'),
		'name' => array('type' => 'VARCHAR'),
		'text' => array('type' => 'TEXT'),
		'created' => array('type' => 'DATETIME'),
		'modified' => array('type' => 'DATETIME')
	); 
	
	public function isValidId($value) {
		return TRUE;
	}


	public function isValidName($value) {
		return TRUE;
	}


	public function isValidText($value) {
		return TRUE;
	}


	public function isValidCreated($value) {
		return TRUE;
	}


	public function isValidModified($value) {
		return TRUE;
	}

}

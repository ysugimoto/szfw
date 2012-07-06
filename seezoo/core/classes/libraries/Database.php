<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Database library ( PDO driver )
 * 
 * @package  Seezoo-Framework
 * @category Libraries
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */


Class SZ_Database extends SZ_Driver
{
	/**
	 * PDO connections
	 * @var PDO resource
	 */
	private $_connectID;
	
	
	/**
	 * PDOStatement instace
	 * @var PDOStatement
	 */
	private $_statement;
	
	
	/**
	 * connection group
	 * @var string
	 */
	private $_group;
	
	
	/**
	 * transaction status
	 * @var bool
	 */
	private $_isTrans = FALSE;
	
	
	/**
	 * database connection info
	 * @var array
	 */
	private $_info;
	
	
	/**
	 * database allowed table characters ( white list )
	 * @var string
	 */
	private $_allowedTableCharacter = "0-9a-zA-Z_,\s";
	
	
	/**
	 * Environment class instance
	 * @var Environment
	 */
	protected $env;
	
	
	/**
	 * Benchmark stack
	 * @var array
	 */
	protected $_stackBench;
	
	
	/**
	 * table list cache
	 * @var array
	 */
	protected $_tablesCache;
	
	
	/**
	 * field list cache
	 * @var array
	 */
	protected $_fieldsCache = array();
	
	
	/**
	 * query log stack
	 * @var array
	 */
	protected $_queryLog = array();
	
	
	protected $_resultClass;
	
	
	/**
	 * DSN string format list
	 * @var array
	 */
	protected $_dsn = array(
		'mysql'    => 'mysql:host=%s;dbname=%s;port=%s',
		'postgres' => 'pgsql:host=%s;port=%s;dbname=%s',
		'sqlite2'  => 'sqlite2:%s',
		'sqlite3'  => 'sqlite:%s',
		'odbc'     => 'odbc:Driver=%s;HOSTNAME=%s;PORT=%d;DATABASE=%s;UID=%s;PWD=%s',
		'firebird' => 'firebird:dbname=%s:%s'
	);

	public function __construct($group)
	{
		$this->env = Seezoo::getENV();
		$this->_group = $group;
		$this->_initialize($group);
		$this->_resultClass = $this->_loadDriver('database', 'Database_result', FALSE, FALSE);
		$this->_loadDriver('database', ucfirst($this->_info['driver']) . '_query');
		
		$this->connect();
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * database connection start
	 * 
	 * @access public
	 */
	public function connect()
	{
		if ( $this->_connectID )
		{
			return;
		}
		$settings = $this->_makeConnectSettings();
		try
		{
			if ( $settings['dsn_only'] === FALSE )
			{
				$this->_connectID = new PDO(
												$settings['dsn_string'],
												$this->_info['username'],
												$this->_info['password'],
												$settings['options']
											);
			}
			else
			{
				$this->_connectID = new PDO($settings['dsn_string']);
			}
			$this->_connectID->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			// below code causes PDOStatement::execute() to error and shutting down...why?
			//$this->_connectID->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
		}
		catch ( PDOException $e )
		{
			throw $e;
		}
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * database connection close
	 * 
	 * @access public
	 */
	public function disconnect()
	{
		// PDO simply resource to null
		$this->_connectID = null;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Get database table prefix
	 * 
	 * @access public
	 * @return string
	 */
	public function prefix()
	{
		return ( isset($this->_info['table_prefix']) )
		         ? $this->_info['table_prefix']
		         : '';
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * get database group name
	 * 
	 * @access public
	 * @return string
	 */
	public function getGroup()
	{
		return $this->_group;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * execute query
	 * 
	 * @access public
	 * @param  string $sql
	 * @param  array $bind
	 */
	public function query($sql, $bind = FALSE)
	{
		$this->_stackBench = $this->_bench();
		if ( is_array($bind) && strpos($sql, '?') !== FALSE )
		{
			
			// query binding chars and bind paramter is match?
			if ( substr_count($sql, '?') !== count($bind) )
			{
				throw new Exception('prepared statement count is not match.', SZ_ERROR_CODE_DATABASE);
				return FALSE;
			}
			
			
			// current statement uses same query
			if ( ! $this->_statement || $this->_statement->queryString !== $sql )
			{
				$this->_statement = $this->_connectID->prepare($sql);
			}
			$index = 0;
			foreach ( $bind as $val )
			{
				$this->_statement->bindValue(++$index, $val, $this->typeof($val));
			}
			if ( $this->_statement->execute() === FALSE )
			{
				$error = '';
				if ( $this->_info['query_debug'] === TRUE )
				{
					$error = $this->_stackQueryLog($sql, $bind);
				}
				throw new Exception('SQL Failed. ' . $this->_statement->errorCode() . ': ' . implode(', ', $this->_statement->errorInfo()) . ' SQL : ' . $error, SZ_ERROR_CODE_DATABASE);
				return FALSE;
			}
			
			// SQL debugging
			if ( $this->_info['query_debug'] === TRUE )
			{
				$this->_stackQueryLog($sql, $bind);
			}
		}
		else
		{
			if ( FALSE === ($this->_statement = $this->_connectID->query($sql)) )
			{
				$error = '';
				if ( $this->_info['query_debug'] === TRUE )
				{
					$error = $this->_stackQueryLog($sql);
				}
				throw new Exception('SQL Failed :' . implode(', ', $this->_connectID->errorInfo()) . ' SQL : ' . $error, SZ_ERROR_CODE_DATABASE);
				return FALSE;
			}
			
			if ( $this->_info['query_debug'] === TRUE )
			{
				$this->_stackQueryLog($sql);
			}
			
		}
		
		// returns database Result
		return $this->_createResult();
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Create Result statement
	 * 
	 * @access public
	 * @return DatabaseResult
	 */
	protected function _createResult()
	{
		return new $this->_resultClass($this->_statement);
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Insert record
	 * 
	 * @access public
	 * @param  string $table
	 * @param  array $data
	 * @param  bool  $returnInsertID
	 * @return mixed
	 */
	public function insert($table, $data = array(), $returnInsertID = FALSE)
	{
		if ( ! $this->isAllowedTableName($table) )
		{
			return FALSE;
		}
		
		if ( count($data) === 0 )
		{
			throw new Exception('Insert values have not be empty!', SZ_ERROR_CODE_DATABASE);
			return FALSE;
		}
		// build query
		$columns    = array();
		$statements = array();
		$bindData   = array();
		foreach ( $data as $column => $value )
		{
			$columns[]    = $this->prepColumn($column);
			$statements[] = '?';
			$bindData[]   = $value;
			
		}
		$sql = sprintf(
					'INSERT INTO %s (%s) VALUES (%s);', 
					$this->prefix() . $table,
					implode(', ', $columns),
					implode(', ', $statements)
				);
		$query = $this->query($sql, $bindData);
		return ( $returnInsertID ) ? $this->insertID() : $query;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Update record
	 * 
	 * @access public
	 * @param  string $table
	 * @param  array $data
	 * @param  array $where
	 * @throws Exception
	 */
	public function update($table, $data = array(), $where = FALSE)
	{
		if ( ! $this->isAllowedTableName($table) )
		{
			return FALSE;
		}
				
		if ( count($data) === 0 )
		{
			throw new Exception('Update values have not be empty!', SZ_ERROR_CODE_DATABASE);
			return FALSE;
		}
		// build query
		$statements = array();
		$bindData   = array();
		
		foreach ( $data as $column => $value )
		{
			$statements[] = $column . ' = ? ';
			$bindData[]   = $value;
		}
		$sql = sprintf(
					'UPDATE %s SET %s',
					$this->prefix() . $table,
					implode(', ', $statements)
				);
		
		// Is limited update?
		if ( is_array($where) )
		{
			$sql .= ' WHERE ';
			$statements = array();
			foreach ( $where as $column => $value )
			{
				$stb = $this->buildOperatorStatement($column, $value);
				if ( is_array($stb) )
				{
					$statements[] = $stb[0];
					if ( is_array($stb[1]) )
					{
						foreach ( $stb[1] as $bind )
						{
							$bindData[] = $bind;
						}
					}
					else
					{
						$bindData[] = $stb[1];
					}
				}
				else
				{
					$statements[] = $stb;
				}
			}
			$sql .= implode(' AND ', $statements);
		}
		
		return $this->query($sql, $bindData);
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Delete record
	 * 
	 * @access public
	 * @param  string $table
	 * @param  array $where
	 */
	public function delete($table, $where = FALSE)
	{
		if ( ! $this->isAllowedTableName($table) )
		{
			return FALSE;
		}
		
		// build query
		$statements = array();
		$bindData   = array();
		$sql        = 'DELETE FROM ' . $this->prefix() . $table;

		if ( is_array($where) )
		{
			$sql .= ' WHERE ';
			$statements = array();
			foreach ( $where as $column => $value )
			{
				$stb = $this->buildOperatorStatement($column, $value);
				if ( is_array($stb) )
				{
					$statements[] = $stb[0];
					if ( is_array($stb[1]) )
					{
						foreach ( $stb[1] as $bind )
						{
							$bindData[] = $bind;
						}
					}
					else
					{
						$bindData[] = $stb[1];
					}
				}
				else
				{
					$statements[] = $stb;
				}
			}
			$sql .= implode(' AND ', $statements);
		}
		else if ( is_string($where) )
		{
			$sql .= 'WHERE ' . $where;
		}
		
		return $this->query($sql, $bindData);
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * get table lists
	 * 
	 * @access public
	 * @param  string $table
	 */
	public function tables()
	{
		if ( ! $this->_tablesCache )
		{
			$sql   = $this->driver->tableListQuery($this->_info['dbname'], $this->prefix());
			$query = $this->_connectID->query($sql);
			$this->_tablesCache = array();
			foreach ( $query->fetchAll(PDO::FETCH_BOTH) as $tables )
			{
				$this->_tablesCache[] = $this->driver->convertTable($tables);
			}
		}
		
		return $this->_tablesCache;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * check table exists
	 * 
	 * @access public
	 * @param  string $table
	 */
	public function tableExists($table)
	{
		return in_array($this->prefix() . $table, $this->tables());
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * get field list
	 * 
	 * @access public
	 * @param  string $table
	 */
	public function fields($table)
	{
		if ( ! $this->isAllowedTableName($table) )
		{
			return FALSE;
		}
		
		if ( ! isset($this->_fieldsCache[$table]) )
		{
			$sql   = $this->driver->columnListQuery($this->prefix() . $table);
			$query = $this->_connectID->query($sql);
			$this->_fieldsCache[$table] = array();
			foreach ( $query->fetchAll(PDO::FETCH_OBJ) as $column )
			{
				$column = $this->driver->convertField($column);
				$this->_fieldsCache[$table][$column->field] = $column;
			}
		}
		
		return $this->_fieldsCache[$table];
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * check field exists
	 * 
	 * @access public
	 * @param  string $fieldName
	 * @param  string $table
	 */
	public function fieldExists($fieldName, $table)
	{
		$fields = $this->fields($table);
		return ( isset($fields[$fieldName]) ) ? TRUE : FALSE;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * start transaction
	 * 
	 * @access public
	 */
	public function transaction()
	{
		$this->_connectID->beginTransaction();
		$this->_isTrans = TRUE;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * transaction commit
	 * 
	 * @access public
	 */
	public function commit()
	{
		if ( $this->_isTrans === FALSE )
		{
			return FALSE;
		}
		$this->_isTrans = FALSE;
		return $this->_connectID->commit();
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * transaction rollback
	 * 
	 * @access public
	 */
	public function rollback()
	{
		if ( $this->_isTrans === FALSE )
		{
			return FALSE;
		}
		$this->_isTrans = FALSE;
		return $this->_connectID->rollBack();
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * get last inserted ID
	 * 
	 * @access public
	 * @return int
	 */
	public function insertID()
	{
		return (int)$this->_connectID->lastInsertId();
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * get last prepered SQL
	 * 
	 * @access public
	 * @return string
	 */
	public function lastQuery()
	{
		return $this->_statement->queryString;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * get query log
	 * 
	 * @access public
	 * @return array
	 */
	public function getQueryLogs()
	{
		return $this->_queryLog;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * database initialize
	 * 
	 * @access protected
	 * @param  string $group
	 * @throws Exception
	 */
	protected function _initialize($group)
	{
		if ( ! is_array($group) )
		{
			// Database already connected?
			if ( isset($this->_connectID) && is_resource($this->_connectID) )
			{
				return;
			}
			
			$database = $this->env->getDBSettings();
			if ( ! isset($database) || ! isset($database[$group]) )
			{
				throw new Exception('Undefined database settings.', SZ_ERROR_CODE_DATABASE);
				return;
			}
			$this->_info = $database[$group];
		}
		else
		{
			$this->_info = $group;
		}
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * connection settings create
	 * 
	 * @access protected
	 * @return array
	 */
	protected function _makeConnectSettings()
	{
		if ( ! isset($this->_dsn[$this->_info['driver']]) )
		{
			throw new Exception('Sysytem unsupported Driver: ' . $this->_info['driver'], SZ_ERROR_CODE_DATABASE);
			return FALSE;
		}
		$dsn = $this->_dsn[$this->_info['driver']];
		
		if ( isset($this->_info['host']) && $this->_info['host'] === 'localhost' )
		{
			$this->_info['host'] = '127.0.0.1';
		}
		$options  = array();
		$dsn_only = FALSE;
		switch ( $this->_info['driver'] )
		{
			case 'mysql':
				$dsn = sprintf($dsn, $this->_info['host'], $this->_info['dbname'], $this->_info['port']);
				$options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8";
				break;
			case 'postgres':
				$dsn = sprintf($dsn, $this->_info['host'], $this->_info['port'], $this->_info['dbname']);
				break;
			case 'sqlite2':
			case 'sqlite3':
				$dsn = sprintf($dsn, rtrim($this->_info['path'], '/') . '/' . $this->_info['dbname']);
				break;
			case 'odbc':
				$dsn = sprintf($dsn,
				               $this->_info['driver_name'],
				               $this->_info['host'],
				               $this->_info['port'],
				               $this->_info['host'],
				               $this->_info['dbname'],
				               $this->_info['username'],
				               $this->_info['password']);
				$dsn_only = TRUE;
			case 'firebird':
				$dsn = sprintf($dsn, $this->_info['host'], $this->_info['path']);
				break;
			default:
				throw new PDOException('Undefiend or non-support database driver selected.', SZ_ERROR_CODE_DATABASE);
				return FALSE;
			
		}
		if ( $this->_info['pconnect'] === TRUE )
		{
			$options[PDO::ATTR_PERSISTENT] = TRUE;
		}
		return array(
			'dsn_string' => $dsn,
			'options'    => $options,
			'dsn_only'   => $dsn_only
		);
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * PDO binding parameter detection
	 * 
	 * @access protected
	 * @param $value
	 * @return PDO::PARAM_*
	 */
	protected function typeof($value)
	{
		$type = PDO::PARAM_STR;
		if ( is_int($value) )
		{
			$type =  PDO::PARAM_INT;
		}
		else if ( is_bool($value) )
		{
			$type =  PDO::PARAM_BOOL;
		}
		else if( is_null($value) )
		{
			$type =  PDO::PARAM_NULL;
		}
		else if ( is_resource($value) )
		{
			$type = PDO::PARAM_LOB;
		}
		
		return $type;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * check tablename is allowed characters
	 * 
	 * @access public
	 * @param  string $table
	 * @return bool
	 */
	public function isAllowedTableName($table)
	{
		if ( ! preg_match('#\A[' . $this->_allowedTableCharacter . ']+\Z#u', $table) )
		{
			throw new Exception('Invalid Table name: ' . $table, SZ_ERROR_CODE_DATABASE);
			return FALSE;
		}
		return TRUE;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * beckmarh start
	 * 
	 * @access protected
	 * @return array
	 */
	protected function _bench()
	{
		return explode(' ', microtime());
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * format and add SQL logs
	 * 
	 * @access protected
	 * @param  string $sql
	 * @param  array $bind
	 */
	protected function _stackQueryLog($sql, $bind = FALSE)
	{
		$end  = $this->_bench();
		$time = number_format(($end[0] + $end[1]) - ($this->_stackBench[0] + $this->_stackBench[1]), 4);
		
		if ( $bind !== FALSE )
		{
			$sqlPiece  = explode('?', $sql);
			$lastPiece = array_pop($sqlPiece);
			$logSQL    = '';
			foreach ( $sqlPiece as $index => $piece )
			{
				$logSQL .= $piece . $this->_connectID->quote($bind[$index]);
			}
			$logSQL .= $lastPiece;
		}
		else
		{
			$logSQL = $sql;
		}
		
		$this->_queryLog[] = array('query' => $logSQL, 'exec_time' => $time);
		return $logSQL;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Prepare column name
	 * 
	 * @access public
	 * @param  string $column
	 * @return string
	 */
	public function prepColumn($column)
	{
		$column = trim($column);
		if ( $column === '*' )
		{
			return $column;
		}
		if ( strpos($column, ',') !== FALSE )
		{
			$exp = explode(',', $column);
			$ret = array();
			foreach ( $exp as $col )
			{
				$ret[] = preg_replace('/[^0-9a-zA-Z\-_\.\s]/', '', trim($col));
			}
			return implode(',', $ret);
		}
		else
		{
			return preg_replace('/[^0-9a-zA-Z\-_\.\s]/', '', $column);
		}
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * Parse where section and build statement
	 * 
	 * @access public
	 * @param  string $key
	 * @param  mixed  $value
	 * @return mixed
	 */
	public function buildOperatorStatement($key, $value)
	{
		$split = explode(' ', $key, 2);
		if ( count($split) > 1 )
		{
			$column = $this->prepColumn($split[0]);
			switch ( strtoupper($split[1]) )
			{
				case 'BETWEEN':
					$column .= ' BETWEEN ? AND ? ';
					break;
				case 'NOT BETWEEN':
					$column .= ' NOT BETWEEN ? AND ? ';
					break;
				default:
					$column .= ' ' . trim($split[1]) . ' ? ';
			}
			return array($column, $value);
		}
		else if ( $value === 'IS NULL' )
		{
			return $this->prepColumn($key) . ' IS NULL';
		}
		else if ( $value === 'IS NOT NULL' )
		{
			return $this->prepColumn($key) . ' IS NOT NULL';
		}
		else
		{
			return array($this->prepColumn($key) . ' = ? ', $value);
		}
	}
}

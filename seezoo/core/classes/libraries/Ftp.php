<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * FTP wapper class
 * 
 * @package  Seezoo-Framework
 * @category Libraries
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */
class SZ_Ftp
{
	/**
	 * FTP connection handle
	 * @var resource
	 */
	protected $handle;
	
	
	/**
	 * Default config set
	 * @var array
	 */
	protected $_config = array(
		'username' => '',
		'password' => '',
		'hostname' => '',
		'port'     => 21,
		'passive'  => TRUE
	);
	
	
	/**
	 * Stack log messages
	 * @var array
	 */
	protected $logMessages = array();
	
	
	// --------------------------------------------------------------------
	
	
	
	public function __construct()
	{
		$env       = Seezoo::getENV();
		$ftpConfig = $env->getConfig('FTP');
		
		$this->configure((array)$ftpConfig);
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Configure connection settings
	 * 
	 * @access public
	 * @param  array $conf
	 */
	public function configure($conf = array())
	{
		$this->_config = array_merge($this->_config, $conf);
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Get settings
	 * 
	 * @access public
	 * @param  sring $key
	 * @return mixed
	 */
	protected function _get($key)
	{
		return ( isset($this->_config[$key]) )
		         ? $this->_config[$key]
		         : FALSE;
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Try to connect FTP server
	 * 
	 * @access public
	 * @return bool
	 */
	public function connect()
	{
		// Connection initialize.
		$this->close();
		
		// normalize hostname
		$hostname = rtrim(preg_replace('|^[.+]://|', '', $this->_get('hostname')), '/');
		
		// Try to connect FTP server
		$this->handle = @ftp_connect($hostname, (int)$this->_get('port'));
		if ( ! is_resource($this->_handle) )
		{
			return $this->_log('CONNECT: FTP server connection failed.');
		}
		
		// Try to login
		$login = @ftp_login(
		           $this->handle,
		           $this->_get('username'),
		           $this->_get('password')
		         );
		
		if ( ! $login )
		{
			return $this->_log('LOGIN: FTP login failed.');
		}
		
		// Set PASV mode if needs
		if ( $this->_get('passive') === TRUE )
		{
			if ( ! ftp_pasv($this->handle, TRUE) )
			{
				return $this->_log('PASSIV: FTP server rejected PASV mode.');
			}
		}
		return TRUE;
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Change remote directory
	 * 
	 * @access public
	 * @param  string $path
	 * @return bool
	 */
	public function chdir($path = '')
	{
		if ( $path === '' || ! is_resource($this->handle) )
		{
			return FALSE;
		}
		
		return ( ! @ftp_chdir($this->handle, $path) )
		         ? $this->_log('CHDIR: Failed to change directory.')
		         : TRUE;
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Create directory as remote server
	 * 
	 * @access public
	 * @param  string $directory
	 * @param  int $permission
	 * @return bool
	 */
	public function mkdir($directory, $permission = NULL)
	{
		if ( $path === '' || ! is_resource($this->handle) )
		{
			return FALSE;
		}
		
		if ( ! @ftp_mkdir($this->handle, $directory) )
		{
			return $this->_log('MKDIR: Failed to make directory.');
		}
		
		if ( ! is_null($permission) )
		{
			$this->chmod($directory, (int)$permission);
		}
		
		return TRUE;
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Send server from file
	 * 
	 * @access public
	 * @param  string $localFile
	 * @param  string $remoteFile
	 * @param  bool $binary
	 * @param  int $permission
	 */
	public function sendFile($localFile, $remoteFile, $binary = FALSE, $permission = NULL)
	{
		if ( ! is_resource($this->handle) )
		{
			return FALSE;
		}
		
		if ( ! file_exists($localFile) )
		{
			return $this->_log('UPLOAD: Local file is not exists.');
		}
		
		if ( is_dir($localFile) )
		{
			return $this->sendDir($localFile, $remotePath, $binary, $permission);
		}
		
		$mode = ( $binary ) ? FTP_BINARY : FTP_ASCII;
		
		if ( ! @ftp_put($this->handle, $remoteFile, $localFile, $mode) )
		{
			return $this->_log('SENDFILE: Failed to send file.');
		}
		
		if ( ! is_null($permission) )
		{
			$this->chmod($remoteFile, (int)$permission);
		}
		
		return TRUE;
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Send server from stream
	 * 
	 * @access public
	 * @param  resource $stream
	 * @param  sring $remoteFile
	 * @param  bool $binary
	 * @param  int $permission
	 * @return bool
	 */
	public function sendStream(resource $stream, $remoteFile, $binary = FALSE, $permission = NULL)
	{
		if ( ! is_resource($this->handle) )
		{
			return FALSE;
		}
		
		$mode = ( $binary ) ? FTP_BINARY : FTP_ASCII;
		rewind($stream);
		
		if ( ! @ftp_fput($this->handle, $remoteFile, $stream, $mode) )
		{
			fclose($stream);
			return $this->_log('SENDFILE: Failed to send stream.');
		}
		fclose($stream);
		
		if ( ! is_null($permission) )
		{
			$this->chmod($remoteFile, (int)$permission);
		}
		
		return TRUE;
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Send server from string buffer
	 * 
	 * @access public
	 * @param  string $string
	 * @param  string $remoteFile
	 * @param  bool $binary
	 * @param  int $permission
	 * @return bool
	 */
	public function sendBuffer($string, $remoteFile, $binary = FALSE, $permission = NULL)
	{
		if ( ! is_resource($this->handle) )
		{
			return FALSE;
		}
		
		$mode = ( $binary ) ? FTP_BINARY : FTP_ASCII;
		
		// create file stream
		$stream = fopen('php://temp', 'wb');
		$length = 0;
		$dest   = strlen($string);
		
		// Write to temp stream
		do
		{
			$length += fwrite($stream, $string);
		}
		while ( $length <= $dest );
		// And rewind
		rewind($stream);
		
		if ( ! @ftp_fput($this->handle, $remoteFile, $stream, $mode) )
		{
			fclose($stream);
			return $this->_log('SENDFILE: Failed to send Buffer.');
		}
		fclose($stream);
		
		if ( ! is_null($permission) )
		{
			$this->chmod($remoteFile, (int)$permission);
		}
		
		return TRUE;
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Send server from directory recursive
	 * 
	 * @access public
	 * @param  string $localDir
	 * @param  string $remoteDir
	 * @param  bool $binary
	 * @param  int $pemission
	 * @return bool
	 */
	public function sendDir($localDir, $remoteDir, $binary = FALSE, $permission = NULL)
	{
		if ( ! is_resource($this->handle) || ! is_dir($localDir) )
		{
			return FALSE;
		}
		
		if ( ! $this->chdir($remotePath) )
		{
			if ( ! $this->mkdir($remotePath, $permission) || ! $this->chdir($remotePath) )
			{
				return FALSE;
			}
		}
		
		$localDir  = trail_slash($localDir);
		$remoteDir = trail_slash($remoteDir);
		
		try
		{
			$dir = new DirectoryIterator($localDir);
			
			foreach ( $dir as $file )
			{
				if ( $file->isDot() )
				{
					continue;
				}
				if ( $file->isDir() )
				{
					$this->sendDir($localDir . (string)$file, $remoteDir . (string)$file, $binary, $permission);
				}
				else if ( $file->isFile )
				{
					$this->sendFile($localDir . (string)$file, $remoteDir . (string)$file, $binary, $permission);
				}
			}
			return TRUE;
		}
		catch ( Exception $e )
		{
			$this->_log('SENDDIR: Failed to send directory recursive.');
		}
		
		return FALSE;
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Rename remote file
	 * 
	 * @access public
	 * @param  string $oldName
	 * @param  string $newName
	 * @return bool
	 */
	public function rename($oldName, $newName)
	{
		if ( ! is_resource($this->handle) )
		{
			return FALSE;
		}
		
		if ( ! @ftp_rename($this->handle, $oldName, $newName) )
		{
			return $this->_log('RENAME: Failed to rename file.');
		}
		
		return TRUE;
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Move file
	 * 
	 * @access public
	 * @param  string $oldName
	 * @param  string $newName
	 * @return bool
	 */
	public function move($oldName, $newName)
	{
		return $this->rename($oldName, $newName);
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Delete remote file
	 * 
	 * @access public
	 * @param  string $remoteFile
	 * @return bool
	 */
	public function deleteFile($remoteFile)
	{
		if ( ! is_resource($this->handle) )
		{
			return FALSE;
		}
		
		if ( ! @ftp_delete($this->handlq, $remoteFile) )
		{
			return $this->_log('DELETE: Failed to delete file.');
		}
		
		return TRUE;
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Delete directory
	 * 
	 * @access public
	 * @param  string $dirPath
	 * @return bool
	 */
	public function deleteDir($dirPath)
	{
		if ( ! is_resource($this->handle) )
		{
			return FALSE;
		}
		
		$dirPath = trail_slash($dirPath);
		$list    = $this->rawFileList($dirPath, TRUE);
		
		// Remove child files recursive
		if ( is_array($list) )
		{
			foreach ( $list as $file )
			{
				if ( $file->isDirectory )
				{
					$this->deleteDir($dirPath . $file->name);
				}
				else
				{
					$this->deleteFile($dirPath . $file->name);
				}
			}
		}
		
		// Remove dest directory
		if ( ! @ftp_rmdir($this->handle, $dirPath) )
		{
			return $this->_log('RMDIR: Failed to remove directory.');
		}
		
		return TRUE;
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Change file permission
	 * 
	 * @access public
	 * @param  string $path
	 * @param  int $permission
	 * @return bool
	 */
	public function chmod($path, $permission)
	{
		if ( ! is_resource($this->handle) )
		{
			return FALSE;
		}
		
		if ( ! @ftp_chmod($this->handle, (int)$permission, $path) )
		{
			return $this->_log('CHMOD: Failed to change permission');
		}
		
		return TRUE;
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Get simple file list
	 * 
	 * @access pubic
	 * @param  string $path
	 * @return mixed
	 */
	public function fileList($path)
	{
		if ( ! is_resource($this->handle) )
		{
			return FALSE;
		}
		
		return ftp_nlist($this->handle, $path);
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Get raw file list
	 * 
	 * @access public
	 * @param  string $path
	 * @return mixed
	 */
	public function rawFileList($path)
	{
		$return = array();
		$list   = ftp_rawlist($this->handle, $path);
		
		if ( is_array($list) )
		{
			foreach ( $list as $raw )
			{
				$stat = new stdClass;
				if ( ! preg_match('/^(.{1}).+\s([^\s]+)$/', $raw, $matches) )
				{
					return $this->_log('RAWLIST: invalid raw list returns.');
				}
				$stat->isDirectory = ( strtolower($matches[1]) === 'd') ? TRUE : FALSE;
				$stat->isLink      = ( strtolower($matches[1]) === 'l') ? TRUE : FALSE;
				$stat->isFile      = ! $stat->isDirectory;
				$stat->name        = $matches[2];
				$return[]          = $stat;
			}
		}
		
		return $return;
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Close connection
	 * 
	 * @access public
	 */
	public function close()
	{
		if ( is_resource($this->handle) )
		{
			@ftp_close($this->handle);
		}
	}
	
	
	// --------------------------------------------------------------------
	
	/**
	 * Guard destructor
	 */
	public function __destruct()
	{
		$this->close();
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Set log
	 * 
	 * @access protected
	 * @param  string $msg
	 */
	protected function _log($msg)
	{
		$this->logMessages[] = $msg;
	}
	
	
	// --------------------------------------------------------------------
	
	
	/**
	 * Get log
	 * 
	 * @access public
	 * @param  bool $all
	 * @return mixed
	 */
	public function getLog($all = FALSE)
	{
		return ( $all ) ? $this->logMessages : end($this->logMessages);
	}
}

<?php
/**
 * User: chorry
 * Date: 13.03.12
 *
TODO: add some kind of bus, and event dispatcher (for now, we'll just hammer in logger)
 */
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class FileManager
{
	private $_error; //keep last error operation

	/**
	 * @var Security
	 */
	private $_security;

	/**
	 * @var Auth_User
	 */
	private $_auth;
	private $_logChannels = array();
	
	public function __construct()
	{

	}

	public function getAuth()
	{
		if (!isset ( $this->_auth ) )
		{
			$this->_auth = new Auth_User( new Components_Session_Storage() );
		}
		return $this->_auth;
	}

	/**
	 * @return object Security
	 */
	public function getSecurity()
	{
		if ( !isset( $this->_security ) )
		{
			$this->_security = new Security( new Components_Session_Storage() );
		}
		return $this->_security;
	}

	/**
	 * @param string $channel channel name
	 * @return object Monolog\Logger
	 */
	public function getLogger($channel = '')
	{
		$channelName = ($channel == '') ?  'default' : $channel;

		if (isset($this->_logChannels[$channelName]) && $this->_logChannels[$channelName] instanceof Monolog\Logger)
		{
			return $this->_logChannels[$channelName];
		}
		else
		{
			$this->_logChannels[$channelName] = new Logger($channelName);
			$this->_logChannels[$channelName]->pushHandler(new StreamHandler( LOG_PATH . '/' . $channelName . '.log', Logger::INFO));
			return $this->_logChannels[$channelName];
		}
	}

	/**
	 * @param $params array(selected_dir, name)
	 * @return bool
	 */
	public function dirCreate( $params )
	{

		if ( $dirPath = $this->getSelectedDir( $params[ 'selected_dir' ] ) )
		{
			$dirName = $dirPath . '/' . $params[ 'name' ];
		}
		else
		{
			$this->_error = WFM_DIR_BADBASEDIR;
			return false;
		}

		if ( $this->getSecurity()->canCreateDir( $dirName )
		     && ( !file_exists( $dirName ) )
		)
		{
			if ( @mkdir( $dirName ) )
			{
				chmod($dirName, PERM_DIR_CREATE);
				return true;
			}
			$this->_error = WFM_DIR_CREATE_FAILED;
		}
		else
		{
			$this->_error = WFM_DIR_CREATE_DENIED;
		}
		return false;
	}

	/**
	 * Get list of root dirs
	 *
	 * @return array
	 */
	public function dirInit()
	{
		$dirs      = AppConfig::getInstance()->getVal( 'dir.root' );
		$dirs_desc = AppConfig::getInstance()->getVal( 'dir.root.desc' );

		$index     = 0;

		if ( !is_array( $dirs ) ) {
			return array( 'dir'=> '' );
		}

		foreach ( $dirs as $k=> $v )
		{
			if ( is_int( $k ) )
			{

				if ( $dirs_desc[ $k ] != '' )
				{
					$roots[ ] = $dirs_desc[ $k ];
				}
				else
				{
					$roots[ ] = basename( $dirs[ $k ] );
				}

			}
			else
			{
				$roots[ ] = $k;
			}
			++$index;
		}
		return array( 'dir'=> $roots );
	}

	/**
	 * @param $params
	 * @return bool
	 */
	public function dirDelete( $params )
	{
		if ( $dirPath = $this->getSelectedDir( $params[ 'selected_dir' ] ) )
		{
			$dirName = $dirPath . '/' . $params[ 'name' ];
		}
		else
		{
			$this->_error = WFM_DIR_BADBASEDIR;
			return false;
		}

		if ( $this->getSecurity()->canDeleteDir( $dirName )
					&& Helper::getInstance()->stripExtraSlashes($dirName) != $dirPath
		)
		{
			if ( @rmdir( $dirName ) )
			{
				$this->getLogger()->addInfo($this->getAuth()->getUsername() . ' DELETES ' . $dirName);
				return true;
			}
			$this->_error = WFM_DIR_DELETE_FAILED;
		}
		else
		{
			$this->_error = WFM_DIR_DELETE_DENIED;
		}

		return false;
	}

	/**
	 * @param $args
	 * @return bool
	 */
	public function dirRename( $args )
	{
		$args = func_get_args();

		if ( $dirPath = $this->getSelectedDir( $args[ 0 ][ 'selected_dir' ] ) )
		{
			$oldName = $dirPath . '/' . $args[ 0 ][ 'name' ];
			$newName = $dirPath . '/' . $args[ 0 ][ 'name2' ];
		}
		else
		{
			$this->_error = WFM_DIR_BADBASEDIR;
			return false;
		}

		if (
			$this->getSecurity()->canRenameDir( $oldName )
			&& $this->getSecurity()->canListDir( $oldName )
			&& !in_array( Helper::getInstance()->stripExtraSlashes( $oldName, true ), AppConfig::getInstance()->getVal( 'dir.root' )
			)
			&& ( !file_exists( $newName ) )
		)
		{
			if ( file_exists( $oldName ) )
			{
				if ( @rename( $oldName, $newName ) == true )
				{
					$this->getLogger()->addInfo($this->getAuth()->getUsername() . ' RENAMES ' . $oldName . " to ". $newName);
					return true;
				}
				$this->_error = WFM_DIR_RENAMEFAILED;
			}
		}
		else
		{
			$this->_error = WFM_DIR_CANTRENAME;
		}
		return false;
	}

	/**
	 * Gets directory listing
	 *
	 * @param $args array('selected_dir', 'name')
	 * @return array|bool
	 */
	public function dirList( $args )
	{
		$args = func_get_args();

		if ( $dirPath = $this->getSelectedDir( $args[ 0 ][ 'selected_dir' ] ) )
		{
			$dirName = $dirPath . '/' . $args[ 0 ][ 'name' ];
		}
		else
		{
			$this->_error = WFM_DIR_BADBASEDIR;
			return false;
		}

		if ( $this->getSecurity()->canListDir( $dirName )
		     || in_array(
					Helper::getInstance()->stripExtraSlashes( $dirName, true ),
					AppConfig::getInstance()->getVal( 'dir.root' )
				)
		)
		{
			if ( file_exists( $dirName ) )
			{
				$dir    = new FilesystemIterator( $dirName, FilesystemIterator::CURRENT_AS_SELF && FilesystemIterator::SKIP_DOTS );
				$result = array( 'dir'=> '' );
				foreach ( $dir as $fileinfo )
				{
					/**
					 * @var FilesystemIterator $fileinfo
					 */
						if ( $fileinfo->isFile() )
						{
							$result[ 'files' ][ ] = array(
								'filename' => $fileinfo->getFilename(),
								'filesize' => $fileinfo->getSize(),
								'last_modif' => $fileinfo->getMTime(),
							);
						}
						else
						{
							if ( !in_array( $fileinfo->getFilename(), $this->getSecurity()->getBannedDirs() ) )
							{
								$result[ 'dir' ][ ] = $fileinfo->getFilename();
							}
						}
				}
				return $result;
			}
			$this->_error = WFM_DIR_NOTEXIST;
		}
		else
		{
			$this->_error = WFM_DIR_CANTLIST;
		}

		return false;
	}

	/**
	 * @param $params
	 * @return bool
	 */
	public function fileCreate( $params )
	{
		if ( $dirPath = $this->getSelectedDir( $params[ 'selected_dir' ] ) )
		{
			$fileName     = $params[ 'name' ];
			$fileFullPath = $dirPath . '/' . $params[ 'name' ];
		}
		else
		{
			$this->_error = WFM_DIR_BADBASEDIR;
			return false;
		}

		if (
			$this->getSecurity()->canCreateFile( $fileName )
			&& ( !file_exists( $fileFullPath ) )
		)
		{
			if ( file_exists( dirname( $fileFullPath ) ) )
			{
				$res =  @file_put_contents( $fileFullPath, '' );
				if ( $res !== false )
				{
					if ( chmod($fileFullPath, PERM_FILE_CREATE) )
					{
						return true;
					}
					else
					{
						$this->_error = WFM_FILE_CANTCHMOD;
					}
				}
				else
				{
					$this->_error = WFM_FILE_WRITING_FAILED;
				}
			}
			else
			{
				$this->_error = WFM_FILE_CREATE_FAILED;
			}
		}
		else
		{
			$this->_error = WFM_FILE_CANTCREATE;
		}
		return false;
	}

	/**
	 * @param $args
	 * @return bool
	 */
	public function fileRename( $args )
	{
		$args        = func_get_args();
		$selectedDir = $args[ 0 ][ 'selected_dir' ];

		if ( $dirPath = $this->getSelectedDir( $selectedDir ) )
		{
			$oldFile = $dirPath . '/' . $args[ 0 ][ 'name' ];
			$newFile = $dirPath . '/' . $args[ 0 ][ 'name2' ];
		}
		else
		{
			$this->_error = WFM_DIR_BADBASEDIR;
			return false;
		}

		if ( $this->getSecurity()->canRenameFile( $newFile ) )
		{
			if ( file_exists( $oldFile ) && !file_exists( $newFile ) )
			{
				if ( @rename( $oldFile, $newFile ) )
				{
					$this->getLogger()->addInfo($this->getAuth()->getUsername() . ' RENAMES ' . $oldFile . " to ". $newFile);
					return true;
				}
				$this->_error = WFM_FILE_RENAME_FAILED;
			}
		}
		else
		{
			$this->_error = WFM_FILE_RENAME_DENIED;
		}
		return false;
	}

	/**
	 * @param $params
	 * @return bool
	 */
	public function fileDelete( $params )
	{
		$selectedDir = $params[ 'selected_dir' ];

		if ( $dirPath = $this->getSelectedDir( $selectedDir ) )
		{
			$fileFullPath = $dirPath . '/' . $params[ 'name' ];
		}
		else
		{
			$this->_error = WFM_DIR_BADBASEDIR;
			return false;
		}

		if ( $this->getSecurity()->canDeleteFile( $fileFullPath ) )
		{
			if ( file_exists( $fileFullPath ) )
			{
				if ( @unlink( $fileFullPath ) )
				{
					$this->getLogger()->addInfo($this->getAuth()->getUsername() . ' DELETES ' . $fileFullPath);
					return true;
				}
				$this->_error = WFM_FILE_DELETE_FAILED;
			}
			$this->_error = WFM_FILE_NOT_EXISTS;
		}
		$this->_error = WFM_FILE_DELETE_DENIED;
		return false;
	}

	/**
	 * @param null $dirName
	 * @return bool
	 */
	public function fileUpload( $dirName = null )
	{
		if ( $dirPath = $this->getSelectedDir( $_POST[ 'dir_id' ] ) )
		{
			$dirFullPath = $dirPath . '/' . $_POST[ 'current_dir' ];
			if ( !realpath( $dirFullPath ) )
			{
				$this->_error = WFM_DIR_BAD_PATH;
				return false;
			}
		}
		else
		{
			$this->_error = WFM_DIR_BADBASEDIR;
			return false;
		}

		if ( $this->getSecurity()->canUploadFile( $dirFullPath ) )
		{
			if ( count( $_FILES ) > 0 )
			{
				foreach ( $_FILES as $uploadedFile )
				{
					$fileName        = $uploadedFile[ 'name' ];
					$tmpFileName     = $uploadedFile[ 'tmp_name' ];
					$newFileLocation = $dirFullPath . '/' . urldecode( $fileName );
					if ( !file_exists( $newFileLocation ) )
					{
						$opCode = @move_uploaded_file( $tmpFileName, $newFileLocation );
						//$opCode = @copy($tmpFileName,$newFileLocation);
						if ( $opCode === false )
						{
							$this->_error = WFM_FILE_UPLOAD_FAILEDMOVINGFILE;
							return false;
						}
					}
					else
					{
						$this->_error = WFM_FILE_UPLOAD_FILEALREADYEXISTS;
						return false;
					}
				}
				chmod($newFileLocation, PERM_FILE_CREATE);
				return true;
			}
		}
		$this->_error = WFM_FILE_UPLOAD_DENIED;
		return false;
	}

	/**
	 * Returns web-link to file
	 * @param $params
	 * @return bool|string
	 */
	public function fileGetLink ($params)
	{
		if ( $dirPath = $this->getSelectedDir( $params[ 'selected_dir' ] ) )
		{
			$fileFullPath = $dirPath . '/' . $params[ 'name' ];
		}
		else
		{
			$this->_error = WFM_DIR_BADBASEDIR;
			return false;
		}

		if ( $this->getSecurity()->canViewFile( $fileFullPath )
			&& AppConfig::getInstance()->getValWithIndex('dir.root.www', $params['selected_dir']) != ''
		)
		{
			if ( file_exists( $fileFullPath ) )
			{
				return array(
					'link' => AppConfig::getInstance()->getValWithIndex('dir.root.www.host', $params['selected_dir']) ."/".AppConfig::getInstance()->getValWithIndex('dir.root.www', $params['selected_dir']).$params['name'],
					'path' => $fileFullPath
				);
			}
			else
			{
				$this->_error = WFM_FILE_NOT_EXISTS;
			}
		}
		else
		{
			$this->_error = WFM_FILE_READING_DENIED;
		}
		return false;
	}

	/**
	 * @param $params
	 * @return bool|string
	 */
	public function fileGetContent( $params )
	{

		if ( $dirPath = $this->getSelectedDir( $params[ 'selected_dir' ] ) )
		{
			$fileFullPath = $dirPath . '/' . $params[ 'name' ];
		}
		else
		{
			$this->_error = WFM_DIR_BADBASEDIR;
			return false;
		}

		if ( $this->getSecurity()->canViewFile( $fileFullPath ) )
		{
			if ( file_exists( $fileFullPath ) )
			{
				$data = file_get_contents( $fileFullPath );
				if ( $data !== false )
				{
					return $data; //htmlspecialchars( $data );
				}
				$this->_error = WFM_FILE_READING_FAILED;
			}
			else
			{
				$this->_error = WFM_FILE_NOT_EXISTS;
			}
		}
		else
		{
			$this->_error = WFM_FILE_READING_DENIED;
		}
		return false;
	}

	/**
	 * Saves incoming POST['file_content'] to specified file
	 *
	 * @param array $params {dir_id, current_dir, name}
	 * @return bool
	 */
	public function fileSaveContent( $params )
	{
		if ( $dirPath = $this->getSelectedDir( $params[ 'dir_id' ] ) )
		{
			$fileFullPath = $dirPath . '/' . $params[ 'current_dir' ] . '/' . $params[ 'name' ];
			if ( !realpath( $fileFullPath ) )
			{
				$this->_error = WFM_FILE_NOT_EXISTS;
				return false;
			}
		}
		else
		{
			$this->_error = WFM_DIR_BADBASEDIR;
			return false;
		}

		$content = $_POST[ 'file_content' ];

		if ( $this->getSecurity()->canEditFile( $fileFullPath ) )
		{
			if ( $this->checkMaliciousContent( $content ) )
			{
				$res = @file_put_contents( $fileFullPath, $content );
				if ( $res !== false )
				{
					$this->getLogger()->addInfo($this->getAuth()->getUsername() . ' SAVES EDITED FILE ' . $fileFullPath );
					return true;
				}
				$this->_error = WFM_FILE_WRITING_FAILED;
			}
			else
			{
				$this->_error = WFM_FILE_EDIT_BADCONTENTFOUND;
			}
		}
		else
		{
			$this->_error = WFM_FILE_WRITING_DENIED;
		}
		return false;
	}

	/**
	 * @param      $content
	 * @param bool $stripBadContent
	 * @return bool
	 */
	public function checkMaliciousContent( $content, $stripBadContent = false )
	{
		//check for ssi includes (turned off)
		//if (preg_match('/<!--#/si', $content)) return false;

		//check for php code - replace all <?php for &lt;
		$content = preg_replace( array( '/<\?/', '/\?>/' ), array( '&lt?;', '?&gt;' ), $content );
		if ( $content == null )
		{
			return false; //return false on error
		}

		//check for java/vb/etc/script
		if ( preg_match( '/((\%3C)|<)(|([\s]+))script\b[^>]*((\%3E)|>)(.*?)((\%3C)|<)\/script((\%3E)|>)/si', $content ) )
		{
			//return false;
		}

		//detect js events attached to html elements
		if ( preg_match( '/on([a-zA-Z]+)=/si', $content ) )
		{
			//return false;
		}

		return true;
	}

	/**
	 * public only for testing purposes
	 *
	 * @param $dirIndex
	 * @return bool|mixed
	 */
	public function getSelectedDir( $dirIndex )
	{
		if ( $dirPath = AppConfig::getInstance()->getValWithIndex( 'dir.root', $dirIndex ) )
		{
			return $dirPath;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @return mixed
	 */
	public function getError()
	{
		return $this->_error;
	}

	/**
	 * Returns allowed exts for supplied dir (if any)
	 * @param string $dirname absolute path
	 * @return bool
	 */
	public function configGetAllowedExtension($dirname)
	{
		return $this->getSecurity()->getAllowedExtensions($dirname);
	}


	/**
	 * Return all current config data
	 * @param $params
	 * @return array
	 */
	public function configGet($params)
	{
		$dirName = false;
		if ( $dirPath = $this->getSelectedDir( $params[ 'selected_dir' ] ) )
		{
			$dirName = $dirPath . '/' . $params[ 'name' ];
		}

		$extensions = $this->configGetAllowedExtension( $dirName );
		$data = AppConfig::getInstance()->getAllVals();

		return
				array(
					'file.create.banned' => $data['file.create.banned'],
					'editor.banned.extensions' => $data['editor.banned.extensions'],
					'editor.allowed.extensions' => $extensions,
					'editor.groups_all' => $this->getAuth()->getGroups(true),
					'editor.groups' => array_values( $this->getAuth()->getGroups() )
				);
	}

}
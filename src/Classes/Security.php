<?php
/**
 * Security permissions class
 * User: chorry
 * Date: 13.03.12
 */

class Security
{
	/**
	 * @var Components_Session_Storage
	 */
	private $_storage;

	/**
	 * @param null $storage (Object to keep auth data)
	 */
	public function __construct( $storage = null )
	{
		if ( $storage != null )
		{
			$this->_storage = $storage;
		}
	}

	/**
	 * Checks $param for regexp match
	 *
	 * @param array  $rules
	 * @param string $param
	 * @return mixed rule/false
	 */
	private function _checkRule( $rules, $param )
	{
		if ( count( $rules ) < 1 )
		{
			return false;
		}
		foreach ( $rules as $rule )
		{

			if ( is_array($rule) )
			{
				$ruleKeys  = array_keys( $rule );
			}
			else
			{
				$ruleKeys = array($rule);
			}

			foreach($ruleKeys as $ruleItem)
			{
				if ( preg_match( DELIMITER.$ruleItem.DELIMITER, $param ) )
				{
					return $ruleItem;
					break;
				}
			}
		}

		return false;
	}

	/**
	 * Checks if user can create specified directory
	 *
	 * @param null $dirName
	 * @return bool
	 */
	public function canCreateDir( $dirName = null )
	{
		$dirName = Helper::getInstance()->stripExtraSlashes( $dirName );

		if (
			!$this->_checkRule( AppConfig::getInstance()->getVal( 'dir.create' ), $dirName )
		)
		{
			return false;
		}

		//russian names are banned
		if ( preg_match( '/[А-Яа-я]/si', $dirName ) )
		{
			return false;
		}
		return true;
	}

	/**
	 * Checks if user can delete rename specified directory
	 *
	 * @param null $dirName
	 * @return bool
	 */
	public function canRenameDir( $dirName )
	{
		$dirName = Helper::getInstance()->stripExtraSlashes( $dirName );
		$allowed = false;

		if (
			!$this->_checkRule( AppConfig::getInstance()->getVal( 'dir.delete' ), $dirName )
		)
		{
			return false;
		}

		if ( preg_match( '/[А-Яа-я]/si', $dirName ) )
		{
			return false;
		}
		return true;
	}

	/**
	 * Checks if user can delete specified directory
	 *
	 * @param $dirName
	 * @return bool
	 */
	public function canDeleteDir( $dirName )
	{
		if (
			!$this->_checkRule( AppConfig::getInstance()->getVal( 'dir.delete' ), $dirName )
		)
		{
			return false;
		}

		return true;
	}

	/**
	 * Checks if user can get specified dir listing
	 *
	 * @param null $dirPath
	 * @return bool
	 */
	public function canListDir( $dirPath = null )
	{
		//no uber-auth for now, just make sure user doesnt go above his sandbox dir
		if ( $dirPath != null )
		{
			if ( !realpath( $dirPath )
			     || !$this->_checkRule( AppConfig::getInstance()->getVal( 'dir.list' ), $dirPath )
			)
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Checks if user can create specified file
	 *
	 * @param null $fileName
	 * @return bool
	 */
	public function canCreateFile( $fileName = null )
	{

		if ( preg_match( '/[А-Яа-я]/si', $fileName )
			||
			$this->_checkRule( AppConfig::getInstance()->getVal( 'file.create.banned' ), $fileName ))
		{
			return false;
		}
		return true;
	}

	/**
	 * Checks if user can rename specified file
	 *
	 * @param null $fileName
	 * @return bool
	 */
	public function canRenameFile( $fileName = null )
	{
		if ( preg_match( '/[А-Яа-я]/si', $fileName )
		    ||
		  	$this->_checkRule( AppConfig::getInstance()->getVal( 'file.create.banned' ), $fileName ))
		{
			return false;
		}

		return true;
	}

	/**
	 * Checks if user can delete specified file
	 * @param $fileName
	 * @return bool
	 */
	public function canDeleteFile( $fileName )
	{
		return $this->canViewFile($fileName);
	}

	/**
	 * Checks if user can upload file to specified dir
	 *
	 * @param $dirName
	 * @return bool
	 */
	public function canUploadFile( $dirName )
	{
		return $this->canListDir( $dirName );
	}

	/**
	 * Checks if user is allowed to view file's content
	 *
	 * @param null $fileName
	 * @return bool
	 */
	public function canViewFile( $fileName = null )
	{
		if ( $fileName != null )
		{
			$allowedExtensions = ($this->getAllowedExtensions($fileName));

			$baseFileName = basename( $fileName );
			$extension = array_pop( explode(".",$baseFileName) );

			if (in_array( $extension, $allowedExtensions))
			{
				return true;
			}

			if (in_array(
						is_null($extension) ? $fileName : $extension, AppConfig::getInstance()->getVal( 'editor.banned.extensions' )
					)
			)
			{
				return false;
			}
		}
		return true;
	}

		/**
	 * Checks if user can save edited file
	 *
	 * @param string $fileName absolute path, please
	 * @return bool
	 */
	public function canEditFile( $fileName = null )
	{
		if ( $fileName != null )
		{
			$file              = new SplFileInfo( $fileName );

			$allowedExtensions = $this->getAllowedExtensions($fileName);

			if ( in_array( $file->getExtension(), $allowedExtensions )
			     || in_array( '*', $allowedExtensions )
			)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns list of directories never to be shown
	 *
	 * @return array
	 */
	public function getBannedDirs()
	{
		return AppConfig::getInstance()->getVal( 'dir.banned' );
	}

	/**
	 * Returns list of allowed editor extensions
	 * @param $fileName (optional) - full file path <br>
	 * If set, then extra checking for matching 'editor.allowed.extensions.by.dirs' pattern will be made
	 * @return mixed
	 */
	public function getAllowedExtensions( $fileName )
	{
		$allowedExtensions = AppConfig::getInstance()->getVal( 'editor.allowed.extensions' );

		if ( is_array( AppConfig::getInstance()->getVal( 'editor.allowed.extensions.by.dirs' ) ) )
		{
			$rules  = array_keys( AppConfig::getInstance()->getVal( 'editor.allowed.extensions.by.dirs' ) );
			$ruleId = $this->_checkRule( $rules, $fileName );
			if ( $ruleId !== false )
			{
				$allowedExtensions = AppConfig::getInstance()->getValWithIndex( 'editor.allowed.extensions.by.dirs', $ruleId );
			}
		}
		return $allowedExtensions;
	}

}

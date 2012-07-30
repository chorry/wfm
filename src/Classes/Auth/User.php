<?php
/**
 * Provides authentication via auth transport
 * User: chorry
 * Date: 22.03.12
 */

class Auth_User
{
	/**
	 * @var Components_Session_Storage
	 */
	protected $_session;
	protected $_transport;

	/**
	 * @param null $transport
	 */
	public function __construct( $transport = null )
	{
		if ( isset ( $transport ) )
		{
			$this->_transport = $transport;
		}
	}

	/**
	 * @param object $transport
	 * @return Auth_User
	 */
	public function setTransport( $transport )
	{
		$this->_transport = $transport;
		return $this;
	}

	/**
	 * @return object Components_SessionServer_SessionServerAuth
	 * @throws Exception
	 */
	public function getTransport()
	{
		if ( isset( $this->_transport ) )
		{
			return $this->_transport;
		}
		throw new Exception ( 'Auth transport is not set' );
	}

	/**
	 * @return bool
	 */
	public function checkAuth()
	{
		if ( $this->getSession()->get( 'user.auth.id' ) )
		{
			//restore session
			return true;
		}
		return false;
	}

	/**
	 * @return bool
	 */
	public function getUsername()
	{
		if ( $username = $this->getSession()->get( 'user.login' ) )
		{
			return $username;
		}
		return false;
	}


	public function getLogin()
	{
		return $this->getSession()->get( 'user.login' );
	}

	/**
	 * Returns list of user groups
	 *
	 * @param bool $nofilter    (if set to true, will return groups even with non-existent rule configs)
	 * @param bool $withDesc    (if set to true, will return group descriptions)
	 * @return mixed bool|array
	 */
	public function getGroups( $nofilter = false, $withDesc = false )
	{

		$groups = $this->getSession()->get( 'user.groups' );
		if ( !$nofilter )
		{
			$files = array_map( function( $v )
				{
					return basename( $v, '.php' );
				}, glob( RULES_DIR . '/*.php' )
			);

			if ( $this->getSession()->get( 'user.groups' ) )
			{
				$groups =  array_intersect( $files, $this->getSession()->get( 'user.groups' ) );
			}
		}

		if ($withDesc)
		{
			$groups = array_map( function($v) {
					$names = AppConfig::getInstance()->getVal('group.names');
					return array( 'id' => $v, 'name' => ( isset($names[$v]) ? $names[$v] : $v ) );
				}, $groups);
		}

		return $groups;
	}


	/**
	 * Returns user's currently active group
	 *
	 * @param  bool  $withName
	 * @return mixed bool|string|array
	 */
	public function getActiveGroup($withName = false)
	{
		if ( $rule = $this->getSession()->get( 'user.group.active' ) )
		{
			if ($withName)
			{
				return array(
					'id'=>$rule,
					'name'=> ( $name = AppConfig::getInstance()->getValWithIndex('group.names', $rule) ) ? $name : $rule
				);
			}
			return $rule;
		}
		return false;
	}

	/**
	 * Saves active group name into storage
	 *
	 * @param $group
	 * @return Auth_User
	 */
	public function setActiveGroup( $group )
	{
		$this->getSession()->set( 'user.group.active', $group );
		return $this;
	}

	/**
	 * @param $login
	 * @param $pass
	 * @return bool|Exception
	 */
	public function auth( $login, $pass )
	{
		$data = array();
		try
		{

			$result = $this->getTransport()->auth( $login, $pass, $data );
			if ( $result )
			{
				$this->getSession()->set( 'user.auth.id', $data[ 'session_id' ] );
				$this->getSession()->set( 'user.groups', explode( ' ', trim($data[ 'groups' ]) ) );
				$this->getSession()->set( 'user.login', $data[ 'login' ] );
				return $result;
			}
			else
			{
				return false;
			}
		}
		catch ( Exception $e )
		{
			return $e;
		}
	}

	/**
	 * @return bool
	 */
	public function deauth()
	{
		$output = array();
		try
		{
			$result = $this->getTransport()->logout( $this->getSession()->get( 'user.auth.id' ), $output );
			$this->_session->destroy();
			return $result;
		}
		catch ( Exception $e )
		{
			//already deauthed
			return true;
		}
	}

	/**
	 * @return Components_Session_Storage
	 */
	public function getSession()
	{
		if ( !isset( $this->_session ) )
		{
			$this->_session = new Components_Session_Storage();
			$this->_session->start();
		}

		return $this->_session;
	}

}

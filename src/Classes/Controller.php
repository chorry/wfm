<?php
/**
 * User: chorry
 * Date: 13.03.12
 */

class Controller
{
	/**
	 * @var View
	 */
	private $_view;
	private $_fileManager;
	protected $_auth;

	public function __construct()
	{
		$this->_auth = new Auth_User( new Components_SessionServer_SessionServerAuth( DOMAIN ) );
		if ( $this->_auth->checkAuth() )
		{
			$this->_loadRules();
		}
		return;
	}

	/**
	 * TODO: Move to auth, maybe?
	 *
	 * @return mixed
	 */
	private function _loadRules()
	{
		//load rules
		require_once( PROJECT_ROOT . '/data/configs/groups/global.php' );

		//TODO: make group selector
		if ( is_array( $this->_auth->getGroups( ) ) )
		{
			//load active group
			$activeGroup = $this->_auth->getActiveGroup();

			if ( !$activeGroup
			     || !file_exists( PROJECT_ROOT . '/data/configs/groups/' . $activeGroup . '.php' )
			)
			{
				foreach ( $this->_auth->getGroups() as $groupId )
				{
					$ruleFile = PROJECT_ROOT . '/data/configs/groups/' . $groupId . '.php';
					if ( file_exists( $ruleFile ) )
					{
						$activeGroup = $groupId;
						break;
					}
				}
			}

			if ( $activeGroup )
			{
				require_once( PROJECT_ROOT . '/data/configs/groups/' . $activeGroup . '.php' );
				$this->_auth->setActiveGroup( $activeGroup );
			}

			return;
		}
		return;
	}

	public function actionIndex()
	{

		$params       = array();
		$templateName = 'login';
		if ( $username = $this->_auth->getUsername() )
		{
			$params       = array(
				'username'   => $username,
				'group_active' => $this->_auth->getActiveGroup(true),
				'groups'     => $this->_auth->getGroups( false, true),
			);
			$templateName = 'index';
		}

		$this->getView()->showTemplate( $templateName . '.twig', $params );
	}

	public function setView( $view )
	{
		$this->_view = $view;
	}


	/**
	 * @return View object
	 */
	public function getView()
	{
		if ( !isset( $this->_view ) )
		{
			$this->_view = new View();
		}
		return $this->_view;
	}

	public function setFileManager( $fileManager )
	{
		$this->_fileManager = $fileManager;
	}

	/**
	 * @return FileManager object
	 */
	public function getFileManager()
	{
		if ( !isset( $this->_fileManager ) )
		{
			$this->_fileManager = new FileManager();
		}
		return $this->_fileManager;
	}

	protected function _sendResult( $result )
	{
		print json_encode( $result );
	}
}

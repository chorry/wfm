<?php
/**
 * User: chorry
 * Date: 22.03.12
 */

class Controller_Login extends Controller
{
	/**
	 * @params
	 * @return bool
	 */
	public function actionLogin()
	{
		$method = func_get_arg( 0 );
		$result = false;
		try
		{
			if ( $this->_auth->checkAuth() )
			{
				$result = true;
			}
			else
			{
				if ( $result = $this->_auth->auth( $_POST[ 'login' ], $_POST[ 'password' ] ) )
				{
					$result = true;
				}
			}
		}
		catch ( Exception $e )
		{
			//whatever
		}

		if ( preg_match ('#'.REPLACE_REDIRECT.'#', $_SERVER['REDIRECT_URL']) )
    {
	    $newHeader = "/".REPLACE_REDIRECT;
    }
    else
    {
	    $newHeader = '/';
    }

		if ($result) header( 'Location: '. $newHeader );
		$this->actionIndex();

	}

	/**
	 * Changes user group
	 *
	 * @return null
	 */
	public function actionChangeGroup()
	{
		$args = func_get_args();
		if ( in_array( $args[ 0 ], $this->_auth->getGroups() ) )
		{
			$this->_auth->setActiveGroup( $args[ 0 ] );
			$result = true;
		}
		else
		{
			$result = false;
		}

		$this->_sendResult( $result );
	}

	/**
	 * Do logout
	 *
	 * @return bool
	 */
	public function actionLogout()
	{
		$this->_auth->deauth();
		return true;
	}

}

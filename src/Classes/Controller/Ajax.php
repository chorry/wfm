<?php
/**
 * User: chorry
 * Date: 13.03.12
 */

class Controller_Ajax extends Controller
{
	/**
	 * @param $params : {type=file/dir; action=create/delete/etc; name=>%name%}
	 * @return bool
	 */
	public function actionManage( $params )
	{
		//ajax sends us data in utf-8, even when its' cp1251.
		if ( isset( $_POST[ 'file_content' ] ) )
		{
			$content = ( iconv( 'utf-8', 'cp1251', $_POST[ 'file_content' ] ) );
			if ( strlen( $content ) > 0 )
			{
				$_POST[ 'file_content' ] = $content;
			}
			else
			{
				return false;
			}
		}

		$actionName = $params[ 'type' ] . $params[ 'action' ];

		if ( method_exists( $this->getFileManager(), $actionName ) )
		{
			unset( $params[ 'action' ] );
			unset( $params[ 'type' ] );
			if ( count( $params ) > 1 )
			{
				$result = call_user_func_array(
					array( $this->getFileManager(), $actionName ),
					array( $params )
				);
			}
			else
			{
				$result = $this->getFileManager()->$actionName( $params[ 'name' ] );
			}

			//we give user file's content
			if ( is_string( $result ) )
			{
				//потому что нелюбовь с кириллицей у json_encode :(
				/*
				print
						str_replace(
							array( '\\', '"', '/' ),
							array( '\\\\', '\"', '\/' ),
							$result
						);
				*/
				print $result;
			}
			else
			{
				$this->sendResponse( $result );
			}
		}
		else
		{
			print 'Action [' . $actionName . '] is not supported';
		}
		return false;
	}

	/**
	 * Sends response as JSON
	 *
	 * @param $result
	 * @return null
	 */
	public function sendResponse( $result )
	{
		$resp[ 'data' ] = $result;
		if ( !$result )
		{
			$resp[ 'error' ] = $this->getFileManager()->getError();
		}
		print json_encode( $resp );
	}

}




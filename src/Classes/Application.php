<?php
/**
 * User: chorry
 * Date: 22.02.12
 */

class Application
{

	public function __construct()
	{
		$router = new Components_Router();

		$this->_router = &$router;
		include_once ROUTES_CONFIG;

		$this->processRequest();
	}

	/**
	 * Process REST request
	 */
	public function processRequest()
	{
		$params = array();
		
		if ( REPLACE_REDIRECT != '' )
		{
			$_SERVER['REQUEST_URI'] = str_replace(REPLACE_REDIRECT,'',$_SERVER['REQUEST_URI']);
		}

		$match = $this->_router->match( $_SERVER[ 'REQUEST_METHOD' ], $_SERVER[ 'REQUEST_URI' ] );

		$controller = $match[ 'controller' ];
		$action     = $match[ 'action' ];

		if ( strstr( $match[ 'params' ], '=' ) )
		{
			$params = $this->parseParams( $match[ 'params' ] );
		}
		else
		{
			$params[ 'params' ] = $match[ 'params' ];
		}

		$params[ 'format' ] = $match[ 'format' ];

		$this->dispatch( $controller, 'action' . $action, $params );

	}

	/**
	 * Parses GET params into array (a=5&b=3 => {a:5,b:3} )
	 * @param $params
	 * @return array|bool
	 */
	public function parseParams( $params )
	{

		$paramsString = $params;
		if ( substr( $paramsString, 0, 1 ) == '?' )
		{
			$paramsString = substr( $paramsString, 1, ( strlen( $paramsString ) - 1 ) );
		}

		$chunks       = explode( '&', $paramsString );
		$resultParams = array();

		foreach ( $chunks as $chunk )
		{
			$kv                       = explode( '=', $chunk );
			$resultParams[ $kv[ 0 ] ] = $kv[ 1 ];
		}

		if ( count( $resultParams ) > 0 )
		{
			return array( $resultParams );
		}
		else
		{
			return false;
		}
	}

	public function dispatch( $class, $action, $params )
	{
		try
		{
			if ( !class_exists( $class ) )
			{
				throw new Exception ( 'no class ' . $class );
			}
			$classObject = new $class;
		}
		catch ( Exception $e )
		{
			//TODO: 404 handler
			//print 'Class [' . $class . '] not found';
			return;
		}

		if ( method_exists( $classObject, $action ) )
		{
			$result = call_user_func_array( array( $classObject, $action ), $params );
			return $result;
		}
		else
		{
			print $class . ' knows nothing \'bout ' . $action . ', bro.';
			return false;
		}
	}
}

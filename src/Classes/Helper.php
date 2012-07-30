<?php
/**
 * User: chorry
 * Date: 23.03.12
 */

class Helper
{
	static private $_instance;

	/**
	 * @static
	 * @return Helper
	 */
	static public function getInstance()
	{
		if ( !isset( self::$_instance ) )
		{
			self::$_instance = new Helper();
		}
		return self::$_instance;
	}

	public function __clone()
	{
	}

	/**
	 * Converts double slashes to single from string
	 * @param $val
	 * @param bool $removeTrailing
	 * @return mixed|string
	 */
	public function stripExtraSlashes( $val, $removeTrailing = false )
	{
		$val = preg_replace( '#[\/]+#', '/', $val );
		if ( $removeTrailing )
		{
			if ( substr( $val, -1 ) == '/' )
			{
				$val = substr( $val, 0, -1 );
			}
		}
		return $val;
	}
}

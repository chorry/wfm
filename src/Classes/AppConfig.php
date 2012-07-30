<?php
/**
 * User: chorry
 * Date: 19.03.12
 */

class AppConfig
{
	/**
	 * @var AppConfig
	 */
	static $_instance;

	/**
	 * @var array
	 */
	static $_config;

	public function __construct()
	{
	}

	public function __clone()
	{
	}

	/**
	 * @static
	 * @return AppConfig
	 */
	static public function getInstance()
	{
		if ( !isset( self::$_instance ) )
		{
			self::$_instance = new AppConfig();
		}
		return self::$_instance;
	}

	/**
	 * Returns all config values
	 * @return array
	 */
	public function getAllVals()
	{
		return self::$_config;
	}

	/**
	 * Returns value if exists, otherwise - false
	 * @param $var
	 * @return mixed
	 */
	public function getVal( $var )
	{
		if (isset (self::$_config[ $var ]) )
		{
			return self::$_config[ $var ];
		}
		return false;
	}

	/**
	 * Returns value from array data by its index. If its not set - returns false.
	 * @param $var
	 * @param $index
	 * @return mixed string|bool
	 */
	public function getValWithIndex( $var, $index )
	{

		if (
			is_array(self::$_config[ $var ]) &&
			isset( self::$_config[ $var ][ $index ] )
		)
		{
			return self::$_config[ $var ][ $index ];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Sets value.
	 * @param $k
	 * @param $v
	 * @return AppConfig
	 */
	public function setVal( $k, $v )
	{
		self::$_config[ $k ] = $v;
		return self::$_instance;
	}

}

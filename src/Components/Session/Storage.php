<?php
/**
 * User: chorry
 * Date: 22.03.12
 */

class Components_Session_Storage
{
	protected $_options;
	protected $_exists = false;

	public function __construct( $options = null )
	{
		$cookieParams   = session_get_cookie_params();
		if (!is_array($options))
		{
			$options = array();
		}

		$this->_options = array_merge(
			array(
			     'lifetime' => $cookieParams[ 'lifetime' ],
			     'path'     => $cookieParams[ 'path' ],
			     'domain'   => $cookieParams[ 'domain' ],
			     'secure'   => $cookieParams[ 'secure' ],
			     'httponly' => isset( $cookieParams[ 'httponly' ] ) ? $cookieParams[ 'httponly' ] : false
			), $options
		);
	}

	public function start() {
		if (!$this->_exists)
		{
			if (isset($this->_options['session_id']))
			{
				session_id($this->_options['session_id']);
			}

			$this->_exists = true;
			session_start();
			$this->_options['session_id'] = session_id();
		}
		return $this;
	}

	/**
	 * @return bool
	 */
	public function destroy()
	{
		return session_destroy();
	}

	/**
	 * @return string
	 */
	public function getSessionId()
	{
		return session_id();
	}

	/**
	 * @param $k
	 * @return mixed
	 */
	public function get($k)
	{
		if (array_key_exists($k, $_SESSION))
		{
			return $_SESSION[$k];
		}
		return false;
	}

	public function del($k){
		unset ($_SESSION[$k]);
		return $this;
	}

	public function set($k, $v)
	{
		$_SESSION[$k] = $v;
		return $this;
	}

}
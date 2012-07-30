<?php
/**
 * User: chorry
 * Date: 13.03.12
 */

class View
{
	public function __construct()
	{
		$loader      = new Twig_Loader_Filesystem( PROJECT_ROOT . '/data/templates' );
		$this->_twig = new Twig_Environment( $loader, array('charset'=>'windows-1251') );
		$this->_twig->addExtension( new Twig_Extension_Number() );
		$this->_twig->addExtension( new Twig_Extension_Escaper() );
		$this->_twig->addExtension( new Twig_Extension_Hours() );
		$this->_twig->addExtension( new Twig_Extension_Simpledump() );
	}

	/**
	 * @param $templateName
	 * @param null $params
	 */
	public function showTemplate( $templateName, $params = null )
	{
		$template = $this->_twig->loadTemplate( $templateName );

		if ( $params == null )
		{
			$params = array();
		}
		print $template->render( $params );
	}

	//simple 404
	public function show404() {
		header("HTTP/1.0 404 Not Found");
		die();
	}
}
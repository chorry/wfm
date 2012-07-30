<?php
/**
 * User: chorry
 * Date: 22.12.11
 */
 
class Twig_Extension_Simpledump extends Twig_Extension
{
	public function getFilters()
	{
			 return array('simpledump' => new Twig_Filter_Function('twig_dump_filter'));
	}

	 /**
		* Name of this extension
		*
		* @return string
		*/
	public function getName()
	{
			 return 'SimpleDump';
	}

}

function twig_dump_filter($params = null)
{
	print '<pre>'; var_dump($params); print '</pre><hr noshade size=1>';
}
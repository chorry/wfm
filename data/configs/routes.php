<?php
/**
* Default routes
*/

/**
 * @var $router Components_Router
 */
$router->route('default', '/')
->defaults(array(
	'controller' => 'Controller',
	'action' => 'index'
));

$router->route('userLogin', '/login')
	->defaults(array(
	'controller' => 'Controller_Login',
	'action' => 'Login',
	'params'=>'params'
))
->get(array(
	'controller' => 'Controller_Login',
	'action' => 'Login',
	'params'=>'GET'
))
->post(array(
	'controller' => 'Controller_Login',
	'action' => 'Login',
	'params'=>'POST'
));

$router->route('userLogout', '/logout')
	->defaults(array(
	'controller' => 'Controller_Login',
	'action' => 'Logout',
	'params'=>'params'
));

$router->route('userChangeGroup','/group/<:params>(/<*ignore>)')
	->defaults(array(
	'controller' => 'Controller_Login',
	'action' => 'changeGroup',
	'params'=>'params'
));

$router->route('ajaxControllerForFileManager', '/manageFiles/<*params>')
	->defaults(array(
	'controller' => 'Controller_Ajax',
	'action' => 'Manage',
	'params'=>'params'
));
<?php
/**
 * Date: 13.03.12
 */

define ('REPLACE_REDIRECT',''); //if application is located in subdir, and redirected via rewrite - set value to relative subdir location

define ( 'WWW_HOST', 'localhost' );
define ( 'PROJECT_ROOT', '/www/stpFileOffice' );
define ( 'FILES_DIR', PROJECT_ROOT . '/shared' ); //used in testing
define ( 'SITENAME', 'WFM v0.3');
define ( 'ROUTES_CONFIG', PROJECT_ROOT . '/data/configs/routes.php' );
define ( 'LOG_PATH', PROJECT_ROOT . '/data/logs' );

//where to look for usergroup rules
define ( 'RULES_DIR', PROJECT_ROOT . '/data/configs/groups' );

define ( 'PERM_DIR_CREATE', 0777);
define ( 'PERM_FILE_CREATE', 0666);
define ( 'DOMAIN', 'ldap.host'); //address to check ldap rights
define ( 'DELIMITER', '#');



<?php
/**
 * Bootstrap application
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id$
 */

define('IN_TRUELEGEND', true);

// TrueLegend requires PHP 5.2+
if (!version_compare(PHP_VERSION, '5.2', '>='))
{
    die('TrueLegend requirement PHP 5.2 or higher to run. this version is ' . PHP_VERSION . ' .');
}

// Debug switch
defined('DEBUG') || define('DEBUG', false);
if (DEBUG)
{
    @ini_set('display_errors', 1);
    error_reporting(E_ALL | E_STRICT);
}
else
{
    @ini_set('display_errors', 0);
    error_reporting(0);
}

// Set the default timezone
if (function_exists('date_default_timezone_set'))
{
    date_default_timezone_set('Asia/Shanghai');
}

// The name of the user/group to run httpd
define('HTTPD_USER_NAME', 'nobody');
define('HTTPD_GROUP_NAME', 'nobody');

// Define the absolute paths for configured directories,NOT be changed.
define('TRUELEGEND_LINK_PATH', '/elink/truelegend');
define('BASE_QUEUE_SOURCE_PATH', TRUELEGEND_LINK_PATH . '/source');
define('BASE_QUEUE_SOURCE_APP_PATH', TRUELEGEND_LINK_PATH . '/source/App');
define('BASE_QUEUE_DATA_PATH', TRUELEGEND_LINK_PATH . '/data');
define('BASE_QUEUE_LOGS_PATH', TRUELEGEND_LINK_PATH . '/logs');
define('BASE_QUEUE_MONITOR_PATH', '/dev/shm/truelegend');

// Maximum value of loading queue files,too much files will cause PHP memory overflow
define('MAX_QUEUE_FILE_LOAD', 1000);

// Enabled auto-loader
spl_autoload_register('__autoload__');

// __autoload any class given as param $className
function __autoload__($className)
{
    $className = ucfirst($className);

    $prefix = 'TrueLegend_';
    $prefixLen = strlen($prefix);

    if (substr($className, 0, $prefixLen) == $prefix)
    {
        $newClassName = substr($className, $prefixLen);
    }
    else
    {
        $newClassName = $className;
    }

    $classFile = BASE_QUEUE_SOURCE_PATH . '/' . str_replace('_', '/', $newClassName) . '.php';

    if (is_file($classFile))
    {
        include_once($classFile);
    }
    else
    {
        throw new Exception('The class file for $className could not be found');
    }
}

?>
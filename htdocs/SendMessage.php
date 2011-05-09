<?php
/**
 * Send message API
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id:$
 */

// Load bootstrap
require '/elink/truelegend/source/Bootstrap.php';

// Remove slashes added by magic quotes
if (get_magic_quotes_gpc())
{
    function stripslashes_gpc(&$value)
    {
        $value = stripslashes($value);
    }
    array_walk_recursive($_GET, 'stripslashes_gpc');
    array_walk_recursive($_POST, 'stripslashes_gpc');
    array_walk_recursive($_COOKIE, 'stripslashes_gpc');
    array_walk_recursive($_REQUEST, 'stripslashes_gpc');
}

function errorOut($data)
{
    $output = array
    (
        'status' => 0,
        'data'   => $data
    );

    die(json_encode($output));
}

function succOut()
{
    $output = array
    (
        'status' => 1
    );

    die(json_encode($output));
}

$appname = isset($_REQUEST['appname']) ? trim($_REQUEST['appname']) : '';

$message = isset($_REQUEST['message']) ? trim($_REQUEST['message']) : '';

if (empty($appname) || empty($message))
{
    errorOut('param empty');
}

$app = BASE_QUEUE_SOURCE_APP_PATH . '/' . $appname . '/Process.php';

if (!is_file($app))
{
    errorOut('app no exists');
}

require $app;

$class = "App_{$appname}_Process";

$_obj = new $class();

if (!$_obj->sendMessage($message))
{
    errorOut('system error');
}

succOut();

?>
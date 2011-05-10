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
        'data' => $data
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

$queuename = isset($_REQUEST['queuename']) ? trim($_REQUEST['queuename']) : '';

$queuedata = isset($_REQUEST['queuedata']) ? trim($_REQUEST['queuedata']) : '';

if (empty($queuename) || empty($queuedata))
{
    errorOut('param empty');
}

$queueProcess = Queue_Factory::getQueueProcess($queuename);

if (!$queueProcess)
{
    errorOut('queue no exists');
}

if (!$queueProcess->sendMessage($queuedata))
{
    errorOut('system error');
}

succOut();

?>
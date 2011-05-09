<?php defined('IN_TRUELEGEND') or die('No direct script access.');
/**
 * Demo queue process
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id:$
 */

// Path to app queue monitor
define('APP_QUEUE_MONITOR_PATH', BASE_QUEUE_MONITOR_PATH . '/Demo/demo');

// Path to app queue data
define('APP_QUEUE_DATA_PATH', BASE_QUEUE_DATA_PATH . '/Demo');

// Path to app queue log 
defined('APP_QUEUE_LOG_PATH') || define('APP_QUEUE_LOG_PATH', BASE_QUEUE_LOGS_PATH . '/Demo');

class App_Demo_Process extends Queue_Process
{
    /**
     * Constructor
     *
     * @return void
     */
    function __construct()
    {
        parent::__construct(APP_QUEUE_MONITOR_PATH, APP_QUEUE_DATA_PATH);
    }

    /**
     * Initialize
     *
     * @return bool
     */
    protected function initialize()
    {
        return true;
    }

    /**
     * Do process
     *
     * @param string $message
     * @return bool
     */
    public function doProcess($message)
    {
        echo $message;

        return true;
    }

    /**
     * Finish
     *
     * @return bool
     */
    protected function finish()
    {
        return true;
    }
}

?>
<?php
/**
 * Demo queue daemon
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id:$
 */

// Sets debugging off.
define('DEBUG', false);

// Load bootstrap
require '/elink/truelegend/source/Bootstrap.php';

// Path to app queue log 
define('APP_QUEUE_LOG_PATH', BASE_QUEUE_LOGS_PATH . '/Demo');

class App_Demo_Daemon extends System_UnixDaemon
{
    /**
     * Constructor
     *
     * @return void
     */
    function __construct($pidFileLocation)
    {
        parent::__construct($pidFileLocation);
    }

    /**
     * Do task
     *
     * @return bool
     */
    function doTask()
    {
        $app = new App_Demo_Process();

        if ($app->error())
        {
            echo "Queue construct failure,error:" . $app->error() . "\n";

            $this->error($app->error());

            $this->stop();

            return false;
        }

        while (true)
        {
            if (!$app->process())
            {
                echo "Queue process failure:" . $app->error() . "\n";
            }
            // Delays 100 micro second
            usleep(100000);
        }

        return true;
    }
}

/**
 * Print help info
 *
 * @return void
 */
function help()
{
    print <<<EOF
	Usage:
		php Demod.php <option>

	Options:
		start - Start the daemon
		stop - Stop daemon
		help - Show help
EOF;
}

function main()
{
    $option = $_SERVER['argc'] > 1 ? $_SERVER['argv'][1] : 'start';

    $daemon = new App_Demo_Daemon(APP_QUEUE_LOG_PATH . '/daemon.pid');

    switch ($option)
    {
        case "start":

            echo "Daemon starting...\n";

            if (!$daemon->start())
            {
                echo 'Daemon start failure, error: ' . $daemon->error() . "\n";
                exit();
            }

            echo "Running...\n";

            if (!$daemon->doTask())
            {
                echo 'Daemon do task failure, error: ' . $daemon->error() . "\n";
            }

            break;

        case "stop":

            echo "Daemon stopping...\n";

            if (!$daemon->stop())
            {
                echo 'Daemon stop failure, error: ' . $daemon->error() . "\n";
                exit();
            }

            echo "Daemon stoped\n";

            break;

        case "help":

            help();

            break;

        default:

            echo "Unkown option: " . $option . "\n";

            break;
    }
}

main();

?>
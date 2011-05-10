<?php defined('IN_TRUELEGEND') or die('No direct script access.');
/**
 * Daemon base class
 *
 * Requirements:
 * Unix like operating system
 * PHP 4 >= 4.3.0 or PHP 5
 * PHP compiled with:
 * --enable-sigchild
 * --enable-pcntl
 *
 * @package binarychoice.system.unix
 * @author Michal 'Seth' Golebiowski <seth at binarychoice dot pl>
 * @copyright Copyright 2005 Seth
 * @since 1.0.3
 */

/**
 * UnixDaemon class

 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id:$
 */
abstract class System_UnixDaemon
{
    /**
     * User ID
     *
     * @var int
     */
    protected $userID = 99;

    /**
     * Group ID
     *
     * @var integer
     */
    protected $groupID = 99;

    /**
     * Terminate daemon when set identity failure ?
     *
     * @var bool
     */
    protected $requireSetIdentity = false;

    /**
     * Path to PID file
     *
     * @var string
     */
    protected $pidFileLocation = '/tmp/daemon.pid';

    /**
     * Home path
     *
     * @var string
     */
    protected $homePath = '/';

    /**
     * Current process ID
     *
     * @var int
     */
    private $pid = 0;

    /**
     * Is this process a children
     *
     * @var boolean
     */
    private $isChildren = false;

    /**
     * Is daemon running
     *
     * @var boolean
     */
    private $isRunning = false;

    /**
     * Stores error message
     *
     * @var string
     */
    private $error = '';

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct($pidFileLocation = "")
    {
        error_reporting(0);
        set_time_limit(0);
        ob_implicit_flush();
        clearstatcache();

        register_shutdown_function(array(&$this, 'releaseDaemon'));

        if (!empty($pidFileLocation) && is_dir(dirname($pidFileLocation)))
        {
            $this->pidFileLocation = $pidFileLocation;
        }
    }

    /**
     * Get/Set the error message
     *
     * @return string
     */
    public function error($error = null)
    {
        if (null === $error)
        {
            return $this->error;
        }
        else
        {
            $this->error = $error;
        }
    }

    /**
     * Starts daemon
     *
     * @return bool
     */
    public function start()
    {
        if (!defined('SIGHUP'))
        {
            $this->error('PHP is compiled without --enable-pcntl directive');

            Logger::fatal($this->error());

            return false;
        }
        // Check for CLI
        if (php_sapi_name() !== 'cli')
        {
            $this->error('You can only create daemon from the command line (CLI-mode)');

            Logger::fatal($this->error());

            return false;
        }
        // Check for POSIX
        if (!function_exists('posix_getpid'))
        {
            $this->error('PHP is compiled without --enable-posix directive');

            Logger::fatal($this->error());

            return false;
        }

        Logger::trace('Starting daemon');

        if (!$this->daemonize())
        {
            Logger::error('Could not start daemon');

            return false;
        }

        Logger::trace('Running...');

        $this->isRunning = true;

        return true;
    }

    /**
     * Stops daemon
     *
     * @return void
     */
    public function stop()
    {
        Logger::trace('Stoping daemon');

        if (!is_file($this->pidFileLocation))
        {
            Logger::trace('Daemon (no pid file) not running');
            
            return true;
        }
        
        $oldPid = @file_get_contents($this->pidFileLocation);

        if ($oldPid !== false && posix_kill(trim($oldPid), SIGTERM))
        {
            Logger::trace('Daemon stoped');

            $this->isRunning = false;

            return true;
        }
        else
        {
            $this->error = 'Could not stop daemon,pid: ' . $oldPid;

            Logger::error($this->error);

            return false;
        }
    }

    /**
     * Process
     *
     * @return void
     */
    abstract protected function doTask();

    /**
     * Daemonize
     *
     * Several rules or characteristics that most daemons possess:
     * 1) Check is daemon already running
     * 2) Fork child process
     * 3) Sets identity
     * 4) Make current process a session laeder
     * 5) Write process ID to file
     * 6) Change home path
     * 7) umask(0)
     *
     * @return void
     */
    private function daemonize()
    {
        ob_end_flush();

        if ($this->isDaemonRunning())
        {
            // Deamon is already running. Exiting
            return false;
        }

        if (!$this->fork())
        {
            // Coudn't fork. Exiting.
            return false;
        }

        if ($this->requireSetIdentity && !$this->_setIdentity())
        {
            // Required identity set failed. Exiting
            return false;
        }

        if (!posix_setsid())
        {
            $this->error('Could not make the current process a session leader');

            Logger::error($this->error());

            return false;
        }

        if (!$fp = @fopen($this->pidFileLocation, 'w'))
        {
            $this->error('Could not write to PID file');

            Logger::error($this->error());

            return false;
        }
        else
        {
            fputs($fp, $this->pid);
            fclose($fp);
        }
        //  @chdir ($this->homePath);
        // umask(0);
        declare(ticks = 1);

        pcntl_signal(SIGCHLD, array(&$this, 'sigHandler'));
        pcntl_signal(SIGTERM, array(&$this, 'sigHandler'));

        return true;
    }

    /**
     * Cheks is daemon already running
     *
     * @return bool
     */
    private function isDaemonRunning()
    {
        $oldPid = @file_get_contents($this->pidFileLocation);

        if ($oldPid !== false && posix_kill(trim($oldPid), 0))
        {
            $this->error('Daemon already running with PID: ' . $oldPid);

            Logger::error($this->error());

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Forks process
     *
     * @return bool
     */
    private function fork()
    {
        Logger::trace('Forking...');

        $pid = pcntl_fork();

        // error
        if ($pid == -1)
        {
            $this->error('Could not fork');

            Logger::error($this->error());

            return false;
        }
        // parent
        else if ($pid)
        {
            Logger::trace('Killing parent');

            exit();
        }
        // children
        else
        {
            $this->isChildren = true;
            $this->pid = posix_getpid();

            return true;
        }
    }

    /**
     * Sets identity of a daemon and returns result
     *
     * @return bool
     */
    private function setIdentity()
    {
        if (!posix_setgid($this->groupID) || !posix_setuid($this->userID))
        {
            $this->error('Could not set identity');

            Logger::error($this->error());

            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * Signals handler
     *
     * @return void
     */
    private function sigHandler($sigNo)
    {
        switch ($sigNo)
        {
            case SIGTERM: // Shutdown
                Logger::trace('Shutdown signal');
                sleep(1);
				echo "Daemon stopped.";
                exit();
                break;
            case SIGCHLD: // Halt
                Logger::trace('Halt signal');
                while (pcntl_waitpid(-1, $status, WNOHANG) > 0);
                break;
        }
    }

    /**
     * Releases daemon pid file
     * This method is called on exit (destructor like)
     *
     * @return void
     */
    public function releaseDaemon()
    {
        if ($this->isChildren && file_exists($this->pidFileLocation))
        {
            Logger::trace('Releasing daemon');

            @unlink($this->pidFileLocation);
        }
    }
}

?>
<?php defined('IN_TRUELEGEND') or die('No direct script access.');
/**
 * Queue manager abstract class
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id:$
 */

abstract class Queue_Process
{
    /**
     * Queue manager
     *
     * @var object
     */
    private $queue;

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
    public function __construct($monitorPath, $dataPath)
    {
        $this->queue = Queue_Factory::getQueueManager($monitorPath, $dataPath);

        if (!$this->queue)
        {
            $this->error('Create queue manager failure');

            Logger::error($this->error());
        }
    }

    /**
     * Initialize
     *
     * @return bool
     */
    abstract protected function initialize();

    /**
     * Do process
     *
     * @return bool
     */
    abstract protected function doProcess($message);

    /**
     * Finalize
     *
     * @return bool
     */
    abstract protected function finalize();

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
     * Process queue
     *
     * @return bool
     */
    public function process()
    {
        if ($this->error() || !$this->initialize())
        {
            return false;
        }

        while (!$this->queue->isEmpty())
        {
            $this->queue->read();

            if ($this->queue->error())
            {
                $this->error('Read queue messages failure,error:' . $this->queue->error());

                Logger::error($this->error());

                return false;
            }

            while (($message = $this->queue->pop()) !== null)
            {
                $error = false;

                $mid = key($message);

                $content = trim($message[$mid]);

                $lines = explode("\n", $content);

                foreach ($lines as $line)
                {
                    if (DEBUG)
                    {
                        $timestart = microtime(true);
                    }

                    if (!$this->doProcess($line))
                    {
                        $error = true;
                        continue;
                    }

                    if (DEBUG)
                    {
                        $timeend = microtime(true);

                        Logger::trace('Process successful,time uered: ' . round($timeend - $timestart, 3));
                    }
                }
                if (!$error)
                {
                    $this->queue->remove($mid);
                }
            }
        }
        return $this->finalize();
    }

    /**
     * Send message to queue
     *
     * @return bool
     */
    public function sendMessage($message)
    {
        if ($this->error())
        {
            return false;
        }

        $ret = $this->queue->push($message);

        if ($ret === false || $this->queue->error())
        {
            $this->error('Send messages failure,error:' . $this->queue->error());

            Logger::error($this->error());

            return false;
        }

        return true;
    }
}

?>
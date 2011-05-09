<?php defined('IN_TRUELEGEND') or die('No direct script access.');
/**
 * Queue manager class
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id:$
 */

class Queue_Manager
{
    /**
     * Queue monitor
     *
     * @var object
     */
    private $monitor;

    /**
     * Queue implement
     *
     * @var object
     */
    private $implement;

    /**
     * Quque messages
     *
     * @var array
     */
    private $messages = array();

    /**
     * Error message
     *
     * @var string
     */
    private $error = '';

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct($monitor, $implement)
    {
        $this->monitor = $monitor;

        $this->implement = $implement;
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
     * Read queue messages
     *
     * @return bool
     */
    public function read()
    {
        $ret = $this->implement->read(MAX_QUEUE_FILE_LOAD);

        if (!$ret || $this->implement->error())
        {
            $this->error = $this->implement->error();
            
            return false;
        }

        $this->messages = $this->implement->elements();

        return true;
    }

    /**
     * Push a message onto the end of the queue
     *
     * @param array $message
     * @return mixed
     */
    public function push($message)
    {
        $mid = $this->implement->create($message);

        if ($mid === false || $this->implement->error())
        {
            $this->error = $this->implement->error();
            
            return false;
        }

        if ($this->monitor->isReady())
        {
            $this->monitor->update();
        }

        return $mid;
    }

    /**
     * Pop a message from the front of the queue
     *
     * @return mixed
     */
    public function pop()
    {
        if (!empty($this->messages))
        {
            return $this->implement->pop();
        }
        else
        {
            return null;
        }
    }

    /**
     * Remove a message from the queue
     *
     * @param string $mid
     * @return mixed
     */
    public function remove($mid)
    {
        $ret = $this->implement->delete($mid);

        if ($ret === false || $this->implement->error())
        {
            $this->error = $this->implement->error();
            
            return false;
        }

        return true;
    }

    /**
     * Is the queue empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        if ($this->monitor->isReady())
        {
            if ($this->monitor->isUpdated())
            {
                return false;
            }
            return true;
        }
        return $this->implement->isEmpty();
    }

    /**
     * Print the queue info
     *
     * @return void
     */
    public function dump()
    {
        $content = var_export($this->messages, true) . "\n";

        error_log($content, 3, getcwd() . '/dump');

        $this->monitor->dump();
    }
}

?>
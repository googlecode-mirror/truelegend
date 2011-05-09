<?php defined('IN_TRUELEGEND') or die('No direct script access.');
/**
 * Queue factory class
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id:$
 */

class Queue_Factory
{
    /**
     * Create queue manager
     *
     * @param string $monitorPath
     * @param string $dataPath
     * @return bool
     */
    public static function createQueue($monitorPath, $dataPath)
    {
        $monitor = new Queue_Monitor($monitorPath);

        $implement = new QueueImpl_File($dataPath);

        if ($implement->error())
        {
            Logger::fatal('Get queue impl failure,error: ' . $implement->error());
            return false;
        }

        $queue = new Queue_Manager($monitor, $implement);

        if ($queue->error())
        {
            Logger::fatal('Get queue manager failure,error: ' . $queue->error());
            return false;
        }

        return $queue;
    }
}

?>
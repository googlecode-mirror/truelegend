<?php defined('IN_TRUELEGEND') or die('No direct script access.');
/**
 * Queue factory class
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id$
 */

class Queue_Factory
{
    /**
     * Get queue manager
     *
     * @param string $monitorPath
     * @param string $dataPath
     * @return object
     */
    public static function getQueueManager($monitorPath, $dataPath)
    {
        $monitor = new Queue_Monitor($monitorPath);

        $implement = new QueueImpl_File($dataPath);

        if ($implement->error())
        {
            Logger::fatal('Get queue impl failure,error: ' . $implement->error());
            return false;
        }

        $manager = new Queue_Manager($monitor, $implement);

        if ($manager->error())
        {
            Logger::fatal('Get queue manager failure,error: ' . $manager->error());
            return false;
        }

        return $manager;
    }

    /**
     * Get queue process
     *
     * @param string $name
     * @return object
     */
    public static function &getQueueProcess($name)
    {
    	static $instances = array();

    	if (isset($instances[$name]) && is_object($instances[$name]))
    	{
    		return $instances[$name];
    	}

    	$className = "App_{$name}_Process";

        if (!class_exists($className, false)) {

        	$classFile = BASE_QUEUE_SOURCE_APP_PATH . '/' . $name . '/Process.php';

        	if (!is_file($classFile))
			{
			    return null;
			}
			require $classFile;
        }

		$instances[$name] = new $className();

        return $instances[$name];
    }
}

?>
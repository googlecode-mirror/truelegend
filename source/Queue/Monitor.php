<?php defined('IN_TRUELEGEND') or die('No direct script access.');
/**
 * Queue monitor class
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id:$
 */

class Queue_Monitor
{
    /**
     * Path to monitor file
     *
     * @var array
     */
    private $file = '';

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
    public function __construct($file = "")
    {
        if (!empty($file))
        {
            $this->file = $file;
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
     * Check monitor file exists ? if not exists try to create
     *
     * @return bool
     */
    public function isReady()
    {
        if (!is_file($this->file) || $this->error)
        {
            return $this->create();
        }
        return true;
    }

    /**
     * Check monitor file is updated,check it's last modified time & last recorded time and compare with each other.
     *
     * @return bool
     */
    public function isUpdated()
    {
        clearstatcache();

        $mtime = intval(filemtime($this->file));
        $rtime = intval(trim(file_get_contents($this->file)));

        if ($mtime > $rtime)
        {
            $time = time();
            if (!file_put_contents($this->file, $time))
            {
                $this->error('Write contents to monitor file failure: ' . $this->file);

                Logger::error($this->error());

                return false;
            }
            touch($this->file, $time, $time);

            return true;
        }

        return false;
    }

    /**
     * Update monitor file
     *
     * @return bool
     */
    public function update()
    {
        $time = time();
        
        touch($this->file, $time, $time);
        
        clearstatcache();
        
        return true;
    }

    /**
     * Create monitor file
     *
     * @return bool
     */
    private function create()
    {
        $dir = dirname($this->file);
        if (!is_dir($dir))
        {
            if (!mkdir($dir, 0777, true))
            {
                $this->error('Create monitor dir failure: ' . $dir);

                Logger::error($this->error());

                return false;
            }
        }
        
        chmod($dir, 0777);
        chown($dir, HTTPD_USER_NAME);
        chgrp($dir, HTTPD_GROUP_NAME);

        if (!file_put_contents($this->file, '0'))
        {
            $this->error('Create monitor file failure: ' . $this->file);

            Logger::error($this->error());

            return false;
        }
        
        chmod($this->file, 0777);
        chown($this->file, HTTPD_USER_NAME);
        chgrp($this->file, HTTPD_GROUP_NAME);

        return true;
    }

    /**
     * Dump monitor file's last accessed & recorded time
     *
     * @return void
     */
    public function dump()
    {
        clearstatcache();

        $atime = intval(fileatime($this->file));
        $rtime = intval(file_get_contents($this->file));

        $content = "Last accessed time: " . date("Y-m-d H:i:s", $atime) . "\n";
        $content .= "Last recorded time: " . date("Y-m-d H:i:s", $rtime) . "\n";

        error_log($content . "\n", 3, getcwd() . "/dump");
    }
}

?>
<?php defined('IN_TRUELEGEND') or die('No direct script access.');
/**
 * Using the file system to store queue data
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id:$
 */

class QueueImpl_File extends QueueImpl
{
    /**
     * Stores dir path of input
     *
     * @var string
     */
    private $dir = '';

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
    public function __construct($dir)
    {
        if (!empty($dir) && is_dir($dir))
        {
            $this->dir = rtrim($dir, '/');
        }
        else
        {
            $this->error('Invalid dir name: ' . $dir);

            Logger::error($this->error());
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
     * Create a queue data file
     *
     * @return mixed
     */
    public function create($message)
    {
        if ($this->error)
        {
            return false;
        }

        $mid = $this->generateMessageId();

        $mfile = $this->dir . '/' . $mid;
        if (!file_put_contents($mfile, trim($message)))
        {
            $this->error('Write contents to message file failure: ' . $mfile);

            Logger::error($this->error());

            return false;
        }

        $ofile = $mfile . '.ok';

        $time = time();
        if (!touch($ofile, $time, $time))
        {
            // Try again
            if (!touch($ofile, $time, $time))
            {
                @unlink($mfile);

                $this->error('Create ok file failure: ' . $ofile);

                Logger::error($this->error());

                return false;
            }
        }

        return $mid;
    }

    /**
     * Read $limit queue data file contents
     *
     * @return bool
     */
    public function read($limit)
    {
        if ($this->error)
        {
            return false;
        }

        $files = $this->listdir($limit, '^[0-9a-f]{32,32}\.ok$');
        if ($this->error)
        {
            return false;
        }

        if (empty($files))
        {
            return true;
        }
        // Sort files by create time
        usort($files, array(&$this, 'compareCtime'));

        for($i = 0, $j = count($files); $i < $j; $i++)
        {
            $ofile = $files[$i];

            $mfile = rtrim($ofile, '.ok');

            if (!is_file($mfile))
            {
                $this->error('Message file not find or access denied: ' . $mfile);

                Logger::error($this->error());

                return false;
            }

            $mid = basename($mfile);

            if (false === ($content = file_get_contents($mfile)))
            {
                $this->error('Get file contents failure: ' . $mfile);

                Logger::error($this->error());

                return false;
            }

            $lfile = $mfile . '.lck';

            if (!@rename($ofile, $lfile))
            {
                $this->error('File rename failure,src file: ' . $ofile . ',dest file: ' . $lfile);

                Logger::error($this->error());

                return false;
            }

            $this->push(array($mid => $content));
        }

        return true;
    }

    /**
     * Delete a queue data file
     *
     * @param string $mid
     * @return bool
     */
    public function delete($mid)
    {
        if ($this->error)
        {
            return false;
        }

        $mfile = $this->dir . '/' . $mid;
        $lfile = $this->dir . '/' . $mid . '.lck';

        if (!is_file($mfile) || !unlink($mfile))
        {
            $this->error('Delete the message file failure: ' . $mfile);

            Logger::error($this->error());

            return false;
        }

        if (!is_file($lfile) || !unlink($lfile))
        {
            $this->error('Delete the locked file failure: ' . $lfile);

            Logger::error($this->error());

            return false;
        }

        return true;
    }

    /**
     * Is this directory empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        $files = $this->listdir(1, '^[0-9a-f]{32,32}\.ok$');

        if ($this->error || !empty($files))
        {
            return false;
        }

        return true;
    }

    /**
     * Generate unique message ID
     *
     * @return string
     */
    private function generateMessageId()
    {
        $timestamp = date('YmdHis');

        $md5 = md5(uniqid(mt_rand(), true));

        return $timestamp . substr($md5, 0, 18);
    }

    /**
     * Compare two files's create time
     *
     * @return int
     */
    private static function compareCtime($file1, $file2)
    {
        $ctime1 = intval(@filectime($file1));
        $ctime2 = intval(@filectime($file2));

        if ($ctime1 == $ctime2)
        {
            return 0;
        }

        return ($ctime1 < $ctime2) ? -1 : 1;
    }

    /**
     * Get files in this directory
     *
     * @return array
     */
    private function listdir($limit, $pattern = ".*")
    {
        $limit = intval($limit);
        $limit || $limit = 1000;

        $files = array();

        clearstatcache();
        if (false == ($handle = opendir($this->dir)))
        {
            $this->error('Open dir failure: ' . $this->dir);

            Logger::error($this->error());

            return $files;
        }

        $counter = 0;
        while (false != ($filename = readdir($handle)))
        {
            if ($filename == "." || $filename == ".." || !preg_match("/" . $pattern . "/i", $filename))
            {
                continue;
            }

            if ($counter++ >= $limit)
            {
                break;
            }

            $file = $this->dir . '/' . $filename;
            if (is_file($file))
            {
                $files[] = $file;
            }
        }
        closedir($handle);

        return $files;
    }
}

?>
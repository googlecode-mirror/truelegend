<?php defined('IN_TRUELEGEND') or die('No direct script access.');
/**
 * Logger Class
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id$
 */

class Logger
{
    const LEVEL_TRACE = 1;
    const LEVEL_DEBUG = 2;
    const LEVEL_WARNING = 3;
    const LEVEL_ERROR = 4;
    const LEVEL_FATAL = 5;

    /**
     * Logs debug message
     *
     * @param string $message
     */
    public static function debug($message)
    {
        return self::log($message, self::LEVEL_DEBUG);
    }

    /**
     * Logs warning message
     *
     * @param string $message
     */
    public static function warning($message)
    {
        return self::log($message, self::LEVEL_WARNING);
    }

    /**
     * Logs error message
     *
     * @param string $message
     */
    public static function error($message)
    {
        return self::log($message, self::LEVEL_ERROR);
    }

    /**
     * Logs trace message
     *
     * @param string $message
     */
    public static function trace($message)
    {
        return self::log($message, self::LEVEL_TRACE);
    }

    /**
     * Logs fatal message
     *
     * @param string $message
     */
    public static function fatal($message)
    {
        return self::log($message, self::LEVEL_FATAL);
    }

    /**
     * Logs a message to the log file at the given level
     *
     * @param string $message
     * @param int $level
     */
    private static function log($message, $level)
    {
        switch ($level)
        {
            case self::LEVEL_TRACE:
                $logLevel = 'TRACE';
                break;
            case self::LEVEL_DEBUG:
                $logLevel = 'DEBUG';
                break;
            case self::LEVEL_WARNING:
                $logLevel = 'WARNING';
                break;
            case self::LEVEL_ERROR:
                $logLevel = 'ERROR';
                break;
            case self::LEVEL_FATAL:
                $logLevel = 'FATAL';
                break;
            default:
                $logLevel = 'N/A';
        }

        $dir = defined('APP_QUEUE_LOG_PATH') ? APP_QUEUE_LOG_PATH : '/tmp';

        if (!is_dir($dir))
        {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
            chown($dir, HTTPD_USER_NAME);
            chgrp($dir, HTTPD_GROUP_NAME);
        }

        $file = $dir . '/status.log';

        $message = '[' . $logLevel . '] - ' . date('Y-m-d H:i:s') . ' --> ' . trim($message) . "\n";

        if (defined('DEBUG') && DEBUG)
        {
            echo $message;
        }

        if (!@error_log($message, 3, $file))
        {
            return false;
        }

        chmod($file, 0777);
        chown($file, HTTPD_USER_NAME);
        chgrp($file, HTTPD_GROUP_NAME);

        return true;
    }
}

?>
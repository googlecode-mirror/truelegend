<?php
/**
 * Installer
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id:$
 */

// Check for CLI
if (php_sapi_name () !== 'cli')
{
    die('[ERROR] You can only install from the command line (CLI-mode)');
}

// Set assert options
assert_options(ASSERT_ACTIVE, true);
assert_options(ASSERT_BAIL, true);
assert_options(ASSERT_WARNING, true);

// Sets debugging on
define('DEBUG', true);

// Load bootstrap
require(realpath(dirname(__FILE__) . '/source/Bootstrap.php'));

/**
 * Print help info
 *
 * @return void
 */
function help()
{
    print <<<EOF
	Usage:
		php install.php <option>

	Options:
		install             - Create the queue related directory, the first time in the new server installation to use
		uninstall           - Delete the queue directory
		newapp appname      - Create a new application related to the directory
		delapp appname      - Delete a application related directory
		help                - Show help

EOF;

    exit();
}

function main()
{
    if ($_SERVER['argc'] > 1)
    {
        $option = $_SERVER['argv'][1];
    }
    else
    {
        help();
    }

    switch ($option)
    {
        case "install" :

            install();

            break;

        case "uninstall" :

            uninstall();

            break;

        case "newapp" :

            $_SERVER['argc'] != 3 and help();

            newapp($_SERVER['argv'][2]);

            break;

        case "delapp" :

            $_SERVER['argc'] != 3 and help();

            delapp($_SERVER['argv'][2]);

            break;
        case "help" :

            help();

            break;
        default :

            help();

            break;
    }
}

function install()
{
    echo "\nBegin install.....\n\n";

    $root = dirname(__FILE__);

    $app = $root . '/source/App';
    assert(_mkdir($app));
    echo "[SUCC] Create app dir $app ok!\n";

    $logs = $root . '/logs';
    assert(_mkdir($logs));
    echo "[SUCC] Create logs dir $logs  ok!\n";

    $data = $root . '/data';
    assert(_mkdir($data));
    echo "[SUCC] Create data dir  $data  ok!\n";

    $monitor = BASE_QUEUE_MONITOR_PATH;
    assert(_mkdir($monitor));
    echo "[SUCC] Create monitor dir $monitor ok!\n";

    assert(_mkdir('/elink'));
    echo "[SUCC] Create dir /elink ok!\n";

    assert(symlink($root, TRUELEGEND_LINK_PATH));
    echo "[SUCC] Create symbolic link " . TRUELEGEND_LINK_PATH . " ok!\n";

    echo "\nDone!\n\n";
}

function uninstall()
{
    echo "\nBegin uninstall.....\n\n";

    $target = readlink(TRUELEGEND_LINK_PATH);
    assert(trim($target) != '');
    echo "[SUCC] Read symbolic link " . TRUELEGEND_LINK_PATH . " ok!\n";

    $app = $target . '/source/App';
    assert(_rmdir($app));
    echo "[SUCC] Delete app dir $app ok!\n";

    $logs = $target . '/logs';
    assert(_rmdir($logs));
    echo "[SUCC] Delete logs dir $logs ok!\n";

    $data = $target . '/data';
    assert(_rmdir($data));
    echo "[SUCC] Delete data dir $data ok!\n";

    $monitor = BASE_QUEUE_MONITOR_PATH;
    assert(_rmdir($monitor));
    echo "[SUCC] Delete monitor dir $monitor ok!\n";

    assert(unlink(TRUELEGEND_LINK_PATH));
    echo "[SUCC] Delete symbolic link " . TRUELEGEND_LINK_PATH . "ok!\n";

    echo "\nDone!\n\n";
}

function newapp($name)
{
    $name = ucfirst($name);

    echo "\nBegin new app $name .....\n\n";

    $logs = BASE_QUEUE_LOGS_PATH . '/' . $name;
    assert(_mkdir($logs));
    echo "[SUCC] Create app logs dir. $logs ok!\n";

    $data = BASE_QUEUE_DATA_PATH . '/' . $name;
    assert(_mkdir($data));
    echo "[SUCC] Create app data dir $data ok!\n";

    $app = BASE_QUEUE_SOURCE_APP_PATH . '/' . $name;
    assert(_mkdir($app));
    echo "[SUCC] Created app source dir $app  ok!\n";

    $processtmpl = BASE_QUEUE_SOURCE_PATH . '/Template/Process.tmpl';
    assert(file_exists($processtmpl));

    $content = file_get_contents($processtmpl);
    $patterns[] = "/%APP_QUEUE_NAME%/";
    $patterns[] = "/%APP_QUEUE_MONITOR_NAME%/";
    $replacements[] = $name;
    $replacements[] = strtolower($name);
    $content = preg_replace($patterns, $replacements, $content);

    $processfile = BASE_QUEUE_SOURCE_APP_PATH . '/' . $name . '/Process.php';
    if (!file_put_contents($processfile, $content))
    {
        echo "[ERROR] Writes content to $processfile failure!\n";
        return false;
    }

    $daemontmpl = BASE_QUEUE_SOURCE_PATH . '/Template/Daemond.tmpl';
    assert(file_exists($daemontmpl));

    $content = file_get_contents($daemontmpl);
    $patterns[] = "/%APP_QUEUE_NAME%/";
    $replacements[] = $name;
    $content = preg_replace($patterns, $replacements, $content);

    $daemonfile = BASE_QUEUE_SOURCE_APP_PATH . '/' . $name . '/Daemond.php';
    if (!file_put_contents($daemonfile, $content))
    {
        echo "[ERROR] Writes content to $daemonfile failure!\n";
        return false;
    }

    echo "\nDone!\n\n";

    return true;
}

function delapp($name)
{
    $name = ucfirst($name);

    echo "\nBegin delete app $name .....\n\n";

    $logs = BASE_QUEUE_LOGS_PATH . '/' . $name;
    assert(_rmdir($logs));
    echo "[SUCC] Delete app logs dir  $logs ok!\n";

    $data = BASE_QUEUE_DATA_PATH . '/' . $name;
    assert(_rmdir($data));
    echo "[SUCC] Deleted app data dir $data  ok\n";

    $app = BASE_QUEUE_SOURCE_APP_PATH . '/' . $name;
    assert(_rmdir($app));
    echo "[SUCC] Delete app source dir $app ok\n";

    echo "\nDone!\n\n";
}

function _mkdir($dir, $mode = 0777)
{
    if (!is_dir($dir))
    {
        _mkdir(dirname($dir), $mode);
        if (!mkdir($dir, $mode))
        {
            echo "[ERROR] Delete dir $dir failure!\n";
            return false;
        }
        $owner = posix_getpwuid(fileowner($dir));
        if ($owner['name'] != HTTPD_USER_NAME)
        {
            if (!chown($dir, HTTPD_USER_NAME))
            {
                echo "[ERROR] $dir chown by " . HTTPD_USER_NAME . " failure!\n";
                return false;
            }
            if (!chgrp($dir, HTTPD_GROUP_NAME))
            {
                echo "[ERROR] $dir chgrp by " . HTTPD_GROUP_NAME . " failure!\n";
                return false;
            }
        }
        chmod($dir, $mode);
    }
    return true;
}

function _rmdir($dir)
{
    $dir = realpath($dir);
    if ($dir == '' || $dir == '/' ||
        (strlen($dir) == 3 && substr($dir, 1) == ':\\'))
    {
        echo "[ERROR] $dir not exists or forbidden!\n";
        return false;
    }

    if (false !== ($dh = opendir($dir)))
    {
        while (false !== ($file = readdir($dh)))
        {
            if ($file == '.' || $file == '..')
            {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path))
            {
                if (!_rmdir($path))
                {
                    echo "[ERROR] Delete $path failure!\n";
                    return false;
                }
            }
            else
            {
                unlink($path);
            }
        }
        closedir($dh);
        rmdir($dir);
        return true;
    }
    else
    {
        echo "[ERROR] Open dir $dir failure!\n";
        return false;
    }
}

main();

?>
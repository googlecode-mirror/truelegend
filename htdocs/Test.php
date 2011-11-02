<?php
/**
 * Test Send message
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id$
 */

$data = array
(
    'queuename' => 'Demo',
    'queuedata' => 'act=add&id=100'
);

$result = json_decode(httpPost('localhost', '/SendMessage.php', $data), true);
if (false === $result)
{
    echo "Request failure!\n";
}
else
{    
    if (isset($result['status']) && $result['status'] == 1)
    {
        echo "Send message successful!\n";
    }
    else
    {
        echo "Send message failure,error:{$result['data']}\n";
    }
}

/////////////////////////////////////////////////////////////////////
/**
 * HTTP post request
 *
 * @param string $host
 * @param string $url
 * @param sting $data
 * @param int $port
 * @param init $timeout
 * @return mixed
 */
function httpPost($host, $url, $data, $port = 80, $timeout = 10)
{
    $errno = $errstr = '';

    $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
    if (!$fp)
    {
        return false;
    }
    else
    {
        if (is_array($data))
        {
            $_data = array();
            foreach ($data as $key => $val)
            {
                $_data[] = urlencode($key) . "=" . urlencode($val);
            }
            $content = implode('&' , $_data);
        }
        else
        {
            $content = $data;
        }

        fwrite($fp, "POST " . $url . " HTTP/1.0\r\n");
        fwrite($fp, "Content-Length: " . strlen($content) . "\r\n");
        fwrite($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
        fwrite($fp, "Host: " . $host . "\r\n\r\n");
        fwrite($fp, $content);

        $line = '';
        while (!feof($fp))
        {
            $line .= fgets($fp, 1024);
        }
        fclose($fp);
    }

    $line = trim(preg_replace("/(.+?)\\r\\n\\r\\n(.+?)/is", "\\2", $line, 1));

    return $line;
}

?>
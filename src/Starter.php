<?php

namespace JLaso\ToolsLib;


/**
 * Class Starter
 *
 * based on the article https://segment.com/blog/how-to-make-async-requests-in-php/ of Calvin French-Owen
 */

class Starter extends CommonAbstract
{
    const SUCCESS = 1;
    const STARTED_ALREADY = 2;
    const ERROR = 2;
    const ERROR_ID = 3;

    /** @var  String */
    protected $url;
    /** @var  String */
    protected $host;
    /** @var  int */
    protected $port;
    /** @var  String */
    protected $lastError;

    /**
     * Starter constructor.
     * @param string $url
     * @param string $task
     * @param bool $debug
     * @throws Exception
     * @internal param mixed $data
     */
    public function __construct($url, $task, $debug = false)
    {
        parent::__construct($task, $debug);
        if (strpos($url, "http://") !== 0) {
            $url = "http://" . $_SERVER["SERVER_NAME"] . "/" . $url;
        }
        if (!preg_match("~(?<host>http[s]?:\/\/[^\/|^:]+)(?<port>:\d+)?(?<url>\/.*)$~i", $url, $matches)) {
            throw new Exception("The url passed doesn't match http://host:port/url");
        };
        $this->host = isset($matches["host"]) ? $matches["host"] : "http://localhost";
        $this->url = isset($matches["url"]) ? $matches["url"] : "/";
        $this->port = isset($matches["port"]) && !empty($matches["port"]) ? $matches["port"] : (stripos($this->host, "https") === 0 ? "443" : "80");
        $this->host = preg_replace("~^http[s]?://~", "", $this->host);
    }

    /**
     * invoke the task though an http request in order to end quickly and return the control
     * to the requester, then call periodically to status script in order to know how is going
     *
     * @param array $data payload to the request
     * @param boolean $insecure allows curl to accept insecure requests
     * @return integer
     */
    public function invoke($data = array(), $insecure = true)
    {
        if (!isset($data['id'])) {
            return self::ERROR_ID;
        }
        $data["_task"] = $this->task;
        $unique = ((strstr($this->url, "?") === false) ? "?" : "&") . "_nc=" . date("U");

        $payload = http_build_query($data);
        $fp = fsockopen($this->host, $this->port, $errno, $errstr, 30);

        if (!$fp) {

            $this->lastError = "$errstr ($errno)";

            return self::ERROR;

        } else {

            $out  = "POST " . $this->url . $unique . " HTTP/1.1\r\n";
            $out .= "Host: {$this->host}\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "Content-Length: " . strlen($payload) . "\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "\r\n";
            fwrite($fp, $out);
            fwrite($fp, $payload);
            fclose($fp);

        }

        return self::SUCCESS;
    }

    /**
     * @return String
     */
    public function getLastError()
    {
        return $this->lastError;
    }



}
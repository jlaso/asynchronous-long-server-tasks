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

    /**
     * Starter constructor.
     * @param string $url
     * @param bool $debug
     * @internal param mixed $data
     */
    public function __construct($url, $debug = false)
    {
        parent::__construct($debug);
        $this->url = $url;
    }

    /**
     * invoke the task though an http request in order to end quickly and return the control
     * to the requester, then call periodically to status script in order to know how is going
     *
     * @param array $data           payload to the request
     * @param boolean $insecure     allows curl to accept insecure requests
     * @return integer
     */
    public function invoke($data = array(), $insecure = true)
    {
        if (!isset($data['id']) || empty($data['id'])){
            return self::ERROR_ID;
        }
        $unique = ((strstr($this->url, "?")!==false) ? "?" : "&") . date("U");
        $payload = json_encode($data);
        $cmd = "curl ".($insecure ? "-k" : "")." -X POST -H 'Content-Type: application/json'";
        $cmd.= " -d '" . $payload . "' " . "'" . $this->url . $unique . "'";

        if (!$this->debug) {
            $cmd .= " > /dev/null 2>&1 &";
        }

        exec($cmd, $output, $exit);
        return ($exit == 0) ? self::SUCCESS : self::ERROR;
    }


}
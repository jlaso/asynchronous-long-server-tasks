<?php

namespace JLaso\ToolsLib;

class Json
{

    public static function dump($data)
    {
        header('Content-Type: application/json');
        print json_encode($data);
    }

    public static function error($reason)
    {
        self::dump(array(
            'result' => false,
            'reason' => $reason,
        ));
    }

    public static function ok($data = array())
    {
        $data['result'] = true;
        self::dump($data);
    }

    public static function getBodyParams()
    {
        $postBody = file_get_contents("php://input");

        return json_decode($postBody, true);
    }

}
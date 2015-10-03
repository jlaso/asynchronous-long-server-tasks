<?php

require_once __DIR__.'/../vendor/autoload.php';

use JLaso\ToolsLib\Json;
use JLaso\ToolsLib\Starter;

$id = $_REQUEST["id"];
$task = $_REQUEST["_task"];

if (!$id || !$task) {

    Json::error('The "id" and "_task" are mandatory in order to process your request!');

}else{
    $url  = ($_SERVER["SERVER_PORT"] == "443") ? "https://" : "http://";
    $url .= $_SERVER["SERVER_NAME"] . "/server/{$task}.php";
    $starter = new Starter($url, $task);

    if ($starter->invoke(array("id"=>$id))) {
        Json::ok(array('id'=>$id));
    }else{
        Json::error('something wrong happened trying to start the task on the server!');
    }

}
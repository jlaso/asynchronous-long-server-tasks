<?php

require_once __DIR__.'/../vendor/autoload.php';

use JLaso\ToolsLib\Json;
use JLaso\ToolsLib\Starter;

$id = $_REQUEST["id"];
$task = $_REQUEST["_task"];

if (!$id) {

    Json::error('The id is mandatory in order to process your request!');

}else{

    $starter = new Starter("/server/".$task.".php", $task);

    if ($starter->invoke(array("id"=>$id))) {
        Json::ok(array('id'=>$id));
    }else{
        Json::error('something wrong happened trying to start the task on the server!');
    }

}
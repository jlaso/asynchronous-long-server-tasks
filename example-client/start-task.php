<?php

require_once __DIR__.'/../vendor/autoload.php';

use JLaso\ToolsLib\Json;
use JLaso\ToolsLib\Starter;

$id = $_REQUEST["id"];

if (!$id) {

    Json::error('The id is mandatory in order to process your request!');

}else{

    $starter = new Starter("localhost:40001/task.php");

    if ($starter->invoke(array("id"=>$id))) {
        Json::ok(array('id'=>$id));
    }else{
        Json::error('something wrong happened trying to start the task on the server!');
    }

}
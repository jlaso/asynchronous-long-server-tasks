<?php

require_once __DIR__.'/../vendor/autoload.php';

use JLaso\ToolsLib\Json;
use JLaso\ToolsLib\Status;

$id = $_REQUEST["id"];

if (!$id) {

    Json::error('The id is mandatory in order to process your request!');

}else{

    $status = new Status();
    Json::ok(array('id'=>$id, 'status'=>$status->getInfo($id)));

}
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use JLaso\ToolsLib\Json;
use JLaso\ToolsLib\Starter;

$id = isset($_REQUEST["id"]) ? intval($_REQUEST["id"]) : null;
$file = $_REQUEST["file"];
$task = $_REQUEST["_task"];

if ((null === $id) | !is_array($file)) {

    Json::error('The id is mandatory in order to process your request!');

} else {

    $starter = new Starter("http://asyn-demo.local/server/copy-file.php", $task);

    if (Starter::SUCCESS == ($result = $starter->invoke(array("id" => $id, "name" => $file["name"], "size" => $file["size"])))) {
        Json::ok(array('id' => $id));
    } else {
        Json::error('something wrong happened trying to start the task on the server! error code '.$result);
    }

}
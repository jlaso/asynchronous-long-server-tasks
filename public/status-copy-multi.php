<?php

require_once __DIR__ . '/../vendor/autoload.php';

use JLaso\ToolsLib\Json;
use JLaso\ToolsLib\Status;

$task = $_REQUEST["_task"];
$ids = rtrim($_REQUEST["ids"], ",");

if (!$task || !$ids) {

    Json::error('The "_task" and "ids" are mandatory in order to process your request!');

} else {

    $result = array();
    foreach (explode(",", $ids) as $id) {
        $id = intval($id);
        $statusService = new Status($task."-".$id);
        $status = $statusService->getInfo($id);
        if (!isset($result[$id])) {
            $result[$id] = array(
                "id" => $id,
            );
            $temp = explode(":", $status);
            $result[$id]["percent"] = isset($temp[1]) ? intval($temp[1]) : 0;
            $result[$id]["status"] = isset($temp[0]) ? $temp[0] : $status;
        }
    }
    Json::ok(array('info' => array_values($result)));

}
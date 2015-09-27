<?php

require_once __DIR__ . '/../vendor/autoload.php';

use JLaso\ToolsLib\Json;
use JLaso\ToolsLib\Status;

$id = isset($_REQUEST["id"]) ? intval($_REQUEST["id"]) : null;
$task = $_REQUEST["_task"];

if (null === $id) {

    Json::error('The "id" is mandatory in order to process your request!');

} else {

    $statusService = new Status($task);
    $taskStatus = explode(":", $statusService->getInfo($id));
    $status = isset($taskStatus[0]) ? $taskStatus[0] : 'unknown';
    $percent = isset($taskStatus[1]) ? intval($taskStatus[1]) : ($status == "done" ? 100 : 0);
    Json::ok(array('id' => $id, 'status' => $status, 'percent' => $percent, 'raw' => $statusService->getInfo($id)));

}
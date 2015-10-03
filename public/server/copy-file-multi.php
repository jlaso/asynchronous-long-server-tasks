<?php

set_time_limit(0);
ignore_user_abort(true);

// this file wants to simulate a real large process that have to be executed in background

require_once __DIR__.'/../../vendor/autoload.php';

use JLaso\ToolsLib\Json;
use JLaso\ToolsLib\Status;

$id = isset($_POST['id']) ? $_POST['id'] : null;
$fileName = isset($_POST['name']) ? $_POST['name'] : "";
$fileSize = isset($_POST['size']) ? $_POST['size'] : "";

if ((null === $id) || !$fileName || !$fileSize) {

    Json::error("you have to send 'id','name' and 'size' to start processing");
    exit();

}

// the next lines terminates the output buffer and let believe the requester that the program had finished
ob_start();
Json::ok();
header("Content-Length: ".ob_get_length());
header('Connection: close');
ob_end_flush();
flush();
session_write_close();

$status = new Status($_POST["_task"]);

$status->touchStatusFile();

// wait a little before the huge work
sleep(1);

$status->updateStatus($id, Status::PROCESSING);

process($status, $id, $fileName, $fileSize);

$status->updateStatus($id, Status::DONE);

sleep(2);   // give time to frontend to recover updated status

$status->freeStatusFile();


/**
 * @param Status $status
 * @param int $id
 * @param String $name
 * @param int $size
 */
function process(Status $status, $id, $name, $size)
{
    $factor = intval($size/100);
    for($i=0;$i<=$size;$i+=$factor){
        sleep(1);  // simulate that is copying a piece of the file
        $status->updateStatus($id, Status::PROCESSING.":".intval($i/$factor));
    }
    sleep(1);
    $status->updateStatus($id, Status::DONE);
    sleep(2);
}
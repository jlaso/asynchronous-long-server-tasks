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

$status = new Status($_POST["_task"]);

// if the status file exists means that another instance of this task is working
if ($status->existsStatusFile()) {
    Json::ok(array('id'=>$id, 'status'=>Status::QUEUED));
    exit();
}

$status->touchStatusFile();

// the next lines terminates the output buffer and let believe the requester that the program had finished
ob_start();
Json::ok();
header("Content-Length: ".ob_get_length());
header('Connection: close');
ob_end_flush();
flush();
session_write_close();

// wait a little before the huge work
sleep(1);

// this is some kind of magic, in order to copy the status file  if something wrong happens
function master_shutdown()
{
    global $status;
    $status->hangOn();
}
register_shutdown_function('master_shutdown');

$tasks = array();

do {
    // get the first task in the queue
    if (!$id){
        foreach($tasks as $key=>$task){
            $id = $key;
            break;
        }
    }

    if ($status->getInfo($id) != Status::DONE) {

        $status->updateStatus($id, Status::PROCESSING);
        process($status, $id, $fileName, $fileSize);
        $status->updateStatus($id, Status::DONE);

    }

    // continue while existing pending tasks
    $id = null;
    $tasks = $status->getNotDoneTasks();

} while(count($tasks));

sleep(2);   // give time to frontend to recover updated status

// frees status file indicating that this process has been terminated
$status->freeStatusFile();


function process(Status $status, $id, $name, $size)
{
    $factor = intval($size/100);
    for($i=0;$i<=$size;$i+=$factor){
        usleep(100000);  // simulate that is copying a piece of the file
        $status->updateStatus($id, Status::PROCESSING.":".intval($i/$factor));
    }
    sleep(1);
    $status->updateStatus($id, Status::DONE);
    sleep(2);
}
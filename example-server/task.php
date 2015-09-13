<?php

set_time_limit(0);
ignore_user_abort(true);

// this file wants to simulate a real large process that have to be executed in background

require_once __DIR__.'/../vendor/autoload.php';

use JLaso\ToolsLib\Json;
use JLaso\ToolsLib\Status;

// get the parameters as JSON
$postParams = Json::getBodyParams();

$id = isset($postParams['id']) ? $postParams['id'] : 0;

if (!$id) {

    Json::error("you have to send and ID to start processing");
    exit();

}

$status = new Status();

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
        process($id);
        $status->updateStatus($id, Status::DONE);

    }

    // continue while existing pending tasks
    $id = null;
    $tasks = $status->getNotDoneTasks();

} while(count($tasks));

sleep(2);   // give time to frontend to recover updated status

// frees status file indicating that this process has been terminated
$status->freeStatusFile();


function process($id)
{
    // simulate a long long process
    sleep(120);
}
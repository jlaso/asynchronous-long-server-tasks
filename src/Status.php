<?php

namespace JLaso\ToolsLib;

/**
 * A class to control the status of the tasks, have to be used by the invoker (start-task) and by the
 * status requester (status-task)
 */
class Status extends CommonAbstract
{
    const QUEUED = 'queued';
    const PROCESSING = 'processing';
    const DONE = 'done';

    /**
     * obtain the status for the id passed, if not exists the status returned is ""
     *
     * @param $id
     * @return string
     */
    public function getInfo($id)
    {
        if (!$this->existsStatusFile()) {
            return "";
        }

        $status = $this->getStatusContent();

        return isset($status[$id]) ? $status[$id] : "";

    }

    /**
     * get the content of the status file as an array with $id=>$status
     *
     * @return array
     */
    public function getStatusContent()
    {
        $status = array();

        $content = $this->getStatusFileContent();

        $rows = preg_split("/$\R?^/m", $content);

        foreach ($rows as $row) {
            $row = str_replace("\n", "", $row);
            if (trim($row)) {
                list($id, $text) = explode("|", $row);
                $status[intval($id)] = $text;
            }
        }

        return $status;
    }

    /**
     * get the list of the tasks that are unfinished
     *
     * @return array
     */
    function getNotDoneTasks()
    {
        $statuses = $this->getStatusContent();
        foreach ($statuses as $id => $status) {
            if (strpos($status, self::DONE) === 0) {
                unset($status[$id]);
            }
        }

        return $statuses;
    }

    /**
     * update the status for the $id into the status file
     *
     * @param $id
     * @param $message
     */
    public function updateStatus($id, $message)
    {
        $status = $this->getStatusContent();
        $status[$id] = $message;
        $this->dumpStatusContent($status);
    }

    /**
     * saves the content of the internal status array to the status file
     *
     * @param array $content
     */
    protected function dumpStatusContent($content = array())
    {
        $tmp = "";
        foreach ($content as $id => $msg) {
            $tmp .= $id . '|' . $msg . "\n";
        }
        $this->putStatusFileContent($tmp);
    }
}
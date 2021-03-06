<?php

namespace JLaso\ToolsLib;

/**
 * Both main classes must extends from this one, in order to have in one place all the stuff related with
 * the status control file
 */
abstract class CommonAbstract
{
    const STATUS_FILE = "status.pid";

    /** @var  string */
    protected $dataFolder;
    /** @var bool  */
    protected $debug;
    /** @var  string */
    protected $file;
    /** @var  string */
    protected $task;
    /** @var string  */
    protected $oldFile;

    protected $handle = null;

    const DONT_UNLOCK = true;

    /**
     * CommonAbstract constructor.
     * @param string $task
     * @param bool $debug
     * @internal param string $dataFolder
     */
    public function __construct($task, $debug = false)
    {
        $this->task = $task;
        $this->dataFolder = dirname(dirname(__FILE__)).'/data';
        $this->debug = $debug;
        if (!file_exists($this->dataFolder)) {
            mkdir ($this->dataFolder, 0777);
        }
        $this->file = $this->dataFolder.'/'.$task.'-'.self::STATUS_FILE;
        $this->oldFile = $this->dataFolder.'/'.$task.'-'.self::STATUS_FILE.'.old';
    }

    /**
     * @return string
     */
    public function getStatusFile()
    {
        return $this->file;
    }

    /**
     * @return bool
     */
    public function existsStatusFile()
    {
        return file_exists($this->file);
    }

    /**
     * the mechanism of control of the task process is the status file, to notice to subsequents calls that
     * the process is started already have to create this file, and on it is written the status of the
     * different tasks
     */
    public function touchStatusFile()
    {
        if (file_exists($this->oldFile)){
            rename($this->oldFile, $this->file);
        }else {
            touch($this->file);
        }
        chmod($this->file, 0777);
    }

    /**
     * get (in raw) the content of the status file
     *
     * @return string
     */
    public function getStatusFileContent($dontUnlock = false)
    {
        if ($this->handle == null){
            $this->lockStatusFile(true);
        }
        $content = fread($this->handle, filesize($this->file));
        if (!$dontUnlock){
            $this->unlockStatusFile();
        }
        return $content;
    }

    public function lockStatusFile($read = true)
    {
        $this->handle = fopen($this->file, "r+");
        while (!flock($this->handle, LOCK_EX | LOCK_NB)){ usleep(rand($read ? 100 : 1000,$read ? 500 :1500)); }
    }

    public function unlockStatusFile()
    {
        flock($this->handle, LOCK_UN);
        fclose($this->handle);
        $this->handle = null;
    }

    /**
     * saves into the status file the content passed
     *
     * @param string $content
     */
    public function putStatusFileContent($content)
    {
        if ($this->handle == null){
            $this->lockStatusFile();
        }
        ftruncate ($this->handle, 0);
        fwrite($this->handle, $content);
        fflush($this->handle);
        usleep(1000);
        $this->unlockStatusFile();
    }

    /**
     * when the main process finish have to call this method in order to allow subsequents calls,
     * be in mind that the status file is the mechanism that allow this system to know if the task
     * large process is still running or not
     */
    public function freeStatusFile()
    {
        unlink($this->file);
    }

    /**
     * In the even the main process hangs it must call this in order to allow next process calls.
     */
    public function hangOn()
    {
        if ($this->existsStatusFile()) {
            @unlink($this->oldFile);
            rename($this->file, $this->oldFile);
            chmod($this->file, 0777);
        }
    }
}
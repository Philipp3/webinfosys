<?php
namespace grp12\logger;

class Logger {
    //Only one Logger instance allowed
    private static $instance = null;
    public static function getLogger() {
        if($instance == null)
            $instance = new Logger();
        return $instance;
    }
    private function __construct() { }
    
    const LEVEL_NONE = 0;
    const LEVEL_ERR  = 1;
    const LEVEL_WARN = 2;
    const LEVEL_INFO = 3;
    private $LEVELNAMES = array(
        LEVEL_ERR => "ERROR",
        LEVEL_WARN => "WARNING",
        LEVEL_INFO => "INFO",
        LEVEL_DEBUG => "DEBUG"
    );
    
    const LOGFILENAME = "/home/user/server.log";
    
    private $logLevel = self::LEVEL_INFO;
    private $outputToSite = false;
    private $messages = array();

    public function __set($name, $value) {
        switch($name) {
        case "logLevel":
            if(gettype($value) == "integer")
                $this->logLevel = $value;
        case "outputToSite":
            if(gettype($value) == "boolean")
                $this->outputToSite = $value;
        }
    }
    public function __get($name) {
        switch($name) {
        case "logLevel":
            return $this->logLevel;
            break;
        case "outputToSite":
            return $this->outputToSite;
            break;
        }
    }
    
    public function error($msg) {
        makeLogEntry(new LogMsg(self::LEVEL_ERROR,$msg));
    }
    public function warining($msg) {
        makeLogEntry(new LogMsg(self::LEVEL_WARNING,$msg));
    }
    public function info($msg) {
        makeLogEntry(new LogMsg(self::LEVEL_INFO,$msg));
    }
    public function debug($msg) {
        makeLogEntry(new LogMsg(self::LEVEL_DEBUG,$msg));
    }
    
    private function makeLogEntry($logMsg) {
        array_push(messages,$logMsg);
        if($this->outputToSite) {
            $message = "[" . $LEVELNAMES[$logMsg->level] . $logMsg->msg."\n";
            echo($message);
        }
        if($this->logLevel >= $logMsg->level) {
            //print log message to file
            $message = "[" . $LEVELNAMES[$logMsg->level] . date("[y-m-d H:i:s] ") . $logMsg->msg."\n";
            $logfile = fopen(self::LOGFILENAME, "a");
            if($logfile != null) {
                fwrite($logfile, $message);
                fclose($logfile);
                return(true);
            }
            else
                return(false);
        }
        return(true);
    }
}

class LogMsg {
    private $level;
    private $msg;

    function __construct(int $level, $msg) {
        $this->level = $level;
        $this->msg = $msg;
    }
    
    public function __get($name) {
        switch($name) {
        case "level":
            return $this->level;
            break;
        case "msg":
            return $this->msg;
            break;
        }
    }
}
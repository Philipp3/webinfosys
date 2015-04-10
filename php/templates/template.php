<?php
namespace grp12\template;

include("/php/Logger.php");

class Template {
    private $vals = array();
    private $template_file = "";

    public function __get($name) {
        return($this -> vals[$name]);
    }
    public function __set($name, $value) {
        if($name == "this") {
            logger\Logger.getLogger().error('"this" may not be used as name for a template variable.');
            return $value;
        }
        $this -> vals[$name] = $value;
        return $value;
    }
    public function __construct($template_file) {
        $this -> template_file = $template_file;
    }

    public function out() {
        extract($this -> vals);
        include($this -> template_file);
    }
}
?>
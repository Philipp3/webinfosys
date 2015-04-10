<?php

namespace grp12\session;

class Session {
	private static $instance = null;
	public static function getInstance() {
		if ($instance == null)
			$instance = new Session ();
		return $instance;
	}
	private function __construct() {
	}
	private $session_started = false;
	public function start() {
		if (! $session_started)
			return session_start ();
		return true;
	}
	public function __set($name, $value) {
		$_SESSION [$name] = $value;
	}
	public function __get($name) {
		if (isset ( $_SESSION [$name] ))
			return $_SESSION [$name];
	}
	public function __isset($name) {
		return isset ( $_SESSION [$name] );
	}
	public function __unset($name) {
		unset ( $_SESSION [$name] );
	}
}
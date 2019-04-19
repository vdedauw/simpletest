<?php

/**
 *   Name: base.php
 * Author: De Dauw Valentijn
 *   Desc:
 * Basic variables, baseClass
 * History:
 *   2019/03/13, creation
 *   2019/04/01
 *   2019/04/01 version 0.0.2 added error, showError()
 *   2019/04/07 version 0.0.3 added showError
 *   2019/04/08 version 0.0.4 added debug methods
 *   2019/04/16 version 0.0.5 added checkString 
 */

class baseClass {
	public $tab1;
	public $tab2;
	public $tab3;
	public $eol;
	public $debug; 
	public $showErrors;
	public $error; 	// keeps the last error message

	function __construct() {
		$this->tab1="&nbsp;&nbsp;&nbsp;";
		$this->tab2="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$this->tab3="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$this->eol="<br>";
		$this->showErrors=TRUE;
		$this->debug=FALSE;
		$this->error=NULL;
	}
	
	// keeps error state, true=show error messages	
	public function setShowErrors($state) {
		$this->showErrors=$state;
	}
	// keeps debug state, false=no debug
	public function setDebug($state) {
		$this->debug=$state;
	}
	// starts debug message
	public function startDebug($method,$params) {
		if ( !$this->debug )
			return;		
		if ( is_object($params) )
			$params=get_class($params);
		echo "$this->tab1 START $method $params $this->eol";   
	}
	// end debug message
	public function endDebug($method,$params) {
		if ( !$this->debug )
			return;
		if ( is_object($params) )
			$params=get_class($params);
		echo "$this->tab1 END $method $params $this->eol";  
	}
	// shows debug 
	public function showDebug($method,$params) {
		if ( !$this->debug )
			return;
		if ( is_object($params) )
			$params=get_class($params);
		echo "$this->tab2 $method $params $this->eol";  
	}
	// shows the error
	public function showError() {
		if ( !$this->showErrors )
			return;
		echo "$this->tab2 $this->error $this->eol";  
	}
	
	// checks if a var is a string and is not empty
	// uses this->error to indicate false return cause
	public function checkString($str) {
		if ( !is_string($str) ) {
			$this->error="not string";
			return FALSE;
		}
		if ( strlen($str)==0 ) {
			$this->error="empty";
			return FALSE;
		}
		return TRUE;
	}

// end baseClass
}

// end file base.php
?>
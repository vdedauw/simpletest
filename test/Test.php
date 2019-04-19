<?php
/**
 *   Name: Test.php
 * Author: De Dauw Valentijn
 *   Desc:
 *   Testing for php source code
 * History:
 *   	Version 0.0.1 2019/03/20, creation
 *   	Version 0.0.2 2019/03/21, added field depends in testList, continue, addTestVariable
 *    Version 0.0.3 2019/03/25, changed testList tot tests, removed tests::depends
 *                              changed tests::continue to tests::proceed
 *                              adaptation to MySQL database
 *    Version 0.0.3 2019/03/26, added test::error, a possible error message from the tested object
 *    Version 0.0.4 2019/03/27, 'value, result' changed in 'expected, result' (to avoid confusion)
 *    Version 0.0.5 2019/03/31, added object, the object to test, tests::object changed to tests::className 
 *    Version 0.0.6 2019/04/12, added disabled, showResult, description (prepare for database) 
 *    Version 0.0.7 2019/04/17, added $this->func and $this->args, used in execute to test from inside the test 
 */

class test {
	public $id;          	// the id of the test
	public $title;  			// the title of the test
	public $name;				// the name of the variable or method
	public $description;    // a text describing this test

	public $result;      	// the value to test (returned from method, or set through variable ??)
	public $resultSet;   	// the actual result has been set (test is satisfied and can proceed)
	public $resultType;  	// the type of the result
	public $resultString;	// a string representing the result

	public $expected;			// the expected result
	public $expectedType;  	// the type of the expected result
	public $expectedString;	// a string representing the expected result

	public $condition;		// indicates the condition of the test, either a FALSE result, or a TRUE result
	public $processed;		// the test has been performed
	public $success;     	// boolean indicating success or failure
	public $disabled;       // boolean indicating this test is disabled
									// will not be executed, so no failure of success
	public $showResult;     // normally only test status is shown, when true also result is shown (can be extensive)
	public $types;       	// string showing types 
	public $values;      	// string showing values (short)
	public $created;     	// time this test was created
	public $started;			// time the test was started
	public $ended;     		// time the test was ended
	public $error;       	// possibly, an  error message from the tested object
	public $reason;         // the reason the test has failed or had success
	
	private $object;			// the object to test
	private $func;				// the function to execute

	function __construct($id,$title,$name,$expected,$condition) {
		$this->id=$id;
		$this->title=$title;
		$this->name=$name;
		$this->description=NULL;

		$this->result=NULL;
		$this->resultSet=FALSE;
		$this->resultType="";
		$this->resultString="";

		$this->expected=$expected;
		$this->expectedType="";
		$this->expectedString="";
	
		if ( is_null($condition) )
			$condition=TRUE;
		$this->condition=$condition;  // most tests will be a TRUE condition test
		
		$this->processed=FALSE;
		$this->success=FALSE;
		$this->disabled=FALSE;
		$this->showResult=FALSE;
		$this->created=time();
		$this->started=NULL;
		$this->ended=NULL;
		$this->error=NULL;
		$this->reason=NULL;

		$this->types=""; 
		$this->values="";
		
		$this->object=NULL;
		$this->func=NULL;
	}
	
	// sets the actual value,
	// !!! method has run outside test	
	public function setResult($result) {
		$this->result=$result;
		$this->resultSet=TRUE;
	}

	public function isSuccess() {
		$this->success=TRUE;
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		if ( $this->condition )
			echo "<span class=\"done\">";
		else 
			echo "<span class=\"todo\">";
		echo "&nbsp;test " . $this->nr . " success" . $message ."&nbsp;";
		echo " reason: " . $this->reason;
		echo "</span>";
		if ( $this->error ) {
			echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			if ( $this->condition )
				echo "<span class=\"done\">";
			else 
				echo "<span class=\"todo\">";
			if ( strlen($this->error)>0 )
				echo " object error: '" . $this->error . "' ";
			echo "</span>";
		}
		if ( $this->showResult ) {
			echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo " result: '" . $this->result . "' ";
		}
		if ( !$this->condition )		
			$this->showResultExpected();
		return TRUE;
	}

	public function isFailed() {
		$this->success=FALSE;
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		if ( $this->condition )
			echo "<span class=\"todo\">";
		else
			echo "<span class=\"done\">";
		echo "&nbsp;test $this->nr failed&nbsp;";
		echo " reason: " . $this->reason;
		echo "</span>";
		echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		if ( $this->condition )
			echo "<span class=\"todo\">";
		else
			echo "<span class=\"done\">";
		if ( $this->error )
			if ( strlen($this->error)>0 )
				echo " object error: '" . $this->error . "' ";
		echo "</span>";
		if ( $this->condition )		
			$this->showResultExpected();
		return FALSE;
	}

	// dump_var can be extensive, so not in error reporting
	public function showResultExpected() {
		echo "<p class=\"text\">";
		echo "expected: <br>";
		var_dump($this->expected);
		echo "<br>result: <br>";
		var_dump($this->result);
		echo "<br>";
		if ( $this->expected==$this->result )
			echo "equal <br>";
		else
			echo "not equal <br>";
		echo "</p><br>";
	}

	private function valueToString($value) {
		switch(gettype($value)) {
			case "string":
				return "string [" . strlen($value) . "]";
			case "integer":
				return "integer " . $value;
			case "float":
			case "double":
				return "float " . $value;
			case "boolean":
				$retval="boolean ";
				if ( $value )
					$retval=$retval . "TRUE";
				else
					$retval=$retval . "FALSE";
				return $retval;
			case "array":
				return "array " . count($value);
			case "object":
				return "object " . get_class($value);
			case "NULL":
				return "NULL";
			case "resource":
				return $value;
			default:
				return "unknown type";
		}
		return "";
	}

	private function getType($type) {
		if ( is_object($type) )
			return get_class($type);
		return gettype($type);
	}	

	public function compareTypes() {
		$method=get_class($this) . "::" . "compareTypes() ";
		$this->resultType=$this->getType($this->result);
		$this->expectedType=$this->getType($this->expected);
		$this->types=" " . $this->expectedType . " " .$this->resultType;
		// for some reason NULL is not always the correct type ??? (I think: null does not has a type)
		try {
			$this->reason=$method . $this->types . " ";
			if ( is_null($this->expected) )
				if ( is_null($this->result) ) {
					$this->reason=$this->reason . "both NULL";	
					return TRUE;
				} 

			if ( !is_null($this->expected) and is_null($this->result) )
				throw(new Exception("not equal"));
			if ( is_null($this->expected) and !is_null($this->result) )
				throw(new Exception("not equal"));
											
 			if ( strcmp(gettype($this->expected),gettype($this->result))==0 )
				$this->reason=$this->reason . " equal";
			else
				throw(new Exception(" not equal"));
			
			return TRUE;
		} catch(Exception $e) {
			$this->reason=$this->reason . $e->getMessage();
		}
		return FALSE;
	}

	private function resultString($name) {
		$this->resultString=" Result:" . $this->valueToString($this->result);
	}	
	private function expectedString($name) {
		$this->expectedString=" Expected:" . $this->valueToString($this->expected);
	}	

	public function compareValues() {
		// ??? for some reason NULL integer is not the same as NULL string ??? (I think:  null=null)
		// it is unclear where or when the NULL type is defined
		$method="compareValues()";
		$this->expectedString();
		$this->resultString();
		$this->values=$this->expectedString . $this->resultString;
		
		// $this->reason is set by compareTypes()
		try {
			$this->reason=$method . $this->values . " ";
			// NULL is already tested by compareTypes
			// both the same type is already tested by compareTypes
			
			if ( is_string($this->expected) ) {
				if ( strcmp($this->expected,$this->result)==0 ) {
					$this->reason=$this->reason . "equal";
					return TRUE;
				} else 
					throw(new Exception("not equal"));
			}

			if ( $this->expected==$this->result )
				$this->reason=$this->reason . "equal";
			else
				throw(new Exception("not equal"));

			return TRUE;
		} catch(Exception $e) {
			$this->reason=$this->reason . $e->getMessage();
		}
		return FALSE;		
	}

	// execute the test (variable test)
	public function execute() {
		$method=get_class($this) . "::execute()";
		try {
			if ( is_null($this->object) )
				throw(new Exception("cannot execute test, object not set"));

			//echo "executing test $this->id <br>";
			if ( !is_null($this->func) and !is_null($this->args) ) {
				//echo "executing call_user_func_array<br>";
				call_user_func_array($this->func,$this->args);
			} else {
				if ( !is_null($this->func) ) { 
					//echo "executing call_user_func<br>";				
					call_user_func($this->func);
				}
			}

			if ( !$this->resultSet )
				throw(new Exception("cannot execute test, result not set"));			

			$this->processed=TRUE;
		
			// retval must be of type
			if ( !$this->compareTypes() ) {
				$message="incorrect type" . $this->types;
				throw(new Exception($message));
			}

			// some external method has to set the value before the test is run !!!
			if ( !$this->compareValues() ) {
				$message="values not equal"  . $this->values;
				throw(new Exception($message));
			}

			return $this->isSuccess();
		} catch(Exception $e) {
			$this->error="$method " . $e->getMessage();
			$message=$e->getMessage();
		}
		return $this->isFailed();
	}

	public function setObject($object) {
		$this->object=$object;
	}

	public function setFunction($func) {
		$this->func=$func;
	}

	public function addArgument($arg) {
		if ( is_null($this->args) )
			$this->args=array($arg);
		else 
			array_push($this->args,$arg);
	}

// end class test
}

class tests extends SplDoublyLinkedList {
	public $tab1;
	public $tab2;
	public $tab3;
	public $eol;
	public $debug;

	public $test;    		// the current test

	public $version; 		// the version of the class to test
	public $created; 		// date the list of test is created
	public $started; 	   // date the list of test was last started
	public $ended;       // time the list of test was ended
	public $testFile; 	// the file containing the test code
	public $classFile;	// the file containing the class  
	public $className;	// the class name of the object to test
	public $title;   		// the title of this list of test
	public $running;     // indicates this tests are running
	public $proceed;     // boolean, continue testing if a test fails

	function __construct() {
		$this->tab1="&nbsp;&nbsp;&nbsp;";
		$this->tab2="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$this->tab3="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$this->eol="<br>";
		$this->debug=FALSE;

		$this->test=0;

		$this->version=NULL;
		$this->created=NULL;
		$this->started=NULL;
		$this->ended=NULL;
		$this->testFile=NULL;
		$this->classFile=NULL;
		$this->className=NULL;
		$this->title=NULL;
		$this->running=FALSE;
		$this->proceed=FALSE;
	}

	// defines test	
	public function defineTest($title,$name,$description,$expected,$condition) {
		try {
			if ( is_null($title) )
				throw(new Exception("Cannot add test, title is null"));
			if ( is_null($name) )
				throw(new Exception("Cannot add test, name is null"));
			
	 		$htis->test=new test($this->count(),$title,$name,$expected,$condition);
			$this->add($this->count(),$this->test);
			$this->test->description=$description;

			return TRUE;
		} catch(Exception $e) {
			echo $e->getMessage() . $this->eol;
		}
		return FALSE;
	}	
	
	// original outside syntax	
	//$test=new testVariable($tests->count(), " Constructor ", "tab3","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
	//$tests->add($tests->count(),$test);
	//should be public function addTestVariable($title,$name,$result) {, but for now
	public function addTest($title,$name,$expected,$condition,$description,$showResult) {
		try {
			if ( is_null($title) )
				throw(new Exception("Cannot add test, title is null"));
			if ( is_null($name) )
				throw(new Exception("Cannot add test, name is null"));
			
	 		$test=new test($this->count(),$title,$name,$expected,$condition);
			$this->add($this->count(),$test);
			$test->description=$description;
			$test->showResult=$showResult;

			return TRUE;
		} catch(Exception $e) {
			echo $e->getMessage() . $this->eol;
		}
		return FALSE;
	}

	private function printLine($line) {
		echo $line . " ";
		for ( $i=strlen($line); $i<128; $i++)
			echo "=";
		echo $this->eol;
	}

	public function showVars() {
		echo $this->tab1 . "testList variables" . $this->eol;
		echo $this->tab2 . "test '" . $this->test . "'" . $this->eol;
		echo $this->tab2 . "version '" . $this->version . "'" . $this->eol;
		echo $this->tab2 . "created '" . $this->created . "'" . $this->eol;
		echo $this->tab2 . "started '" . date("Y/m/d H:i:s",$this->started) . "'" . $this->eol;
		echo $this->tab2 . "ended '" . date("Y/m/d H:i:s",$this->ended) . "'" . $this->eol;
		echo $this->tab2 . "testFile '" . $this->testFile . "'" . $this->eol;;
		echo $this->tab2 . "classFile '" . $this->classFile . "'" . $this->eol;  
		echo $this->tab2 . "title '" . $this->title . "'" . $this->eol;
		echo $this->tab2 . "proceed " . $this->proceed ? 'TRUE' : 'FALSE' . $this->eol;
		echo $this->tab2 . "className '" . $this->className . "'" . $this->eol;
		echo $this->tab2 . "#tests '" . $this->count() . "'" . $this->eol;
	}

	public function start() {
		echo "<div class=\"text\">";
		echo "<p>";
		$line=$this->title;
		$line=$line . " object='" . $this->className . "'";
		$line=$line . " Version " . $this->version;
		$line=$line . " Created " . $this->created;
		$line=$line . " Started at " . gmdate("Y/m/d H:i:s");
		$this->started=time();
		$this->printLine($line);
		echo "</p>";
	}

	public function end() {
		echo "<p>";
		$line="test " . $this->className;
		$line=$line . " Version " . $this->version;
		$line=$line . " Created " . $this->created;
		$line=$line . " Ended at " . gmdate("Y/m/d H:i:s");
		$this->ended=time();
		$this->printLine($line);
		echo "</p>";
		echo "</div>";
	}

	public function processTest($test) {
		$test->started=time();

		echo "<div class=\"text\">";
		echo "<p>";
		echo gmdate("Y/m/d H:i:s",$test->started) . " Starting test Id:" . $test->id . " " . $test->title . " " . $test->name;
		if ( $test->condition )
			echo " Condition TRUE"; 
		else 
			echo " <span class=\"false\">Condition FALSE</span>"; 
		echo $this->eol;

		try {
			if ( is_null($test) )
				throw(new Exception("Cannot execute: test is NULL"));

			$retval=$test->execute();

			} catch(Exception $e) {
			echo $e.getMessage() . $this->eol;
			$retval=FALSE;
		}
		echo $this->eol . gmdate("Y/m/d H:i:s",$test->started) . " End test " . $this->eol;
		echo "</p>";
		echo "</div>";
		$test->ended=time();
		return $retval;
	}

	public function processTests() {
		try {
			foreach( $this as $key => $test) {
				if ( !$this->processTest($test) ) {
					if ( $test->condition )
						if ( !$this->continue )
							throw(new Exception("True Test failed, done testing"));
				} else { // test == success but false ?
					if ( !$test->condition )					
						if ( !$this->continue )
							throw(new Exception("False Test success, done testing"));
				}
			}
			return TRUE;
		} catch(Exception $e) {
			echo $e->getMessage() . $this->eol;
		}
		return FALSE;
	}
// end class testList
}

// end file Test.php

?>

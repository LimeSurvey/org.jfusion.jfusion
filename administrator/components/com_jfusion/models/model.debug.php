<?php

/**
 * PHP version 5
 *
 * @category  JFusion
 * @package   Models
 * @author    JFusion Team <webmaster@jfusion.org>
 * @copyright 2008 JFusion. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link      http://www.jfusion.org
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Debugging functions for time
 *
 * @category  JFusion
 * @package   Models
 * @author    JFusion Team <webmaster@jfusion.org>
 * @copyright 2008 JFusion. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link      http://www.jfusion.org */
class std {
	/**
	 *    getMicroTime
	 *
	 *    @returns array the actual tim in seconds
	 */
	public static function getMicroTime() {
		list($usec, $sec) = explode(' ', microtime());
		return ($usec + $sec);
	}
	/**
	 * getRequest
	 * merges HTTP_GET, HTTP_POST and HTTP-FILE requests together so that you have
	 * afterwards an all in one array
	 *
	 * @param array $post The HTTP_POST request ($_POST)
	 * @param array $get The HTTP-GET request ($_GET)
	 * @param array $files The HTTP-FILE request, if a form uploaded Files ($_FILES)
	 *
	 * @uses arrayfunc::recursiveMerge
	 *
	 * @return array a recursively merged Array
	 */
	public static function getRequest($post, $get, $files) {
		// Setzt verschiedene $_REQUEST-Daten sinnvoll zusammen
		$files = std::rearrangeFiles($files);
		//debug::show($files);
		$request = arrayfunc::recursiveMerge($files, $get);
		//debug::show($request);
		$request = arrayfunc::recursiveMerge($request, $post);
		//debug::show($request);
		return $request;
	}
	/**
	 * rearrangeFiles - PRIVATE
	 * re-arranges the HTTP-FILE-REQUEST array so that its
	 * organized like the POST and GET array afterwards
	 *
	 * @param array $files the raw FILE-Array
	 *
	 * @return array a POST/GET-Like structured Array
	 */
	private static function rearrangeFiles($files) {
		// Hilfsfunktion for std::getRequest() (recursive)
		$retArr = array();
		foreach ($files as $key => $value) {
			if (arrayfunc::isOneDimensional($value)) {
				$retArr[$key] = $value;
			} else {
				$tmpArr = array();
				foreach ($value as $httpKey => $subValue) {
					foreach ($subValue as $k => $v) {
						$tmpArr[$k][$httpKey] = $v;
					}
				}
				$retArr[$key] = std::rearrangeFiles($tmpArr);
			}
		}
		return $retArr;
	}
	/**
	 * removeEmptyLines
	 *
	 * removes Empty Lines from a Text
	 *
	 * @param string $text the Text to remove the empty Lines From
	 * @param string $newLineChar - OPTIONAL - the New-Line indicator, default is "\n"
	 *
	 * @return string the text without blank lines
	 */
	public static function removeEmptyLines($text, $newLineChar = "\n") {
		$lines = explode($newLineChar, $text);
		foreach ($lines as $idx => $line) {
			if (trim($line) == '') unset($lines[$idx]);
		}
		return join($newLineChar, $lines);
	}
}
/**
 * Main debugging class which is used for detailed outputs
 *
 * @category  JFusion
 * @package   Models
 * @author    JFusion Team <webmaster@jfusion.org>
 * @copyright 2008 JFusion. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link      http://www.jfusion.org
 */
class debug {
	/**
	 *    Will get a globally defined JS-Function
	 *    -  change the name if it messes up with your own JavaScript functions
	 *    -  ..or set it to the empty-String to disable value-toggling
	 */
	static $toggleFunctionName = 'tns';
	static $colorScheme = array();
	static $colorSchemeInited = array();
	static $toggleScriptInited = false;

	static $callback = null;
	/**
	 *    initializes the colorScheme
	 *    would be a Constructors task if the debug would be instantated, since in debug all methods are static,
	 *    call this method before the first time you need color scheme
	 *
	 *    The meaning of the keys:
	 *        - vc = ValueColor, the background of shown Values
	 *        - akc = ArrayKeyColor, the background of array-Keys
	 *        - okc = ObjectKeyColor, the background of shown "object keys" i.e. the names of public accessible object/class-Variables
	 *        - tc = ValueColor, the background of the (optional) Title
	 *        - gc = ValueColor, the color of the structuring-grid, i.e. the background of the table
	 */
	private static function initColorScheme() {
		static::$colorScheme[] = array('vc' => "#ecf8fd", 'akc' => "#dbfede", 'okc' => "#dbfede", 'tc' => '#d6f2ff', 'gc' => '#fbfed6');
		static::$colorScheme[] = array('vc' => "#f2bb94", 'akc' => "#cc9e7c", 'okc' => "#a68065", 'tc' => '#CCCCCC', 'gc' => '#AAAAFF');
		static::$colorScheme[] = array('vc' => "#faea37", 'akc' => "#d4c62f", 'okc' => "#ada226", 'tc' => '#CCCCCC', 'gc' => '#AAAAFF');
		static::$colorScheme[] = array('vc' => "#4bf8d0", 'akc' => "#3fd1af", 'okc' => "#33ab8f", 'tc' => '#CCCCCC', 'gc' => '#AAAAFF');
		static::$colorScheme[] = array('vc' => "#7a7a7a", 'akc' => "#a1a1a1", 'okc' => "#c7c7c7", 'tc' => '#CCCCCC', 'gc' => '#AAAAFF');
		static::$colorScheme[] = array('vc' => "#0099cc", 'akc' => "#009999", 'okc' => "#00ff00", 'tc' => '#CCCCCC', 'gc' => '#AAAAFF');
		static::$colorScheme[] = array('vc' => "#cfc", 'akc' => "#cf6", 'okc' => "#cf0", 'tc' => '#CCCCCC', 'gc' => '#AAAAFF');
		static::$colorScheme[] = array('vc' => "#ffc", 'akc' => "#ff6", 'okc' => "#ff0", 'tc' => '#CCCCCC', 'gc' => '#AAAAFF');
		static::$colorScheme[] = array('vc' => "#f96", 'akc' => "#c66", 'okc' => "#966", 'tc' => '#CCCCCC', 'gc' => '#AAAAFF');
	}
	/**
	 * Creates and returns the JavaScript-Snippet used to toggle values by klicking on the keys
	 * returns the code only once, i.e. the first time, print it to the standard output if you get the snippet
	 *
	 * @return string
	 */
	private static function getToggleScript() {
		$script = '';
		if (static::$toggleScriptInited == false && static::$toggleFunctionName != '') {
			$toggleFunctionName = static::$toggleFunctionName;
			$script = '<script type="text/javascript">';
			$script .= <<<JS
            function {$toggleFunctionName}(event) {
                var evtSource;
                evtSource = window.event ? window.event.srcElement : event.target;
                while (evtSource.nextSibling === null) { evtSource = evtSource.parentNode;  }
                var tNode = evtSource.nextSibling;
                while (tNode.nodeType != 1) { tNode = tNode.nextSibling; }
                tNode.style.display = (tNode.style.display != 'none') ? 'none' : 'block';
            }
JS;
			$script .= '</script>';

			static::$toggleScriptInited = true;
		}
		return $script;
	}
	/**
	 *    Creates the style information for a color schema if needed
	 *    i.e. this schema is used the first time, print it to the standard output if you need it
	 *
	 *    @param int $schema the index of the desired color schema
	 *    @return string style-code if needed
	 */
	private static function setStylesForScheme($schema) {
		$style = '';
		if (count(static::$colorScheme) == 0) static::initColorScheme();
		if (!isset(static::$colorSchemeInited[$schema])) {

			$vc = static::$colorScheme[$schema]['vc'];
			$akc = static::$colorScheme[$schema]['akc'];
			$okc = static::$colorScheme[$schema]['okc'];
			$tc = static::$colorScheme[$schema]['tc'];
			$gc = static::$colorScheme[$schema]['gc'];


			$style = '<style  type="text/css">';
			$style .= <<<CSS
            div.debug_{$schema} .value {
                min-width: 100px;
                background-color: {$vc};
            }
            div.debug_{$schema} .a_key {
                min-width: 100px;
                background-color: {$akc};
            }
            div.debug_{$schema} .o_key {
                min-width: 100px;
                background-color: {$okc};
            }
            div.debug_{$schema} .title {
                background-color: {$tc};
            }
            div.debug_{$schema} .grid {
                font-family: arial, serif;
                background-color: {$gc};
                vertical-align:top;
            }
CSS;
			$style.= '</style>';
			static::$colorSchemeInited[$schema] = true;
			//print($style);

		}
		return $style;
	}

	/** *************************
	 * Craetes and returns a HTML-Code that shows nicely
	 * the Structure and Value(s) of any PHP-Variable, the given Value can be from a simple Integer
	 * to a complex object-structure. This function works recursively.
	 *
	 * @param mixed $arr   the PHP-Variable to look in
	 * @param mixed $start a title for the created structure-table
	 * @param bool|string $style
	 *
	 * @return string a HTML-Code Snippet (e.g. to be Viewed in a Browser)
	 ** ************************
	 */
	public static function get($arr, $start = true, $style = false) {
		$schema = 0;
		$str = '';
		$name = '';
		if (is_numeric($start)) { // All Arguments "move" 1 to the left
			$start = true;
		}
		if (is_string($start)) { // Indicates that we are on "root"-Level
			$name = $start;
			$start = true;
		}
		if (is_array($arr) || is_object($arr)) {
			if ($start == true) { // the "root" run
				$neededScriptCode = static::getToggleScript();
				$neededStyleCode = static::setStylesForScheme($schema);
				$str = $neededScriptCode . '<div class="debug_'.$schema.'">'."\n" . $neededStyleCode;
			}
			$emptyWhat = 'empty-array';
			$keyClass = 'a_key';
			if (is_object($arr)) {
				$keyClass = 'o_key';
				$emptyWhat = 'empty-object';
			}
			$empty = true;
			if (static::isOneDimensional($arr) && !$start) {
				foreach ($arr as $key => $value) {
					$empty = false;

					if (static::$callback) {
						list($target,$function,$args) = static::$callback;
						if ($style == false) {
							$style = '';
							list($style,$value) = $target->$function($key,$value,$args);
						} else {
							list(,$value) = $target->$function($key,$value,$args);
						}
					}
					$str.= '<span class="'.$keyClass.'" style="'.$style.'"> ' . static::decorateValue($key) . '</span> ';
					$str.= '<span class="value" style="'.$style.'" > ' . static::decorateValue($value) . '</span><br/>';
				}
				if ($empty) {
					$str.= '<span class="'.$keyClass.'">'.$emptyWhat.'</span><br>'."\n";
				}
			} else {
				$onClick = '';
				if (static::$toggleFunctionName != '') $onClick = 'onclick="' . static::$toggleFunctionName . '(event)"';
				$str.= '<table class="grid" width="100%">';
				if ($name != '') {
					$str.= '<thead '.$onClick.'><tr><th colspan="2" class="title">'.$name.'</th></tr></thead>';
				}
				$str.= '<tbody>';
				foreach ($arr as $key => $value) {
					$temp = $style;
					$empty = false;
					if (static::$callback) {
						list($target,$function,$args) = static::$callback;
						if ($style == false) {
							$style = '';
							list($style,$value) = $target->$function($key,$value,$args);
						} else {
							list(,$value) = $target->$function($key,$value,$args);
						}
					}
					$str.= '<tr>';
					$str.= '<td class="'.$keyClass.'" '.$onClick.' style="'.$style.'">'.static::decorateValue($key).'</td>';
					$str.= '<td class="value" style="'.$style.'">'.static::get($value, false, $style).'</td>';
					$str.= '</tr>';
					$style = $temp;
				}
				if ($empty) {
					$str.= '<tr><td colspan="2" class="'.$keyClass.'">'.$emptyWhat.'</td></tr>';
				}
				$str.= '</tbody></table>';
			}
			if ($start == true) { // the top-Level run
				$str.= '</div>';
			}
		} else { // the "leave"-run
			$str = static::decorateValue($arr);
			if ($name != '') $str = '<div class="debug_0"><table class="grid" width="100%"><thead onclick="'.static::$toggleFunctionName.'(event)"><tr><th class="title">'.$name.'</th></tr></thead><tbody><tr><td class="a_key" onclick="'.static::$toggleFunctionName.'(event)">'.$str.'</td></tr></tbody></table></div>';
			//flush();

		}
		return $str;
	}

	/**
	 * Craetes and returns a String in html code. that shows nicely in copy paste
	 *
	 * @param mixed $arr   : the PHP-Variable to look in
	 * @param mixed $start : a title for the created structure-table
	 * @param int   $level
	 *
	 * @return string a html string with no tags
	 */
	public static function getText($arr, $start = true, $level=1) {
		$levelText = '';
		for ($i = 0; $i < $level; $i++) {
			$levelText .= "\t";
		}
		$str = '';
		if (is_string($start)) {
			$str.= $start.' - &darr;'."\n";
		}
		if (is_array($arr) || is_object($arr)) {
			$emptyWhat = 'empty-array';
			if (is_object($arr)) {
				$emptyWhat = 'empty-object';
			}
			$empty = true;
			if (static::isOneDimensional($arr)) {
				foreach ($arr as $key => $value) {
					$empty = false;
					if (static::$callback) {
						list($target,$function,$args) = static::$callback;
						list(,$value) = $target->$function($key, $value, $args);
					}
					$str.= $levelText.$key.' &rarr; '.static::decorateValue($value, false)."\n";
				}
				if ($empty) {
					$str.= $levelText.$emptyWhat."\n";
				}
			} else {
				foreach ($arr as $key => $value) {
					$empty = false;
					$emptyWhat = 'empty-array';
					if (is_object($value)) {
						$emptyWhat = 'empty-object';
					}
					if (static::$callback) {
						list($target,$function,$args) = static::$callback;
						list(,$value) = $target->$function($key, $value, $args);
					}
					if ( is_array($value) || is_object($value) ) {
						if (count($value) == 0) {
							$str.= $emptyWhat."\n";
						}
						$str.= $levelText.$key.' - &darr; '."\n".static::getText($value, false, $level+1);
					} else {
						$str.= $levelText.$key.' &rarr; '.static::decorateValue($value, false)."\n";
					}
				}
				if ($empty) {
					$str.= $emptyWhat."\n";
				}
			}
		} else {
			$str = $levelText.$arr."\n";
		}
		return $str;
	}

	/**
	 *    Checks if an array is one-dimensional, i.e. if no one of the values is an array or abject again
	 *    The public version of this function is in arrayfunc, this here is just to use by debug::get
	 *    To avoid that the basic methods debug::get and debug::show have dependencies of other classes
	 *
	 *    @param array|object $arr: the array to check
	 *
	 *    @return boolean if it is one-dimensional
	 */
	private static function isOneDimensional($arr) {
		if (!is_array($arr) && !is_object($arr)) {
			$result = false;
			return $result;
		}
		foreach ($arr as $val) {
			if (is_array($val) || is_object($val)) {
				$result = false;
				return $result;
			}
		}
		$result = true;
		return $result;
	}
	/**
	 * Same as debug::get, but prints the created HTML-Code directly to the Standard Output.
	 * NOTE: This is the one and only debuging Tool!!
	 *
	 * @param mixed $arr the PHP-Variable to look in
	 * @param mixed $title a title for the created structure-table
	 *
	 * @return void
	 */
	public static function show($arr, $title = false) {
		print (static::get($arr, $title));
		//flush();

	}
	static $messungen;
	static $lastTime;
	/**
	 *    Starts a time measurement, deprecated, use starMessung and stopMessung instead.
	 *
	 *    @param string $name: The name for this time measurement.
	 *        the measurement stops if this function is called again with another name.
	 *        measurements with the same name will be summated.
	 *
	 */
	function messung($name) {
		$nowTime = std::getMicroTime();
		if (!isset(static::$messungen[$name])) static::$messungen[$name] = 0;
		if (isset(static::$lastTime)) {
			static::$messungen[$name]+= (($nowTime - static::$lastTime) * 1000);
		}
		static::$lastTime = $nowTime;
	}
	/**
	 *    Shows the result of the measurements
	 *    Prints them HTML-Encoded to the Standard-Output (i.e. Browser)
	 *
	 *    @uses debug::show()
	 */
	function showTime() {
		static::show(static::$messungen, "Zeitmessungen");
	}
	/**
	 *    Returns the results of the measurements, HTML-Encoded
	 *
	 *    @uses debug::get()
	 *
	 *    @return string the HTML-Code
	 */
	function getTime() {
		return static::get(static::$messungen, "Zeitmessungen");
	}
	/**
	 *    Shows the Stacktrace
	 *
	 *    @uses debug::show()
	 */
	function showTrace() {
		$stack = debug_backtrace();
		$niceStack = array();
		for ($n = 1;$n < count($stack);$n++) {
			$key = "{$stack[$n]['file']} ({$stack[$n]['line']})";
			$argv = array();
			foreach ($stack[$n]['args'] as $arg) {
				$argv[] = var_export($arg, true);
			}
			$arglist = join(', ', $argv);
			$niceStack[$key] = $stack[$n]['class'] . $stack[$n]['type'] . $stack[$n]['function'] . '(' . $arglist . ')';
		}
		static::show($niceStack, "{$stack[0]['file']} ({$stack[0]['line']})");
	}
	static $laufzeit = array();
	static $laufzeitStack = array();
	static $stackName = array();
	/**
	 *    Starts a new (time-)measurement with the given Name
	 *    Measurements can be Started within other measurements
	 *    Measurements with the same can be started and stopped multiple times, they will be counted ans summated.
	 *    e.g. within once quicksort, 100 times "HD-read" and 40 times "HD-write"
	 *    - inner Measurements MUST be stopped before the outer measurment stops!
	 *    - all Measurements MUST be stopped before showing the Results!
	 *
	 *    @param string $name The Name for this measurement (e.g. "Quicksearch")
	 *        the measurement stops stops if stopMessung is Called and the given name is identical
	 *
	 */
	static function startMessung($name) {
		if (count(static::$laufzeitStack) == 0) {
			$prtLaufzeit = & static::$laufzeit;
		} else {
			$prtLaufzeit = & static::$laufzeitStack[count(static::$laufzeitStack) - 1];
		}
		if (!isset($prtLaufzeit['childs'][$name])) {
			$prtLaufzeit['childs'][$name] = array('count' => 0, 'time' => 0);
		}
		$prtLaufzeit['childs'][$name]['start'] = std::getMicroTime();
		static::$laufzeitStack[count(static::$laufzeitStack) ] = & $prtLaufzeit['childs'][$name];
		static::$stackName[count(static::$stackName) ] = $name;
	}
	/**
	 *    stops the Measurement with teh given Name
	 *
	 *    @param string $name the Name of the Measurement to stop
	 */
	static function stopMessung($name) {
		$tiefe = 0;
		for ($n = count(static::$laufzeitStack) - 1;$n >= 0;$n--) {
			if (static::$stackName[$n] == $name) $tiefe = max($tiefe, $n);
		}
		$cnt = count(static::$laufzeitStack);
		for ($n = $tiefe;$n < $cnt;$n++) {
			$aktLz = & static::$laufzeitStack[$n];
			if ($n == $tiefe) {
				$aktLz['time']+= std::getMicroTime() - $aktLz['start'];
				$aktLz['count']++;
			} else {
				$aktLz['time']+= std::getMicroTime() - $aktLz['start'];
				$aktLz['count'] = 'Error, Measurement ' . static::$stackName[$n] . ' not closed';
			}
			unset(static::$laufzeitStack[$n]);
			unset(static::$stackName[$n]);
		}
	}
	/**
	 *    PRIVATE - This function works recursively.
	 *
	 *    Rearranges the results from all Measurements so that they will get human-understandable
	 *
	 *    @param array $laufzeit the raw-array produced by calling start- and stopMessung
	 *
	 *    @return array a humen-understandable Version
	 */
	private static function createLaufzeitResult($laufzeit) {
		$result = array();
		if (isset($laufzeit['childs'])) {
			$childTime = 0;
			if (isset($laufzeit['time'])) $result['Calls'] = $laufzeit['count'];
			foreach ($laufzeit['childs'] as $name => $child) {
				$result[$name] = static::createLaufzeitResult($child);
				$childTime+= $child['time'];
			}
			if (isset($laufzeit['time'])) {
				$result['Difference'] = ($laufzeit['time'] - $childTime) * 1000;
				$result['TOTAL'] = $laufzeit['time'] * 1000;
			}
		} else {
			$result = array('Calls' => $laufzeit['count'], 'time' => $laufzeit['time'] * 1000);
		}
		return $result;
	}
	/**
	 *    Shows the measurements results (they from start- and stopMessung)
	 *    prints HTML-Encoded Results directly to the Standard-Output
	 *
	 *    @uses debug::show()
	 *
	 */
	function showLaufzeit() {
		$lz_nice = static::createLaufzeitResult(static::$laufzeit);
		static::show($lz_nice, "Time measurements in ms");
	}
	/**
	 *    Returns the measurements results HTML-Encoded(they from start- and stopMessung).
	 *
	 *    @uses debug::get()
	 *    @return string the HTML-Code
	 *
	 */
	function getLaufzeit() {
		$lz_nice = static::createLaufzeitResult(static::$laufzeit);
		// $str =  debug::get(debug::$laufzeit, "Zeitmessungen in s");
		return static::get($lz_nice, "Time measurements in ms");
	}

	/**
	 *    Prepares Values to be used in debug::show / debug::get used to indicate a values type
	 *    - Strings will be
	 *        - in double-quotes if they are empty (to see something)
	 *        - Normal if not empty
	 *        - < and > will be replaced by "&lt;", "&gt;" to avoid tag-Interpretation by a Browser
	 *    - boolean and all numbers will be bold.
	 *    - the null-Value will be bold and italic
	 *
	 * @param mixed $value the Value to HTML-Encode
	 *
	 * @param bool  $html
	 *
	 * @return string the HTML-Encoded Value
	 */
	private static function decorateValue($value, $html = true) {
		if (is_string($value)) {
			if (trim($value) == '') $decValue = '\''.$value.'\'';
			else $decValue = str_replace(array('<', '>'), array('&lt;', '&gt;'), $value);
		} else if (is_bool($value)) {
			if ($value) $decValue = 'true';
			else $decValue = 'false';
			if ($html){
				$decValue = '<strong>'.$decValue.'</strong>';
			}
		} else if (is_null($value)) {
			$decValue = 'null';
			if ($html){
				$decValue = '<strong><i>'.$decValue.'</i></strong>';
			}
		} else {
			$decValue = $value;
			if ($html){
				$decValue = '<strong>'.$decValue.'</strong>';
			}
		}
		return $decValue;
	}
}
/**
 * Transposing functions
 *
 * @category  JFusion
 * @package   Models
 * @author    JFusion Team <webmaster@jfusion.org>
 * @copyright 2008 JFusion. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link      http://www.jfusion.org
 */
class trans {
	static $jsSpecialChars = array("\\", '"', "\n", "\r");
	static $jsReplaceTo = array("\\\\", '\"', '\n', '\r');
	/**
	 *    Creates a JavaScript-Code that initializes a JavaScript Variable that contains
	 *    the value of the given PHP-Variable
	 *
	 *    The PHP-Variable can be from a simple integer to a complex object / array
	 *
	 *    @param string $jsVarName, the name the Variable should have in the JavaScript
	 *    @param mixed $phpvar, the Value the JS-Variable should be initialized with
	 *    @uses trans::phpValue2js()
	 *
	 *    @return string, the JavaScriptCode
	 */
	function php2js($jsVarName, $phpvar) {
		$jsCode = 'var '.$jsVarName.' = ' . trans::phpValue2js($phpvar) . ";\n";
		return $jsCode;
	}
	/**
	 *    Converts a PHP-Value into a JSON-String
	 *    This function works recursively
	 *
	 *    @param mixed $phpValue: the Value to transform into JSON
	 *    @return string the JSON String
	 */
	public static function phpValue2js($phpValue) {
		$jsCode = false;
		if (is_long($phpValue)) $jsCode = $phpValue;
		if (is_float($phpValue)) $jsCode = $phpValue;
		if (is_integer($phpValue)) $jsCode = $phpValue;
		if (is_int($phpValue)) $jsCode = $phpValue;
		if (is_double($phpValue)) $jsCode = $phpValue;
		if (!$jsCode && $jsCode !== false) $jsCode = '0';
		if (is_bool($phpValue)) {
			if ($phpValue) $jsCode = 'true';
			else $jsCode = 'false';
		}
		if (is_string($phpValue)) $jsCode = '"' . str_replace(trans::$jsSpecialChars, trans::$jsReplaceTo, $phpValue) . '"';
		if (is_null($phpValue)) $jsCode = 'null';
		if (is_array($phpValue)) {
			$elems = array();
			$numeric = arrayfunc::is_numeric_array($phpValue);
			foreach ($phpValue as $key => $seg) {
				$part = array();
				if ($numeric == false) $part[] = '"' . str_replace(trans::$jsSpecialChars, trans::$jsReplaceTo, $key) . '"';
				$part[] = trans::phpValue2js($seg);
				$elems[] = join(':', $part);
			}
			if ($numeric) $jsCode = '[' . join(',', $elems) . ']';
			else $jsCode = '{' . join(',', $elems) . '}';
		}
		if ($jsCode == '') $jsCode = '"Type not supported yet"';
		return $jsCode;
		/*

		If the JSON-Extention is available this would (hopefully) run much faster!


		$f = $r = array();
		foreach (array_merge(range(0, 7), array(11), range(14, 31)) as $v) {
		$f[] = chr($v);
		$r[] = "\\u00".sprintf("%02x", $v);
		}
		return str_replace($f, $r, json_encode($phpValue));
		*/
	}
	/*
	function jsEscape($val)
	{
		return str_replace(trans::$jsSpecialChars, trans::$jsReplaceTo, $val);
	}
	*/
	/**
	 *    Creates a PHP-Code that initializes a PHP Variable that contains
	 *    the value of the given PHP-Variable, this function works recursively.
	 *
	 *    The PHP-Variable can be from a simple integer to a complex object / array
	 *
	 *    @param string $phpVarName, the name the Variable should have
	 *    @param mixed $phpvar, the Value the New-Variable should be initialized with
	 *    @param string $firstDimSpacer - OPTIONAL - Just to butify the Output
	 *
	 *    @return string, the PHP-Code
	 */
	public static function php2phpCode($phpVarName, $phpvar, $firstDimSpacer = '') {
		$phpCode = '';
		$pre = '$';
		if (is_numeric($phpvar)) $phpCode = $pre.$phpVarName.' = '.$phpvar.";\n";
		if (is_bool($phpvar)) {
			if ($phpvar) $phpCode = $pre.$phpVarName.' = true;'."\n";
			else $phpCode = $pre.$phpVarName.' = false;'."\n";
		}
		if (is_string($phpvar) && !is_numeric($phpvar)) $phpCode = $pre.$phpVarName.' = "' . trans::replaceChars($phpvar) . '";'."\n";
		if (is_null($phpvar)) $phpCode = $pre.$phpVarName.' = null;'."\n";
		if (is_array($phpvar)) {
			if (arrayfunc::isOneDimensional($phpvar)) {
				$phpCode = $pre.$phpVarName.' = array(';
				foreach ($phpvar as $key => $value) {
					if (!is_numeric($value)) $value = trans::replaceChars($value);
					if (is_int($key) && $key >= 0) $phpCode.= "$key => \"$value\", ";
					else $phpCode.= "\"$key\" => \"$value\", ";
				}
				if (count($phpvar) > 0) {
					$phpCode = substr($phpCode, 0, -2);
				}
				$phpCode.= ");\n";
			} else {
				$phpCode = $pre.$phpVarName.' = array();'."\n";
				foreach ($phpvar as $key => $value) {
					if (is_int($key) && $key >= 0) $name = $phpVarName . '[' . $key . ']';
					else $name = $phpVarName . '[\'' . $key . '\']';
					$phpCode.= trans::php2phpCode($name, $value) . $firstDimSpacer;
				}
			}
		}
		if ($phpCode == '') $phpCode = $pre.$phpVarName.' = "Type not supported yet";'."\n";
		return $phpCode;
	}
	/**
	 *    replaces Characters which would cause Problems in PHP-Code
	 *
	 *    @param string $str: the raw String
	 *    @return string the escaped String
	 */
	public static function replaceChars($str) {
		$patterns = array("\"", "\n", "\t", "\r", '$');
		$replacements = array("\\\"", "\\\n", "\\\t", "\\\r", "\\\\$");
		return str_replace($patterns, $replacements, $str);
	}
	/**
	 *    converts a HTML-Encoded String into Plaintext
	 *    - all tags will be removed
	 *       - some special tabs will be replaced  by a newline char
	 *    - the <td>-Tag will be replaced ba a tabulator Char
	 *
	 *    @param string $htmlstr the HTML-Encoded String
	 *    @param string $newLineChar - OPTIONAL - the Character to be used as newline.Char, default: "\n"
	 *    @param string $tabChar - OPTIONAL - the Character to be used as tabulator.Char, default: "\t"
	 *
	 *    @return string the plaintext
	 */
	function html2plaintext($htmlstr, $newLineChar = "\n", $tabChar = "\t") {
		$DEF_TAGLIST_LINEBREAK = array("br", "h1", "h2", "h3", "h4", "h5", "h6", "p", "div", "tr");
		$DEF_TAGLIST_TAB = array("td");
		$htmlstr = str_replace("\n", '', $htmlstr);
		$htmlstr = str_replace("\r", '', $htmlstr);
		$htmlstr = str_replace('<br>', $newLineChar, $htmlstr);
		foreach ($DEF_TAGLIST_LINEBREAK as $tag) {
			$htmlstr = str_replace("</$tag>", $newLineChar, $htmlstr);
			$htmlstr = str_replace("<$tag />", $newLineChar, $htmlstr);
			$htmlstr = str_replace("<$tag/>", $newLineChar, $htmlstr);
		}
		foreach ($DEF_TAGLIST_TAB as $tag) {
			$htmlstr = str_replace("</$tag>", $tabChar, $htmlstr);
			$htmlstr = str_replace("<$tag />", $tabChar, $htmlstr);
			$htmlstr = str_replace("<$tag/>", $tabChar, $htmlstr);
		}
		$str = trans::decodeHTML($htmlstr);
		return strip_tags($str);
	}
	/**
	 *
	 *    NOT USED ANYMORE - HOPEFULLY
	 *
	 */
	/*
	function conv($str, $what) {
	$what = strtoupper($what);
	switch ($what) {
	case 'IV':
	$str = str_replace("'", '&#039;', $str);
	$str = str_replace('"', '&quot', $str);
	break;
	case 'DBV':

	break;
	case 'URL':

	break;
	case 'JS':
	$str = addcslashes(addslashes($str), "\n,\r,\t");
	break;
	case 'SAMP':

	break;

	}
	return $str;
	}
	*/
	/**
	 *    Explodes a string into a Hashtable
	 *
	 *    @param string $delim1, the delimiter that separates the entries
	 *    @param string $delim2, the delimiter that separates the key from the Value
	 *    @param string $string the String to explode
	 *
	 *    Example :     String: "width: 100px; height:50px"
	 *            delim1: ";"
	 *            delim2: ":"
	 *        ==> array('width' => '100px', 'height':'50px');
	 *
	 *    @return array the created hashtable
	 */
	function hashExplode($delim1, $delim2, $string) {
		if ($string == '') return array();
		$arr = explode($delim1, $string);
		$hashy = array();
		foreach ($arr as $zuw) {
			$arr2 = explode($delim2, $zuw);
			if (count($arr2) == 1) $hashy[$arr2[0]] = $arr2[0]; // 040928 hinzugef�gt (tylmann)
			if (count($arr2) == 2) $hashy[$arr2[0]] = $arr2[1];
			if (count($arr2) > 2) $hashy[array_shift($arr2) ] = implode($delim2, $arr2); // 040928 hinzugef�gt (tylmann)

		}
		return $hashy;
	}
	/**
	 *    Joins a Hashtable to a String
	 *
	 *    Just the opposite of trans::hashExplode
	 *
	 *    @param string $delim1, the delimiter that separates the entries
	 *    @param string $delim2, the delimiter that separates the key from the Value
	 *    @param array $hashy the Hashtable to join
	 *
	 *    @return string the created string
	 */
	function hashJoin($delim1, $delim2, $hashy) {
		$arr = array();
		foreach ($hashy as $key => $val) {
			$arr[] = $key . $delim2 . $val;
		}
		return join($delim1, $arr);
	}
	/**
	 *    Like trans::hashJoin except the the Values will be Quoted with the giver Quote Character
	 *
	 *    Useful to build-up attribute-lists for HTML Tags
	 *
	 *    @param array $hashy the Hashtable to join
	 *    @param string $delim, the delimiter that separates the entries, typically ' '
	 *    @param string $Quote, The Character used to Quote the Values, typically '"'
	 *    @param string $equiv, the delimiter that separates the key from the Value, typically '='
	 *
	 *    @return string the created string
	 */
	function joinToAttrList($hashy, $delim, $Quote, $equiv) {
		$arr = array();
		foreach ($hashy as $key => $val) {
			$val = htmlentities($val);
			$arr[] = $key . $equiv . $Quote . $val . $Quote;
		}
		return join($delim, $arr);
	}
	/**
	 *    Creates a Mail-Header String from the given hashtable
	 *
	 *    @param array $header the Hashtable to use
	 *
	 *    a shortcut for trans::hashJoin("\r\n", ': ', $header);
	 *
	 *    @return string the created string
	 */
	function mailHeaderFromHash($header) {
		$headers = '';
		foreach ($header as $arg => $value) {
			$headers.= "$arg: $value\r\n";
		}
		return $headers;
	}
	/**
	 *    Builds up the query-part of an URL that represents the given data
	 *    data can be from a simple integer up to a complex object / array
	 *    This function works recursively
	 *    If a complex array/object is given the keys will be the Variablenames in the query
	 *
	 *    NOTE: Due to an URL-Query must by a String this function is NOT type-safe
	 *
	 *    @param mixed $data, the data that should be contained in the Query
	 *    @param string $prefix, the name of the Variable in the query, optional if the given Data is an array/object
	 *
	 *    @return string the URL-Query, values will be URL-Encoded
	 */
	public static function http_build_query($data, $prefix = '') {
		if (!is_array($data) && !is_object($data)) {
			return $prefix . '=' . urlencode($data);
		}
		$cmds = array();
		foreach ($data as $key => $value) {
			if ($prefix != '') $cmds[] = trans::http_build_query($value, $prefix . '[' . $key . ']');
			else $cmds[] = trans::http_build_query($value, $key);
		}
		foreach ($cmds as $idx => $cmd) {
			if ($cmd == '') unset($cmds[$idx]); // avoid empty-arrays

		}
		return join('&', $cmds);
	}
	/**
	 *    Builds up an array from an XML-Encoded String
	 *
	 *    NOTE: On error a HTML-formatted Error Message will be sent to the standard output.
	 *        This is to simplify debugging, remove this in production releases or use ob_start() and ob_clean()
	 *
	 *    @param string $xml, the XML-String to interpreted
	 *    @return array the built up array representing the XML-String, false if it fails
	 */
	function xml2array($xml) {
		$xp = xml_parser_create();
		xml_parser_set_option($xp, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option($xp, XML_OPTION_SKIP_WHITE, true);
		xml_parse_into_struct($xp, trim($xml), $vals, $index);
		$err = xml_get_error_code($xp);
		$result = false;
		if ($err != 0) {
			$error['Code'] = $err;
			$error['Text'] = xml_error_string($err);
			$error['Zeile'] = xml_get_current_line_number($xp);
			$error['Spalte'] = xml_get_current_column_number($xp);
			$error['Byte'] = xml_get_current_byte_index($xp);
			static::show($error, "XML-Fehler");
		} else {
			$data = array();
			$parentList = array($data);
			foreach ($vals as $value) {
				$parent = & $parentList[count($parentList) - 1];
				if ($value['value'] == null) $value['value'] = '';
				switch ($value['type']) {
					case 'open':
						$node = array('tag' => $value['tag'], 'attributes' => $value['attributes'], 'value' => $value['value'], 'childs' => array());
						$parent['childs'][] = $node;
						$parentList[count($parentList) ] = & $parent['childs'][count($parent['childs']) - 1];
						break;
					case 'complete':
						$node = array('tag' => $value['tag'], 'attributes' => $value['attributes'], 'value' => $value['value'], 'childs' => array());
						$parent['childs'][] = $node;
						break;
					case 'close':
						unset($parentList[count($parentList) - 1]);
						break;
				}
			}
			$result = $data['childs'][0];
		}
		xml_parser_free($xp);
		return $result;
	}
	/**
	 *    Decodes HTML-Entities (&gt;, &auml; &nbsp;, etc..)
	 *
	 *    @param string $string, the string to decode
	 *    @return string, the String Where the HTML Entities are replaced by "normal" Characters
	 */
	private static function decodeHTML($string) {
		$string = strtr($string, array_flip(get_html_translation_table(HTML_ENTITIES)));
		$string = preg_replace('/&#([0-9]+);/me', "chr('\\1')", $string);
		return $string;
	}
}
/**
 * Array functions
 *
 * @category  JFusion
 * @package   Models
 * @author    JFusion Team <webmaster@jfusion.org>
 * @copyright 2008 JFusion. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link      http://www.jfusion.org
 */
class arrayfunc {
	var $EQUAL = '=';
	var $LIKE = 'LIKE';
	var $SMALLER = '<';
	var $GREATER = '>';
	/**
	 *    Sorts a 2-Dimensional Array, the intention is to sort arrays like Tables in SQL (by "Column")
	 *    So the $data should be an array of "rows" (represented as Hashtable where the Key will be the
	 *    "column-name" and the Value the Velu od the actual Cell), the keys in each row should be the same
	 *
	 *    @param array $data, the data to sort, an array of rows
	 *    @param mixed $criteria, the "columnname" to sort, can be comma-separated e.g. "date,time";
	 *                can also be an array e.g. array('date', 'time');
	 *    @param mixed $direction the sort Direction ASC or DESC, OPTIONAL, ASC is default
	 *                can also be comma-separated or array, MUST have the same "length" as $criteria
	 *    @param boolean $holdIndizes - OPTIONAL, default: false, if the indizes of the "rows" should stay at their
	 *                rows. This is useful if the row-indizes represent e.g.
	 *                    - the Primary keys of the according Database-Entries
	 *                    - Filenames
	 *                    - URL's, etc...
	 *
	 *
	 *    @return array the sorted array
	 *
	 *    NOTE:     - To test what happens with the different options use debug::show() before and after sorting
	 *        - Runtime: O(n * ln(n))    if one criteria is given, O(m * (n * ln(n)) if m criterias given (worst case!!)
	 */
	public static function tableSort($data, $criteria, $direction = 'ASC', $holdIndizes = false) {
		static::startMessung('tableSort');
		$sortArr = array();
		$retArr = array();
		if (!is_array($criteria)) $criteria = explode(',', $criteria);
		if (!is_array($direction)) $direction = explode(',', $direction);
		$thisCrit = $criteria[0];
		$thisDir = $direction[0];
		unset($criteria[0]);
		unset($direction[0]);
		foreach ($data as $idx => $row) {
			$sortArr[$row[$thisCrit]][$idx] = $row;
		}
		if (count($criteria) > 0) {
			if (count($direction) == 0) $direction = array();
			foreach ($sortArr as $critValue => $matches) {
				$sortArr[$critValue] = arrayfunc::tableSort($matches, join(',', $criteria), join(',', $direction), $holdIndizes);
			}
		}
		if (strtolower($thisDir) == 'desc') krsort($sortArr);
		else ksort($sortArr);
		foreach ($sortArr as $subArr) {
			foreach ($subArr as $idx => $row) {
				if ($holdIndizes) $retArr[$idx] = $row;
				else $retArr[] = $row;
			}
		}
		static::stopMessung('tableSort');
		return $retArr;
	}
	/*------------     HOW TO  -------------------------

	$data : 2 Dimensionales Array, keys des 2. Dimension kommen als Kriterium in Frage
	$criteria  : String oder 1-Dimsionales Array von kriterien, meherere Krinerien k�nnen auch
	komma-getrennt als String �bergeben werden

	--------------------------------------------------*/
	/**
	 *    Groups a 2-Dimensional Array. Like in tableSort the imagination is that we Group a Table
	 *    The data must be an Array of "rows", row must be Hashtables (colname => cellValue)
	 *
	 *    This function groups all rows together where the "cellValue" of the given "column" is the same
	 *    One can also say separates the given Array n Parts where n is the number of different entries in the
	 *    given "column"
	 *
	 *    @param array $data, the data to Group
	 *    @param mixed $criteria, the Column to use for the separation, can also be a comma-separated string or
	 *                an array
	 *    @param boolean $holdIndizes - OPTIONAL, default: false, if the indizes of the "rows" should stay at their
	 *                rows. This is useful if the row-indizes represent e.g.
	 *                    - the Primary keys of the according Database-Entries
	 *                    - Filenames
	 *                    - URL's, etc...
	 *    @return array the grouped data
	 *
	 *    NOTE:     - To test what happens with the different options use debug::show() before and after grouping
	 *        - Runtime: O(n)    if one criteria is given O(m * n) if m criterias given
	 */
	public static function tableGroup($data, $criteria, $holdIndizes = false) {
		static::startMessung('tableGroup');
		$groupedArr = array();
		if (!is_array($criteria)) $criteria = explode(',', $criteria);
		$thisCrit = trim($criteria[0]);
		unset($criteria[0]);
		foreach ($data as $idx => $row) {
			if ($holdIndizes) $groupedArr[$row[$thisCrit]][$idx] = $row;
			else $groupedArr[$row[$thisCrit]][] = $row;
		}
		if (count($criteria) > 0) {
			foreach ($groupedArr as $critValue => $matches) {
				$groupedArr[$critValue] = arrayfunc::tableGroup($matches, join(',', $criteria));
			}
		}
		static::stopMessung('tableGroup');
		return $groupedArr;
	}
	/**
	 *    A Bit like tableGroup, but assumes that the values in the grouping-Column(s) are unique
	 *    => so not an Array of rows will be contained in each section of the grouped array but only
	 *    that single row where the grouping-Column has this value!
	 *
	 *    @param array $data the data to Group
	 *    @param mixed $criteria the Column to use for the separation, can also be a comma-separated string or an array
	 *    @param mixed $field - OPTIONAL -  if you are not interested in the whole Row in each section but only one
	 *                "cell-Value" u can specify from which column this value should be taken.
	 *
	 *    @return array the grouped data
	 *
	 *    NOTE:     - To test what happens with the different options use debug::show() before and after grouping
	 *        - Runtime: O(n)    if one criteria is given O(m * n) if m criterias given
	 *        - YOU as user of this function are responsible that the values in the grouping "Column" are unique
	 *            !! IF NOT YOU WILL LOOSE DATA WITHOUT WARNING !!
	 */
	public static function uniqueTableGroup($data, $criteria, $field = false) {
		static::startMessung('uniqueTableGroup');
		$groupedArr = array();
		if (!is_array($criteria)) $criteria = explode(',', $criteria);
		$thisCrit = trim($criteria[0]);
		foreach ($data as $row) {
			if (count($criteria) == 1) {
				if ($field == false) $groupedArr[$row[$thisCrit]] = $row;
				else $groupedArr[$row[$thisCrit]] = $row[$field];
			} else {
				$groupedArr[$row[$thisCrit]][] = $row;
			}
		}
		unset($criteria[0]);
		if (count($criteria) > 0) {
			foreach ($groupedArr as $critValue => $matches) {
				$groupedArr[$critValue] = arrayfunc::uniqueTableGroup($matches, join(",", $criteria), $field);
			}
		}
		static::stopMessung('uniqueTableGroup');
		return $groupedArr;
	}
	/**
	 *    Used for an SQL-Like Descriptive search for "records"
	 *    Like at the other tableXXX functions the imagination is to have an Array
	 *    of Hashtables representing the rows of a table. The hashkeys will specify the column.
	 *
	 *    Runs through the given dataArray and returns all rows in a new array which mach
	 *    with the given filter. A Filter must at least consist out of an "column"-Name (the hashkey
	 *    of the "Rows" to use) an operator and a value. More than one filter can be given.
	 *
	 *    @param array $data, tha data to filter.
	 *    @param mixed $filter filter the Filter tu use.
	 *
	 *    Filterformats:
	 *    - 1. as String : 1a [Column] [operator] [value]: "name = Hans", the spaces must be set!
	 *        or     1b [Column] [operator] [value] AND [Column] [operator] [value]: "name = Hans AND firstname = Andi"
	 *                - Only "AND" is possible
	 *    - 2. As Array : 2a: array(Column, operator, value) : array('name', '=', 'Hans');
	 *            2b: an array of Strings formatted as shown in 1a: array("name = Hans", "firstname = Andi");
	 *            2c: an array of arrays formatted as shown in 2a
	 *    Possible operators: "=", "LIKE", "<", ">"
	 *
	 *    @return array the filtered rows
	 */
	public static function tableFilter($data, $filter) {
		$operatorOrder = array('=', 'LIKE', '<', '>');
		$filter = arrayfunc::parseFilter($filter, $operatorOrder);
		$filters = arrayfunc::tableGroup($filter, 1);
		foreach ($operatorOrder as $operator) {
			if (isset($filters[$operator])) {
				foreach ($filters[$operator] as $actualFilter) {
					$data = arrayfunc::primitiveFilter($data, $actualFilter[0], $actualFilter[1], $actualFilter[2]);
				}
			}
		}
		return $data;
	}
	/**
	 *    Summates all values of a particular column
	 *
	 *    @param array $data: an Array of "tableRows", tablerows as a Hashtable colName => value
	 *    @param string $field: the column to summate
	 *
	 *    @return number, the sum of the spec. column,
	 *
	 */
	function tableSum($data, $field) {
		$sum = 0;
		foreach ($data as $row) {
			$sum+= $row[$field];
		}
		return $sum;
	}
	/**
	 *    Checks if an array is a Hashtable (i.e. it is NOT a numeric array)
	 *
	 *    @param array $var: the array to check
	 *
	 *    @return boolean if it is associative
	 */
	function is_assoc_array($var) {
		if (!is_array($var)) {
			$result = false;
			return $result;
		}
		$n = 0;
		foreach ($var as $key => $value) {
			if ($key != $n || !is_int($key)) {
				$result = true;
				return $result;
			}
			$n++;
		}
		$result = false;
		return $result;
	}
	/**
	 *    Checks if an array is numeric, i.e. if all keys are integer and
	 *    and countin up form 0 to the (length - 1) of the array.
	 *
	 *    @param array $var: the array to check
	 *
	 *    @return boolean if it is numeric
	 */
	public static function is_numeric_array($var) {
		if (!is_array($var)) {
			$result = false;
			return $result;
		}
		$n = 0;
		foreach ($var as $key => $value) {
			if (!($key === $n)) {
				$result = false;
				return $result;
			}
			$n++;
		}
		$result = true;
		return $result;
	}
	/**
	 *    Checks if an array is one-dimensional, i.e. if no one of the values is an array or abject again
	 *
	 *    @param array $arr: the array to check
	 *
	 *    @return boolean if it is one-dimensional
	 */
	public static function isOneDimensional($arr) {
		if (!is_array($arr) && !is_object($arr)) {
			$result = false;
			return $result;
		}
		foreach ($arr as $val) {
			if (is_array($val) || is_object($val)) {
				$result = false;
				return $result;
			}
		}
		$result = true;
		return $result;
	}
	/**
	 *    Parses a filter given to the tableFilter function into an 2-dimensional array that represents
	 *    the filter-format 2c described in tableFilter
	 *
	 *    @param mixed $filter the filter given to the tableFilter function (cam be any Variant, from 1a to 2c)
	 *    @param array $operators the known operators
	 *
	 *    @return mixed the 2c-like formatted filter array
	 *
	 *    @see arrayfunc::tableFilter
	 */
	private static function parseFilter($filter, $operators) {
		if (is_string($filter)) {
			$filter = explode(' AND ', $filter);
		}
		if (count($filter) == 3 && in_array($filter[1], $operators)) {
			$newFilter = array($filter);
			$filter = $newFilter;
		} else {
			foreach ($filter as $idx => $fil) {
				if (is_string($fil)) $filter[$idx] = explode(' ', $fil);
			}
		}
		return $filter;
	}
	/**
	 *    Filters an array of Hashtbales
	 *
	 *    @param array $data the data to filter
	 *    @param string $field, specifies the hash-key to compare
	 *    @param string $operator, specifies the comparison operator to use: one of : "=", "<", ">", "LIKE"
	 *    @param string $value, the value the the compared value should have (or should being greater..)
	 *
	 *    @return array an array of these hashtables which matched
	 *
	 *    @see arrayfunc::tableFilter, this function is internally user by tableFilter
	 */
	private static function primitiveFilter($data, $field, $operator, $value) {
		// show("PrimitiveFiltering: $field, $operator, $value<br>\n");
		$result = array();
		foreach ($data as $row) {
			if (arrayfunc::compare($row[$field], $operator, $value)) {
				// print("Vergleich {$row[$field]}, $operator, $value, OK<br>\n");
				$result[] = $row;
			} else {
				// print("Vergleich {$row[$field]}, $operator, $value, FEHLGESHLAGEN<br>\n");

			}
		}
		return $result;
	}
	/**
	 *    Compares 2 Values
	 *
	 *    @param string $LHS the Left-Hand-Side Value
	 *    @param string $operator, specifies the comparison operator to use: one of : "=", "<", ">", "LIKE"
	 *    @param string $RHS the Right-Hand-Side Value
	 *
	 *    @return boolean if the LHS and RHS match (according to the given operator)
	 */
	private static function compare($LHS, $operator, $RHS) {
		if ($operator == '=') return ($LHS == $RHS);
		if ($operator == '<') return ($LHS < $RHS);
		if ($operator == '>') return ($LHS > $RHS);
		if ($operator == 'LIKE') return arrayfunc::likeCompare($LHS, $RHS);
		$result = false;
		return $result;
	}
	/**
	 *    Performs a Database LIKE-Compare, Case-Insensitive
	 *    '%' as wild-card is understood, '_' as char for exactly one Character NOT
	 *
	 *    @param string $value the value to check
	 *    @param string $pattern the "target"-Value that my contain the wildcards
	 *
	 *    @return boolean, if the value matches the given pattern
	 */
	private static function likeCompare($value, $pattern) {
		$pattern = strtolower($pattern); // Damit das ganze Case-INsensitive  l�uft
		$value = strtolower($value);
		$pttrn = explode('%', $pattern);
		$lastIdx = count($pttrn) - 1;
		if ($lastIdx == 0) return ($pattern == $value);
		$failed = false;
		foreach ($pttrn as $idx => $str) {
			if ($str != '' && $failed === false) {
				$pos = strpos($value, $str);
				if ($pos === false) $failed = true; // str gar nicht vorhanden => RAUS
				if ($idx == 0 && $pos > 0) $failed = true; // anfang, pos muss = 0 sein sonst => RAUS
				if ($idx == $lastIdx) {
					$pos = strrpos($value, $str);
					if ($pos != strlen($value) - strlen($str)) $failed = true; // letzes, pos muss am ende sein, sonst => RAUS

				}
				if ($failed === false) {
					$value = substr($value, $pos + strlen($str)); // Alles vor und mit dem gefundenen Wert abhacken.

				}
			}
		}
		return ($failed === false);
	}
	/**
	 *    Uses an array of arrays, retrieves maximum length of the arrays contained in the give array
	 *
	 *    @param array $arr an array of arrays;
	 *
	 *    @return int the maximum length;
	 */
	function getMax2ndDimLength($arr) {
		$max = 0;
		foreach ($arr as $value) {
			if (is_array($value)) $max = max($max, count($value));
		}
		return $max;
	}
	/**
	 *    Recursively merges two arrays
	 *    If two keys are identical the second one will be chosen
	 *    If two identical keys are integer both will be taken
	 *
	 *    @param array $arr1, the first array
	 *    @param array $arr2, the second array
	 *
	 *    @return array the merged array
	 */
	public static function recursiveMerge($arr1, $arr2) {
		if (is_array($arr1) && is_array($arr2)) {
			$result = $arr1;
			foreach ($arr2 as $key => $value) {
				if (isset($result[$key])) {
					if (is_integer($key) && !(is_array($result[$key]) && (is_array($arr2[$key])))) $result[] = $value;
					else $result[$key] = arrayfunc::recursiveMerge($result[$key], $arr2[$key]);
				} else {
					$result[$key] = $value;
				}
			}
		} else {
			$result = $arr2;
		}
		return $result;
	}
	/**
	 *    Generates a list of all values of a given "column" in a array of Hashtables
	 *
	 *    @param array $data the array of hastables to use
	 *    @param string $fieldName the hashkey of the value to be taken into the list
	 *    @param boolean $holdIndizes - OPTIONAL - if the indizes in the list should be the same as in the dataset
	 *            default: false => the list will be a numeric array from 0 to length-1
	 *                 true => use this option if the indizes of the array represtent e.g. database Primary keys
	 *
	 *    @return array the newly generated list
	 */
	function getListOfField($data, $fieldName, $holdIndizes = false) {
		$liste = array();
		foreach ($data as $idx => $row) {
			if ($holdIndizes) $liste[$idx] = $row[$fieldName];
			else $liste[] = $row[$fieldName];
		}
		return $liste;
	}
}
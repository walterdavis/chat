<?php
//mb_internal_encoding("utf-8");
error_reporting(E_ALL);
/*if(!function_exists('Markdown')) require_once('markdown.php');
if(!function_exists('SmartyPants')) require_once('smartypants.php');
*//*require_once('inflections.php');
require_once('inflector.php');
*/
/**
 * Subclass of MyActiveRecord to add convenience methods and to correct various things for my taste
 *
 * @package MyActiveRecord
 * @author Walter Lee Davis
 */

class ActiveRecord extends MyActiveRecord{
	/**
	 * Overloads h() from main class to provide UTF-8 compatibility
	 * @param 	string 	key	name of the object to convert to htmlentities
	*/

	function h($key)
	{
		return stripslashes(htmlentities($this->$key,ENT_COMPAT,'UTF-8'));
	}

	function d($key){
		$u = $this->get_timestamp($key);
		return date('j M, Y',$u);
	}

	/**
	 * Validates the value of an attribute against a regular
	 * expression, adding an error to the object if the value
	 * does not match.
	 *
	 * @param	string	strKey	name of field/attribute/key
	 * @param	string	strRegExp	Regular Expression
	 * @param	string	strMessage	Error message to record if value does not match
	 * @return	boolean	True if the field matches. False if it does not match.
	 */
	function validate_existence($strKey, $strMessage=null)
	{
		if( !empty($this->$strKey) )
		{
			return true;
		}
		else
		{
			$this->add_error($strKey, $strMessage ? $strMessage : 'Missing '.$strKey);
			return false;
		}
	}

	/**
	 * Regex test for valid e-mail
	 *
	 * @param string $strKey Database column to use, default = email
	 * @return boolean
	 * @author Walter Lee Davis
	 */

	function valid_email($strKey='email') {
		if(eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,6}$", $this->$strKey)) {
		return TRUE;
		}else{
		return FALSE;
		}
	}


}

/**
 * Convenience wrapper for htmlentities, customized for UTF-8
 *
 * @param string $string Input text
 * @return string, with non-browser-safe characters replaced with entities
 * @author Walter Lee Davis
 */

function h($string)
{
	return stripslashes(htmlentities($string,ENT_COMPAT,'UTF-8'));
}

/**
 * Alternate between two classNames in a loop
 *
 * @param string $strClass1 The first class
 * @param string $strClass2 The second class
 * @return string className
 * @author Walter Lee Davis
 */

function alternate($strClass1="even",$strClass2="odd"){
	static $out;
	$out = ($out == $strClass1) ? $strClass2 : $strClass1;
	return $out;
}

/**
 * Converts a user-input filename into a URL-safe name.
 *
 * @param string $strFileName Input filename
 * @return string With all pathname unfriendly stuff removed
 * @author Walter Lee Davis
 */

function safe_name($strFileName){
	$unsafe = "[^a-zA-Z0-9-_\.]";
	$strFileName = str_replace(' ', '_',$strFileName);
	$file_out = preg_replace($unsafe,'_',$strFileName);
	return preg_replace('/_+/',"_",$file_out);
}


/**
 * Populates a template with object variables
 *
 * @param string $strPartial Name of the partial to load
 * @param object $objObject An Object of class MyActiveRecord
 * @return string
 * @author Walter Lee Davis
 */

function render_partial($strPartial,&$objObject){
	if(!is_string($strPartial) && !is_object($objObject)) die('Expected a string and an object, in that order');
	if(file_exists(APP_ROOT . '/views/_' . $strPartial . '.php')){
		ob_start();
		extract(get_object_vars($objObject));
		global $self;
		include(APP_ROOT . '/views/_' . $strPartial . '.php');
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}else{
		die('Partial ' . $strPartial . ' was missing');
	}
}

/**
 * A really nice tool to clean strings or arrays.
 *
 * @param mixed $mxdInput A string or an array
 * @return mixed same as input, but with trim and strip_tags applied to string or all elements of array, depending on imput format
 * @author Walter Lee Davis
 */

function clean($mxdInput){
	//recursive function for multidimensional arrays
	if(is_string($mxdInput)) return trim(strip_tags($mxdInput));
	$out = array();
	foreach($mxdInput as $k=>$v){
		$out[$k] = clean($v);
	}
	return $out;
}


/**
 * Useful view method, creates a formatted DIV containing a message or messages
 *
 * @param mixed $arrMessages A string or array of strings to become the message box
 * @param string $strClass Optional className to apply to the DIV
 * @return string
 * @author Walter Lee Davis
 */

function flash($arrMessages,$strClass=''){
	$out = '<div class="flash';
	$out .= (!empty($strClass))?' ' . $strClass:'';
	$out .= '">
	<ul>
';
	foreach((array)$arrMessages as $m) $out .= '		<li>' . $m . '</li>
';
	$out .= '	</ul>
</div>';
	return $out;
}

function isAjax() {
	return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
}


/**
 * Recursive function to replace MediaWiki notation with named variables
 *
 * @param string $strText Input text
 * @return string
 * @author Walter Lee Davis
 */

function replace_vars($strText){
	$out = preg_replace_callback('/\[\[(.+?)\]\]/im','replace_vars_callback',$strText);
	if(strpos($out,'[[') !== false) {
		return replace_vars($out);
	}
	return $out;
}

/**
 * Callback function for replace_vars()
 *
 * @param array $arrMatches Matches array from original regex
 * @return string with variables replaced with live variables
 * @author Walter Lee Davis
 */

function replace_vars_callback($arrMatches){
	$match = $arrMatches[1];
	if(isset($GLOBALS[$match])) return $GLOBALS[$match];
	return '[⁠[' . $match . ']⁠]'; //the spaces between the brackets here are Unicode Word Joiner characters (U+2060) not actual spaces. This keeps away the infinite loop.
	//return $arrMatches[0];
}

class messages extends ActiveRecord{
	function save(){
		$this->added_at = ActiveRecord::DbDateTime();
		return parent::save();
	}
	function d($strKey){
		$u = $this->get_timestamp($strKey);
		return date('r',$u);
	}
}

?>

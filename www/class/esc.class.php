<?php

/*
 * A class to include all escaping in one place. 
 * 
 * This class is where escaping standards can be upgraded and reviewed.
 * 
 * The goal is to avoid 'laziness to understand' proper escaping patterns, 
 * when developers 'are in a hurry' to fix something very small, and to make 
 * the class-name & function-name speak for themselves (as opposed to learning 
 * to use htmlEntities() for html attributes and htmlspecialchars for html 
 * content, with the proper settings set). Normally, tools such as the 
 * used Smarty library should encode values, but in many cases, values are 
 * being 'echoed' directly, without using Smarty, or several encodings in 
 * series are not done properly (cross-site/domain encoding). This class 
 * hopes to close the need for escaping presented in clear, and simple, 
 * concise and short coding, to avoid 'rushed' ommisions.
 * 
 * Usage for developers:
 * In general, values need to be escaped for the context in which they 
 * are injected into. Contexts are parts of documents, that are parsed 
 * differently. Eg. Html Attribute values are parsed different than Html 
 * (text-) Content (Html attributes have different characters 
 * to 'break out of' the attribute context (eg. " or '), as opposed to 
 * Html content (eg. <). The same uniqueness is applicable for urls, JS, 
 * CSS etc...) 
 * 
 * Hence, each injected value needs to be "ESCAPE FOR a particular CONTEXT". 
 * Note the capital words. The functions within the class CentreonEscaping 
 * will correspond as much as possible the above quote, to make it as clear 
 * and easy to understand as possible, when where and what to escape. For 
 * example: To 'Escape for a Html Attribute', you use:
 *  CentreonEscaping::forHtmlAttr(...) 
 * or even better using the below shortcut class:
 *  Esc::forHtmlAttr()
 * 
 * Good usage is to use these functions immediately inline with the surrounding 
 * context. For example, in case of direct echoing $sHostName as a Host Name:
 * <a href="<?php echo Esc::forHtmlAttr("main.php?p=20202&o=hd&host_name=".Esc::forUrlValue($sHostName)); ?>"><?php echo Esc::forHtmlContent($sHostName); ?></a>
 * 
 * A lot more information is available on OWASP site, and similar informations 
 * are used in this class file.
 * 
 */
class Esc
{
	
	public function __construct()
	{
		
	}
	
	//TODO: Need to have a look at the sanitizeShellString() function in /www/include/monitoring/external_cmd/functionsPopup.php and potentially add it here
	


	/**
     * Escape for Url Values
     * 
     * For example, in case for:
     * http://www.forexample.com/folder/path/file.php?parameter=[INJECT UNTRUSTED STRING HERE]&anotherparameter=etc
     * 
     * Usage:
     * $sMyUrl = "http://www.forexample.com/folder/path/file.php?parameter=".Esc::forUrlValue($sMyValue)."&anotherparameter=etc";
     *
     * @param $sValue string 
     * @return string The escaped string, ready for injection in url parameter 
     *   values (ie. AFTER the url ?, and BETWEEN the parameter = sign and 
     *   BEFORE the next & parameter!)
	 */
	static public function forUrlValue($sValue) {
		//URL-encode according to RFC 3986
		return rawurlencode($sValue);
		//There are two functions: rawurlencode() and urlencode(). urlencode() is used for posted data from a WWW form. Using the standards compliant function rawurlencode().
		//return urlencode($sValue);
	}

	/**
     * Escape for Html Attributes
     * 
     * For example, in case for:
     * <img href="[INJECT UNTRUSTED STRING HERE]" alt="[INJECT UNTRUSTED STRING HERE]" />etc
     *
     * Usage:
     * $sMyHtmlImage = '<img href="'.Esc::forHtmlAttr($sMyUrl).'" alt="'.Esc::forHtmlAttr($sMyAltText).'" />etc';
     *
     * @param $sValue string 
     * @return string The escaped string, ready for injection in HTML element 
     *    attributes (ie. BETWEEN element attribute quotes (" or ')!)
	 */
	static public function forHtmlAttr($sValue) {
		$sCharSet = ini_get("default_charset") ? ini_get("default_charset") : "UTF-8";
		return htmlEntities($sValue, ENT_QUOTES, $sCharSet, true);
	}

	/**
     * Escape for Html Attributes
     * 
     * For example, in case for:
     * <div>[INJECT UNTRUSTED STRING HERE] <span>[INJECT UNTRUSTED STRING HERE]</span></div>
     *
     * Usage:
     * $sMyHtmlContent = '<div>'.Esc::forHtmlContent($sMyContent).' <span>'.Esc::forHtmlContent($sMySubContent).'</span></div>';
     *
     * @param $sValue string 
     * @return string The escaped string, ready for injection in HTML content 
     *    (ie. BETWEEN elements!)
	 */
	static public function forHtmlContent($sValue) {
		$sCharSet = ini_get("default_charset") ? ini_get("default_charset") : "UTF-8";
		return htmlspecialchars($sValue, ENT_QUOTES, $sCharSet, true);
	}

	/**
     * Escape for Javascript values
     * 
     * For example, in case for:
     * <script type="text/javascript"> var hostname='[INJECT UNTRUSTED STRING HERE]'; </script>
     *
     * Usage:
     * <script type="text/javascript"> var hostname="<?php echo Esc::forJsValue($sMyHostname); ?>"; </script>
     *
     * @param $sValue string 
     * @param $bWithQuotes bool Return the value with encapsulated 
     *    quotes (default: false, which is consistent with the other functions)
     * @return string The escaped string, ready for injection in Javascript 
     *    values (ie. BETWEEN quotes when $bWithQuotes is set to false!)
	 */
	static public function forJsValue($sValue, $bWithQuotes = false) {
		//We reuse the power of json_encode to encode strings for Javascript. 
		//The output is normally already encapsulated with quotes, which we 
		//may need to remove depending on usage.
		//Note: json_encode expects the string to be UTF8
		$sValue = json_encode((string)$sValue, JSON_HEX_QUOT | JSON_HEX_APOS);
		return $bWithQuotes ? $sValue : mb_substr($sValue, 1, -1);
	}
	
	

	/**
	 * Escape for Database Values. Alias for: CentreonDB::escape() (preferred, 
	 *   as it is used everywhere already). The function has been created here for completeness. 
	 *
	 * For example, in case for:
	 * SELECT * FROM hosts WHERE host_name='[INJECT UNTRUSTED STRING HERE]' OR host_alias LIKE '%[INJECT UNTRUSTED STRING HERE]%'
	 *
	 * Usage:
	 * $sMyHostsQuery = "SELECT * FROM hosts WHERE host_name='".Esc::forQueryValue($sMyHostName)."' OR host_alias LIKE '%".Esc::forQueryValue($sMyHostName)."%'";
	 *
	 * @param $sValue string
	 * @return string The escaped string, ready for injection in query
	 *   values (ie. BETWEEN quotes!)
	 */
	static public function forQueryValue($sValue) {
		return CentreonDB::escape($sValue);
	}
}

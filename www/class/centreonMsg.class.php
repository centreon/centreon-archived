<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
 
/*
 *  Class that displays any kind of information between the html header containing logo
 *  and the horizontal menu 
 */
class CentreonMsg {
 	public $div; 	
 	
 	/* Constructor */
 	function CentreonMsg($div_str = NULL) {
 		if (!isset($div_str) && !$div_str)
 			$this->div = "centreonMsg"; 		
 		else
 			$this->div = $div_str;
 		$this->color = "#FFFFFF";	
 	}
 	
 	/*
 	 *  Sets style of text inside Div
 	 */
 	public function setTextStyle($style) {
 		echo "<script type=\"text/javascript\">_setTextStyle(\"$this->div\", \"$style\")</script>";
 	}
 	
 	/*
 	 *  Sets text color
 	 */
 	public function setTextColor($color) {
 		echo "<script type=\"text/javascript\">_setTextColor(\"$this->div\", \"$color\")</script>";
 	}
 	
 	/*
 	 *  Sets text align
 	 */
 	public function setAlign($align) {
 		echo "<script type=\"text/javascript\">_setAlign(\"$this->div\", \"$align\")</script>";
 	}
 	
 	/*
 	 *  Sets vertical align
 	 */
 	public function setValign($align) {
 		echo "<script type=\"text/javascript\">_setValign(\"$this->div\", \"$align\")</script>";
 	}
 	
 	/* Sets background color of Div */
 	public function setBackgroundColor($color) {
 		echo "<script type=\"text/javascript\">_setBackgroundColor(\"$this->div\", \"$color\")</script>";
 	}
 	
 	/* Sets text in Div */
 	public function setText($str) {
 		echo "<script type=\"text/javascript\">_setText(\"$this->div\", \"$str\")</script>";
 	}
 	
 	/* Sets image in Div */
 	public function setImage($img_url) {
 		echo "<script type=\"text/javascript\">_setImage(\"$this->div\", \"$img_url\")</script>";
 	}
 	
 	/* If you want to display your message for a limited time period, just call this function */
 	public function setTimeOut($sec) { 		 		
 		$sec *= 1000;
 		echo "<script type=\"text/javascript\">setTimeout(function(){new Effect.toggle(\"$this->div\")}, $sec)</script>";
 	}
 	
 	/* Clear message box */
 	public function clear() {
 		echo "<script type=\"text/javascript\">_clear(\"$this->div\")</script>";
 	}
 	
 	public function nextLine() {
 		echo "<script type=\"text/javascript\">_nextLine(\"$this->div\")</script>";
 	}
 	
}
?>
<script type="text/javascript">
var __image_lock = 0;

function _setBackgroundColor(div_str, color) {	
	document.getElementById(div_str).style.backgroundColor = color;
}

function _setText(div_str, str) {	
	var my_text = document.createTextNode(str);		
	var my_div = document.getElementById(div_str);
	
	my_div.appendChild(my_text);	
}

function _setImage(div_str, url) {	
	var _image = document.createElement("img");
	_image.src = url;
	_image.id = "centreonMsg_img";
	var my_div = document.getElementById(div_str);
	my_div.appendChild(_image);
}

function _clear(div_str) {
	document.getElementById(div_str).innerHTML = "";
}

function _setAlign(div_str, align) {
	var my_div = document.getElementById(div_str);
	
	my_div.style.textAlign = align;
}

function _setValign(div_str, align) {
	var my_div = document.getElementById(div_str);
	
	my_div.style.verticalAlign = align;
}

function _setTextStyle(div_str, style) {
	var my_div = document.getElementById(div_str);
	
	my_div.style.fontWeight = style;
}

function _setTextColor(div_str, color) {
	var my_div = document.getElementById(div_str);
	
	my_div.style.color = color;
}

function _nextLine(div_str) {
	var my_br = document.createElement("br");
	var my_div = document.getElementById(div_str);	
	my_div.appendChild(my_br);
}
</script>
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
  *  Class that is used for writing & reading XML in utf_8 only!
  */
  class CentreonXML {
  	var $buffer;
  	
  	/*
  	 *  Constructor
  	 */
  	function CentreonXML() {
  		$this->buffer = new XMLWriter();
  		$this->buffer->openMemory();
  		$this->buffer->startDocument('1.0', 'UTF-8');
  	}
  	
  	/*
  	 *  Starts an element that contains other elements
  	 */
  	public function startElement($element_tag) {
  		$this->buffer->startElement($element_tag);
  	}
  	
  	/*
  	 *  Ends an element (closes tag)
  	 */
  	public function endElement() {
  		$this->buffer->endElement();
  	}
  	
  	/*
  	 *  Simply puts text
  	 */
  	public function text($txt) {
  		$txt = preg_replace('/[\x00-\x19\x7F]/', "", $txt);
  		$this->buffer->writeCData(utf8_encode(html_entity_decode($txt)));
  	}
  	
  	/*
  	 *  Creates a tag and writes data
  	 */
  	public function writeElement($element_tag, $element_value) {
  		$this->startElement($element_tag);
  		$element_value = preg_replace('/[\x00-\x19\x7F]/', "", $element_value);
  		$this->buffer->writeCData(utf8_encode(html_entity_decode($element_value)));
  		$this->endElement();
  	}
  	
  	/*
  	 *  Writes attribute
  	 */
  	public function writeAttribute($att_name, $att_value) {
  		$this->buffer->writeAttribute($att_name, $att_value);
  	}
  	
  	/*
  	 *  Output the whole XML buffer
  	 */
  	public function output() {
  		$this->buffer->endDocument();
  		print $this->buffer->outputMemory(true);
  	}
  }
 ?>
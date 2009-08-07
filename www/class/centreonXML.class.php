<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
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
  	public function text($txt, $cdata = true, $encode = true) {
  		$txt = preg_replace('/[\x00-\x19\x7F]/', "", $txt);
  		if ($encode == false) {
  			$this->buffer->writeCData($txt);
  		} else {
	  		if ($cdata)
	  			$this->buffer->writeCData(utf8_encode(html_entity_decode($txt)));
	  		else
	  			$this->buffer->text(utf8_encode(html_entity_decode($txt)));  			
  		}
  	}
  	
  	/*
  	 *  Creates a tag and writes data
  	 */
  	public function writeElement($element_tag, $element_value, $encode = 1) {
  		$this->startElement($element_tag);
  		$element_value = preg_replace('/[\x00-\x19\x7F]/', "", $element_value);
		if ($encode)
	  		$this->buffer->writeCData(utf8_encode(html_entity_decode($element_value)));
  		else
  			$this->buffer->writeCData(html_entity_decode($element_value));
  		
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
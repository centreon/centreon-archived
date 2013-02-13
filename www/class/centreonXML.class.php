<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 *  Class that is used for writing XML in utf_8 only!
 */
class CentreonXML {
    var $buffer;

    /*
     *  Constructor
     */
    function CentreonXML($indent = false) {
        $this->buffer = new XMLWriter();
        $this->buffer->openMemory();
        if ($indent) {
            $this->buffer->setIndent($indent);
        }
        $this->buffer->startDocument('1.0', 'UTF-8');
    }

    /**
     * Clean string
     * 
     * @param string $str
     * @return string
     */
    protected function cleanStr($str)
    {
        $str = preg_replace('/[\x00-\x09\x0B-\x0C\x0E-\x1F\x0D]/', "", $str);
        return $str;
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
    public function text($txt, $cdata = true, $encode = 0) {
        $txt = $this->cleanStr($txt);
        $txt = html_entity_decode($txt);
        if ($encode || !$this->is_utf8($txt)) {
            $this->buffer->writeCData(utf8_encode($txt));
        } else {
            if ($cdata) {
                $this->buffer->writeCData($txt);
            } else {
                $this->buffer->text($txt);
            }
        }
    }

    /**
     * Checks if string is encoded
     *
     * @param string $string
     * @return boolean
     */
    protected function is_utf8($string)
    {
        if (strlen($string) > 1024) {
            $res = $this->is_utf8(substr($string, 0, 1024));
            $res += $this->is_utf8(substr($string, 1025));
        } else {
    		$res = 0;
            $res += preg_match('%^(?:[\x09\x0A\x0D\x20-\x7E] |
                                     [\xC2-\xDF][\x80-\xBF] |
                                     \xE0[\xA0-\xBF][\x80-\xBF] |
                                     [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2})*$%xs', $string);
            $res += preg_match('%^(?:\xED[\x80-\x9F][\x80-\xBF] |
                                    \xF0[\x90-\xBF][\x80-\xBF]{2})*$%xs', $string);
            $res += preg_match('%^(?:[\xF1-\xF3][\x80-\xBF]{3})*$%xs', $string);
            $res += preg_match('%^(?:\xF4[\x80-\x8F][\x80-\xBF]{2})*$%xs', $string);
        }
        return $res;
    }

    /*
     *  Creates a tag and writes data
     */
    public function writeElement($element_tag, $element_value, $encode = 0) {
        $this->startElement($element_tag);
        $element_value = $this->cleanStr($element_value);
        $element_value = html_entity_decode($element_value);
        if ($encode || !$this->is_utf8($element_value)) {
            $this->buffer->writeCData(utf8_encode($element_value));
        } else {
            $this->buffer->writeCData($element_value);
        }

        $this->endElement();
    }

    /*
     *  Writes attribute
     */
    public function writeAttribute($att_name, $att_value, $encode = false) {
        $att_value = $this->cleanStr($att_value);
        if ($encode) {
            $this->buffer->writeAttribute($att_name, utf8_encode(html_entity_decode($att_value)));
        } else {
            $this->buffer->writeAttribute($att_name, html_entity_decode($att_value));
        }
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

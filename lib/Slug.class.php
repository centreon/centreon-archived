<?php
/**
 * Copyright (c) 2010 Fabian Graßl
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */
/**
 * Create a slug for URLs
 *
 * Note that this Class is made for UTF-8-Strings
 *
 * @author      Fabian Graßl <fg@jusmeum.de>
 */
class Slug implements ArrayAccess
{
  protected $original = null;
  protected $slug = null;
  protected $options = array(
    'to_lower'        => true,
    'max_length'      => null,
    'prefix'          => null,
    'postfix'         => null,
    'seperator_char'  => '-'
  );
  protected $char_map = array(
    'Š'=>'S',
    'š'=>'s',
    'Ð'=>'Dj',
    'Ž'=>'Z',
    'ž'=>'z',
    'À'=>'A',
    'Á'=>'A',
    'Â'=>'A',
    'Ã'=>'A',
    'Ä'=>'Ae',
    'Å'=>'A',
    'Æ'=>'A',
    'Ç'=>'C',
    'È'=>'E',
    'É'=>'E',
    'Ê'=>'E',
    'Ë'=>'E',
    'Ì'=>'I',
    'Í'=>'I',
    'Î'=>'I',
    'Ï'=>'I',
    'Ñ'=>'N',
    'Ò'=>'O',
    'Ó'=>'O',
    'Ô'=>'O',
    'Õ'=>'O',
    'Ö'=>'Oe',
    'Ø'=>'O',
    'Ü'=>'Ue',
    'Ù'=>'U',
    'Ú'=>'U',
    'Û'=>'U',
    'Ý'=>'Y',
    'Þ'=>'B',
    'ß'=>'ss',
    'à'=>'a',
    'á'=>'a',
    'â'=>'a',
    'ã'=>'a',
    'ä'=>'ae',
    'å'=>'a',
    'æ'=>'a',
    'ç'=>'c',
    'è'=>'e',
    'é'=>'e',
    'ê'=>'e',
    'ë'=>'e',
    'ì'=>'i',
    'í'=>'i',
    'î'=>'i',
    'ï'=>'i',
    'ð'=>'o',
    'ñ'=>'n',
    'ò'=>'o',
    'ó'=>'o',
    'ô'=>'o',
    'õ'=>'o',
    'ö'=>'oe',
    'ø'=>'o',
    'ü'=>'ue',
    'ù'=>'u',
    'ú'=>'u',
    'û'=>'u',
    'ý'=>'y',
    'ý'=>'y',
    'þ'=>'b',
    'ÿ'=>'y',
    'ƒ'=>'f',
    'Ŕ'=>'R',
    'ŕ'=>'r'
  );
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * to_lower:       Whether the slug should be lowercase (true by default)
   *  * max_length:     NULL for no maximum lenght or maximum lenght of the returned slug (NULL by default)
   *  * prefix:         prefix for the slug
   *  * postfix:        postfix for the slug
   *  * seperator_char: word seperator char for the slug (default -)
   *
   * @param string $original    An array of field default values
   * @param array  $options     An array of options
   * @param array  $char_map    a char-map-array that is used for the strtr() PHP-function in the slug generation process
   */
  public function __construct($original, $options = array(), $char_map = null)
  {
    $this->original = $original;
    $this->options = array_merge($this->options, $options);
    if (null !== $char_map)
    {
      $this->char_map = $char_map;
    }
  }
  /**
   * Generate the slug.
   */
  public function generateSlug()
  {
    $str = $this->original;
    if ($this['to_lower'])
    {
      $str = mb_strtolower($str, 'UTF-8');
    }
    // replace the chars in $this->char_map
    $str = strtr($str , $this->char_map);
    // ensure that there are only valid characters in the url (A-Z a-z 0-9, seperator_char)
    $str = preg_replace('/[^A-Za-z0-9]/', $this['seperator_char'], $str);
    // trim the seperator char at the end and teh beginning
    $str = trim($str, $this['seperator_char']);
    // remove duplicate seperator chars
    $str = preg_replace('/['.preg_quote($this['seperator_char']).']+/', $this['seperator_char'], $str);
    if ($this['max_length'])
    {
      $str = $this->shortenSlug($str, $this['max_length']-mb_strlen($this['prefix'], 'UTF-8')-mb_strlen($this['postfix'], 'UTF-8'));
    }
    // Add prefix & postfix
    $this->slug = $this['prefix'].$str.$this['postfix'];
  }
  /**
   * Shorten the slug.
   */
  protected function shortenSlug($slug, $maxLen)
  {
    // $maxLen must be greater than 1
    if ($maxLen < 1)
    {
      return $slug;
    }
    // check whether there is work to do
    if (strlen($slug) < $maxLen)
    {
      return $slug;
    }
    // cut to $maxLen
    $cutted_slug = trim(substr($slug, 0, $maxLen), $this['seperator_char']);
    // cut to the last position of '-' in cutted string
    $beautified_slug = trim(preg_replace('/[^'.preg_quote($this['seperator_char']).']*$/', '', $cutted_slug), $this['seperator_char']);
    // only return the beautified string when it is long enough
    if (strlen($beautified_slug) < ($maxLen/2))
    {
      return $cutted_slug;
    }
    else
    {
      return $beautified_slug;
    }
  }
  /**
   * Returns the slug-string
   *
   * @return string the slug
   * @see generateSlug()
   */
  public function getSlug()
  {
    if (null === $this->slug)
    {
      $this->generateSlug();
    }
    return $this->slug;
  }
  /**
   * Returns the slug-string
   *
   * @return string the slug
   * @see getSlug()
   */
  public function __toString()
  {
    return $this->getSlug();
  }
  /**
   * Sets a char-map-array that is used for the strtr() PHP-function in the slug generation process
   *
   * @param string $char_map The option name
   */
  public function setCharMap($char_map)
  {
    $this->char_map = $char_map;
  }
  /**
   * Sets the option associated with the offset (implements the ArrayAccess interface).
   *
   * @param string $offset The option name
   * @param string $value The option value
   */
  public function offsetSet($offset, $value)
  {
    if (is_null($offset))
    {
      $this->options[] = $value;
    }
    else
    {
      $this->options[$offset] = $value;
    }
  }
  /**
   * Returns true if the option exists (implements the ArrayAccess interface).
   *
   * @param  string $name The name of option
   * @return Boolean true if the option exists, false otherwise
   */
  public function offsetExists($offset)
  {
    return isset($this->options[$offset]);
  }
  /**
   * Unsets the option associated with the offset (implements the ArrayAccess interface).
   *
   * @param string $offset The option name
   */
  public function offsetUnset($offset)
  {
    $this->options[$offset] = null;
  }
  /**
   * Returns an option (implements the ArrayAccess interface).
   *
   * @param  string $name The offset of the option to get
   * @return mixed The option if exists, null otherwise
   */
  public function offsetGet($offset)
  {
    return isset($this->options[$offset]) ? $this->options[$offset] : null;
  }
}
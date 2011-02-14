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
  *  Language management class
  */
 class CentreonLang
 {
 	protected $_charset;
 	protected $_lang;
 	protected $_path;
 	protected $_charsetList;

 	/**
 	 *  Constructor
 	 *
 	 * @param string $centreon_path
 	 * @param Centreon $centreon
 	 * @return void
 	 */
 	public function CentreonLang($centreon_path, $centreon = null)
 	{
		if (isset($centreon)) {
			$this->_lang = $centreon->user->lang;
			$this->_charset = $centreon->user->charset;
		} else {
			$this->_lang = "en_US";
			$this->_charset = "UTF-8";
		}
		$this->_path = $centreon_path;
		$this->setCharsetList();
 	}

 	/**
 	 *  Sets list of charsets
 	 *
 	 *  @return void
 	 */
 	private function setCharsetList()
 	{
 		$this->_charsetList = array(
 									"ISO-8859-1",
									"ISO-8859-2",
									"ISO-8859-3",
									"ISO-8859-4",
									"ISO-8859-5",
									"ISO-8859-6",
									"ISO-8859-7",
									"ISO-8859-8",
									"ISO-8859-9",
									"UTF-80",
									"UTF-83",
									"UTF-84",
									"UTF-85",
									"UTF-86",
									"ISO-2022-JP",
									"ISO-2022-KR",
									"ISO-2022-CN",
									"WINDOWS-1251",
									"CP866",
									"KOI8",
									"KOI8-E",
									"KOI8-R",
									"KOI8-U",
									"KOI8-RU",
									"ISO-10646-UCS-2",
									"ISO-10646-UCS-4",
									"UTF-7",
									"UTF-8",
									"UTF-16",
									"UTF-16BE",
									"UTF-16LE",
									"UTF-32",
									"UTF-32BE",
									"UTF-32LE",
									"EUC-CN",
									"EUC-GB",
									"EUC-JP",
									"EUC-KR",
									"EUC-TW",
									"GB2312",
									"ISO-10646-UCS-2",
									"ISO-10646-UCS-4",
									"SHIFT_JIS");
		sort($this->_charsetList);
 	}

 	/**
 	 *  Binds lang to the current Centreon page
 	 *
 	 *  @return void
 	 */
 	public function bindLang($domain = "messages", $path = "www/locale/")
 	{
		putenv("LANG=$this->_lang");
		setlocale(LC_ALL, $this->_lang);
		bindtextdomain($domain, $this->_path.$path);
		bind_textdomain_codeset($domain, $this->_charset);
		textdomain('messages');
 	}

 	/**
 	 *  Lang setter
 	 *
 	 *  @param string $newLang
 	 *  @return void
 	 */
 	public function setLang($newLang)
 	{
 		$this->_lang = $newLang;
 	}

 	/**
 	 *  Returns lang that is being used
 	 *
 	 *  @return string
 	 */
 	public function getLang()
 	{
 		return $this->_lang;
 	}

 	/**
 	 *  Charset Setter
 	 *  @param string $newCharset
 	 *  @return void
 	 */
 	public function setCharset($newCharset)
 	{
 		$this->_charset = $newCharset;
 	}

 	/**
 	 *  Returns charset that is being used
 	 *
 	 *  @return string
 	 */
 	public function getCharset()
 	{
 		return $this->_charset;
 	}

 	/**
 	 *  Returns an array with a list of charsets
 	 *
 	 *  @return array
 	 */
 	public function getCharsetList()
 	{
 		return $this->_charsetList;
 	}
 }
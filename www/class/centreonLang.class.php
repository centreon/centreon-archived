<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

 /*
  *  Language management class
  */
class CentreonLang
{
    /**
     *
     * @var string
     */
    protected $charset;
    
    /**
     *
     * @var string
     */
    protected $lang;
    
    /**
     *
     * @var string
     */
    protected $path;
    
    /**
     *
     * @var array
     */
    protected $charsetList;

    /**
     *  Constructor
     *
     * @param string $centreon_path
     * @param Centreon $centreon
     * @return void
     */
    public function __construct($centreon_path, $centreon = null)
    {
        $this->charset = "UTF-8";
        if (!is_null($centreon) && isset($centreon->user->charset)) {
            $this->charset = $centreon->user->charset;
        }
        
        $this->lang = $this->getBrowserDefaultLanguage() . '.' . $this->charset;
        if (!is_null($centreon) && isset($centreon->user->lang)) {
            if ($centreon->user->lang !== 'browser') {
                $this->lang = $centreon->user->lang;
            }
        }
        
        $this->path = $centreon_path;
        $this->setCharsetList();
    }
    
    private function parseHttpAcceptHeader()
    {
        $langs = array();

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            // break up string into pieces (languages and q factors)
            preg_match_all(
                '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
                $_SERVER['HTTP_ACCEPT_LANGUAGE'],
                $lang_parse
            );

            if (count($lang_parse[1])) {
                // create a list like "en" => 0.8
                $langs = array_combine($lang_parse[1], $lang_parse[4]);

                // set default to 1 for any without q factor
                foreach ($langs as $lang => $val) {
                    if ($val === '') {
                        $langs[$lang] = 1;
                    }
                }

                // sort list based on value
                arsort($langs, SORT_NUMERIC);
            }
        }
        
        $languageLocales = array_keys($langs);
        
        $current = array_shift($languageLocales);
        $favoriteLanguage = $current;
        
        return $favoriteLanguage;
    }

    /**
     *
     * @return type
     */
    private function getBrowserDefaultLanguage()
    {
        $currentLocale = '';
        
        if (version_compare(PHP_VERSION, '5.2.0') >= 0) {
            $browserLocale = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $currentLocale .= Locale::acceptFromHttp($browserLocale);
        } else {
            $currentLocale .= $this->parseHttpAcceptHeader();
        }
        
        return $this->getFullLocale($currentLocale);
    }

    /**
     *
     * @param type $shortLocale
     * @return string
     */
    private function getFullLocale($shortLocale)
    {
        $fullLocale = '';

        $as = array(
            'fr' => 'fr_FR',
            'fr_FR' => 'fr_FR',
            'en' => 'en_US',
            'en_US' => 'en_US'
        );

        if (isset($as[$shortLocale])) {
            $fullLocale .= $as[$shortLocale];
        } else {
            $fullLocale = 'en_US';
        }

        return $fullLocale;
    }


    /**
     *  Sets list of charsets
     *
     *  @return void
     */
    private function setCharsetList()
    {
        $this->charsetList = array(
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
            "SHIFT_JIS"
        );
        sort($this->charsetList);
    }

    /**
     *  Binds lang to the current Centreon page
     *
     *  @return void
     */
    public function bindLang($domain = "messages", $path = "www/locale/")
    {
        putenv("LANG=$this->lang");
        setlocale(LC_ALL, $this->lang);
        bindtextdomain($domain, $this->path.$path);
        bind_textdomain_codeset($domain, $this->charset);
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
        $this->lang = $newLang;
    }

    /**
     *  Returns lang that is being used
     *
     *  @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     *  Charset Setter
     *
     *  @param string $newCharset
     *  @return void
     */
    public function setCharset($newCharset)
    {
        $this->charset = $newCharset;
    }

    /**
     *  Returns charset that is being used
     *
     *  @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     *  Returns an array with a list of charsets
     *
     *  @return array
     */
    public function getCharsetList()
    {
        return $this->charsetList;
    }
}

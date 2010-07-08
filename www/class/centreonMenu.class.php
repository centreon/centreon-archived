<?php

class CentreonMenu
{
    protected $_centreonLang;

    /**
     * Constructor
     *
     * @param CentreonLang $centreonLang
     * @return void
     */
    public function __construct($centreonLang)
    {
        $this->_centreonLang = $centreonLang;
    }

    /**
     * translates
     *
     * @param int $isModule
     * @param string $url
     * @parma string $menuName
     * @return string
     */
    public function translate($isModule, $url, $menuName)
    {
        $moduleName = "";
	    if ($isModule && $url) {
            if (preg_match("/\.\/modules\/([a-zA-Z-_]+)/", $url, $matches)) {
                if (isset($matches[1])) {
                    $moduleName = $matches[1];
                }
            }
		}
		$name = _($menuName);
		if ($moduleName) {
            $this->_centreonLang->bindLang('messages', 'www/modules/'.$moduleName.'/locale/');
            $name = _($menuName);
            $this->_centreonLang->bindLang();
		}
        return $name;
    }
}
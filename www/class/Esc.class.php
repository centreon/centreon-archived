<?php

include_once("centreonEscaping.class.php");

/*
 * Please see CentreonEscaping
 * 
 * A class for convenience.
 * This class makes short code style possible using Esc::forHtmlAttr().
 *
 * This is prefered as opposed to CentreonEscaping::forHtmlAttr(), which
 * can be long and unclear, unoverseeable in lines with more than 3 values to
 * escape, and hence prone to security mistakes. This class makes it clearer
 * for developers' understanding and analogous with verbal understanding
 * eg. "Escape (this value) for Html Attributes":  Esc::forHtmlAttr($sThisValue)
 */
class Esc extends CentreonEscaping { }
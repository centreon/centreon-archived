<?php
require_once(dirname(__FILE__)."/../dev_settings.php");
ini_set('max_execution_time',300);
ini_set('xdebug.max_nesting_level',300);

// Create Lexer
require_once './LexerGenerator.php';
$lex = new PHP_LexerGenerator('smarty_internal_templatelexer.plex');
$contents = file_get_contents('smarty_internal_templatelexer.php');
$contents = str_replace(array('SMARTYldel','SMARTYrdel'),array('".$this->ldel."','".$this->rdel."'),$contents);
file_put_contents('smarty_internal_templatelexer.php', $contents);
copy('smarty_internal_templatelexer.php','../../distribution/libs/sysplugins/smarty_internal_templatelexer.php');

// Create Parser
passthru("$smarty_dev_php_cli_bin ./ParserGenerator/cli.php smarty_internal_templateparser.y");

$contents = file_get_contents('smarty_internal_templateparser.php');
$contents = '<?php
/**
* Smarty Internal Plugin Templateparser
*
* This is the template parser.
* It is generated from the internal.templateparser.y file
* @package Smarty
* @subpackage Compiler
* @author Uwe Tews
*/
'.substr($contents,6);
file_put_contents('smarty_internal_templateparser.php', $contents."?>");
copy('smarty_internal_templateparser.php','../../distribution/libs/sysplugins/smarty_internal_templateparser.php');

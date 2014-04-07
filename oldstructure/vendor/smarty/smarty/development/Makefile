all: config-compiler template-compiler unit-test

config-compiler:
	cd lexer; php Create_Config_Parser.php

template-compiler:
	cd lexer; php Create_Template_Parser.php

unit-test:
	cd PHPunit; phpunit smartytests.php

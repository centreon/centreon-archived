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
 
 	// Creating a Cookie to test the cookie activation of the browser
	setcookie('COOKIE','COOKIE-TEST',time()+3600);
	
	aff_header("Centreon Setup Wizard", "Welcome to Centreon Setup", 1);
	print "<p>This installer creates the Centreon database tables and sets the configuration variables that you need to start. The entire process should take about ten minutes.</p>";
	aff_middle();
	print "<input class='button' type='submit' name='goto' value='Start' id='defaultFocus' /></td>";
	aff_footer();

?>
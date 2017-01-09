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
  * Script found on http://www.blazonry.com/javascript/password.php
  * simplified to generate a random password for autologin_key
  */
function generatePassword(what)
{
    if (parseInt(navigator.appVersion) <= 3) {
        alert("Sorry this only works in 4.0+ browsers");
        return true;
    }

    var length=8;
    var sPassword = "";

    /*
    * Stick on 8 chars for user password, use random lenght for autologin key
    * at least 8, at max 64, more changes to keep something small wich is not bad as it will be used in url
    */
    if (what == "aKey") {
        length = Math.random();
        length = parseInt(length * 156);
        length = (length % 3) + 8;
    }

    for (i=0; i < length; i++) {
        numI = getRandomNum();
        while (checkPunc(numI)) { 
            numI = getRandomNum();
        }
        sPassword = sPassword + String.fromCharCode(numI);
    }

    /*
     * If for aKey => just enter the value
     * If for password => change the input type to text to allow to write down the pass. (Done on both for visual consistence)
     * TODO (maybe): Rechange the type if typing into the box...
     */
    if (what == "aKey") {
		document.getElementById('aKey').value = sPassword;
    } else {
		document.getElementById('passwd1').value = sPassword;
		document.getElementById('passwd1').setAttribute('type','text');
		document.getElementById('passwd2').value = sPassword;
		document.getElementById('passwd2').setAttribute('type','text');
    }
    return true;
}

function getRandomNum()
{
	// between 0 - 1
	var rndNum = Math.random()

	// rndNum from 0 - 1000
	rndNum = parseInt(rndNum * 1000);

	// rndNum from 33 - 127
	rndNum = (rndNum % 94) + 33;

	return rndNum;
}

function checkPunc(num)
{
	if ((num >=33) && (num <=47)) { return true; }
	if ((num >=58) && (num <=64)) { return true; }
	if ((num >=91) && (num <=96)) { return true; }
	if ((num >=123) && (num <=126)) { return true; }

	return false;
}

function resetPwdType(elem)
{
	elem.setAttribute('type', 'password');
}

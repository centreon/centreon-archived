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
function generatePassword(what, securityPolicy = null)
{
    securityPolicy = JSON.parse(securityPolicy);
    if (parseInt(navigator.appVersion) <= 3) {
        alert("Sorry this only works in 4.0+ browsers");
        return true;
    }

    var length = securityPolicy.password_length;
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
        sPassword = generatePasswordWithSecurityPolicy(securityPolicy)
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

//Password generated with at least 1 number, 1 upper case character, 1 lower case character and 1 Special character
function generatePasswordWithSecurityPolicy(securityPolicy)
{
  const passwordLength = securityPolicy.password_length;
  const numberChars = "0123456789";
  const upperChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  const lowerChars = "abcdefghijklmnopqrstuvwxyz";
  const specialChars = "@$!%*?&";
  const allChars = numberChars + upperChars + lowerChars + specialChars;
  let randPasswordArray = Array(parseInt(passwordLength, 10));
  randPasswordArray[0] = numberChars;
  randPasswordArray[1] = upperChars;
  randPasswordArray[2] = lowerChars;
  randPasswordArray[3] = specialChars;
  randPasswordArray = randPasswordArray.fill(allChars, 4);
  if (window.crypto && window.crypto.getRandomValues){
      return shuffleArray(randPasswordArray.map(function(x) {
        return x[Math.floor(
          window.crypto.getRandomValues(new Uint32Array(1))[0] / (0xffffffff + 1) * x.length
        )]
      })).join('');
  } else if(window.msCrypto && window.msCrypto.getRandomValues) {
      return shuffleArray(randPasswordArray.map(function(x) {
        return x[Math.floor(
          window.msCrypto.getRandomValues(new Uint32Array(1))[0] / (0xffffffff + 1) * x.length
        )] })).join('');
  } else {
      return shuffleArray(randPasswordArray.map(function(x) {
        return x[Math.floor(
          Math.random() * x.length
        )] })).join('');
  }
}

function shuffleArray(array)
{
  for (var i = array.length - 1; i > 0; i--) {
    var j = Math.floor(Math.random() * (i + 1));
    var temp = array[i];
    array[i] = array[j];
    array[j] = temp;
}
  return array;
}

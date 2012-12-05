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
       while (checkPunc(numI)) { numI = getRandomNum(); }
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
    sPassword = str_md5(sPassword);
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
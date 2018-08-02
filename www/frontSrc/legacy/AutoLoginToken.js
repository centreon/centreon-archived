export default class AutoLoginToken {

  generatePassword(what) {
    if (parseInt(navigator.appVersion) <= 3) {
      alert("Sorry this only works in 4.0+ browsers");
      return true;
    }

    let length = 8;
    let sPassword = "";

    /*
    * Stick on 8 chars for user password, use random lenght for autologin key
    * at least 8, at max 64, more changes to keep something small wich is not bad as it will be used in url
    */
    if (what == "aKey") {
      length = (parseInt(Math.random() * 156) % 3) + 8;
    }

    for (let i = 0; i < length; i++) {
      let numI = this.getRandomNum();
      while (this.checkPunc(numI)) {
        numI = this.getRandomNum();
      }
      sPassword = sPassword + String.fromCharCode(numI);
    }

    /**
     * Put the new autologin value on the account form's input
     */
    if (what == "aKey") {
      document.getElementById('aKey') ? document.getElementById('aKey').value = sPassword : null;
    }

    return sPassword;
  }

  getRandomNum() {
    // between 0 - 1
    let rndNum = Math.random()

    // rndNum from 0 - 1000
    rndNum = parseInt(rndNum * 1000);

    // rndNum from 33 - 127
    rndNum = (rndNum % 94) + 33;

    return rndNum;
  }

  checkPunc(num) {
    if ((num >=33) && (num <=47)) { return true; }
    if ((num >=58) && (num <=64)) { return true; }
    if ((num >=91) && (num <=96)) { return true; }
    if ((num >=123) && (num <=126)) { return true; }

    return false;
  }
}
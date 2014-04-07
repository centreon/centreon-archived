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

/*
 *  Change Tab
 */

function initChangeTab(){
	for (var i = 2; document.getElementById('tab'+i); i++) {
		document.getElementById('tab'+i).style.display='none';
	}
}

function montre(id) {
	for (var i = 1; document.getElementById('c'+i); i++) {
		document.getElementById('c'+i).className='b';
	}
	document.getElementById('c'+id).className='a';
	var d = document.getElementById('tab'+id);
	for (var i = 1; document.getElementById('tab'+i); i++) {
		document.getElementById('tab'+i).style.display='none';
	}
	if (d) {
		d.style.display='block';
	}
}		

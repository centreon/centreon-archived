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
// JavaScript Document

<!-- Begin

function toggleCheckAll(theElement, id){	
	var a = document.getElementById(id);

	for (var i = 0; document.getElementById(id+'_'+i) ;i++){
		var b = document.getElementById(id+'_'+i);
		if (a.checked)
			b.checked = true;
		else
			b.checked = false;

		for(var j = 0; document.getElementById(id+'_'+i+'_'+j) ;j++){
			var c = document.getElementById(id+'_'+i+'_'+j);
			if(b.checked)
				c.checked = true;
			else
				c.checked = false;
			for(var k = 0; document.getElementById(id+'_'+i+'_'+j+'_'+k) ;k++){
				var d = document.getElementById(id+'_'+i+'_'+j+'_'+k);
				if (c.checked)
					d.checked = true;
				else
					d.checked = false;
			}
		}
	}
}					
		
function toggleDisplay(id)	{
	var d = document.getElementById(id);
	if (d){
		var img = document.getElementById('img_'+id);
		if (img){
			if(d.style.display == 'block')
				img.src = 'img/icones/16x16/navigate_plus.gif';
			else
				img.src = 'img/icones/16x16/navigate_minus.gif';
		}
		if (d.style.display == 'block') {
			d.style.display='none';
		} else {
			d.style.display='block';
		}
	}	
}

function checkUncheckAll(theElement) {
     var theForm = theElement.form, z = 0;
	 for (z=0; z<theForm.length;z++){
      	if (theForm[z].type == 'checkbox' && theForm[z].disabled == '0'){
		  	if (theForm[z].checked) {
				theForm[z].checked = false;
		   	} else {
		  		theForm[z].checked = true;
			}
		}
	}
}

function DisplayHidden(id) {
	var d = document.getElementById(id);
	if (d){
		if (d.style.display == 'block') {
			d.style.display='none';
		} else	{
			d.style.display='block';
		}
	}
}

function isdigit(c) {return(c >= '0' && c <= '9');}
		
function atoi(s){
	var t = 0;
 		
	for(var i = 0; i < s.length; i++){
   		var c = s.charAt(i);
   		if(!isdigit(c)) return t;
   		else t = t*10 + (c-'0');
	}
	return t;
}

//  End -->
/**
Oreon is developped with Apache Licence 2.0 :
http://www.apache.org/licenses/LICENSE-2.0.txt
Developped by : Cedrick Facon

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
// JavaScript Document

<!-- Begin

	function checkUncheckAll(theElement) {

     var theForm = theElement.form, z = 0;
	 for(z=0; z<theForm.length;z++){
      if(theForm[z].type == 'checkbox' && theForm[z].disabled == '0'){
		  if(theForm[z].checked)
		  {
		   theForm[z].checked = false;
		   }
		  else{
		  theForm[z].checked = true;
		  }
	  }
     }
   }


	function DisplayHidden(id) {
		var d = document.getElementById(id);

if(d)
{
	if (d.style.display == 'block') {
	d.style.display='none';
	}
	else
	{
	d.style.display='block';
	}
}
}


//  End -->

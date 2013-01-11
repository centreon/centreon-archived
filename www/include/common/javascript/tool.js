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

function checkItem(element, toCheck)
{
	if (element.type == 'checkbox') {
		if (toCheck) {
			element.checked = true;
		} else {
			element.checked = false;
		}
	} else if (element.type == 'radio') {
		var element = document.Form[element.name];
		var value = 0;
		if (toCheck) {
			value = 1;
		}
		for (var j = 0; j < element.length; j++) {
			if (element[j].value == value) {
				element[j].checked = true;
			}
		}
	}
}

function getChecked(element)
{
	if (element.type == 'checkbox') {
		return element.checked;
	}
	var element = document.Form[element.name];
	for (var j = 0; j < element.length; j++) {
		if (element[j].checked) {
			if (element[j].value > 0) {
				return true;
			}
		}
	}
	return false;
}

function toggleCheckAll(theElement, id)
{
	var a = document.getElementById(id);

	// enable/disable all subnodes of id
	for (var i = 0; document.getElementById(id+'_'+i) ;i++){
		var b = document.getElementById(id+'_'+i);
		checkItem(b, getChecked(a));

		for(var j = 0; document.getElementById(id+'_'+i+'_'+j) ;j++) {
			var c = document.getElementById(id+'_'+i+'_'+j);
			checkItem(c, getChecked(b));
			for(var k = 0; document.getElementById(id+'_'+i+'_'+j+'_'+k) ;k++) {
				var d = document.getElementById(id+'_'+i+'_'+j+'_'+k);
				checkItem(d, getChecked(c));
			}
		}
	}
	// enable/disable upper nodes of id
	var node = id;
	var pos = node.lastIndexOf("_");
	var upnode;
	var elem, elem_up;
	while (pos>0) {
	    upnode = node.substr(0, pos);
	    elem = document.getElementById(node);
	    elem_up = document.getElementById(upnode);
	    if (getChecked(elem)) {
	    	checkItem(elem_up, true);
	    } else {
			var enabled = false;
			for (var k = 0; document.getElementById(upnode+'_'+k) ;k++) {
			    var elem_sub = document.getElementById(upnode+'_'+k);
				if (getChecked(elem_sub)) {
				    enabled = true;
				    break;
				}
			}
			if (!enabled) {
				checkItem(elem_up, false);
			}
	    }
	    node = upnode;
	    pos = node.lastIndexOf("_");
	}
}					
		
function toggleDisplay(id)
{
	var d = document.getElementById(id);
	if (d){
		var img = document.getElementById('img_'+id);
		if (img){
			if (d.style.display == 'block') {
				img.src = 'img/icones/16x16/navigate_plus.gif';
			} else {
				img.src = 'img/icones/16x16/navigate_minus.gif';
			}
		}
		if (d.style.display == 'block') {
			d.style.display='none';
		} else {
			d.style.display='block';
		}
	}	
}

function checkUncheckAll(theElement)
{
    jQuery(theElement).parents('tr').nextAll().find('input[type=checkbox]').each(function() {
        if (theElement.checked && !jQuery(this).attr('checked')) {
            jQuery(this).attr('checked',true);
            if (typeof(_selectedElem) != 'undefined') {
                putInSelectedElem(jQuery(this).attr('id'));
            }
        } else if (!theElement.checked && jQuery(this).attr('checked')) {
            jQuery(this).attr('checked', false);
            if (typeof(_selectedElem) != 'undefined') {
                removeFromSelectedElem(jQuery(this).attr('id'));
            }
        }
    });
}

function DisplayHidden(id)
{
	var d = document.getElementById(id);
	if (d) {
		if (d.style.display == 'block') {
			d.style.display='none';
		} else {
			d.style.display='block';
		}
	}
}

function isdigit(c)
{
	return(c >= '0' && c <= '9');
}
		
function atoi(s)
{
	var t = 0;

	for (var i = 0; i < s.length; i++) {
   		var c = s.charAt(i);
   		if (!isdigit(c)) {
   			return t;
   		} else {
   			t = t*10 + (c-'0');
   		}
	}
	return t;
}

function setDisabledRowStyle(img)
{
	document.observe("dom:loaded", function() {
		if (!img) {
			var img = "element_next.gif";
		}
		$$('img[src$="element_next.gif"]').each(function(el) {
			el.up(2).setAttribute('class', 'row_disabled');
		});
	});
}

        
/**
 * Synchronize input fields that bear the same name
 * 
 * @param string name
 * @param val int
 * @return void
 */
function syncInputField(name, val) {
    jQuery("input[name='"+name+"']").val(val);
}

//  End -->
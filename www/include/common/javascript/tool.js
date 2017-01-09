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
			var img = "enabled.png";
		}
		$$('img[src$="enabled.png"]').each(function(el) {
			el.up(2).setAttribute('class', 'row_disabled');
		});
	});
}

        
/**
 * Synchronize input fields that bear the same name
 * 
 * @param string name
 * @param mixed val
 * @return void
 */
function syncInputField(name, val) {
    jQuery("input[name='"+name+"']").val(val);
}

function isChecked()
{
    var ret = false;
    jQuery("input[type=checkbox]:checked").each( 
        function() {
            ret = true;
        } 
    );
    return ret;
}

//  End -->
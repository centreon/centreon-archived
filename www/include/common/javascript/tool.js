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

function checkItem(element, toCheck) {
  if (element.type === 'checkbox') {
    if (toCheck) {
      element.checked = true;
    } else {
      element.checked = false;
    }
  } else if (element.type === 'radio') {
    var element = document.Form[element.name];
    const value = toCheck ? 2 : 0;
    for (var j = 0; j < element.length; j++) {
      if (element[j].value == value) {
        element[j].checked = true;
      }
    }
  }
}

function getChecked(element) {
  if (element.type === 'checkbox') {
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

function updateACLRulesInputsLines(element) {
  const elementType = jQuery(element).prop('type');
  const level = jQuery(element).parents('tr:first').prop('className').split(' ')
    .find((cssClass) => cssClass.startsWith('level_'));

  let cssClassArbo = '';

  switch (level) {
    case 'level_1':
      cssClassArbo = '.arbo_b';
      break;

    case 'level_2':
      cssClassArbo = '.arbo_c';
      break;

    case 'level_3':
      cssClassArbo = '.arbo_d';
      break;
  }

  if (level && cssClassArbo !== '') {
    const cssSelector = `input[type="checkbox"]:not(.not_select_item),
			input[type="radio"]:not(.not_select_item)`;

    const inputs = jQuery(element).parents('table:first').next(cssClassArbo)
      .find(cssSelector);

    Object.values(inputs).forEach((input) => {
      let valueInputParent = element.value;

      if (element.type === 'checkbox') {
        input.checked = element.checked;
      }

      if (element.type === 'radio') {
        if (input.type === 'radio') {
          input.checked = input.value === valueInputParent;
        }
        if (input.type === 'checkbox') {
          input.checked = [1, 2].includes(parseInt(valueInputParent));
        }
      }
    });
  }
}

function toggleDisplay(id) {
  var d = document.getElementById(id);
  if (d) {
    var img = document.getElementById('img_' + id);
    if (img) {
      if (d.style.display == 'block') {
        img.src = 'img/icones/16x16/navigate_plus.gif';
      } else {
        img.src = 'img/icones/16x16/navigate_minus.gif';
      }
    }
    if (d.style.display == 'block') {
      d.style.display = 'none';
    } else {
      d.style.display = 'block';
    }
  }
}

function checkUncheckAll(theElement) {
  jQuery(theElement).parents('tr').nextAll().find('input[type=checkbox]').each(function () {
    if (theElement.checked && !jQuery(this).prop('checked')) {
      jQuery(this).prop('checked', true);
      if (typeof (_selectedElem) != 'undefined') {
        putInSelectedElem(jQuery(this).attr('id'));
      }
    } else if (!theElement.checked && jQuery(this).prop('checked')) {
      jQuery(this).prop('checked', false);
      if (typeof (_selectedElem) != 'undefined') {
        removeFromSelectedElem(jQuery(this).attr('id'));
      }
    }
  });
}

function DisplayHidden(id) {
  var d = document.getElementById(id);
  if (d) {
    if (d.style.display == 'block') {
      d.style.display = 'none';
    } else {
      d.style.display = 'block';
    }
  }
}

function isdigit(c) {
  return (c >= '0' && c <= '9');
}

function atoi(s) {
  var t = 0;

  for (var i = 0; i < s.length; i++) {
    var c = s.charAt(i);
    if (!isdigit(c)) {
      return t;
    } else {
      t = t * 10 + (c - '0');
    }
  }
  return t;
}

function setDisabledRowStyle(img) {
  jQuery(document).ready(function () {
    if (!img) {
      var img = "enabled.png";
    }
    jQuery('img[src$="enabled.png"]').each(function (index) {
      jQuery(this).parent().parent().parent().addClass('row_disabled');
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
  jQuery("input[name='" + name + "']").val(val);
}

function isChecked() {
  var ret = false;
  jQuery("input[type=checkbox]:checked").each(
    function () {
      ret = true;
    }
  );
  return ret;
}

//  End -->

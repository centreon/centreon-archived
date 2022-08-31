// For the multiple type of groups, we have to group fields together in order to clone them.
function clonifyTableFields(attributeName, displayName) {
    // First, find the fields and group them in one array for each multiple group
    var GroupArray = {};
    var GroupDisplayName = {};
    jQuery("["+attributeName+"]:not([alreadyProcessed])").each(function(ind,el){
        var groupname = jQuery(el).attr(attributeName);
        if(!GroupArray[groupname]){
            GroupArray[groupname] = [];
        }
        GroupArray[groupname].push(el);
        GroupDisplayName[groupname] = jQuery(el).attr(displayName);
    });

    // Then we group the fields like this abose :
    // <oldTable> .... <tr newdiv> <td1> <table1 clonable> <tr clone_template > <td> <table2>
    // <oldTr1> <oldTd1> <input1/> </oldTd1> </oldTr1> <oldTr2> <oldTd2> <input2/> </oldTd2> </olTtr2>  --> detached from oldTable
    // </table2> </td> </tr clone_template> <tr control> <td> </td> </tr> </table1 clonable> </td1> </tr newdiv> .... </oldTable>
    for(var obj in GroupArray) {
        var td1 = jQuery('<td>').attr('colspan','2').css({'padding': '2px'});
//        var newdiv = jQuery('<tr>').addClass('elem-toCollapse').attr('style', 'display:table-row').append(td1);
//        var newdiv = jQuery('<tr>').addClass('elem-toCollapse').attr('style', 'display:table-row').append(td1);
        var newdiv = jQuery('<tr>').addClass('elem-toCollapse').append(td1);
        var control = jQuery('<th>').append(jQuery('<span>').attr('id',obj+'_add').html('+ Add a new entry').css({
            'cursor': 'pointer',
            'color': '#009fdf'
        }));

        var theader = jQuery('<thead>').
        append(jQuery('<tr>').
        addClass('list_lvl_3').
        append(jQuery('<th>').
        html(' | ' + GroupDisplayName[obj])).
        append(control));

        var table1 = jQuery('<table>').addClass('formTable table clonable').attr('id',obj );
        table1.append(theader);
        td1.append(table1);
        var table2 = jQuery('<table>').addClass('formTable table').css({'position': 'relative','border': '1px solid #e7e7e8'});
        var clone_template = jQuery('<tr>').attr('id',obj +"_template" ).addClass('clone_template').append(jQuery('<td>').attr('colspan','2').css({'padding': '0 12px'}).append(table2));
        table1.append(clone_template).append(jQuery('<tr>').attr('id',obj +"_noforms_template" ));
        var img = jQuery('<img>').attr('src','./img/icons/circle-cross.png').addClass('ico-14').css('vertical-align','middle');
        var remove = jQuery('<span>').css({'cursor': 'pointer', 'position': 'absolute', 'top': '56px', 'right': '-17px'}).attr('id', obj+'_remove_current').append(img);
        table2.append(jQuery('<tr>').append(jQuery('<td>').css({'text-align': 'right', 'height': '1px'}).attr('rowspan','5').attr('colspan','2').append(remove)));

        if(GroupArray.hasOwnProperty(obj)){
            var firstPosition = false;
            GroupArray[obj].forEach(function(element){
                tdSize = jQuery(element).parents("tr").first().children('.FormRowField').first().width();
                // since the element is in a subTab and because of the auto-sizing of each <td>,
                // we have to set the width of the <td> to the old value, this break responsive design for thoose fields
                jQuery(element).parents("tr").first().children('.FormRowField').first().css('width',tdSize);
                jQuery(element).attr('alreadyProcessed','1');

                var parent = jQuery(element).parents("tr").first().removeClass("elem-toCollapse");
                if(!firstPosition){
                    firstPosition = parent.prev();
                }
                parent.detach();
                table2.prepend(parent);
            });
            //table1.prepend(control);
            firstPosition.after(newdiv);
        }
    }

    // Finaly, we make each group of fields clonable (if not alreadyProcessed)
    jQuery(".clonable:not([alreadyProcessed])").each(function(idx, el) {
        jQuery(el).attr('alreadyProcessed','1');
        var suffixid = jQuery(el).attr('id');
        jQuery(el).sheepIt({
            separator: '',
            allowRemoveLast: true,
            allowRemoveCurrent: true,
            allowRemoveAll: true,
            minFormsCount: 0,
            maxFormsCount: 200,
            continuousIndex : false,
            iniFormsCount: jQuery("#clone-count-" + suffixid).data("clone-count-" + suffixid),
            data: jQuery("#clone-values-" + suffixid).data("clone-values-" + suffixid)
        });
    });
}

function addCollapse(id_name) {
    if(id_name === undefined){
        var tbody = jQuery(".collapse-wrapper");
    } else {
        var tbody = jQuery("#" + id_name + ".collapse-wrapper");
    }
    tbody.find(".list_one").addClass("elem-toCollapse");
    tbody.find(".list_two").addClass("elem-toCollapse");
}

function initCollapsebyTab(tab) {
    var tbody = jQuery("#" + tab + " .collapse-wrapper");
    var othertBodyInput = tbody.not(tbody.eq(0)).find(".elem-toCollapse input.v_required");
    othertBodyInput.qtip('destroy');

    tbody.eq(0).find(".elem-toCollapse").show();
    tbody.eq(0).find(".list_lvl_1").addClass("open");
    tbody.eq(0).find(".list_lvl_1 .expand").addClass("expand");
    tbody.find(".list_lvl_1 .expand").slice(1).addClass("expand-icon");
}

function openNewElem(id_name) {
    var newtBody = jQuery("#" + id_name);
    var othertBodyElem = newtBody.siblings().find(".elem-toCollapse");
    if(othertBodyElem.is(':visible')) {
        othertBodyElem.hide();
    }
    newtBody.find(".elem-toCollapse").show();
    newtBody.find('.list_lvl_1').addClass('open').removeClass('close');
    newtBody.siblings().find('.list_lvl_1').addClass('close').removeClass('open');
}

jQuery(function () {
    addCollapse();

    jQuery('body').delegate('.collapse-wrapper .list_lvl_1', 'click', function (e) {
       var elem = jQuery(e.currentTarget);
       var tbody = elem.parent('.collapse-wrapper');

       var nextElemChildren = tbody.parents('.tab').find('tr.elem-toCollapse').filter(function (idx, elem) {
           return !tbody.is(jQuery(elem).parent('.collapse-wrapper'));
       });

       var elemChildren = tbody.find('.elem-toCollapse');

       if (elemChildren.is(':visible')) {
           elemChildren.hide();
           elem.removeClass('open').addClass('close');
           elem.find('.expand').addClass("expand-icon");
       }
       else {
           elemChildren.show();
           elem.addClass('open').removeClass('close');
           elem.find('.expand').removeClass("expand-icon");
           nextElemChildren.hide();
           nextElemChildren.siblings(".list_lvl_1").find('.expand').addClass("expand-icon");
           nextElemChildren.siblings(".list_lvl_1").removeClass('open').addClass('close');
           jQuery.each(jQuery('[data-ontab-fn]'), function () {
               window[jQuery(this).attr('data-ontab-fn')].onLoad(this, jQuery(this).attr('data-ontab-arg'))();
           });
       }
   });
});

// Hooks for some fields
var countConnections = {
    // Hook on load tab
    onLoad: function (element, argument) {
        argument = window.JSON.parse(argument);
        return function () {
            var entry = element.name.match('(input|output)(\\[\\d\\])\\[(\\w*)\\]');
            var target = entry[1] + entry[2] + '[' + argument.target + ']';

            if (document.getElementsByName(target)[1].value == '') {
                document.getElementsByName(target)[1].value = 1;
            }
        }
    },
    // Hook on change the target
    onChange: function (argument) {
        return function (self) {
            var entry = self.name.match('(input|output)(\\[\\d\\])\\[(\\w*)\\]');
            var target = entry[1] + entry[2] + '[' + argument.target + ']';
            var entryValue = document.getElementsByName(target)[1].value.replace(",", ".");

            if (entryValue == '' || isNaN(entryValue) || entryValue < 1) {
                document.getElementsByName(target)[1].value = 1;
            } else {
                document.getElementsByName(target)[1].value = Math.trunc(entryValue);
            }
        }
    }
}

// Hooks for some fields
var rrdArguments = {
    // Hook on load tab
    onLoad: function (element, argument) {
        argument = window.JSON.parse(argument);
        return function () {
            var entry = element.name.match('(input|output)(\\[\\d\\])\\[(\\w*)\\]');
            var option = entry.input;
            var target = entry[1] + entry[2] + '[' + argument.target + ']';

            if (document.querySelector('input[name="' + option + '"]:checked').value === 'disable') {
                document.getElementsByName(target)[1].disabled = true;
            }
        }
    },
    // Hook on change the target
    onChange: function (argument) {
        return function (self) {
            var entry = self.name.match('(input|output)(\\[\\d\\])\\[(\\w*)\\]');
            var option = entry.input;
            var target = entry[1] + entry[2] + '[' + argument.target + ']';

            if (document.querySelector('input[name="' + option + '"]:checked').value === 'disable') {
                document.getElementsByName(target)[1].value = '';
                document.getElementsByName(target)[1].disabled = true;
            } else {
                document.getElementsByName(target)[1].disabled = false;
            }
        }
    }
}

// Hooks for some fields
var luaArguments = {
    // Hook on load tab
    onLoad: function (element, argument) {
        argument = window.JSON.parse(argument);
        return function () {
            var type = jQuery(element).val();
            var entry = element.name.match('(input|output)(\\[\\d\\])\\[(\\w*)\\]');
            var block = entry[3].split('_');
            var name = argument.target.replace("%d", block[block.length - 1]);
            var target = entry[1] + entry[2] + '[' + name + ']';
            luaArguments.changeInput(type, target)
        }
    },
    // Hook on change the target
    onChange: function (argument) {
        return function (self) {
            var entry = self.name.match('(input|output)(\\[\\d\\])\\[(\\w*)\\]');
            var block = entry[3].split('_');
            var name = argument.target.replace("%d", block[block.length - 1]);
            var target = entry[1] + entry[2] + '[' + name + ']';
            var type = jQuery(self).val();
            luaArguments.changeInput(type,target)
        }
    },
    // Internal function for apply the input change
    changeInput: function (type, name) {
        // Get all attributes
        var attrs = {};
        name = '[name="' + name + '"]:input';
        jQuery.each(jQuery(name)[0].attributes, function (idx, attr) {
            attrs[attr.name] = attr.value;
        });
        delete(attrs.type);
        delete(attrs.size);
        delete(attrs.class);
        var $elParent = jQuery(name).parent();
        var value = jQuery(name).val();
        jQuery(name).remove();
        // Find the good input for the type by default text => string
        if (type === 'number') {
            var newEl = jQuery('<input />')
                .attr(attrs)
                .attr('size', 10)
                .attr('type', 'text')
                .addClass('v_number')
                .val(value);
        } else if (type === 'password') {
            var newEl = jQuery('<input />')
                .attr(attrs)
                .attr('size', 120)
                .attr('type', 'password')
                .val(value);
        } else {
            var newEl = jQuery('<input />')
                .attr(attrs)
                .attr('size', 120)
                .attr('type', 'text')
                .val(value);
        }
        $elParent.append(newEl);
     }
}

var bbdoStreams = {
    // Hook on load tab
    onLoad: function (element, argument) {
        argument = window.JSON.parse(argument);
        return function () {
            // if element collapsed then do nothing
            if ($(element).closest('tbody').find('.list_lvl_1').hasClass('close')) {
                return ;
            }

            var entry = element.name.match('(input|output)(\\[\\d\\])\\[(\\w*)\\]');

            if (argument.hasOwnProperty("tag")) {
                bbdoStreams.displayInputDependingOnTag(entry[1], argument.tag, 'input[name="' + entry.input + '"]');
                return ;
            }

            var source = 'input[name="' + entry.input + '"]:checked';

            if (Array.isArray(argument.target)) {
                argument.target.forEach(targetElem => {
                    var target = entry[1] + entry[2] + '[' + targetElem + ']';

                    bbdoStreams.displayInputDependingOnInput(source, argument.value, target);
                });
            } else {
                var target = entry[1] + entry[2] + '[' + argument.target + ']';

                bbdoStreams.displayInputDependingOnInput(source, argument.value, target);
            }
        }
    },
    // Hook on change the target
    onChange: function (argument) {
        return function (self) {
            var entry = self.name.match('(input|output)(\\[\\d\\])\\[(\\w*)\\]');
            var source = 'input[name="' + entry.input + '"]:checked';

            if (Array.isArray(argument.target)) {
                argument.target.forEach(targetElem => {
                    var target = entry[1] + entry[2] + '[' + targetElem + ']';

                    bbdoStreams.displayInputDependingOnInput(source, argument.value, target);
                });
            } else {
                var target = entry[1] + entry[2] + '[' + argument.target + ']';

                bbdoStreams.displayInputDependingOnInput(source, argument.value, target);
            }
        }
    },
    displayInputDependingOnInput: function (source, expectedSourceValue, target) {
        if (document.querySelector(source).value === expectedSourceValue) {
            $(document.getElementsByName(target)[1].closest('tr')).show();
        } else {
            document.getElementsByName(target)[1].value = '';
            $(document.getElementsByName(target)[1].closest('tr')).hide();
        }
    },
    displayInputDependingOnTag: function (tag, expectedTag, target) {
        if (tag === expectedTag) {
            $(document.querySelector(target).closest('tr')).show();
        } else {
            $(document.querySelector(target).closest('tr')).hide();
        }
    }
}

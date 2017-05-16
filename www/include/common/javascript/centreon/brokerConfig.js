// For the multiple type of groups, we have to group fields together in order to clone them.
function clonifyTableFields(attributeName,displayName){
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
        table2.append(jQuery('<tr>').addClass('elem-toCollapse').append(jQuery('<td>').css({'text-align': 'right', 'height': '1px'}).attr('rowspan','5').attr('colspan','2').append(remove)));

        if(GroupArray.hasOwnProperty(obj)){
            var firstPosition = false;
            GroupArray[obj].each(function(element){
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

function addCollapse() {
    var tbody = jQuery(".collapse-wrapper");

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

       if(elemChildren.is(':visible')) {
           elemChildren.hide();
           elem.removeClass('open').addClass('close');
           elem.find('.expand').addClass("expand-icon");
       }
       else {
           elemChildren.show();
           elem.addClass('open').removeClass('close');
           elem.find('.expand').removeClass("expand-icon");
           nextElemChildren.hide();
           console.log(nextElemChildren.siblings(".list_lvl_1"));
           nextElemChildren.siblings(".list_lvl_1").find('.expand').addClass("expand-icon");
       }
   });
});

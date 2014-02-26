<script>
    $(document).ready(function() {
        oTable = $('#datatable{$object}').dataTable( {
            "bProcessing": true,
            "sAjaxSource": "{url_for url=$objectUrl}",
            "bStateSave": false,
            "bServerSide": true,
            "iDisplayLength": 25,
            "aLengthMenu": [[10, 25, 50], [10, 25, 50]],
            "sPaginationType": "bootstrap",
            "bSortCellsTop": true,
            'sDom': "<'row'r<'clear'><'col-sm-6'l><'col-sm-6'T>>t<'row'<'col-sm-6'i><'col-sm-6'p>>",
            "oTableTools": {
                "sSwfPath": "{'/static/centreon/swf/dataTables/copy_csv_xls_pdf.swf'|url}",
                "aButtons": [
                    {
                        "sExtends": "collection",
                        "sButtonText": "Export",
                        "aButtons": [ "copy", "csv", "xls", "pdf", "print" ]
                    }
                ]
            },
            "aoColumnDefs": [
                { "bAutoWidth" : false, "bSortable": false, "sWidth": "10px", "aTargets": [0] }
            ]
        }).columnFilter({
            sPlaceHolder: 'head:after',
            nbFixedTr: {$datatableParameters.nbFixedTr},
            aoColumns: [
                {foreach $datatableParameters.header as $header}
                    {foreach $header as $headerType=>$headerData}
                        {if $headerType === 'select'}
                            { type: "select", cls: "form-control input-sm", values: [ {foreach $headerData as $optName=>$optValue} { label:'{$optName}', value:'{$optValue}' } , {/foreach} ] },
                        {elseif $headerData === 'none'}
                            { type: "cleanup" },
                        {else}
                            { type: "text", cls: "form-control input-sm"},
                        {/if}
                    {/foreach}
                {/foreach}
            ]
        });
    });

    function toggleSelectedAction() {
        var countElem = $('table[id^="datatable"] tbody input[type="checkbox"][class^="all"]').length;
        var countChecked = $('table[id^="datatable"] tbody input[type="checkbox"][class^="all"]:checked').length;
        if (countElem == countChecked) {
            $('table[id^="datatable"] thead input[id^="all"]').prop("checked", true);
        } else {
            $('table[id^="datatable"] thead input[id^="all"]').prop("checked", false);
        }
        if (countChecked > 0) {
            $('#selected_option').show();
        } else {
            $('#selected_option').hide();
        }
    }
    
    $(".search_field").keyup(function() {
        row = $(this).parent().parent().children().index($(this).parent());
        oTable.fnFilter(this.value, row);
    });
    
    $.extend($.fn.dataTableExt.oStdClasses, {
        "sSortAsc": "header headerSortDown",
        "sSortDesc": "header headerSortUp",
        "sSortable": "header"
    });
    
    $(".search_type").change(function() {
        oTable.fnFilter(this.value, jQuery(".search_type").index(this));
    });

    $('table[id^="datatable"] thead input[id^="all"]').on('click', function(e) {
        var $checkbox = $(e.currentTarget);
        $checkbox.parents('table').find('tbody input[type="checkbox"][class^="all"]').each(function() {
            $(this).prop("checked", $checkbox.is(':checked'));
        });
        toggleSelectedAction();
    });

    $('table[id^="datatable"] tbody').on('click', 'input[type="checkbox"][class^="all"]', function(e) {
        toggleSelectedAction();
    });

    /* Add modal */
    $('#modalAdd').on('click', function(e) {
        $('#modal').removeData('bs.modal');
        $('#modal').removeData('centreonWizard');
        $('#modal .modal-content').text('');
        $('#modal').one('loaded.bs.modal', function(e) {
            $(this).centreonWizard();
        });
        $('#modal').modal({
            'remote': '{url_for url=$objectAddUrl}'
        });
    });

    /* Delete modal */
    $('#modalDelete').on('click', function(e) {
        e.preventDefault();
        $('#modal .modal-content').text('');

        /* Delete modal header */
        var $deleteHeader = $('<div></div>').addClass('modal-header');
        $('<button></button>')
            .attr('type', 'button')
            .attr('aria-hidden', 'true')
            .attr('data-dismiss', 'modal')
            .addClass('close')
            .html('&times;')
            .appendTo($deleteHeader);
        $('<h4></h4>').addClass('modal-title').text("{t}Delete{/t}").appendTo($deleteHeader);
        $deleteHeader.appendTo('#modal .modal-content');

        /* Delete modal body */
        var $deleteBody = $('<div></div>').addClass('modal-body');
        $('<span></span>').text('{t}Are you sure to delete ?{/t}').appendTo($deleteBody);
        var $listElement = $('<ul></ul>').addClass('list-unstyled');
        $('table[id^="datatable"] tbody input[type="checkbox"][class^="all"]:checked').each(function(k, v) {
            $('<li></li>').html($(v).data('name')).appendTo($listElement);
        });
        $listElement.appendTo($deleteBody);
        $deleteBody.appendTo('#modal .modal-content');

        var $deleteFooter = $('<div></div>').addClass('modal-footer');
        $('<a></a>')
            .attr('aria-hidden', 'true')
            .attr('data-dismiss', 'modal')
            .addClass('btn').addClass('btn-default')
            .text('{t}Cancel{/t}')
            .appendTo($deleteFooter);
        $('<button></button>')
            .attr('type', 'button')
            .addClass('btn')
            .addClass('btn-danger')
            .text('{t}Delete{/t}')
            .appendTo($deleteFooter);
        $deleteFooter.appendTo('#modal .modal-content');
        
        $('#modal')
            .removeData('bs.modal')
            .modal();
    });

    /* Duplicate modal */
    $('#modalDuplicate').on('click', function(e) {
        e.preventDefault();
        $('#modal').removeData('bs.modal');
        $('#modal .modal-content').text('');

        /* MC modal header */
        var $duplicateHeader = $('<div></div>').addClass('modal-header');
        $('<button></button>')
            .attr('type', 'button')
            .attr('aria-hidden', 'true')
            .attr('data-dismiss', 'modal')
            .addClass('close')
            .html('&times;')
            .appendTo($duplicateHeader);
        $('<h4></h4>').addClass('modal-title').text("{t}Duplicate{/t}").appendTo($duplicateHeader);
        $duplicateHeader.appendTo('#modal .modal-content');

        /* Modal body */
        var $duplicateBody = $('<div></div>').addClass('modal-body');
        $('<span></span>').text('{t}Choose number of duplicate{/t}').appendTo($duplicateBody);
        var $form = $('<form></form>').attr('role', 'form').addClass('form-horizontal');
        $('table[id^="datatable"] tbody input[type="checkbox"][class^="all"]:checked').each(function(k, v) {
            var $group = $('<div></div>').addClass('form-group');
            $('<label></label>')
                .addClass('col-sm-4')
                .addClass('control-label')
                .attr('for', 'duplicate_' + $(v).val())
                .html($(v).data('name'))
                .appendTo($group);
            $('<div></div>').addClass('col-sm-1').append(
                $('<input></input>')
                    .attr('id', 'duplicate_' + $(v).val())
                    .attr('name',  $(v).val())
                    .attr('type', 'text')
                    .val(1)
                    .addClass('form-control')
            ).appendTo($group);
            $group.appendTo($form);
        });
        $form.appendTo($duplicateBody);
        $duplicateBody.appendTo('#modal .modal-content');

        var $duplicateFooter = $('<div></div>').addClass('modal-footer');
        $('<a></a>')
            .attr('aria-hidden', 'true')
            .attr('data-dismiss', 'modal')
            .addClass('btn').addClass('btn-default')
            .text('{t}Cancel{/t}')
            .appendTo($duplicateFooter);
        $applyBtn = $('<button></button>')
            .attr('type', 'button')
            .addClass('btn')
            .addClass('btn-primary')
            .text('{t}Apply{/t}')
            .appendTo($duplicateFooter);
        $applyBtn.on('click', function(e) {
            var formValues = {};
            $.each($form.serializeArray(), function(k, v) {
                formValues[v.name] = v.value;
            });
            $.ajax({
                url: '{url_for url=$objectDuplicateUrl}',
                type: 'POST',
                dataType: 'json',
                data: {
                    'duplicate': JSON.stringify(formValues)
                },
                success: function(data, textStatus, jqXHR) {
                    $('#modal').modal('hide');
                    if (data.success) {
                        $('.dataTable').dataTable().fnDraw();
                    }
                }
            });
        });
        $duplicateFooter.appendTo('#modal .modal-content');

        $('#modal').modal();
    });

    /* Massive change modal */
    $('#modalMassiveChange').on('click', function(e) {
        e.preventDefault();
        $('#modal').removeData('bs.modal');
        $('#modal .modal-content').text('');

        /* MC modal header */
        var $mcHeader = $('<div></div>').addClass('modal-header');
        $('<button></button>')
            .attr('type', 'button')
            .attr('aria-hidden', 'true')
            .attr('data-dismiss', 'modal')
            .addClass('close')
            .html('&times;')
            .appendTo($mcHeader);
        $('<h4></h4>').addClass('modal-title').text("{t}Massive change{/t}").appendTo($mcHeader);
        $mcHeader.appendTo('#modal .modal-content');

        /* MC modal body */
        var $mcBody = $('<div></div>').addClass('modal-body');
        var $form = $('<form></form>').attr('role', 'form').addClass('form-horizontal');
        var $formGroup = $('<div></div>').addClass('form-group');
        $('<div></div>')
            .addClass('col-sm-4')
            .addClass('text-right')
            .append(
                $('<label></label>')
                    .attr('for', 'mcChooseAttr')
                    .addClass('label-controller')
                    .text('{t}Choose the attribute to change{/t}')
            ).appendTo($formGroup);
        /* Get first select for choose attribute */
        var $divSelect = $('<div></div>').addClass('col-sm-6');
        var $select = $('<select></select>')
            .attr('id', 'mcChooseAttr')
            .css('width', '100%')
            .append('<option></option>')
            .appendTo($divSelect);
        $divSelect.appendTo($formGroup);
        $formGroup.appendTo($form);
        $form.appendTo($mcBody);
        $mcBody.appendTo('#modal .modal-content');

        var $mcFooter = $('<div></div>').addClass('modal-footer');
        $('<a></a>')
            .attr('aria-hidden', 'true')
            .attr('data-dismiss', 'modal')
            .addClass('btn').addClass('btn-default')
            .text('{t}Cancel{/t}')
            .appendTo($mcFooter);
        var $applyBtn = $('<button></button>')
            .attr('type', 'button')
            .addClass('btn')
            .addClass('btn-primary')
            .text('{t}Apply{/t}')
            .appendTo($mcFooter);
        $mcFooter.appendTo('#modal .modal-content');

        $.ajax({
            url: "{url_for url=$objectMcFieldsUrl}",
            type: "GET",
            dataType: "json",
            success: function(data, textStatus, jqXHR) {
                $.each(data.listMc, function(k, v) {
                    $("<option></option>").val(k).text(v).appendTo("#mcChooseAttr");
                });
                $("#mcChooseAttr").select2({
                    allowClear: true
                });
                $("#mcChooseAttr").on("change", function(e) {
                    $("#mcChooseAttr").select2("val", "");
                    $("#mcChooseAttr > option[value='" + e.added.id + "']").prop({
                        disabled: true
                    });
                    $.ajax({
                        url: "{url_for url=$objectMcFieldsUrl}/" + e.added.id,
                        type: "GET",
                        dataType: "html",
                        success: function(data, textStatus, jqXHR) {
                            $(data).appendTo($form);
                        }
                    });
                });
            }
        });

        $('#modal').modal();
    });
</script>

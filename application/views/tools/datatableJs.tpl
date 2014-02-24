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

    $('#modalAdd').on('loaded.bs.modal', function(e) {
        $(this).centreonWizard();
    });
</script>

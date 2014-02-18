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
    
    $(".search_field").keyup(function() {
        row = $(this).parent().parent().children().index($(this).parent());
        oTable.fnFilter(this.value, row);
    });
    
    $.extend($.fn.dataTableExt.oStdClasses, {
        "sSortAsc": "header headerSortDown",
        "sSortDesc": "header headerSortUp",
        "sSortable": "header"
    });
    
    $('.all{$object}').click(function() {
        if (this.checked) {
            $('.all{$object}Box').prop('checked', true);
            $('.all{$object}').prop('checked', true);
        } else {
            $('.all{$object}Box').prop('checked', false);
            $('.all{$object}').prop('checked', false);
        }
    });
    
    $(document).on('click', ".all{$object}Box", function() {
        if (this.checked === false) {
            $('.all{$object}').prop('checked', false);
        }
    });

    $(".search_type").change(function() {
        oTable.fnFilter(this.value, jQuery(".search_type").index(this));
    });
</script>

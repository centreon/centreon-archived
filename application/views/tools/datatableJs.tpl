<script>
    $(document).ready(function() {
        oTable = $('#datatable{$object}').dataTable( {
            "bProcessing": true,
            "sAjaxSource": "{url_for url=$objectUrl}",
            "bStateSave": true,
            "bServerSide": true,
            "iDisplayLength": 25,
            "aLengthMenu": [[10, 25, 50], [10, 25, 50]],
            "sPaginationType": "bootstrap",
            'sDom': "<'row-fluid'Tr<'clear'><'span6'l><'span6'>t<'row-fluid'<'span6'i><'span6'p>>",
            "oTableTools": {
                "sSwfPath": "{'/static/centreon/swf/dataTables/copy_csv_xls_pdf.swf'|url}"
            }
        });
    });
    
    $(".search_field").keyup( function () {
        row = $(this).parent().parent().children().index($(this).parent());
        oTable.fnFilter(this.value, row);
    });
    
    $.extend( $.fn.dataTableExt.oStdClasses, {
        "sSortAsc": "header headerSortDown",
        "sSortDesc": "header headerSortUp",
        "sSortable": "header"
    } );

    $(".search_type").change(function() {
        oTable.fnFilter(this.value, jQuery(".search_type").index(this));
    });
</script>

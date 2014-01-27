<script>
    $(document).ready(function() {
        oTable = $('#dataTable{$object}').dataTable( {
            "bProcessing": true,
            "sAjaxSource": "{url_for url=$objectUrl}",
            "bStateSave": true,
            "bServerSide": true,
            "iDisplayLength": 50,
            "aLengthMenu": [[10, 25, 50], [10, 25, 50]],
            "sPaginationType": "bootstrap",
            "sDom": "<'row-fluid'T<'clear'><'span6'l><'span6'f>t<'row-fluid'<'span6'i><'span6'p>>",
            "oTableTools": {
                "sSwfPath": "{'/static/centreon/swf/dataTables/copy_csv_xls_pdf.swf'|url}"
            }
        });

        $('#form').submit( function() {
            var sData = $('input', oTable.fnGetNodes()).serialize();
            alert( "The following data would have been submitted to the server: \n\n"+sData );
            return false;
        } );
    });
    
    $("tfoot input").keyup( function () {
        /* Filter on the column (the index) of this element */
        console.log(jQuery("tfoot input").index(this));
        oTable.fnFilter( this.value, jQuery("tfoot input").index(this) );
    });
    
    $.extend( $.fn.dataTableExt.oStdClasses, {
        "sSortAsc": "header headerSortDown",
        "sSortDesc": "header headerSortUp",
        "sSortable": "header"
    } );
    
    $(".search_type").click(function() {
         oTable.fnFilter(this.value, 2);
    });
</script>
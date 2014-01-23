{extends file="../../viewLayout.tpl"}

{block name="title"}Command{/block}

{block name="content"}
    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" width="100%" id="myDatatableTest">

        <thead>
            <tr>
                <th>
                    <select id="search_type" name="search_type">
                        <option value="2">Check</option>
                        <option value="1">Notifications</option>
                        <option value="3">Miscelleanous</option>
                    </select>
                </th>
                <th>
                    <input type="text" name="search_name" placeholder="Identifiant" class="search_init" size='10' />
                </th>
                <th>
                    <input type="text" name="search_line" placeholder="Identifiant" class="search_init" size='10' />
                </th>
            </tr>
            <tr>
                <th>Type</th>
                <th>Name</th>
                <th>Command Line</th>
            </tr>
        </thead>

        <tbody>
        </tbody>

        <tfoot>
            <tr>
                <th>
                    <select id="search_type_2" name="search_type_2">
                        <option value="2">Check</option>
                        <option value="1">Notifications</option>
                        <option value="3">Miscelleanous</option>
                    </select>
                    <input type="text" name="search_name" placeholder="Identifiant" class="search_init" size='10' />
                </th>
                <th>
                    <input type="text" name="search_line" placeholder="Identifiant" class="search_init" size='10' />
                </th>
            </tr>
        </tfoot>

    </table>
{/block}

{block name="javascript-bottom" append}
    <script>
        $(document).ready(function() {
            oTable = $('#myDatatableTest').dataTable( {
                "bProcessing": true,
                "sAjaxSource": 'datatable',
                "bStateSave": true,
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
    </script>

    <script>
        $("tfoot input").keyup( function () {
            /* Filter on the column (the index) of this element */
            console.log(jQuery("tfoot input").index(this));
            oTable.fnFilter( this.value, jQuery("tfoot input").index(this) );
        });
    </script>

    <script>
        $.extend( $.fn.dataTableExt.oStdClasses, {
            "sSortAsc": "header headerSortDown",
            "sSortDesc": "header headerSortUp",
            "sSortable": "header"
        } );
    </script>

    <script>
        $("#search_type").click(function() {
             oTable.fnFilter(this.value, 2);
        });
        
        $("#search_type_2").click(function() {
             oTable.fnFilter(this.value, 2);
        });
    </script>

{/block}
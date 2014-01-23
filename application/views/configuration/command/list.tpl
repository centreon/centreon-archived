{extends file="../../viewLayout.tpl"}

{block name="title"}Command{/block}

{block name="content"}
    <input type="button" id="mycheckbutton" value="Check" />
    &nbsp;&nbsp;
    <input type="button" id="mynotifbutton" value="Notifications" />
    &nbsp;&nbsp;
    <input type="button" id="mymiscbutton" value="Miscellaneous" />
    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" width="100%" id="myDatatableTest">

        <thead>
            <tr>
                <th>
                    <input type="text" name="search_name" placeholder="Identifiant" class="search_init" size='10' />
                </th>
                <th>
                    <input type="text" name="search_line" placeholder="Identifiant" class="search_init" size='10' />
                </th>
                <th>
                    <input type="text" name="search_type" placeholder="Identifiant" class="search_init" size='10' />
                </th>
            </tr>
            <tr>
                <th>Name</th>
                <th>Command Line</th>
                <th>Type</th>
            </tr>
        </thead>

        <tbody>
        </tbody>

        <tfoot>
            <tr>
                <th>
                    <input type="text" name="search_name" placeholder="Identifiant" class="search_init" size='10' />
                </th>
                <th>
                    <input type="text" name="search_line" placeholder="Identifiant" class="search_init" size='10' />
                </th>
                <th>
                    <input type="text" name="search_type" placeholder="Identifiant" class="search_init" size='10' />
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
                "sDom": "<'row-fluid'T<'clear'><'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
                "oTableTools": {
                    "sSwfPath": "static/centreon/swf/dataTables/copy_csv_xls_pdf.swf"
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
        $("#mynotifbutton").click(function() {
            //Creating of our own filtering function
            console.log(jQuery("tfoot input").index(this));

            oTable.fnFilter('1', 2)
        });
        $("#mycheckbutton").click(function() {
            //Creating of our own filtering function
            console.log(jQuery("tfoot input").index(this));
            oTable.fnFilter('2', 2)
        });
        $("#mymiscbutton").click(function() {
            //Creating of our own filtering function
            console.log(jQuery("tfoot input").index(this));
            oTable.fnFilter('3', 2)
        });
    </script>

{/block}
{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{t}Issues{/t}{/block}

{block name="content"}
<div class="content-container">
  <table class="table table-striped table-condensed" id="issues">
  <thead>
    <tr>
      <th class="span-1">&nbsp;</th>
      <th class="span-2">{t}Host{/t}</th>
      <th class="span-2">{t}Service{/t}</th>
      <th class="span-2">{t}Status{/t}</th>
      <th class="span-2">{t}Start time{/t}</th>
      <th class="span-2">{t}End time{/t}</th>
      <th class="span-1">{t}Ticket{/t}</th>
      <th class="badge-new-events" style="display: none;"><a href="#"><i class="fa fa-caret-up"></i> <span></span></a></th>
    </tr>
  </thead>
  <tbody>
  </tbody>
  </table>
</div>
{/block}

{block name="javascript-bottom" append}
<script>
$(function() {
  $("#issues").centreonTableInfiniteScroll({
    "ajaxUrlGetScroll": "{url_for url="/realtime/issues"}",
    "templateRows": "<tr> \
      <td class='span-1'> \
        <a href='#' class='ext_infos' data-id='<%id%>'><i class='fa fa-plus-square-o'></i></a> \
        <a href='<%id%>'><i class='fa fa-list-alt'></i></a> \
        <a href='<%id%>'><i class='fa fa-sitemap'></i></a> \
      </tb> \
      <td class='span-2'><%host_name%></td> \
      <td class='span-2'><%service_desc%></td> \
      <td class='span-2 centreon-status-<%status_num%>'><%status%></td> \
      <td class='span-2'><%starttime%></td> \
      <td class='span-2'><%endtime%></td> \
      <td class='span-1'> \
        <%#ticket%><a href=''><%ticket%></a><%/ticket%> \
        <%^ticket%><button class='btn btn-default btn-xs'>Open</button><%/ticket%> \
      </td> \
    </tr> \
    <tr style='display: none;' id='ext_infos_<%id%>'> \
      <td class='span-12'>&nbsp;</td> \
    </tr>"
  });

  $("#issues > tbody").on("click", "a.ext_infos", function(e) {
    e.preventDefault();
    $elem = $(e.currentTarget);
    $("#ext_infos_" + $elem.data("id")).toggle();
  });
});
</script>
{/block}

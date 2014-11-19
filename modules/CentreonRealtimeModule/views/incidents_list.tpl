{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{t}Incidents{/t}{/block}

{block name="content"}
<div class="content-container">
  <table class="table table-striped table-condensed table-bordered dataTable" id="incidents">
  <thead>
    <tr>
      <th class="span-1">&nbsp;</th>
      <th class="span-2">{t}Host{/t}</th>
      <th class="span-2">{t}Service{/t}</th>
      <th class="span-1">{t}Status{/t}</th>
      <!--<th class="span-2">{t}Start time{/t}</th>-->
      <th class="span-1">{t}Duration{/t}</th>
      <th class="span-5">{t}Output{/t}</th>
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
var incidentExtInfoTmpl = "<div class='span-8'> \
<table class='table table-condensed table-bordered table-hover table-striped span-12'> \
  <tbody> \{literal}
  {{{#children}}} \
  <tr> \
    <td class='centreon-status-{{{status}}} span-2'>{{{name}}}</td> \
    <td class='span-10'>{{{output}}}</td> \
  </tr> \
  {{{/children}}} \
  </tbody> \
</table> \
</div>";{/literal}

$(function() {
  var incidentExtInfoCompiled = Hogan.compile( incidentExtInfoTmpl );

  $("#incidents").centreonTableInfiniteScroll({
    "ajaxUrlGetScroll": "{url_for url="/realtime/incident"}",
    "templateRows": "<tr> \
      <td class='span-1'> \{literal}
        <a href='#' class='ext_infos' data-id='{{{issue_id}}}'><i class='fa fa-plus-square-o'></i></a> \
        <a href='{{{id}}}'><i class='fa fa-list-alt'></i></a> \
        <a href='{{{url_graph}}}'><i class='fa fa-sitemap'></i></a> \
        <a href='#'><i class='fa fa-ticket'></i></a> \
      </tb> \
      <td class='span-2'><a href='./realtime/host/{{{host_id}}}'>{{{host_name}}}</a></td> \
      <td class='span-2'><a href='./realtime/service/{{{service_id}}}'>{{{service_desc}}}</a></td> \
      <td class='span-1 centreon-status-{{{state}}}' style='text-align:center;'>{{{status}}}</td> \
      <!--<td class='span-2'>{{{start_time}}}</td>--> \
      <td class='span-1' style='text-align:right;'>{{{duration}}}</td> \
      <td class='span-5'>{{{output}}}</td> \
    </tr> \
    <tr style='display: none;' id='ext_infos_{{{issue_id}}}'> \
      <td class='span-12 incident-extended-info'>&nbsp;</td> \
    </tr>"{/literal}
  });

  $("#incidents > tbody").on("click", "a.ext_infos", function(e) {
    var incidentId, $elem, $icon;
    e.preventDefault();
    $elem = $( e.currentTarget );
    incidentId = $elem.data( "id" );
    $icon = $elem.find( "i" );
    if ($icon.hasClass("fa-minus-square-o")) {
      $("#ext_infos_" + incidentId).toggle();
      $icon.removeClass("fa-minus-square-o").addClass("fa-plus-square-o");
    } else {
      $.ajax({
        url: "{url_for  url="/realtime/incident/extented_info"}",
        method: "POST",
        dataType: "json",
        data: { incidentId: incidentId },
        success: function( data, textStatus, jqXHR ) {
          /* Render extended information */
          var rendered = incidentExtInfoCompiled.render( data );
          $( "#ext_infos_" + incidentId + " > td " ).html( rendered );
          $( "#ext_infos_" + incidentId ).toggle();
          $icon.removeClass( "fa-plus-square-o" ).addClass( "fa-minus-square-o" );
        }
      });
    }
  });
});
</script>
{/block}

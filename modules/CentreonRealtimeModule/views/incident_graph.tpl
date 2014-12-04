{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{t}Incident map{/t}{/block}

{block name="content"}
<div class="content-container graph">
</div>
{/block}

{block name="javascript-bottom" append}
<script>
$(function() {
  $( ".graph" ).height(
    $( "#main" ).height() - $( ".breadcrumb-bar" ).height()
  );
  $( ".graph" ).centreonIncidentsGraph({
    urlGetInfo: "{url_for url='/centreon-realtime/incident/graph'}"
  });
  $( ".graph" ).centreonIncidentsGraph( "loadIncident", {$incident_id} );
});
</script>
{/block}

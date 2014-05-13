{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{t}Issue map{/t}{/block}

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
  $( ".graph" ).centreonIssuesGraph({
    urlGetInfo: "{url_for url='/realtime/issueGraph'}"
  });
  $( ".graph" ).centreonIssuesGraph( "loadIssue", {$issue_id} );
});
</script>
{/block}

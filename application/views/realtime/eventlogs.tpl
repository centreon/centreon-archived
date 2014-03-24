{extends file="../viewLayout.tpl"}

{block name="title"}{t}Event Logs{/t}{/block}

{block name="content"}
<div class="content-container">
  <div class="row search">
  </div>
  <table class="table table-striped table-condensed" id="eventlogs">
  <thead>
    <tr>
      <th class="span-2">{t}Date{/t}</th>
      <th class="span-2">{t}Host{/t}</th>
      <th class="span-2">{t}Service{/t}</th>
      <th class="span-2">{t}Instance{/t}</th>
      <th class="span-4">{t}Message{/t}</th>
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
  $('#eventlogs').centreonTableInfiniteScroll({
    ajaxUrlGetScroll: "{url_for url="/realtime/eventlogs"}",
    ajaxUrlGetNew: "{url_for url="/realtime/eventlogs/refresh"}"
  });
});
</script>
{/block}

{extends file="file:[Core]widgetLayout.tpl"}

{block name="title"}{t}Graphs{/t}{/block}

{block name="content"}
<div class="container-fluid">
  <div class="row">
    <div class="col-xs-6 text-center">
      <strong>{t}Hosts{/t}</strong>
    </div>
    <div class="col-xs-6 text-center">
      <strong>{t}Services{/t}</strong>
    </div>
    <div class="col-xs-6">
      <div class="gauge-simple" id="hostStatus"></div>
    </div>
    <div class="col-xs-6">
      <div class="gauge-simple" id="serviceStatus"></div>
    </div>
    <div class="col-xs-6 text-center">
      {$nbHostOk} / {$nbHostTotal}
    </div>
    <div class="col-xs-6 text-center">
      {$nbServiceOk} / {$nbServiceTotal}
    </div>
  </div>
</div>
{/block}

{block name="javascript-bottom" append}
<script>
centreonGaugeSimple.generate({
  sections: [ "#FF795F", "#FEFF5F", "#B2FF5F" ],
  percent: {$hostPercent},
  element: "#hostStatus"
});
centreonGaugeSimple.generate({
  sections: [ "#FF795F", "#FEFF5F", "#B2FF5F" ],
  percent: {$servicePercent},
  element: "#serviceStatus"
});
</script>
{/block}

{extends file="file:[Core]widgetLayout.tpl"}

{block name="title"}{t}Graphs{/t}{/block}

{block name="content"}
<div class="container">
  <div class="row">
    <div class="col-xs-6 text-center">
      <strong>{t}Hosts{/t}</strong>
    </div>
    <div class="col-xs-6 text-center">
      <strong>{t}Services{/t}</strong>
    </div>
    <div class="col-xs-6 text-center">
      <input type="text"
             id="hostStatus"
             data-angleOffset="-125"
             data-angleArc="250"
             data-fgColor="#dff0d8"
             data-inputColor="#3c763d"
             data-readOnly="true"
             data-max="{$nbHostTotal}"
             value="{$nbHostOk}"
             class="dial">
    </div>
    <div class="col-xs-6 text-center">
      <input type="text"
             id="serviceStatus"
             data-angleOffset="-125"
             data-angleArc="250"
             data-fgColor="#dff0d8"
             data-inputColor="#3c763d"
             data-readOnly="true"
             data-max="{$nbServiceTotal}"
             value="{$nbServiceOk}"
             class="dial">
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
var size = 200;

size = parseInt($('.dial').parent().width() * 0.6);
$('.dial').attr('data-width', size).attr('data-height', size);
$('.dial').knob();
</script>
{/block}

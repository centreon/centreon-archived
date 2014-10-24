<div class="graph" data-serviceId="{$variables.svc_id}">
  <div class="c3" id="graph-{$variables.svc_id}" style="height: 220px;">
  </div>
</div>
<script>
$(function() {
  var endTime = moment().format( "X" ),
      startTime = endTime - 3600;
  addChart("graph-{$variables.svc_id}", {$variables.svc_id}, startTime, endTime);
});
</script>

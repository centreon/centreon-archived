{extends file="file:[Core]widgetLayout.tpl"}
{block name="content"}
<div class='centreon_table' style='padding:0;margin:0;'>
<table class="table table-condensed-more table-striped table-bordered">
<tr>
    <td colspan='3'><strong><a href="#">Top 10 CPU Usage</a></strong></td>
</tr>
{foreach $data as $item}
<tr style='height:20px  '>
    <td><span><a href='/realtime/host/{$item['host_id']}' target='_parent'><i class='fa fa-hdd-o'></i> {$item['host_name']}</a> / <a href='/realtime/service/{$item['service_id']}' target='_parent'>{$item['service_description']}</a><span></td>
    <td width='40%'>
    <div class="progress">
         <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {$item['percent']}%;">{$item['percent']}%</div>
         </div>
    </td>
    <td style='text-align:right;'>{$item['percent']}{$item['unit']}</td>
</tr>
{/foreach}
</table>
</div>
{/block}

{extends file="file:[Core]widgetLayout.tpl"}
{block name="content"}
<table class="table table-condensed-more table-striped table-bordered">
<tr>
    <td colspan='3'><strong><a href="#">Top 10 Disk Usage</a></strong></td>
</tr>
{foreach $data as $item}
<tr style='height:20px  '>
    <td><span><a href='/centreon-realtime/host/{$item['host_id']}'>{$item['host_name']}</a> - <a href='/centreon-realtime/service/{$item['service_id']}'>{$item['service_description']}</a><span></td>
    <td width='40%'>
    <div class="progress">
         <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {$item['percent']}%;">{$item['percent']}%</div>
         </div>
    </td>
    <td style='text-align:center;'>{$item['used']} / {$item['size']}</td>
</tr>
{/foreach}
</table>
{/block}

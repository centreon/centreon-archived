{extends file="file:[Core]widgetLayout.tpl"}
{block name="content"}
<div class='centreon_table' style='padding:0;margin:0;'>
<table cellpadding='0' cellspacing="0"class="table table-condensed-more table-striped DataTable">
<tr>
    <td colspan='4'><strong><a href="#">Last 10 incidents</a></strong></td>
</tr>
{foreach $data as $item}
<tr style='height:18px;' class='centreon-border-status-s-{$item["state_id"]}' role='row'>
    <td><span><a href='/realtime/host/{$item['host_id']}' target='_parent'><i class='fa fa-hdd-o'></i> {$item['name']}</a></span></td>
    <td><span><a href='/realtime/service/{$item['service_id']}' target='_parent'>{$item['description']}</a><span></td>
    <td>{$item['status']}</td>
    <td style='text-align:center;'>{$item['duration']}</td>
</tr>
{/foreach}
<tr>
    <td colspan='4' style='text-align:center;'><a href='../realtime/incident' target='_parent'>See more incidents</a></td>
</tr>
</table>
</div>{/block}

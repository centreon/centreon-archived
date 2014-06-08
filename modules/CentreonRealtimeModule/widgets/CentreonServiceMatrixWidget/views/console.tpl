{extends file="file:[Core]widgetLayout.tpl"}
{block name="content"}
<div class="angled-headers">
<table style='padding:0;margin:0;'>
<thead>
<tr>
<th></th>
{foreach $topLine as $key => $item}
<th class="skew"><div class=""><span>{$key}</span></div></th>
{/foreach}
</tr>
</thead>

<tbody>
{foreach $leftCol as $key => $item}
<tr >
<td style="text-align:right;" class='project-name'>{$key}<!--<a href='./realtime/host/{$hostID.$key}'>{$key}</a>--></td>
{foreach $data[$key] as $k => $i}
{if $i.state || $i != -1} 
<td style="background-color:{$status[$i.state]};padding:0;margin:0;"><a href='./realtime/host/{$hostID.$key}' title='{$i.output}' data-toggle="tooltip">&nbsp;</a></td>
{else}
<td style='text-align:center;'>-</td>
{/if}
{/foreach}
</tr>
{/foreach}
</tbody>
</table>
</div>
{/block}

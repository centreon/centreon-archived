{if isset($error)}
	{$error}
{else}
<div class='centreon_table' style='padding:0;margin:0;'>
	<h4 class="text-center">	
	    <span class="label label-{$state|service_color}"><i class='fa fa-hdd-o'></i> {$host} / <i class='fa fa-gear'> {$svc}</i></span>
	</h4>
	<table class="table table-striped table-condensed ">
	{foreach from=$data item=d}
		<tr>
                    <td>{$d.label}</td>
                    {if $d.label == 'Last check'}
                        <td id="last_check" data-time="{$d.value}"></td>
                    {elseif $d.label == 'Next check'}
                        <td id="next_check" data-time="{$d.value}"></td>
                    {else}
                        <td>{$d.value}</td>
                    {/if}
		</tr>
	{/foreach}
	</table>
</div>
{/if}
{hook name="displaySvcTooltipDetail" container="" params=$params}
{block name="javascript-bottom" append}
<script>

$(function() {
    $('#last_check').text(moment.unix($('#last_check').data('time')).format(sDefaultFormatDate));
    $('#next_check').text(moment.unix($('#next_check').data('time')).format(sDefaultFormatDate));
});
displayDate();
</script>
{/block}
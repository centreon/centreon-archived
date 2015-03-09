{if isset($error)}
	{$error}
{else}
<div class='centreon_table' style='padding:0;margin:0;'>
    <h4 class="text-center">
	<span class="label label-{$state|host_color}"><i class='fa fa-hdd-o'></i> {$title}</span>
    </h4>
    <table class="table table-striped table-condensed">
	{foreach from=$data item=d}
		<tr>
                        <td>{$d.label}</td>
                        {if $d.label == 'Last check' || $d.label == 'Next check'}
                            <td data-time="">{$d.value}</td>
                        {else}
                            <td>{$d.value}</td>
                        {/if}
		</tr>
	{/foreach}
    </table>
</div>
{/if}
{hook name="displayHostTooltipDetail" container="" params=$params}
{block name="javascript-bottom" append}
<script>
/*
$(function() {
    var aFieldTime = $.find('[data-time]');
    if (aFieldTime.length > 0) {
        $.each(aFieldTime, function(idx, el) {
            $(el).text(displayDate($(el).text(), 'date')+" ("+$(el).text()+")");
        });
    }
        
});
*/
displayDate();
</script>
{/block}

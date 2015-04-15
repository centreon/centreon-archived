{extends file="file:[Core]viewLayout.tpl"}
{block name="title"}
	{t}Monitoring{/t}
{/block}
{block name="content"}
    <div class="first-content"></div>
	{datatable module=$moduleName object=$objectName configuration=false datatableObject=$datatableObject}
	<div class="modal fade" role="dialog" id="modal-console">
		<div class="modal-dialog modal-lg">
			<div class="modal-content" id="modal-console-content"></div>
		</div>
	</div>
{/block}
{block name="javascript-bottom" append}
	{datatablejs module=$moduleName object=$objectName objectUrl=$objectListUrl datatableObject=$datatableObject}
        {if isset($jsUrl) }    
            <script>
                var jsUrl = {$jsUrl|json_encode};
            </script>
        {/if}
	<script>
		$(function() {
			/* we remove the empty label, for it conflicts with our action bar */
			var labelToRemove = 'label[for=datatable{$objectName}_length_select]';
			$(document).delegate(labelToRemove, 'DOMSubtreeModified', function() {
				$(labelToRemove).hide();
			});
		});
	</script>
{/block}

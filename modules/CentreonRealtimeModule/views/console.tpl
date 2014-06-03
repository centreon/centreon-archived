{extends file="file:[Core]viewLayout.tpl"}
{block name="title"}
	{t}Service monitoring{/t}
{/block}
{block name="content"}
	{datatable module=$moduleName object=$objectName configuration=false}
{/block}
{block name="javascript-bottom" append}
	{datatablejs module=$moduleName object=$objectName objectUrl=$objectListUrl}
	<script>
		$(function() {
			$(document).delegate('.rt-tooltip', 'mouseover', function() {
				var url = $(this).parent("a").attr("href");
				$(this).qtip({
					overwrite: false,
					content: {
						text: function(event, api) {
							$.ajax({
								url: url + '/tooltip'
							})
							.then(function(content) {
								api.set('content.text', content);
							}, function(xhr, status, error) {
								api.set('content.text', status + ':' + error);
							});
						}
					},
					show: {
						event: event.type,
						ready: true
					}
				});
			});
		});
	</script>
{/block}

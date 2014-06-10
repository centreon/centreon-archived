{extends file="file:[Core]viewLayout.tpl"}
{block name="title"}
	{t}Service monitoring{/t}
{/block}
{block name="content"}
	{datatable module=$moduleName object=$objectName configuration=false datatableObject=$datatableObject}
	<div class="modal fade" role="dialog" id="modal-console">
		<div class="modal-dialog modal-lg">
			<div class="modal-content" id="modal-console-content"></div>
		</div>
	</div>
{/block}
{block name="javascript-bottom" append}
	{datatablejs module=$moduleName object=$objectName objectUrl=$objectListUrl datatableObject=$datatableObject}
	<script>
		$(function() {
			/* tooltips */
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
						ready: true
					},
					style: {
						classes: 'qtip-bootstrap centreon-qtip',
						width: 'auto'
					},
					position: {
						viewport: $(window),
						adjust: {
							screen: true
						}
					}
				});
			});

			/* monitoring actions */

			/* we remove the empty label, for it conflicts with our action bar */
			var labelToRemove = 'label[for=datatable{$objectName}_length_select]';
			$(document).delegate(labelToRemove, 'DOMSubtreeModified', function() {
				$(labelToRemove).hide();
			});

			$('#datatable{$objectName}_length')
				.append(
					'<div style="margin-left: 100px;"> \
						<select id="console-action" size="1" class="form-control input-sm"> \
							<option value="0">Actions</option> \
							{foreach from=$actions item=value}
								<optgroup label="{$value.group}"> \
								{foreach from=$value.actions key=val item=label}
									<option value="{$val}">{$label}</option> \
								{/foreach}
								</optgroup> \
							{/foreach}
						</select> \
					</div>'
				);

			$('#console-action').change(function() {
				var selectedItems = [];

				$('.all{$objectName}Box:checked').each(function(index) {
					selectedItems[index] = $(this).val();
				});

				$.ajax({
					type: 'POST',
					url: 'externalcommands/' + $(this).val(),
					data: { ids: selectedItems }
				}).done(function(html) {
					$("#modal-console-content").html(html);
					$("#modal-console").modal({
						show: true
					});
				});
				$('#console-action').val(0);
			});

		});
	</script>
{/block}

<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" area-hidden="true">x</button>
	<h4>{t}Downtime{/t}<span id="command-result"></span></h4>
</div>
<div class="row-divider"></div>
<form role="form" class="form-horizontal" id="downtime-form">
	<div class="content">
		<div id="command-result"></div>
		<div class="form-group">
			<div class="col-sm-2 text-right">{t}Options{/t}</div>
			<div class="col-sm-9">
				<div class="btn-group" data-toggle="buttons">
					<label class="btn btn-primary">
						<input type="checkbox" name="fixed"> {t}Fixed{/t}
					</label>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-2 text-right">{t}Period{/t}</div>
			<div class="col-sm-9">
				<input type="text" class="form-control" name="period">
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-2 text-right">{t}Duration{/t}</div>
			<div class="col-sm-9">
				<input type="text" class="form-control" name="duration">
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-2 text-right">{t}Message{/t}</div>
			<div class="col-sm-9">
				<textarea class="form-control" rows="3" name="comment">{t}Downtime set by{/t} {$user}</textarea>
			</div>
		</div>
		<input type="hidden" name="author" value="{$user}"></input>
		{foreach from=$ids item=objectId}
			<input type="hidden" name="ids[]" value="{$objectId}"></input>
		{/foreach}
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-danger" id="cancel-btn">{t}Cancel{/t}</button>
		<button type="button" class="btn btn-success" id="send-btn">{t}Send{/t}</button>
	</div>
</form>
<script>
	$("#cancel-btn").click(function() {
		$("#modal-console").modal('hide');
	});

	$("#send-btn").click(function() {
		$.ajax({
			type: 'POST',
			url: 'externalcommands/advanced/{$cmdid}',
			data:  $('#downtime-form').serialize()
		}).done(function(result) {
			$("#command-result").html(' - ' + result.message);
			$("#cancel-btn").remove();
			$("#send-btn").html('OK');
			$("#send-btn").click(function() { $("#modal-console").modal('hide'); } );
		});	
	});

	$("input[name='period']").daterangepicker({
		timePicker: true,
		timePickerIncrement: 5,
		timePicker12Hour: false,
		format: 'YYYY-MM-DD HH:mm'
	});
</script>

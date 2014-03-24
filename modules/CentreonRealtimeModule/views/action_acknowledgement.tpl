<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" area-hidden="true">x</button>
	<h4>{t}Acknowledgement{/t}<span id="command-result"></span></h4>
</div>
<div class="row-divider"></div>
<form role="form" class="form-horizontal" id="ack-form">
	<div class="content">
		<div class="form-group">
			<div class="col-sm-2 text-right">
				{t}Options{/t}
			</div>
			<div class="col-sm-9">
				<div class="btn-group" data-toggle="buttons">
					<label class="btn btn-primary">
						<input type="checkbox" name="sticky"> {t}Sticky{/t}
					</label>
					<label class="btn btn-primary">
						<input type="checkbox" name="notify"> {t}Notify{/t}
					</label>
					<label class="btn btn-primary">
						<input type="checkbox" name="persistent"> {t}Persistent{/t}
					</label>
					<label class="btn btn-primary">
						<input type="checkbox" name="forcecheck"> {t}Force active checks{/t}
					</label>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-2 text-right">
				{t}Message{/t}
			</div>
			<div class="col-sm-9">
				<textarea class="form-control" name="comment" rows="3">{t}Acknowledged by{/t} {$user}</textarea>
			</div>
			<input type="hidden" name="author" value="{$user}"></input>
			{foreach from=$ids item=objectId}
				<input type="hidden" name="ids[]" value="{$objectId}"></input>
			{/foreach}
		</div>
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
			data:  $('#ack-form').serialize()
		}).done(function(result) {
			$("#command-result").html(' - ' + result.message);
			$("#cancel-btn").remove();
			$("#send-btn").html('OK');
			$("#send-btn").click(function() { $("#modal-console").modal('hide'); } );
		});	
	});
</script>

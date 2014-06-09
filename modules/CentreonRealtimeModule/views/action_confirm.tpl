<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" area-hidden="true">x</button>
	<h4>{t}Command result{/t}</h4>
</div>
<div class="row-divider"></div>
<div class="text-center">
	{$commandResult}
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-primary" id="confirm-btn">OK</button>
</div>
<script>
	$("#confirm-btn").click(function() {
		$("#modal-console").modal('hide');
	});
</script>

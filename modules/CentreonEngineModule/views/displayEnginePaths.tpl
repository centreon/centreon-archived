<h4 class="page-header">{t}Engine configuration{/t}</h4>
{foreach from=$variables.paths item=mypath key=k}
	<div class="form-group">
		<div class="col-sm-2" style="text-align:right;">
			<label class="label-controller" for="{$k}">{$mypath.label}</label>
		</div>
		<div class="col-sm-9">
			<span>
				<input id="{$k}" type="text" name="{$k}" value="{$mypath.value}" class="form-control">
			</span>
		</div>
		<div class="col-sm-1">
			<button id="{$k}_help" type="button" class="btn btn-info param-help" data-helptitle="{$mypath.label}" data-help="{$mypath.help}">?</button>
		</div>
	</div>
{/foreach}
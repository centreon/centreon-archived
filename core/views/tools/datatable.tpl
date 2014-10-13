<div class="first-content">
    <div class="col-sm-12 col-md-7 configuration-actions">
      <div><button class="btn btn-default" id="modalAdd">{t}Add{/t}</button></div>
      <div class="btn-group" id="selected_option" style="display: none;">
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            {t}Actions{/t}
            <span class="caret"></span>
          </button>
          <ul name="action-bar" class="dropdown-menu">
            {if $isDisableable}
                <li><a href="#" id="modalEnable">{t}Enable{/t}</a></li>
                <li><a href="#" id="modalDisable">{t}Disable{/t}</a></li>
            {/if}
            <li><a href="#" id="modalDelete">{t}Delete{/t}</a></li>
            <li><a href="#" id="modalDuplicate">{t}Duplicate{/t}</a></li>
            <li><a href="#" id="modalMassiveChange">{t}Massive change{/t}</a></li>
          </ul>
        </div>
        <div class="btn-group hidden" id="addToGroup">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            {t}Add to{/t}
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            {if isset($datatableParameters.groupname) }<li><a href="#">{$datatableParameters.groupname}</a></li>{/if}
            {if isset($datatableParameters.hasCategory) }<li><a href="#">{t}Category{/t}</a></li>{/if}
            {hook name='displayAppendAddTo' container='[hook]' params=$datatableParameters.addToHook}
          </ul>
        </div>
      </div>
    </div>
</div>

{include file="tools/datatable-table.tpl"}

<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="wizard" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    </div>
  </div>
</div>

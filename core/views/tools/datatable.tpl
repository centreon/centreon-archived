<div class="first-content">
    <div class="col-sm-12 col-md-6">
      <button class="btn btn-default" id="modalAdd">{t}Add{/t}</button>
      <div class="btn-group" id="selected_option" style="display: none;">
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            {t}Actions{/t}
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            {if $isDisableable}
                <li><a href="#" id="modalEnable">{t}Enable{/t}</a></li>
                <li><a href="#" id="modalDisable">{t}Disable{/t}</a></li>
            {/if}
            <li><a href="#" id="modalDelete">{t}Delete{/t}</a></li>
            <li><a href="#" id="modalDuplicate">{t}Duplicate{/t}</a></li>
            <li><a href="#" id="modalMassiveChange">{t}Massive change{/t}</a></li>
          </ul>
        </div>
        {if $datatableParameters.groupname || $datatableParameters.hasCategory}
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            {t}Add to{/t}
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            {if $datatableParameters.groupname }<li><a href="#">{$datatableParameters.groupname}</a></li>{/if}
            {if $datatableParameters.hasCategory}<li><a href="#">{t}Category{/t}</a></li>{/if}
          </ul>
        </div>
        {/if}
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

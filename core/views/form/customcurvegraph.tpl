<div id="{$element.name}_controls">
  <div>
    <a id="load_metrics" style="cursor: pointer;">
      {t}Load metric from database{/t} <i class="fa fa-upload"></i>
    </a>
  </div>
  <div id="{$element.name}_add" class="clone-trigger">
    <a id="{$element.name}_add_link" class="addclone" style="padding-right: 5px;cursor: pointer;">
      {t}Add a new entry{/t} <i data-action="add" class="fa fa-plus-square"></i>
    </a>
  </div>
</div>
<ul id="{$element.name}" class="clonable no-deco-list">
  <li id="{$element.name}_noforms_template">
    <p class="muted">{t}Nothing here, use the "Add" button{/t}</p>
  </li>
  <li id="{$element.name}_clone_template" class="clone_template" style="display: none;">
    <hr style="margin: 2px">
    <div class="row clone-cell">
      <div class="col-sm-1"><label class="label-controller">{t}Metric{/t}</label></div>
      <div class="col-sm-3"><input class="form-control" name="metric_id[#index#]"></div>
      <div class="col-sm-2"><input class="color-picker" name="color[#index#]"></div>
      <div class="col-sm-2"><label class="label-controller">{t}Negative{/t}</label></div>
      <div class="col-sm-3">
        <div class="btn-group btn-toggle" data-toggle="buttons">
          <label class="btn btn-sm btn-default">
            <input type="radio" name="negative[#index#]" value="1"> {t}Yes{/t}
          </label>
          <label class="btn btn-sm btn-primary active">
            <input type="radio" name="negative[#index#]" value="0" checked> {t}No{/t}
          </label>
        </div>
      </div>
    </div>
  </li>
  {foreach $metrics as $metric}
  {assign var="metricIndex" value=$metric@index+1}
  <li id="{$element.name}_clone_template" class="cloned_element" style="display: block;">
    <hr style="margin: 2px">
    <div class="row clone-cell">
      <div class="col-sm-1"><label class="label-controller">{t}Metric{/t}</label></div>
      <div class="col-sm-3"><input class="form-control" name="metric_id[{$metricIndex}]" value="{$metric['metric_name']}"></div>
      <div class="col-sm-2"><input class="color-picker" name="color[{$metricIndex}]" value="{$metric['color']}"></div>
      <div class="col-sm-2"><label class="label-controller">{t}Negative{/t}</label></div>
      <div class="col-sm-3">
        <div class="btn-group btn-toggle" data-toggle="buttons">
          <label class="btn btn-sm btn-default">
            <input type="radio" name="negative[{$metricIndex}]" value="1"{if $metric['is_negative'] == 1} checked{/if}> {t}Yes{/t}
          </label>
          <label class="btn btn-sm btn-primary active">
            <input type="radio" name="negative[{$metricIndex}]" value="0"{if $metric['is_negative'] == 0} checked{/if}> {t}No{/t}
          </label>
        </div>
      </div>
    </div>
  </li>
  {/foreach}
</ul>

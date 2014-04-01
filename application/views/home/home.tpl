{extends file="../viewLayout.tpl"}

{block name=title}Home{/block}

{block name=content}
<div class="first-content">
    <div class="btn-group custom-view-actions">
        <button type="button" class="btn btn-default btn-sm">
            <span class="fa fa-plus" id="view_add"> {t}New{/t}</span>
        </button>
        <button type="button" class="btn btn-default btn-sm">
            <span class="fa fa-gears" id="view_settings"> {t}Settings{/t}</span>
        </button>
        <button type="button" class="btn btn-default btn-sm">
            <span class="fa fa-trash-o" id="view_delete"> {t}Delete{/t}</span>
        </button>
        <button type="button" class="btn btn-default btn-sm">
            <span class="fa fa-star" id="view_default"> {t}Set default{/t}</span>
        </button>
        <button type="button" class="btn btn-default btn-sm">
            <span class="fa fa-tag" id="view_tag"> {t}Tag{/t}</span>
        </button>
        <button type="button" class="btn btn-default btn-sm">
            <span class="fa fa-plus" id="view_widget"> {t}Add widget{/t}</span>
        </button>
        <button type="button" class="btn btn-default btn-sm">
            <span class="fa fa-play" id="view_rotation"> {t}Rotation{/t}</span>
        </button>
        <span class="custom-tag-label"><button type="button" class="btn btn-primary btn-sm">Tags <span class="badge">4</span></button></span>
        <span class="label label-primary custom-owner-label">{t}This view was created by admin{/t}</span>
    </div>
</div>
<hr/>
<div class="gridster">
<ul>
{foreach item=widget from=$widgets}
    <li data-row="1" data-col="1" data-sizex="5" data-sizey="1">
        <div class="portlet-header bg-primary">
            <span class="widgetTitle">
                <span>test</span>
                <span class="portlet-ui-icon">
                    <i class="fa fa-refresh"></i>
                    <i class="fa fa-gears"></i>
                    <i class="fa fa-trash-o"></i>
                </span>
            </span>
        </div>
        <div class="portlet-content">test</div>
    </li>
{/foreach}
</ul>
</div>
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="wizard" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content"></div>
    </div>
</div>
{/block}

{extends file="file:[Core]viewLayout.tpl"}

{block name=title}Home{/block}

{block name=content}
<div class="first-content">
    <div class="btn-group custom-view-actions">
        <button type="button" class="btn btn-default btn-sm" id="view_add">
            <span class="fa fa-plus"> {t}New{/t}</span>
        </button>
        <button type="button" class="btn btn-default btn-sm" id="view_settings">
            <span class="fa fa-gears"> {t}Settings{/t}</span>
        </button>
        <button type="button" class="btn btn-default btn-sm" id="view_delete">
            <span class="fa fa-trash-o"> {t}Delete{/t}</span>
        </button>
        <button type="button" class="btn btn-default btn-sm" id="view_default">
            <span class="fa fa-star"> {t}Set default{/t}</span>
        </button>
        <button type="button" class="btn btn-default btn-sm" id="view_bookmark">
            <span class="fa fa-tag"> {t}Bookmark{/t}</span>
        </button>
        <button type="button" class="btn btn-default btn-sm" id="view_widget">
            <span class="fa fa-plus"> {t}Add widget{/t}</span>
        </button>
        <button type="button" class="btn btn-default btn-sm" id="view_rotation">
            <span class="fa fa-play"> {t}Rotation{/t}</span>
        </button>
        <button type="button" class="btn btn-default btn-sm" id="view_filters">
            <span class="fa fa-search"> {t}Filters{/t}</span>
        </button>
        <button type="button" class="btn btn-default btn-sm" id="view_save">
            <span class="fa fa-save"> {t}Save{/t}</span>
        </button>
        <!--
        <span class="custom-tag-label">
            <button type="button" class="btn btn-primary btn-sm">
                Tags
                <span class="badge custom-view-badge">4</span>
            </button>
        </span>
        <span class="label label-primary custom-owner-label">{t}This view was created by admin{/t}</span>
        -->
    </div>
</div>
<div class="filter_zone" style="display:none;">
    <hr/>
    <div class="container">
        <div class="row" id="filter-zone">
            {$filterHtml}
            <div class="col-md-1">
                <button type="button" class="btn btn-default" id="add-filter">
                    <span class="fa fa-search-plus"> </span>
                </button>
                <button type="button" class="btn btn-success" id="apply-filter">
                    <span class="fa fa-check"> {t}Apply{/t}</span>
                </button>
            </div>
        </div>
    </div>
</div>
<hr/>
<div class="gridster"><ul></ul></div>
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="wizard" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content"></div>
    </div>
</div>
{/block}
{block name="javascript-bottom" append}
<script>
    $(function() {
        $("#view_filters").click(function() {
            $(".filter_zone").toggle('slow');
        });
        
        $("#filter-zone").delegate('.remove-filter', 'click', function() {
            var p = $(this).parent();
            $(p).hide('slow', function() {
                $(p).remove();
            });
        });

        $("#add-filter").click(function() {
            $('{$filterHtmlForJs}').insertBefore($(this).parent());
        });

        $("#apply-filter").click(function() {
            var filterNames = new Array();
            var filterValues = new Array();
            var filterCmp = new Array();

            $(".filter-name").each(function(index) {
                filterNames[index] = $(this).val();
            });
            $(".filter-value").each(function(index) {
                filterValues[index] = $(this).val();
            });
            $(".filter-cmp").each(function(index) {
                filterCmp[index] = $(this).val();
            });
            $.ajax({
                type: 'POST',
                url: '/centreon-customview/applyfilters',
                data: {
                    'filterNames': JSON.stringify(filterNames),
                    'filterValues': JSON.stringify(filterValues),
                    'filterCmp': JSON.stringify(filterCmp)
                },
                success: function() {
                    //@todo reload all widgets 
                    $('.portlet-content').each(function() {
                        $(this).attr('src', $(this).attr('src'))
                    });
                }
            });
        });
    });
</script>
{/block}

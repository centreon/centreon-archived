<li>
<a href="#">
        <i class="fa fa-bookmark"></i>
        <span class="nav-label">{t}Bookmarked views{/t}</span>
        {if count($variables.bookmarkedViews) > 0}
            <span class="fa arrow"></span>
        {/if}
 </a>
<ul class="nav nav-second-level">
    {foreach from=$variables.bookmarkedViews item=view key=k}
    <li>
        {assign var=view_url value="/centreon-customview/"|cat:$view.custom_view_id}
        <a href="{url_for url=$view_url}">{$view.name}</a>
    </li>
    {/foreach}
</ul>
</li>
<li>
    <a href="#">
        <i class="fa fa-users"></i>
        <span class="nav-label">{t}Public views{/t}</span>
        {if count($variables.publicViews) > 0}
            <span class="fa arrow"></span>
        {/if}
    </a>
    <ul class="nav nav-second-level">
        {foreach from=$variables.publicViews item=view}
        <li>
            {assign var=view_url value="/centreon-customview/"|cat:$view.custom_view_id}
            <a href="{url_for url=$view_url}">
                <span>{$view.name}</span>
            </a>
        </li>
        {/foreach}
    </ul>
</li>

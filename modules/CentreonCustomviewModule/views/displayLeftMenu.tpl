<li>
    <a class="accordion-toggle collapsed">
        <i></i>
        <span>{t}Bookmarked views{/t}</span>
    </a>
    <ul class="nav submenu collapse in" style="height: auto;">
        {foreach from=$variables.bookmarkedViews item=view key=k}
        <li>
            {assign var=view_url value="/centreon-customview/"|cat:$view.custom_view_id}
            <a href="{url_for url=$view_url}">
                <i></i>
                <span>{$view.name}</span>
            </a>
        </li>
        {/foreach}
    </ul>
</li>
<li>
    <a class="accordion-toggle collapsed">
        <i></i>
        <span>{t}Public views{/t}</span>
    </a>
    <ul class="nav submenu collapse in" style="height: auto;">
        {foreach from=$variables.publicViews item=view}
        <li>
            {assign var=view_url value="/centreon-customview/"|cat:$view.custom_view_id}
            <a href="{url_for url=$view_url}">
                <i></i>
                <span>{$view.name}</span>
            </a>
        </li>
        {/foreach}
    </ul>
</li>

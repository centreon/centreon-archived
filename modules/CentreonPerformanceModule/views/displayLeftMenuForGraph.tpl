<li>
    <a class="accordion-toggle collapsed">
        <i class="fa fa-bookmark"></i>
        <span>{t}Bookmarked views{/t}</span>
    </a>
    <ul class="nav submenu collapse in" style="height: auto;">
        {foreach from=$variables.bookmarkedGraphs item=view key=k}
        <li>
            {assign var=view_url value="/centreon-performance/graph"}
            <a href="{url_for url=$view_url}?quick-access-graph={$k}">
                <i></i>
                <span>{$view}</span>
            </a>
        </li>
        {/foreach}
    </ul>
</li>

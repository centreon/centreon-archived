{extends file="../baseLayout.tpl"}

{block name=title}Test Add Hidden{/block}

{block name=appMenu}
    My Menu
{/block}

{block name=appContent}
    {$form.hidden}
{/block}
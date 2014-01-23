{extends file="../baseLayout.tpl"}

{block name=title}Test Add Radio{/block}

{block name=appMenu}
    My Menu
{/block}

{block name=appContent}
    {$form.hidden}
    {$form.testClassiqueRadio.html}
{/block}
{extends file="../baseLayout.tpl"}

{block name=title}Test Add Radio Group{/block}

{block name=appMenu}
    My Menu
{/block}

{block name=appContent}
    {$form.hidden}
    {$form.testClassiqueRadio.html}
{/block}
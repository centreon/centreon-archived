{extends file="../baseLayout.tpl"}

{block name=title}Test Add Button{/block}

{block name=appMenu}
    My Menu
{/block}

{block name=appContent}
    {$form.hidden}
    {$form.testClassiqueInput.html}
{/block}
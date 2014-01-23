{extends file="../baseLayout.tpl"}

{block name=title}Test Add Simple Select{/block}

{block name=appMenu}
    My Menu
{/block}

{block name=appContent}
    {$form.hidden}
    {$form.testClassiqueSelect.html}
{/block}

{extends file="../baseLayout.tpl"}

{block name=title}Test Add Text{/block}

{block name=appMenu}
    My Menu
{/block}

{block name=appContent}
    {$form.testClassiqueInput.html}
{/block}
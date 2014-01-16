<html>
    
    <head>
        <title>Centreon - {block name=title}{/block}</title>
        {foreach from=$cssFileList item='cssFile'}
            {$cssFile|css}
        {/foreach}
        {foreach from=$jsTopFileList item='jsFile'}
            {$jsFile|js}
        {/foreach}
    </head>
    
    <body>
        
        <div id="appLayout">
            
            <div id="appHeader">
                My Header
            </div>

            <div id="appBody">
                <div id="appLeftPanel">{block name=appMenu}{/block}</div>
                <div id="appRightPanel">{block name=appContent}{/block}</div>
            </div>

            <div id="appFooter">
                My Footer
            </div>
            
            {foreach from=$jsBottomFileList item='jsFile'}
                {$jsFile|js}
            {/foreach}
        
        </div>
        
    </body>
    
</html>

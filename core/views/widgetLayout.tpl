<!DOCTYPE html>
<html>
<head>
    <title>Centreon widget</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{url_static url='/centreon/img/favicon_centreon.ico'}" type="image/x-icon">
    {foreach from=$cssFileList item='cssFile'}
    {$cssFile|css}
    {/foreach}
</head>
<body>
<div id="wrapper">
  {block name="content"}
  {/block}
</div>
{foreach from=$jsBottomFileList item='jsFile'}
{$jsFile|js}
{/foreach}
{block name="javascript-bottom"}
<script>{get_custom_js}</script>
{/block}
</body>
</html>

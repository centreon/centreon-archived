<!DOCTYPE html>
<html>
<head>
    <title>{t}Internal Server Error{/t} - Centreon : IT Monitoring</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{url_static url='/centreon/img/favicon_centreon.ico'}" type="image/x-icon">
    {foreach from=$cssFileList item='cssFile'}
    {$cssFile|css}
    {/foreach}
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <h2>Internal server error</h2>
        </div>
    </div>
</div>
</body>
</html>

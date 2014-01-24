<!DOCTYPE html>
<html>
<head>
    <title>{t}Log in{/t} - Centreon : IT Monitoring</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="" type="image/x-icon">
    {foreach from=$cssFileList item='cssFile'}
    {$cssFile|css}
    {/foreach}
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-sm-4 col-sm-offset-4">
            <div class="panel panel-default panel-login"> 
                <form action="" method="POST" role="form">
                <input type="hidden" name="csrf" value="{$csrf}">
                <!-- <div class="panel-heading">
                    <h3 class="panel-title">{t}Log in{/t}</h3>
                </div>-->
                <div class="panel-body">
                    <div class="form-group">
                        <label for="login">{t}Login{/t}</label>
                        <div class="input-group">
                            <input type="login" class="form-control" id="login" placeholder="Login">
                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="passwd">{t}Password{/t}</label>
                        <div class="input-group">
                            <input type="passwd" class="form-control" id="passwd" placeholder="Password">
                            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="buttons">
                        <button type="submit" name="submit" class="btn btn-login pull-right">{t}Log in{/t}</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
{foreach from=$jsBottomFileList item='jsFile'}
{$jsFile|js}
{/foreach}
</body>
</html>

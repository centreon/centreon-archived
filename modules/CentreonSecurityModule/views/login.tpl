<!DOCTYPE html>
<html>
<head>
    <title>{t}Log in{/t} - Centreon : IT Monitoring</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{url_static url='/centreon/img/favicon_centreon.ico'}" type="image/x-icon">
    {foreach from=$cssFileList item='cssFile'}
    {$cssFile|css}
    {/foreach}
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-sm-4 col-sm-offset-4 login-box">
            <div class="login-title">{t}Centreon{/t}</div>
            <div class="panel panel-default panel-login"> 
                <form action="" method="POST" role="form">
                <input type="hidden" name="csrf" value="{$csrf}">
                <input type="hidden" name="redirect" value="{$redirect}">
                <div class="panel-body">
                    <div class="alert alert-danger" style="display: none;" id="login_error"></div>
                </div>
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
                            <input type="password" class="form-control" id="passwd" placeholder="Password">
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
<script>
function logIn() {
    $.ajax({
        url: "{url_for url='/login'}",
        type: "POST",
        dataType: "json",
        data: {
            login: $("#login").val(),
            passwd: $("#passwd").val(),
            csrf: $("input[name='csrf']").val()
        },
        success: function(data, textStatus, jqXHR) {
            if (data.status) {
                if ('{$base_url}' == '{$redirect}') {
                    window.location.href = data.redirectRoute;
                } else {
                    window.location.href = '{$redirect}';
                }
            } else {
                $("#login_error").text(data.error).show();
            }
        }
    });
}

$(function() {
    $("form").on('submit', function(e) {
        e.preventDefault();
        logIn();
    });

    $("#login").focus();
});
</script>
</body>
</html>

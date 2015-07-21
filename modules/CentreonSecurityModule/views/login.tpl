<!DOCTYPE html>
<html class="loginBg">
<head>
    <title>{t}Log in{/t} - Centreon : IT Monitoring</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{url_static url='/centreon/img/centreonFavicon.ico'}" type="image/x-icon">
    {foreach from=$cssFileList item='cssFile'}
    {$cssFile|css}
    {/foreach}
</head>
<body>
<div class="login-wrap">
  <div class="container">
          <div class="login-title">
            <img  src="{url_static url='centreon/img/centreon.png'}" alt="Centreon Logo" />

            <h2 class="hidden">{t}Centreon{/t}</h2>
          </div>
          <form action="" method="POST" role="form" class="CentreonForm login-box">
            <div class="panel panel-default panel-login">

                <input type="hidden" name="redirect" value="{$redirect}">
                <div class="panel-body">
                    <div class="form-group">
                        <div class="alert alert-danger alertLogin" style="display: none;" id="login_error"></div>

                        <label for="login" class="hidden">{t}Login{/t}</label>
                        <div class="input-group">
                            <input type="login" class="form-control" id="login" placeholder="Login">
                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="passwd" class="hidden">{t}Password{/t}</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="passwd" placeholder="Password">
                            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                        </div>
                    </div>

                    <div class="buttons">
                        <button type="submit" name="submit" class="btnC btnInfo pull-right">{t}Log in{/t}</button>
                    </div>
                </div>

            </div>
            <div class='login-footer'>&copy; Centreon 2015 | <a href='http://www.centreon.com/' target='_blank'>Centreon 3.0</a></div>
          </form>
  </div>
</div>
{foreach from=$jsBottomFileList item='jsFile'}
{$jsFile|js}
{/foreach}
<script>
var formValidRule = [];

function logIn() {
    $.ajax({
        url: "{url_for url='/login'}",
        type: "POST",
        dataType: "json",
        data: {
            login: $("#login").val(),
            passwd: $("#passwd").val(),
        },
        success: function(data, textStatus, jqXHR) {
            if(!isJson(data)){
                alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
                return false;
            }
            if (data.status) {
                if ('{$base_url}' == '{$redirect}') {
                    window.location.href = data.redirectRoute;
                } else {
                    window.location.href = '{$redirect}';
                }
            } else {
                $("#login_error").text(data.error).show();
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          if (jqXHR.status == '403') {
            $("#login_error").text(jqXHR.responseJSON.message).show();
          } else {
            $("#login_error").text("An error when login").show();
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

    $(".login-wrap").height($(window).height());
});
</script>
</body>
</html>

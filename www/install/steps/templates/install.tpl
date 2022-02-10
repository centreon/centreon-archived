<!DOCTYPE html>
<head>
    <title>{t}Centreon Installation{/t}</title>
    <link rel="shortcut icon" href="../img/favicon.ico">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta name="Generator" content="Centreon - Copyright (C) 2005 - 2017 Open Source Matters. All rights reserved."/>
    <meta name="robots" content="index, nofollow"/>
    <link rel="stylesheet" href="../Themes/Generic-theme/style.css" type="text/css">
    <link rel="stylesheet" href="./install.css" type="text/css">
    <link rel="stylesheet" href="./pub_install.css" type="text/css">
    <script type="text/javascript" src="../include/common/javascript/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="../include/common/javascript/jquery/jquery-ui.js"></script>
    <script type="text/javascript">jQuery.noConflict();</script>
    <script type='text/javascript'>
        {literal}
        loadStep('stepContent');

        function loadStep(step = 'stepContent') {
            jQuery.ajax({
                method: 'GET',
                url: `./steps/step.php?action=${step}`,
                success: (data) => jQuery('#installationContent').html(data),
            });
        }
        {/literal}
    </script>
</head>
<body rightmargin="0" topmargin="0" leftmargin="0">
<div id='installationFrame'>
    <div id='installationContent'></div>
</div>
</body>
</html>
<?php
if (strlen(session_id()) < 1) {
    session_start();
}
$step = 1;
if (isset($_SESSION['step'])) {
    $step = $_SESSION['step'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <title><?php echo _('Centreon Installation');?></title>
    <link rel="shortcut icon" href="../img/favicon.ico">
    <link rel="stylesheet" href="./install.css" type="text/css">
    <script type="text/javascript" src="../include/common/javascript/jquery/jquery.js"></script>
    <script type="text/javascript" src="../include/common/javascript/jquery/jquery-ui.js"></script>
    <script type="text/javascript">jQuery.noConflict();</script>
    <script type='text/javascript'>
        jQuery(function() {
            var curstep = <?php echo $step;?>;
            jQuery('#installationContent').load('./step_upgrade/step'+curstep+'.php');
        });

        /**
        * Go back to previous page
        *
        * @param int stepNumber
        * @return void
        */
        function jumpTo(stepNumber) {
            jQuery('#installationContent').load('./step_upgrade/step'+stepNumber+'.php');
        }

        /**
        * Do background process
        *
        * @param boolean async
        * @param string url
        * @param array data
        * @param function callbackOnSuccess
        * @return void
        */
        function doProcess(async, url, data, callbackOnSuccess) {
            jQuery.ajax({
                type: 'POST',
                url: url,
                data: data,
                success: callbackOnSuccess,
                async: async
            });
        }
    </script>
</head>
<body rightmargin="0" topmargin="0" leftmargin="0">
    <div id='installationFrame'>
        <div id='installationContent'></div>
    </div>
</body>
</html>

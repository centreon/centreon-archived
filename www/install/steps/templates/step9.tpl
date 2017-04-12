
<div id="installPub">
    <div>{t}Congratulations, you have successfully installed Centreon!{/t}</div>
</div>

<script type="text/javascript">
    {literal}
    function pubcallback(html) {
        jQuery("#installPub").html(html);
    }

    jQuery(document).ready(function() {
        jQuery.ajax({
            url: 'https://advertising.centreon.com/centreon-2.8.1/pub.json',
            type: 'GET',
            dataType: 'jsonp',
            crossDomain: true
        });
    });

    function validation() {
        jQuery.ajax({
            type: 'POST',
            url: './steps/process/process_step9.php'
        }).success(function (data) {
            var data = JSON.parse(data);
            if (data.result) {
                javascript:self.location="../main.php";
            }
        });
    }

    {/literal}
</script>
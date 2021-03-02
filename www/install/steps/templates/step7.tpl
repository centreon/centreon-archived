{t}Currently installing database and generating cache... please do not interrupt this process.{/t}<br/><br/>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="StyleDottedHr" align="center">
    <thead>
    <tr>
        <th>{t}Step{/t}</th>
        <th>{t}Status{/t}</th>
    </tr>
    </thead>
    <tbody id="step_contents">
    </tbody>
</table>

<script type="text/javascript">

    {literal}

    var dbSteps = {
        'configfile': {
            'file': './steps/process/configFileSetup.php',
            'label': '{/literal}{t}Setting up configuration file{/t}{literal}'
        },
        'dbconf': {
            'file': './steps/process/installConfigurationDb.php',
            'label': '{/literal}{t}Configuration database{/t}{literal}'
        },
        'dbstorage': {
            'file': './steps/process/installStorageDb.php',
            'label': '{/literal}{t}Storage database{/t}{literal}'
        },
        'createuser': {
            'file': './steps/process/createDbUser.php',
            'label': '{/literal}{t}Creating database user{/t}{literal}'
        },
        'baseconf': {
            'file': './steps/process/insertBaseConf.php',
            'label': '{/literal}{t}Setting up basic configuration{/t}{literal}'
        },
        'dbpartitioning': {
            'file': './steps/process/partitionTables.php',
            'label': '{/literal}{t}Partitioning database tables{/t}{literal}'
        },
        'generationCache': {
            'file': './steps/process/generationCache.php',
            'label': '{/literal}{t}Generating application cache{/t}{literal}'
        }
    };

    jQuery(function() {
        jQuery("input[type=button]").hide();
        nextInstallStep('configfile');
    });

    function nextInstallStep(key) {
        jQuery('#step_contents').append(
            '<tr><td>' + dbSteps[key].label + '</td><td style="font-weight: bold;" id="' + key + '"><img src="../img/misc/ajax-loader.gif"></td></tr>'
        );

        jQuery.ajax({
            type: 'POST',
            url: dbSteps[key].file,
            success: (response) => {
                var data = jQuery.parseJSON(response);
                if (data['result'] == 0) {
                    jQuery('#' + data['id']).html('<span style="color:#88b917;">OK</span>');
                    if (key == 'configfile') {
                        nextInstallStep('dbconf');
                    } else if (key == 'dbconf') {
                        nextInstallStep('dbstorage');
                    } else if (key == 'dbstorage') {
                        nextInstallStep('createuser');
                    } else if (key == 'createuser') {
                        nextInstallStep('baseconf');
                    } else if (key == 'baseconf') {
                        nextInstallStep('dbpartitioning');
                    } else if (key == 'dbpartitioning') {
                        nextInstallStep('generationCache');
                    } else if (key == 'generationCache') {
                        jQuery("#next").show();
                    }
                } else {
                    jQuery("#previous").show();
                    jQuery("#refresh").show();
                    jQuery('#'+data['id']).html('<span style="color:#8B0000;">' + data['msg'] + '</span>');
                }
            }
        });
    }

    function validation() {
        return true;
    }

    {/literal}

</script>

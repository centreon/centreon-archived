<script type="text/javascript">
    {literal}

    jQuery(document).ready(function() {
        manageLVMBackupFields();
        jQuery('input[name="backup_database_type[backup_database_type]"]').change(function() {
            manageLVMBackupFields();
        });
    });

    function manageLVMBackupFields() {
        var backup_type = jQuery('input[name="backup_database_type[backup_database_type]"]:checked').val();
        var partial_backup = jQuery('img[name="tip_backup_database_partial"]').closest('tr');
        if (backup_type == 1) {
            partial_backup.show();
        } else {
            partial_backup.hide();
        }
    }

    {/literal}
</script>
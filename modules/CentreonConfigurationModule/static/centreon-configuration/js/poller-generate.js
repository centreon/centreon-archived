$(function() {
    $("ul[name=action-bar]").append('<li><a href="#" id="modalApplyConf">Apply configuration</a></li>');

    $('#modalApplyConf').on('click', function(e) {
        $('#modal').removeData('bs.modal');
        $('#modal').removeData('centreonWizard');
        $('#modal .modal-content').text('');
        $('#modal').one('loaded.bs.modal', function(e) {
            $(this).centreonWizard();
        });
        $('#modal').modal({
            'remote': 'poller/applycfg'
        });
    });
});

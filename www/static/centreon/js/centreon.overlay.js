$(function() {
    /* Real time data */
    $(document).delegate('.overlay', 'mouseover', function() {
        var overlayurl = $(this).parent().data('overlay-url');
        $(this).qtip({
            overwrite: false,
            content: {
                text: function(event, api) {
                    $.ajax({
                        url: overlayurl
                    })
                    .then(function(content) {
                        api.set('content.text', content);
                    }, function(xhr, status, error) {
                        api.set('content.text', status + ':' + error);
                    });
                }
            },
            show: { ready: true },
            style: {
                classes: 'qtip-bootstrap',
                width: 'auto'
            },
            position: {
                viewport: $(window),
                adjust: {
                    screen: true
                }
            }
        });
    });
});

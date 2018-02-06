/* Refresh session every 15 seconds*/
setInterval(function(){
    jQuery.ajax({
        method: 'GET',
        url: './api/internal.php?object=centreon_keepalive&action=keepalive'
    });
},15000);

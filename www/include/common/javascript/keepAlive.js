/* Refresh session every 15 seconds*/
setInterval(function(){
    var xhttp = new XMLHttpRequest();
    xhttp.open('GET', './api/internal.php?object=centreon_keepalive&action=keepalive', true);
    xhttp.send();
},15000);

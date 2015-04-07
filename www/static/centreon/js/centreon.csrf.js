/* Configure ajaxHeader for csrf */
$.ajaxSetup({
   beforeSend: function (xhr) {
     var token = "";
     var cookies = document.cookie.split(";");
     for (var i = 0; i < cookies.length; i++) {
       cookie = cookies[i];
       while (cookie.charAt(0) == " ") cookie = cookie.substring(1);
       if (cookie.indexOf("XSRF-TOKEN=") === 0) {
         token = cookie.substring(11 ,cookie.length);
       }
     }
     xhr.setRequestHeader("x-csrf-token", token);
   }
});

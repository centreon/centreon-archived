/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

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

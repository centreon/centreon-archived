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

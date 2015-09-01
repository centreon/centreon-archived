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

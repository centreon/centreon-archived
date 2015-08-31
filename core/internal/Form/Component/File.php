<?php
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
 */

namespace Centreon\Internal\Form\Component;

use Centreon\Internal\Di;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class File extends Component
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        $tpl = Di::getDefault()->get('template');
        
        // Load CssFile
        $tpl->addCss('jquery.fileupload.css')
            ->addCss('centreon-wizard.css');

        // Load JsFile
        $tpl->addJs('tmpl.min.js')
            ->addJs('load-image.min.js')
            ->addJs('canvas-to-blob.min.js')
            ->addJs('jquery.fileupload.js')
            ->addJs('jquery.fileupload-process.js')
            ->addJs('jquery.fileupload-image.js')
            ->addJs('jquery.fileupload-validate.js')
            ->addJs('jquery.fileupload-ui.js')
            ->addJs('centreon-wizard.js');
        
        $uploadUrl = Di::getDefault()
                            ->get('router')
                            ->getPathFor('/file/upload');
        
        $fileInputHtml = '
            <div id="fileupload">
                <div class="row fileupload-buttonbar">
                    <div class="col-sm-3">
                        <!-- The fileinput-button span is used to style the file input field as button -->
                        <span class="btn btn-success btn-sm fileinput-button">
                            <i class="glyphicon glyphicon-plus"></i>
                            <span>Add files...</span>
                            <input type="file" name="centreonUploadedFile" multiple>
                        </span>
                    </div>
                    <div class="col-sm-3">
                        <button type="button" class="btn btn-primary btn-sm start">
                            <i class="glyphicon glyphicon-upload"></i>
                            <span>Start upload</span>
                        </button>
                    </div>
                    <div class="col-sm-3">
                        <button type="reset" class="btn btn-warning btn-sm cancel">
                            <i class="glyphicon glyphicon-ban-circle"></i>
                            <span>Cancel upload</span>
                        </button>
                    </div>
                    <div class="col-sm-3">
                        <button type="button" class="btn btn-danger btn-sm delete">
                            <i class="glyphicon glyphicon-trash"></i>
                            <span>Delete</span>
                        </button>
                        <input type="checkbox" class="toggle">
                        <!-- The global file processing state -->
                        <span class="fileupload-process"></span>
                    </div>
                </div>
                <!-- The global progress state -->

                <div class="row">
                    <div class="col-sm-12">
                        <div class="fileupload-progress fade">
                            <!-- The global progress bar -->
                            <div class="progress progress-striped active" 
                                role="progressbar" aria-valuemin="0" aria-valuemax="100">
                                <div id="progress" class="progress-bar progress-bar-success bar" style="width:0%;">
                                </div>
                            </div>
                            <!-- The extended global progress state -->
                            <div class="progress-extended">&nbsp;</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <!-- The table listing the files available for upload/download -->
                        <table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
                    </div>
                </div>

            </div>


            <!-- The template to display files available for upload -->
            <script id="template-upload" type="text/x-tmpl">
            {% for (var i=0, file; file=o.files[i]; i++) { %}
                <tr class="template-upload fade">
                    <td>
                        <span class="preview"></span>
                    </td>
                    <td>
                        <p class="name">{%=file.name%}</p>
                        <strong class="error text-danger"></strong>
                    </td>
                    <td>
                        <p class="size">Processing...</p>
                        <div 
                            class="progress progress-striped active" 
                            role="progressbar" 
                            aria-valuemin="0" 
                            aria-valuemax="100" 
                            aria-valuenow="0">
                            <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                        </div>
                    </td>
                    <td>
                        {% if (!i && !o.options.autoUpload) { %}
                            <button class="btn btn-primary btn-sm start" disabled>
                                <i class="glyphicon glyphicon-upload"></i>
                                <span>Start</span>
                            </button>
                        {% } %}
                        {% if (!i) { %}
                            <button class="btn btn-warning btn-sm cancel">
                                <i class="glyphicon glyphicon-ban-circle"></i>
                                <span>Cancel</span>
                            </button>
                        {% } %}
                    </td>
                </tr>
            {% } %}
            </script>
            
            <!-- The template to display files available for download -->
            <script id="template-download" type="text/x-tmpl">
            {% for (var i=0, file; file=o.files[i]; i++) { %}
                <tr class="template-download fade">
                    <td>
                        <span class="preview">
                            {% if (file.thumbnailUrl) { %}
                                <a 
                                    href="{%=file.url%}" 
                                    title="{%=file.name%}" 
                                    download="{%=file.name%}" 
                                    data-gallery
                                >
                                    <img src="{%=file.thumbnailUrl%}">
                                </a>
                            {% } %}
                        </span>
                    </td>
                    <td>
                        <p class="name">
                            {% if (file.url) { %}
                                <a 
                                    href="{%=file.url%}" 
                                    title="{%=file.name%}" 
                                    download="{%=file.name%}" 
                                    {%=file.thumbnailUrl?\'data-gallery\':\'\'%}
                                >
                                    {%=file.name%}
                                </a>
                            {% } else { %}
                                <span>{%=file.name%}</span>
                            {% } %}
                        </p>
                        {% if (file.error) { %}
                            <div><span class="label label-danger">Error</span> {%=file.error%}</div>
                        {% } %}
                    </td>
                    <td>
                        <span class="size">{%=o.formatFileSize(file.size)%}</span>
                    </td>
                    <td>
                        {% if (file.deleteUrl) { %}
                            <button 
                                class="btn btn-danger btn-sm delete" 
                                data-type="{%=file.deleteType%}" 
                                data-url="{%=file.deleteUrl%}"
                                {% if (file.deleteWithCredentials) {
                                    %} data-xhr-fields=\'{"withCredentials":true}\'{% } 
                                %}
                            >
                                <i class="glyphicon glyphicon-trash"></i>
                                <span>Delete</span>
                            </button>
                            <input type="checkbox" name="delete" value="1" class="toggle">
                        {% } else { %}
                            <button class="btn btn-warning btn-sm cancel">
                                <i class="glyphicon glyphicon-ban-circle"></i>
                                <span>Cancel</span>
                            </button>
                        {% } %}
                    </td>
                </tr>
            {% } %}
            </script>';
        
        $fileUploadJs = '$("#fileupload").fileupload({
							url: "'.$uploadUrl.'",
						});
                        
                        $("#fileupload").fileupload("option", {
                            url: "'.$uploadUrl.'",
                            // Enable image resizing, except for Android and Opera,
                            // which actually support image resizing, but fail to
                            // send Blob objects via XHR requests:
                            disableImageResize: /Android(?!.*Chrome)|Opera/
                                .test(window.navigator.userAgent),
                            maxFileSize: 5000000,
                            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i
                        });';
        
        return array(
            'html' => $fileInputHtml,
            'js' => $fileUploadJs
        );
    }
}

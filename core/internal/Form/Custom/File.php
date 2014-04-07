<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */
namespace Centreon\Internal\Form\Custom;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Core
 */
class File extends Customobject
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        $tpl = \Centreon\Internal\Di::getDefault()->get('template');
        
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
        
        $uploadUrl = \Centreon\Internal\Di::getDefault()
                            ->get('router')
                            ->getPathFor('/file/upload');
        
        $fileInputHtml = '
            <div id="fileupload">
                <div class="row fileupload-buttonbar">
                    <div class="col-sm-3">
                        <!-- The fileinput-button span is used to style the file input field as button -->
                        <span class="btn btn-success fileinput-button">
                            <i class="glyphicon glyphicon-plus"></i>
                            <span>Add files...</span>
                            <input type="file" name="centreonUploadedFile" multiple>
                        </span>
                    </div>
                    <div class="col-sm-3">
                        <button type="button" class="btn btn-primary start">
                            <i class="glyphicon glyphicon-upload"></i>
                            <span>Start upload</span>
                        </button>
                    </div>
                    <div class="col-sm-3">
                        <button type="reset" class="btn btn-warning cancel">
                            <i class="glyphicon glyphicon-ban-circle"></i>
                            <span>Cancel upload</span>
                        </button>
                    </div>
                    <div class="col-sm-3">
                        <button type="button" class="btn btn-danger delete">
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
                            <button class="btn btn-primary start" disabled>
                                <i class="glyphicon glyphicon-upload"></i>
                                <span>Start</span>
                            </button>
                        {% } %}
                        {% if (!i) { %}
                            <button class="btn btn-warning cancel">
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
                                class="btn btn-danger delete" 
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
                            <button class="btn btn-warning cancel">
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

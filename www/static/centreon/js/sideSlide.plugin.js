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

/*-- Plugin Slide --*/
$(document).ready(function() {
    $.fn.sideSlide = function() {

        var $slide = $(this);

        var $mainTabWrapper  = $('<section>');


        var $bodyContent = $('<section class="bodyContent col-md-11">');
        var $sideNav = $('<nav class="sideNav col-md-2">');
        $slide.append($mainTabWrapper).append($sideNav).append($bodyContent);
        $('.bodyContent').wrap('<section class="bodyWrapper">');

        /*-- Slim scroll sideSlide Content --*/

        $('.bodyContent').slimscroll({
            height: '100%',
            width: '83.33333333%'
        })


        $.fn.sideSlide.add = function(elem,data){

            var t = data.DT_RowData.right_side_menu_list;
            var d = data.DT_RowData.right_side_default_menu;

            // default menu here

            $.ajax({
                url: d.url,
                type: "GET",
                dataType: 'JSON',
                "jsonpCallback": 'callback',
                success : function(e){
                    if(!isJson(e)){
                        alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
                        return false;
                    }
                    // Call hogan templates
                    if(e.success){
                        $.get(d.tpl, function(tpl){

                            var template = Hogan.compile(tpl);
                            var rendered = template.render(e);
                            $mainTabWrapper.html(rendered);
                        });
                        
                        $('#tableLeft').css('margin-right','310px');
                        $('#sideRight').css('display','block');
                    }else{
                        alertModalMessage("an error occured", "alert-danger");
                    }
                    

                },
                error : function(error){
                    alertModalMessage("an error occured", "alert-danger");
                }
            });

            var $sideMenu = $('<ul class="sideMenu">');

            $.each(t , function(index,item){

                var li = $('<li>');
                var a = $('<a>').attr('href','#').append('<i class="icon-'+item.name+'" >');
                li.append(a);
                $sideMenu.append(li);

                a.on('click',function(e){
                    if(!isJson(e)){
                        alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
                        return false;
                    }
                    e.preventDefault();

                    $.ajax({
                        url: item.url,
                        type: "GET",
                        dataType: 'JSON',
                        //"jsonpCallback": 'callback',
                        success : function(e){

                            // Call hogan templates
                            if(e.success){
                                $.get(item.tpl, function(tpl){
                                    var template = Hogan.compile(tpl);
                                    var rendered = template.render(e);
                                    $bodyContent.html(rendered);
                                });
                            }else{
                                alertModalMessage("an error occured", "alert-danger");
                            }
                        },
                        error : function(error){
                            alertModalMessage("an error occured", "alert-danger");
                        }
                    });
                });

                if(typeof(item.default) !== 'undefined' && item.default === 1){
                    $.ajax({
                        url: item.url,
                        type: "GET",
                        dataType: 'JSON',
                        success : function(e){
                            if(!isJson(e)){
                                alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
                                return false;
                            }
                            // Call hogan templates
                            if(e.success){
                                $.get(item.tpl, function(tpl){
                                    var template = Hogan.compile(tpl);
                                    var rendered = template.render(e);
                                    $bodyContent.html(rendered);
                                });
                            }else{
                                alertModalMessage("an error occured", "alert-danger");
                            }
                            
                        },
                        error : function(error){
                            alertModalMessage("an error occured", "alert-danger");
                        }
                    });
                }

            });
            $sideNav.html($sideMenu);
        };
        $.fn.sideSlide.refresh = function(){
        };
    };
});
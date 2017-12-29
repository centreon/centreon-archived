/*
 * Copyright 2005-2012 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */
(function($) {
    var methods = {
        init: function(options) {
        	return this.each(function(){
        		var $this = $(this),
        		data = $this.data('centreonWizard');
        		if (!data) {
        			$(this).data('centreonWizard', {
        				url: options.url,
        				method: options.method,
        				wizardName: options.name,
        				wizardUuid: options.uuid,
        				frameWidth: $this.width(),
        				step: 1
        			});
        		}
                $('#button_next').click(function(e) {
                    $this.centreonWizard('nextStep');
                });
                $('#button_previous').click(function(e) {
                    $this.centreonWizard('previousStep');
                });
                $('#button_finish').click(function(e) {
                	$this.centreonWizard('submitWizard');
                });
                $this.centreonWizard('nextStep');
        	});
        },
        nextStep: function() {
        	return this.each(function(){
        		var $this = $(this),
        		data = $this.data('centreonWizard');
        		if (!data) {
        			$.error('Data is not initialize');$(this).data('centreonWizard', {
        		        url: data.url,
        		        method: data.method,
        			wizardName: data.wizardName,
        			wizardUuid: data.wizardUuid,
        		        frameWidth: data.frameWidth,
        			step: data.step - 1
        		});
        			return;
        		}
        		/* Validate the form */
        		$('form#step' + (data.step - 1)).centreonValidate('validate');
        		if ($('form#step' + (data.step - 1)).centreonValidate('hasError')) {
        			return;
        		}
        		/* Resize the wizard div */
        		if ($('#c-wiz-step-' + data.step).length == 0) {
        			var frames = $this.children()[0];
        			$(frames).width($(frames).width() + data.frameWidth);
        			$('<div/>', {
        				id: 'c-wiz-step-' + data.step,
        				'class': 'frame'
        			}).appendTo(frames);
        		}
        		$.ajax({
        			url: data.url,
        			dataType: 'html',
        			type: data.method,
        			cache: false,
                    data: {
                        'uuid': data.wizardUuid,
                        'step': data.step,
                        'name': data.wizardName,
                        'values': $this.centreonWizard('getvalues', data.step)
                    },
                    success: function(html) {
                    	$('#c-wiz-step-' + data.step).html(html);
                        $('#c-wiz-step-' + data.step + ' :radio').each(function(idx, el) {
                        	$(el).parent().click(function() {
                        		$(el).attr('checked', true);			       
                        	});
                        });
                        $('#c-wiz-step-' + data.step + ' :checkbox').each(function(idx, el) {
                        	$(el).parent().click(function() {
                        		$(el).attr('checked', !$(el).is(':checked'));
                        	});
                        });
                        $('form#step' + data.step).centreonValidate();
                        $(frames).animate({marginLeft: - (data.frameWidth * (data.step - 1)) });
                        if (data.step != 1) {
                            $('#button_previous').show();
                        }
                        if ($('#c-wiz-step-' + data.step + ' > .content > input[name="end"]').length > 0) {
                            $('#button_next').hide();
                            $('#button_finish').show();
                        }
                    }
        		});
        		$(this).data('centreonWizard', {
        			url: data.url,
        			method: data.method,
        			wizardName: data.wizardName,
        			wizardUuid: data.wizardUuid,
        			frameWidth: data.frameWidth,
        			step: data.step + 1
        		});
        	});
        },
        previousStep: function() {
            return this.each(function() {
            	var $this = $(this),
            	data = $this.data('centreonWizard');
            	if (!data) {
            		$.error('Data is not initialize');
            		return;
            	}
            	var frames = $this.children()[0];
            	$(frames).animate({marginLeft: - (data.frameWidth * (data.step - 3)) });
            	$('#c-wiz-step-' + (data.step - 1)).remove();
            	$(frames).width($(frames).width() - data.frameWidth);
            	if ((data.step - 2) == 1) {
                    $('#button_previous').hide();
                }
                if ($('#c-wiz-step-' + (data.step - 2) + ' > .content > input[name="end"]').length == 0) {
                    $('#button_next').show();
                    $('#button_finish').hide();
                } else {
                	$('#button_next').hide();
                    $('#button_finish').show();
                }
                $(this).data('centreonWizard', {
                	url: data.url,
                	method: data.method,
                	wizardName: data.wizardName,
                	wizardUuid: data.wizardUuid,
                	frameWidth: data.frameWidth,
                	step: data.step - 1
                });
            });
        },
        submitWizard: function() {
        	return this.each(function(){
        		var $this = $(this),
    		    data = $this.data('centreonWizard');
	    		if (!data) {
	    		    $.error('Data is not initialize');
	    		    return;
	    		}
	    		if ($('#c-wiz-step-' + (data.step - 1) + ' > .content > p.error').length != 0) {
	    			console.log('Has error');
	    			return;
	    		}
	    		/* Validate the form */
	    		$('form#step' + (data.step - 1)).centreonValidate('validate');
	    		if ($('form#step' + (data.step - 1)).centreonValidate('hasError')) {
	    			console.log('Has error 2');
	    		    return;
	    		}
	    		$.ajax({
	    		    url: data.url,
	    		    dataType: 'html',
	    		    type: data.method,
	    		    cache: false,
                    data: {
                        'uuid': data.wizardUuid,
                        'step': data.step,
                        'name': data.wizardName,
                        'finish': true,
                        'values': $this.centreonWizard('getvalues', data.step)
                    },
                    success: function(html) {
                		/* Resize the wizard div */
                		if ($('#c-wiz-step-' + data.step).length == 0) {
                		    var frames = $this.children()[0];
                		    $(frames).width($(frames).width() + data.frameWidth);
                		    var newdiv = $('<div/>', {
                		        id: 'c-wiz-step-' + data.step,
                		        'class': 'frame'
                		    }).appendTo(frames);
                		}
                		newdiv.html(html);
                		$(frames).animate({marginLeft: - (data.frameWidth * (data.step - 1)) });
                		$('#button_finish').hide();
                		if ($('#c-wiz-step-' + (data.step) + ' > .content > p.error').length == 0) {
                    		$('#button_previous').hide();
                    	}
                    }
	    		});
	    		$(this).data('centreonWizard', {
    		        url: data.url,
    		        method: data.method,
        			wizardName: data.wizardName,
        			wizardUuid: data.wizardUuid,
    		        frameWidth: data.frameWidth,
        			step: data.step + 1
        		});
        	});
        },
        getvalues: function(step) {
        	var o = {};
        	var a = $('form#step' + (step - 1)).serializeArray();
        	$.each(a, function() {
        		if (o[this.name]) {
        			if (!o[this.name].push) {
        				o[this.name] = [o[this.name]];
        			}
        			o[this.name].push(this.value || '');
        		} else {
        			o[this.name] = this.value || '';
        		}
        	});
            return o;
        }
    };
    $.fn.centreonWizard = function(method) {
        if (methods[method]) {
        	return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method == 'object' || !method) {
        	return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exists on jQuery.centreonWizard.');
        }
    };
})(jQuery);

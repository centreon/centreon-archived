/*
 * Copyright 2005-2012 MERETHIS
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
 * SVN : $URL$
 * SVN : $Id$
 *
 */
(function($) {
    var methods = {
        init: function() {
	    return this.each(function(){
	        var $this = $(this),
		    data = $this.data('centreonValidate');
		if (!data) {
		    $(this).data('centreonValidate', {
		        errors: 0
		    });
		}
		$this.find(':input').each(function(idx, el) {
		     if ($(el).hasClass('v_required') && $(el).attr('id')) {
		         $this.find('[for="' + ($(el).attr('id')) + '"]').each(function(idx, label) {
			     $('<span />').addClass('v_required_star').html('*').appendTo(label);
			 });
		     }
		});
	    });
	},
	validate: function() {
	    return this.each(function() {
	        var $this = $(this),
		    data = $this.data('centreonValidate');
		if (!data) {
		    $.error('Data is not initialize');
		    return;
		}
		data.errors = 0;
		$this.find(':input').each(function(idx, el) {
		    var rules = [
		        'required',
			'number'
		    ];
		    $.each(rules, function(idx, rule) {
		         if ($(el).hasClass('v_' + rule)) {
			    if (!$this.centreonValidate(rule, $(el))) {
			        data.errors++;
			        return false;
			    }
			 }
			 $(el).qtip('hide').qtip('destroy');
		         return true;
		    });
		    $(this).data('centreonValidate', {
		        errors: data.errors
		    });
		});
	    });
	},
	hasError: function() {
	    var inError = false;
	    this.each(function() {
	        var $this = $(this),
		    data = $this.data('centreonValidate');
		if (!data) {
		    $.error('Data is not initialize');
		    return;
		}
	        if (data.errors > 0) {
		    inError = true;
		}
	    });
	    return inError;
	},
	displayError: function(el, error) {
            el.qtip({
	        overwrite: true,
	        content: error,
	        position: {
	            my: 'left center',
	            at: 'right center',
	            viewport: $(window)
	        },
	        show: {
	            event: false,
	            ready: true,
		    effect: function(offset) {
		        $(this).fadeIn(300);
		    }
	        },
	        hide: {
		    event: 'click',
		    effect: function(offset) {
		        $(this).fadeOut(300);
		    }
		},
	        style: {
	            classes: 'ui-tooltip-red'
	        }
             }).qtip('option', 'context.text', error).qtip('show');

	},
	required: function(el) {
	    var $this = $(this);
	    if ($.trim(el.val()) == '') {
	        $this.centreonValidate('displayError', el, 'It\'s required');
	        return false;
	    }
	    return true;
	},
	number: function(el) {
	    var $this = $(this);
	    if (/\d+/.test(el.val())) {
	        return true;
	    }
	    $this.centreonValidate('displayError', el, 'It\'s not number');
	    return false;
	}
    };
    $.fn.centreonValidate = function(method) {
        if (methods[method]) {
	    return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
	} else if (typeof method == 'object' || !method) {
	    return methods.init.apply(this, arguments);
	} else {
	    $.error('Method ' + method + ' does not exists on jQuery.centreonValidate.');
	}
    };
})(jQuery);

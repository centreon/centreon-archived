/*
 * Copyright 2005-2015 Centreon
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
 */

(function ($, window) {
  'use strict';
  
  function CentreonPopin(settings, $elem) {
    var self = this;
    var closeBtn = $('<a class="close" href="#"><img src="./img/icons/circle-cross.png" class="ico-18"></a>');
    var $newElem = $('<div></div>');
    self.settings = settings;

    /* Add class */
    $elem.addClass('popin-wrapper');
    $newElem.addClass('centreon-popin');
    $newElem.hide();
    $elem.wrap($newElem);
  
    self.$elem = $elem.parents('.centreon-popin').detach();
    self.$elem.appendTo('body');

    /* Append close button */
    closeBtn.appendTo($elem);
    closeBtn.on('click', function () {
      self.close();
    });

    self.initOverlay();
    
    if(self.settings.url !== null){
        $.ajax({
           url : self.settings.url,
           type: self.settings.ajaxType,
           dataType : self.settings.ajaxDataType,
           data: self.settings.postDatas,
           success : function(html){

               /* Execute callback if defined on settings */
               if (typeof(self.settings.formatResponse) === 'function') {
                   html = self.settings.formatResponse(html);
               }

               $elem.append(html);

               if (self.settings.open) {
                   self.open();
                   self.reset();
               }

               /* Execute callback if defined on settings */
               if (typeof(self.settings.onComplete) === 'function') {
                   self.settings.onComplete();
               }
           }
        });
    }else{
        self.reset();
        if (self.settings.open) {
           self.open();
        }
    }
  }

  CentreonPopin.prototype = {
    initOverlay: function () {
      var self = this;
      if ($('#centreonPopinOverlay').length === 0) {
        $('<div></div>')
          .attr('id', 'centreonPopinOverlay')
          .addClass('centreon-popin-overlay')
          .hide()
          .prependTo('body');
      }
      $('#centreonPopinOverlay').on('click', function (e) {
        if (self.settings.closeOnDocument) {
          if ($(e.target).parents('.centreon-popin').length === 0) {
            self.close();
          }
        }
      });
    },
    setUrl : function(url){
        this.settings.url = url;
    },

    reset: function() {
      var self = this;
      $('.centreon-popin .bt_default').on('click', function () {
        self.close();
      });
    },

    setCenter: function () {
      var windowH = $(window).height();
      var windowW = $(window).width();
      var modalH = this.$elem.height();
      var modalW = this.$elem.width();
      var top = (windowH - modalH) / 2;
      top = (top < 0) ? 0 : top;
      var left = (windowW - modalW) / 2;
      left = (left < 0) ? 0 : left;
      this.$elem.css({
        top: top + "px",
        left: left + "px"
      });
    },
    open: function () {
      $('#centreonPopinOverlay').show();
      this.$elem.show();
      this.setCenter();
      this.opened = true;
    },
    close: function () {
      this.opened = false;

      if (this.settings.url !== null) {
        this.$elem.remove();
      } else {
        this.$elem.hide();
      }
      $('#centreonPopinOverlay').hide();

      /* Execute callback if defined on settings */
      if (typeof(this.settings.onClose) === 'function') {
        this.settings.onClose();
      }
    }
  };
  
  $.fn.centreonPopin = function (options) {
    var args = Array.prototype.splice.call(arguments, 1);
    var settings = $.extend({}, $.fn.centreonPopin.defaults, options);
    var methodReturn;
    var $set = this.each(function () {
      var $this = $(this);
      var data = $this.data('centreonPopin');
      if (!data) {
        $this.data('centreonPopin', (data = new CentreonPopin(settings, $this)));
      }
      if (typeof options === 'string') {
        methodReturn = data[options].apply(data, args);
      }
    });
    return (methodReturn === undefined) ? $set : methodReturn;
  };
  
  $.fn.centreonPopin.defaults = {
    closeOnDocument: true,
    open: false,
    url : null,
    ajaxDataType: 'html',
    ajaxType : 'POST',
    postDatas : "",
    formatResponse: null,
    onComplete: null,
    onClose: null
  };
})(jQuery, window);

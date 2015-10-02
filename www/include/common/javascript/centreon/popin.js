(function ($, window) {
  'use strict';
  
  function CentreonPopin(settings, $elem) {
    var self = this;
    var closeBtn = $('<a class="close" href="#"><img src="./img/icons/circle-cross.png"></a>');
    var $newElem = $('<div></div>');
    this.settings = settings;

    /* Add class */
    $newElem.addClass('centreon-popin');
    $newElem.hide();
    $elem.wrap($newElem);
  
    this.$elem = $elem.parents('.centreon-popin').detach();
    this.$elem.appendTo('body');
    
    /* Append close button */
    closeBtn.appendTo(this.$elem);
    closeBtn.on('click', function () {
      self.close();
    });
    
    this.initOverlay();
    
    if (this.settings.open) {
      this.open();
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
    setCenter: function () {
      var windowH = $(window).height();
      var windowW = $(window).width();
      var modalH = this.$elem.height();
      var modalW = this.$elem.width();
      this.$elem.css({
        top: ((windowH - modalH) / 2) + "px",
        left: ((windowW - modalW) / 2) + "px"
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
      this.$elem.hide();
      $('#centreonPopinOverlay').hide();
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
      
      return (methodReturn === undefined) ? $set : methodReturn;
    });
  };
  
  $.fn.centreonPopin.defaults = {
    closeOnDocument: true,
    open: false
  };
})(jQuery, window);
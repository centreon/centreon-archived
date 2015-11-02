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
        jQuery.ajax({
           url : self.settings.url,
           type: (typeof self.settings.ajaxType !== null) ? self.settings.ajaxType : "POST" ,
           dataType : "html",
           data: (typeof self.settings.postDatas !== null) ? self.settings.postDatas : "",
           success : function(html){
               $elem.append(html);
                if (self.settings.open) {
                    self.open();
                }
           }
        });
    }else{
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
    open: false,
    url : null,
    ajaxType : null,
    postDatas : null
  };
})(jQuery, window);
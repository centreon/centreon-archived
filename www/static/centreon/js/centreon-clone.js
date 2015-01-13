(function($) {
  function CentreonClone(settings, $elem) {
    var pos = 0, self = this;

    this.settings = settings;
    this.$elem = $elem;

    if ('addBtn' in this.settings) {
      this.$addBtn = $(this.settings.addBtn);
    } else {
      this.$addBtn = $('.addclone');
    }

    this.$template = $elem.find('.clone_template');

    /* Add event */
    this.$addBtn.on('click', function() {
      self.addElement();
    });
    this.$elem.on('click', '.remove-trigger', function() {
      var $el = $(this).closest('li.cloned_element');
      self.removeElement($el);
    });
    this.$elem.on('change', 'input.hidden-value-trigger', function() {
      var $el = $(this).closest('li.cloned_element');
      self.changeElement($el);
    });
  }

  CentreonClone.prototype = {
    addElement: function(values) {
      var $newEl = this.$template.clone().css('display', 'block')
                     .removeClass('clone_template')
                     .addClass('cloned_element')
                     .appendTo(this.$elem);

      if (typeof values !== 'undefined') {
        $.each(values, function(key, value) {
          $newEl.find(key).val(value);
        });
      }

      $.each(this.settings.events.add, function(idx, fct) {
        fct($newEl);
      });
    },
    removeElement: function($el) {
      $.each(this.settings.events.remove, function(idx, fct) {
        fct($el);
      });
      $el.remove();
    },
    changeElement: function($el) {
      /* @todo factorize */
      $.each(this.settings.events.change, function(idx, fct) {
        fct($el);
      });
    }
  };

  $.fn.centreonClone = function(options) {
    var $set,
        args = Array.prototype.slice.call(arguments, 1),
        settings = $.extend({}, $.fn.centreonClone.defaults, options),
        methodReturn;

    $set = this.each(function() {
      var $this = $(this),
          data = $this.data('centreonClone');

      if (!data) {
        $this.data('centreonClone', (data = new CentreonClone(settings, $this)));
      }
      if (typeof options === 'string') {
        methodReturn = data[options].apply(data, args);
      }
    });

    return (methodReturn === undefined) ? $set : methodReturn;
  };

  $.fn.centreonClone.defaults = {
    name: null,
    events: {
      add: [],
      remove: [],
      change: []
    }
  };
})(jQuery);

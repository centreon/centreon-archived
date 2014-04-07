/*global jQuery:false */
/**
 * Wizard components based on wizard fuelux
 */
(function( $ ) {
  function CentreonWizard( settings, $elem ) {
    var nextKids;

    this.settings = settings;
    this.$elem = $elem;
    /* Load steps */
    this.currentStep = this.settings.selectedItem.step;
    this.numSteps = this.$elem.find(".steps li").length;
    this.settings.disablePreviousStep = ( this.$elem.data().restrict === "previous" ) ? true : false;

    /* Configure buttons */
    this.$prevBtn = this.$elem.find("button.btn-prev");
    this.$nextBtn = this.$elem.find("button.btn-next");

    nextKids = this.$nextBtn.children().detach();
    this.nextText = $.trim(this.$nextBtn.text());
    this.$nextBtn.append(nextKids);

    /* Handle events */
    this.$prevBtn.on("click", $.proxy(this.previous, this));
    this.$nextBtn.on("click", $.proxy(this.next, this));
    this.$elem.on("click", "li.complete", $.proxy(this.stepclicked, this));

    if ( this.currentStep > 1 ) {
      this.selectedItem( this.settings.selectedItem );
    }

    if ( this.settings.disablePreviousStep ) {
      this.$prevBtn.attr( "disabled", true );
      this.$elem.find( ".steps" ).addClass( "previous-disabled" );
    }

    this.setState();

    return this;
  }

  CentreonWizard.prototype = {
    setState: function() {
      var text, kids, $steps, prevSelector, $prevSteps, currentSelector, $currentStep,
          target,
          canMovePrev = (this.currentStep > 1),
          isFirstStep = (this.currentStep === 1),
          isLastStep = (this.currentStep === this.numSteps),
          data = this.$nextBtn.data();

      /* Disable previous button if the first step */
      if ( !this.settings.disablePreviousStep ) {
        this.$prevBtn.attr("disabled", (isFirstStep === true || canMovePrev === false));
      }

      /* Change next button in last step */
      if ( data && data.last ) {
        this.lastText = data.last;
        if ( typeof this.lastText !== "undefined" ) {
          text = (isLastStep !== true) ? this.nextText : this.lastText;
          kids = this.$nextBtn.children().detach();
          this.$nextBtn.text(text).append(kids);
        }
      }

      /* Reset steps */
      $steps = this.$elem.find(".steps li");
      $steps.removeClass("active").removeClass("complete");

      /* Set class for all previous steps */
      prevSelector = ".steps li:lt(" + (this.currentStep - 1) + ")";
      $prevSteps = this.$elem.find(prevSelector);
      $prevSteps.addClass("complete");

      /* Set class for current step */
      currentSelector = ".steps li:eq(" + (this.currentStep - 1) + ")";
      $currentStep = this.$elem.find(currentSelector);
      $currentStep.addClass("active");

      /* Display current step */
      target = $currentStep.data().target;
      $(target).parent(".step-content").find(".step-pane").removeClass("active");
      $(target).addClass("active");

      /* Reset wizard position */
      this.$elem.find(".steps").first().attr("style","margin-left: 0");

      /* TODO See wizard size > container size */

      this.$elem.trigger("changed");
    },

    stepclicked: function(e) {
      var evt,
          li = $(e.currentTarget),
          index = this.$elem.find(".steps li").index(li),
          canMovePrev = true;

      if ( this.settings.disablePreviousStep ) {
        if ( index < this.currentStep ) {
          canMovePrev = false;
        }
      }

      if ( canMovePrev ) {
        evt = $.Event("stepclick");
        this.$elem.trigger( evt, {step: index + 1} );
        if ( evt.isDefaultPrevented() ) {
          return;
        }
        this.currentStep = (index + 1);
        this.setState();
      }
    },

    previous: function(evt) {
      var e,
          canMovePrev = (this.currentStep > 1);

      evt.preventDefault();
      if( this.settings.disablePreviousStep ) {
        canMovePrev = false;
      }
      if (canMovePrev) {
        e = $.Event("change");
        this.$elem.trigger(e, {step: this.currentStep, direction: "previous"});
        if (e.isDefaultPrevented()) {
          return;
        }
        this.currentStep -= 1;
        this.setState();
      }
    },

    next: function(evt) {
      var e,
          canMoveNext = (this.currentStep + 1 <= this.numSteps),
          lastStep = (this.currentStep === this.numSteps);

      evt.preventDefault();
      if (canMoveNext) {
        e = $.Event("change");
        this.$elem.trigger(e, {step: this.currentStep, direction: "next"});
        if (e.isDefaultPrevented()) {
          return;
        }
        this.currentStep += 1;
        this.setState();
      } else if (lastStep) {
        this.$elem.trigger("finished");
      }
    },

    selectedItem: function( selectedItem ) {
      var retVal, step;

      if ( selectedItem ) {
        step = selectedItem.step || -1;

        if ( step >= 1 && step <= this.numSteps ) {
          this.currentStep = step;
          this.setState();
        }

        retVal = this;
      } else {
        retVal = { step: this.currentStep };
      }
      return retVal;
    }
  };

  $.fn.centreonWizard = function( options ) {
    var $set,
        args = Array.prototype.slice.call( arguments, 1 ),
        settings = $.extend( {}, $.fn.centreonWizard.defaults, options ),
        methodReturn;

    $set = this.each(function() {
      var $this = $( this ),
          data = $this.data( "centreonWizard" );

      if ( !data ) {
        $this.data( "centreonWizard", ( data = new CentreonWizard( settings, $this ) ) );
      }
      if ( typeof options === "string") {
        methodReturn = data[ options ].apply( data, args );
      }
    });

    return ( methodReturn === undefined ) ? $set : methodReturn;
  };

  $.fn.centreonWizard.defaults = {
    selectedItem: {
      step: 1
    }
  };
})( jQuery );

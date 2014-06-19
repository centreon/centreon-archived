( function( $ ) {
  $.fn.centreonsearch = function( options ) {
    var args = Array.prototype.slice.call( arguments, 1 ),
        settings = $.extend( {}, $.fn.centreonsearch.defaults, options ),
        methodReturn; 
    return this.each( function() {
      var $this = $( this ),
          data = $this.data( "centreonsearch" );

      if ( !data ) {
        $this.data( "centreonsearch", new $.CentreonSearch(
            $this,
            $.meta ? $.extend( {}, settings, $this.data() ) : settings
        ));
      }

      if ( typeof options === "string" ) {
        data[ options ].apply( data, args );
      }
    });
  };

  $.fn.centreonsearch.defaults = {
    minChars: 3,
    tags: {},
    associateFields: {}
  };

  $.CentreonSearch = function( $elem, options ) {
    var self = this;
    this.options = options;

    this.tags = this.options.tags;

    this.associateFields = this.options.associateFields;

    this.active = false;

    this.currentTag = null;

    this.currentList = [];

    this.mousedover = false;
    
    this.dom = {};

    this.dom.$elem = $elem;

    this.dom.$elem.attr( "autocomplete", "off" );

    this.dom.$results = $( "<ul></ul>" ).addClass( "typeahead" ).addClass( "dropdown-menu" ).css({
      position: "absolute"
    });
    /* Resize for input size */
    this.resize();
    $( "body" ).append( this.dom.$results );

    /* Bind event listener */
    $elem.on( "keyup" , $.proxy( self.switchAction, self ) );
    $elem.on( "blur", $.proxy( self.blur, self ) );
    this.dom.$results.on( "click", "li", function( e ) {
      self.valid( $( e.currentTarget ) );
    });
    this.dom.$results.on( "mouseover", "li", function( e ) {
      self.mousedover = true;
      self.dom.$results.find( ".active" ).removeClass( "active" );
      $( e.currentTarget ).addClass( "active" );
    });
    this.dom.$results.on( "mouseout", "li", function( e ) {
      self.mousedover = false;
      $( e.currentTarget ).removeClass( "active" );
    });
    $( window ).resize( $.proxy( self.resize, self ) );
  };

  $.CentreonSearch.prototype.switchAction = function( e ) {
    var self = this;
    switch ( e.keyCode ) {
      case 35: // end
      case 36: // home
      case 16: // shift
      case 17: // ctrl
      case 18: // alt
      case 37: // left
      case 39: // right
        break;

      case 38: // up
        e.preventDefault();
        if ( this.active ) {
          this.focusPrev();
        }
        break;

      case 40: // down
        e.preventDefault();
        if ( this.active ) {
          this.focusNext();
        }
        break;

      case 13: // enter
        this.valid();
        break;

      case 27: // escape
        this.cleanChoices();
        break;

      case 32: // space
        this.fillAssociateFields();
        break;

      default:
        this.search();
        break;
    }
  };

  $.CentreonSearch.prototype.search = function() {
    var typeSearch, lastElement, valid, sepPos, searchStr, listChoices,
        children, tmpList, tmpStr, i,
        self = this,
        input = this.dom.$elem;
    this.currentList = $( input ).val().split( " " );
    lastElement = this.currentList[ this.currentList.length - 1 ];
    /* Action only if more than 'minChars' characters */
    if ( lastElement.length >= this.options.minChars ) {
       sepPos = lastElement.indexOf( ":" );
       /* Search for tags */
       if ( sepPos == -1 ) {
         this.currentTag = null;
         valid = Object.keys( this.tags ).filter( function( el ) {
           if ( el.substring( 0, lastElement.length ) == lastElement ) {
             return el;
           }
         });
         if ( valid.length > 0 ) {
           this.displayChoices( valid );
         } else {
           this.cleanChoices(); 
         }
       /* Search for informations */
       } else {
         this.cleanChoices();
         typeSearch = lastElement.substring( 0, sepPos );
         this.currentTag = typeSearch;
         searchStr = lastElement.substring( sepPos + 1 );
         listChoices = [];
         if ( $.inArray( typeSearch, Object.keys( this.tags ) ) != -1 &&
           searchStr.length >= this.options.minChars ) {
           if ( typeof( this.tags[ typeSearch ] ) == "function" ) {
             /* Is a function */
             listChoices = this.tags[ typeSearch ]( searchStr );
           } else if ( typeof( this.tags[ typeSearch ] ) == "string" ) {
             /* Is a select */
             children = $( this.tags[ typeSearch ] ).children( "option" );
             tmpList = children.filter( function( el, list ) {
               tmpStr = $( list ).text();
               if ( tmpStr.substring( 0, searchStr.length ).toLowerCase() == searchStr.toLowerCase() ) {
                 return true;
               }
             });
             i = 0;
             for ( i; i < tmpList.length; i++ ) {
               if ( typeof( $( tmpList[ i ] ).text() ) == "string") {
                 listChoices.push( $( tmpList[ i ] ).text() );
               }
             }
           } else if ( typeof( this.tags[ typeSearch ] ) == "object" ) {
             /* Is a object : array */
             listChoices = this.tags[ typeSearch ].filter( function( el ) {
               if ( el.substring( 0, searchStr.length ).toLowerCase() == searchStr.toLowerCase() ) {
                 return true;
               }
             });
           }
           if ( listChoices.length > 0 ) {
             this.displayChoices( listChoices );
           }
         }
       }
    } else {
      this.cleanChoices();
    }
  };
  
  $.CentreonSearch.prototype.displayChoices = function( list ) {
    var $li,
        self = this;
    this.active = true;
    this.dom.$results.html( "" );
    $.each( list, function( idx, value ) {
      $li = $( "<li></li>" ).css( "cursor", "pointer" );
      $( "<a></a>" ).text( value ).appendTo( $li );
      $li.appendTo( self.dom.$results );
    });
    this.dom.$results.show();
    this.position();
  };

  $.CentreonSearch.prototype.cleanChoices = function() {
    this.dom.$results.hide();
    this.dom.$results.html( "" );
    this.active = false;
  };

  $.CentreonSearch.prototype.position = function() {
    var topIfUp,
        offset = this.dom.$elem.offset(),
        height = this.dom.$results.outerHeight(),
        totalHeight = $( window ).outerHeight(),
        inputBottom = offset.top + this.dom.$elem.outerHeight(),
        bottomIfDown = inputBottom + height,
        position = {
          top: inputBottom,
          left: offset.left
        };
    if ( bottomIfDown > totalHeight ) {
      topIfUp = offset.top - height;
      if ( topIfUp >= 0 ) {
        position.top = topIfUp;
      }
    }
    this.dom.$results.css( position );
  };

  $.CentreonSearch.prototype.focusNext = function() {
    var newActive,
        el = this.dom.$results.children( ".active" );
    if ( el.length > 0 ) {
      newActive = $( el ).next( "li" );
      $( el ).removeClass( "active" );
      if ( newActive.length === 0 ) {
        this.dom.$results.children( ":first" ).addClass( "active" );  
      } else {
        newActive.addClass( "active" );
      }
    } else {
      this.dom.$results.children( ":first" ).addClass( "active" );
    }
  };

  $.CentreonSearch.prototype.focusPrev = function() {
    var el = this.dom.$results.children( ".active" );
    if ( el.length > 0 ) {
      var newActive = $( el ).prev( "li" );
      $( el ).removeClass( "active" );
      if ( newActive.length === 0 ) {
        this.dom.$results.children( ":last" ).addClass( "active" );
      } else {
        newActive.addClass( "active" );
      }
    } else {
      this.dom.$results.children( ":last" ).addClass( "active" );
    }
  };

  $.CentreonSearch.prototype.valid = function ( el ) {
    var activeEl, concat;
    if ( typeof( el ) == "undefined" ) {
      activeEl = this.dom.$results.children( ".active" );
    } else {
      activeEl = el;
    }
    if ( activeEl.length > 0 ) {
      concat = activeEl.children( "a" ).text();
      if ( this.currentTag !== null ) {
        concat = this.currentTag + ":" + concat;
      } else {
        concat += ":";
      }
      this.currentList[ this.currentList.length - 1 ] = concat;
      this.dom.$elem.val( this.currentList.join( " " ) );
      this.cleanChoices();
    } else {
      this.fillAssociateFields();
    }
  };

  $.CentreonSearch.prototype.resize = function() {
    this.dom.$results.width( this.dom.$elem.width() );
  };

  $.CentreonSearch.prototype.blur = function() {
    if ( !this.mousedover ) {
      this.cleanChoices();
    }
  };

  $.CentreonSearch.prototype.fillAssociateFields = function() {
    var listUsedTags = {},
        self = this,
        input = this.dom.$elem;
    /* Clean all fields */
    $.each( self.associateFields, function( tagName, element ) {
      if ( typeof( element ) == "string" ) {
        $( element ).val( "" );
      } else if ( typeof( element ) == "object" && element instanceof jQuery ) {
        element.val( "" );
      } else if ( typeof( element ) == "function" ) {
        element( "" );
      }
    });

    /* Get list of tags */
    this.currentList = $( input ).val().split( " " );
    /* Found values */
    $.each( this.currentList, function( idx, element ) {
      var tagName,
          sepPos = element.indexOf( ":" );
      if ( sepPos != -1 ) {
        tagName = element.substring( 0, sepPos );
        if ( $.inArray( tagName, Object.keys( self.associateFields ) ) != -1 ) {
          if ( $.inArray( tagName, Object.keys( listUsedTags ) ) == -1 ) {
            listUsedTags[tagName] = [];
          }
          listUsedTags[tagName].push( element.substring( sepPos + 1, element.lenght ) );
        }
      }
    });
    /* Fill the fields with information */
    $.each( listUsedTags, function( tagName, values ) {
      var elTarget = self.associateFields[ tagName ];
      if ( typeof( elTarget ) == "string" ) {
        elTarget = $( elTarget );
      } else if ( typeof( elTarget ) == "function" ) {
        elTarget( values );
      }
      if ( typeof( elTarget ) == "object" && elTarget instanceof jQuery ) {
        if ( elTarget.is( "input" ) ) {
          elTarget.val( values.join( " " ) );
        } else if ( elTarget.is( "select" ) ) {
          elTarget.val( function() {
            var listValuesId = [];
            elTarget.find( "option" ).filter( function( idx, element ) {
              $.each( values, function( idx, value ) {
                if ( $( element ).text().toLowerCase() == value.toLowerCase() ) {
                  listValuesId.push( $( element ).val() );
                }
              });
            });
            return listValuesId;
          });
        }
      }
    });
  };
})( jQuery );

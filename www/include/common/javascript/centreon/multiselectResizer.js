/**
 * This is used for auto resizing the multiselect boxes when they
 * tend to get too large
 *
 * minAllowedWidth can be modified
 */
jQuery(function() {
    // minimum width of the selectbox, change it if you want
    var minAllowedWidth = 270;

    // maximum width of the selectbox, twice larger than minimum width
    var maxAllowedWidth = (minAllowedWidth * 2);

    var maxWidth = 0;

    // get maximum width of all multiselect boxes
    // we want them to have the same size
    jQuery("select[multiple=multiple]").each(function() {
        if(!jQuery(this).width()) {
            return;
        }

        var htmlText = jQuery('<span style="display:none;"></span>');
        htmlText.appendTo(jQuery(this).parent());

        jQuery(this).children("option").each(function() {
            var curLen;

            htmlText.text(jQuery(this).text());
            curLen = htmlText.width() + 20; // ~ scrollbar width
            if (curLen > maxWidth) {
                maxWidth = curLen;
            }
        });
    });

    // set min width
    if (maxWidth < minAllowedWidth) {
        maxWidth = minAllowedWidth;
    }
                                         
    // set max width
    if (maxWidth > maxAllowedWidth) {
        maxWidth = maxAllowedWidth;
    }
   
    // resize all boxes 
    jQuery("select[multiple=multiple]").each(function() {
        if(!jQuery(this).width()) {
            return;
        }

        // resize
        jQuery(this).width(maxWidth);
    });
});

function centreonCollapse() {
    var tbody = jQuery(".collapse-wrapper");
    var tab = jQuery(".tab");

    tbody.find(".list_lvl_1").addClass("elem-header");
    tbody.find(".list_one").addClass("elem-toCollapse");
    tbody.find(".list_two").addClass("elem-toCollapse");

    tbody.eq(0).find(".elem-toCollapse").show();
    tbody.eq(0).find(".list_lvl_1").addClass("open");
    tbody.find(".list_lvl_1").slice(1).addClass("close");

        tbody.each(function() {
            var elem = jQuery(this).find('.list_lvl_1');
            var nextElemChildren = elem.parent().siblings().find('.elem-toCollapse');

            elem.on('click', function() {

                var elemChildren = jQuery(this).siblings('.elem-toCollapse');

                if(elemChildren.is(':visible')) {
                    elemChildren.hide();
                    jQuery(this).removeClass('open').addClass('close');
                }
                else {
                    jQuery(this).addClass('open').removeClass('close');
                    tbody.eq(0).find(".list_lvl_1").removeClass("open").addClass('close');
                    nextElemChildren.hide();
                    elemChildren.show();
                }
            });
        });
};
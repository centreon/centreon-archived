/* jPaginator - author: Remy Elazare - http://remylab.git/jpaginator/ */
(function($) {
    $.fn.jPaginator = function(o) {

        if (this.size() != 1)
            $.error('You must use this plugin with a unique element');

        var s = {
            selectedPage: null,
            nbPages: 100,
            nbVisible: 10,
            widthPx: 30,
            marginPx: 1,
            overBtnLeft: null,
            overBtnRight: null,
            maxBtnLeft: null,
            maxBtnRight: null,
            withSlider: true,
            withAcceleration: true,
            speed: 2,
            coeffAcceleration: 2,
            minSlidesForSlider: 3,
            onPageClicked: null
        };

        var c = {
            realWid: 0,
            curNum: 1,
            infRel: 0,
            cInfMax: 0,
            cInf: 0,
            nbMove: 0,
            isMoving: false,
            isLimitL: false,
            isLimitR: false,
            listenSlider: true
        };

        return this.each(function() {

            var $this = $(this);
            if (o)
                $.extend(s, o);

            init();

            // events
            $(this).bind('reset', function(event, o) {
                $.extend(s, o);
                init();
            });

            if (s.withSlider) {
                $this.find(".paginator_slider").slider({
                    animate: false
                });

                $this.find(".paginator_slider").bind("slidechange.jPaginator", function(event, ui) {
                    return handleSliderChange(event, ui);
                });

                $this.find(".paginator_slider").bind("slide.jPaginator", function(event, ui) {
                    return handleSliderChange(event, ui);
                });

                if ((s.nbVisible < s.nbPages)) {
                    moveSliderTo(c.cInf);
                }
            }

            if (s.overBtnLeft) {
                $(s.overBtnLeft).bind('mouseenter.jPaginator', function() {
                    return onEnterButton($(this), 'left');
                });
            }
            if (s.overBtnLeft) {
                $(s.overBtnLeft).bind('mouseleave.jPaginator', function() {
                    return onLeaveButton($(this));
                });
            }
            if (s.overBtnRight) {
                $(s.overBtnRight).bind('mouseenter.jPaginator', function() {
                    return onEnterButton($(this), 'right');
                });
            }
            if (s.overBtnRight) {
                $(s.overBtnRight).bind('mouseleave.jPaginator', function() {
                    return onLeaveButton($(this));
                });
            }

            if (s.maxBtnLeft) {
                $(s.maxBtnLeft).bind('click.jPaginator', function() {
                    return moveToLimit('left');
                });
            }
            if (s.maxBtnRight) {
                $(s.maxBtnRight).bind('click.jPaginator', function() {
                    return moveToLimit('right');
                });
            }

            $this.find(".paginator_p").bind('mouseenter.jPaginator', function() {
                return onEnterNum($(this));
            });

            $this.find(".paginator_p").bind('mouseleave.jPaginator', function() {
                return onLeaveNum($(this));
            });

            function onClickNum(e) {

                var newPage = 1 * e.html();
                $this.find(".paginator_p.selected").removeClass("selected");
                s.selectedPage = newPage;

                //goToSelectedPage(); // uncomment to center page num on click
                $($this.find(".paginator_p_bloc .paginator_p").get(s.selectedPage - c.curNum + 1)).addClass("selected");

                if (s.onPageClicked)
                    s.onPageClicked.call(this, $this, s.selectedPage);
            };

            function onEnterButton(e, dir) {
                c.isMoving = true;
                move(dir);
            };

            function onLeaveButton(e) {
                reset();
            };

            function onEnterNum(e) {
                $this.find(".paginator_p.over").removeClass("over");
                e.addClass("over");
            };

            function onLeaveNum(e) {
                $this.find(".paginator_p.over").removeClass("over");
            };

            function goToSelectedPage() {

                var newNum = s.selectedPage - Math.floor((s.nbVisible - 1) / 2);
                updateNum(newNum);

                c.listenSlider = false;
                moveSliderTo(c.cInf);
                c.listenSlider = true;
            };

            function updateNum(newNum) {

                $this.find(".paginator_p.selected").removeClass("selected");

                newNum = Math.min(s.nbPages - s.nbVisible + 1, newNum);
                newNum = Math.max(1, newNum);

                var n = newNum - 2;
                $this.find(".paginator_p_bloc .paginator_p").each(function(i) {
                    n += 1;
                    $(this).html(n);
                    if (s.selectedPage == n) {
                        $(this).addClass("selected");
                    }
                });

                $this.find(".paginator_p_bloc").css("left", "-" + c.realWid + "px");

                c.curNum = newNum;
                c.cInf = (newNum - 1) * c.realWid;
                c.infRel = 0;

            };

            function moveSliderTo(pos) {

                $this.find(".paginator_slider").slider();
                var newPc = Math.round((pos / c.cInfMax) * 100);
                var oldPc = $this.find(".paginator_slider").slider("option", "value");

                if ((typeof newPc == "number" && !isNaN(newPc)) && newPc != oldPc) {
                    $this.find(".paginator_slider").slider("option", "value", newPc);
                }
            };

            function handleSliderChange(e, ui) {

                if (!c.listenSlider)
                    return;

                if (!c.isMoving) {
                    moveToPc(ui.value);
                }
            };

            function moveToPc(pc) {

                pc = Math.min(100, pc);
                pc = Math.max(0, pc);

                var realInf = Math.round(c.cInfMax * pc / 100);
                var gap = realInf - c.cInf;

                if (pc == 100) {
                    updateNum(s.nbPages - s.nbVisible + 1);
                    return;
                };
                if (pc == 0) {
                    updateNum(1);
                    return;
                };

                moveGap(gap);
            };

            function moveGap(gap) {

                var iGap = Math.abs(gap) / gap;
                var pxGap = c.infRel + gap;
                var pageGap = iGap * Math.floor(Math.abs(pxGap) / c.realWid);
                var dGap = pxGap % c.realWid;

                c.infRel = dGap;

                var cInfTmp = (c.curNum - 1) * c.realWid + c.infRel;

                var newPage = c.curNum + pageGap;
                if (newPage < 1) {
                    cInfTmp = -1
                };
                if (newPage > s.nbPages) {
                    cInfTmp = c.cInfMax + 1
                };

                if (cInfTmp < 0) {
                    updateNum(1);
                    c.cInf = 0;
                    c.infRel = 0;
                    moveSliderTo(0);
                    c.isLimitL = true;
                    reset();
                    return;
                }
                if (cInfTmp > c.cInfMax) {
                    updateNum(s.nbPages);
                    c.cInf = c.cInfMax;
                    c.infRel = 0;
                    moveSliderTo(c.cInfMax);
                    c.isLimitR = true;
                    reset();
                    return;
                }

                c.isLimitL = false;
                c.isLimitR = false;

                c.cInf = cInfTmp;

                if (gap == 0)
                    return;
                if (pageGap != 0)
                    updateNum(newPage);

                moveSliderTo(c.cInf);
                $this.find(".paginator_p_bloc").css("left", -1 * dGap - c.realWid + "px");

            };

            function reset() {
                c.nbMove = 0;
                c.isMoving = false;
            };

            function moveToLimit(dir) {

                if (c.isLimitR && dir == 'right') {
                    return;
                }
                if (c.isLimitL && dir == 'left') {
                    return;
                }

                var gap = Math.round(c.cInfMax / 10);

                if (dir == 'left') {
                    gap *= -1;
                }

                moveGap(gap);

                setTimeout(function() {
                    c.nbMove += 1;
                    moveToLimit(dir);
                }, 20);

            };

            function move(dir) {

                if (c.isMoving) {

                    var gap = Math.min(Math.abs(s.speed), 5);
                    var coeff = Math.min(Math.abs(s.coeffAcceleration), 5);
                    if (s.withAcceleration) {
                        gap = Math.round(gap + Math.round(coeff * (c.nbMove * c.nbMove) / 80000));
                    }

                    if (dir == 'left') {
                        gap *= -1;
                    }

                    moveGap(gap);

                    setTimeout(function() {
                        c.nbMove += 1;
                        move(dir);
                    }, 10);
                }
            };
            function init() {

                var totalSlides,
                bSlider,
                bOver;

                s.nbVisible = Math.min(s.nbVisible, s.nbPages);

                $this.find(".paginator_p_bloc > .paginator_p").remove();
                // init c data
                for (i = 1; i <= s.nbVisible + 2; i++) {
                    $this.find(".paginator_p_bloc").append($("<a class='paginator_p'></a>"));
                }
                // hide over and max buttons if they're useless...    
                bOver = (s.nbVisible < s.nbPages);
                if (s.overBtnLeft) {
                    if (bOver)
                        $(s.overBtnLeft).show();
                    else
                        $(s.overBtnLeft).hide();
                }
                if (s.overBtnRight) {
                    if (bOver)
                        $(s.overBtnRight).show();
                    else
                        $(s.overBtnRight).hide();
                }
                if (s.maxBtnLeft) {
                    if (bOver)
                        $(s.maxBtnLeft).show();
                    else
                        $(s.maxBtnLeft).hide();
                }
                if (s.maxBtnRight) {
                    if (bOver)
                        $(s.maxBtnRight).show();
                    else
                        $(s.maxBtnRight).hide();
                }

                if (!bOver) {
                    $this.find(".paginator_slider").hide();
                    $this.find(".paginator_slider").children().hide();
                } else {
                    totalSlides = Math.ceil(s.nbPages / s.nbVisible);
                    bSlider = s.withSlider;
                    if (totalSlides < s.minSlidesForSlider)
                        bSlider = false;
                    // hide slider when needed
                    else
                        bSlider = s.withSlider;

                    if (!bSlider) {
                        $this.find(".paginator_slider").hide();
                        $this.find(".paginator_slider").children().hide();
                    } else {
                        $this.find(".paginator_slider").show();
                        $this.find(".paginator_slider").children().show();
                    }
                }

                var borderPx = 0;
                var sBorder = $this.find(".paginator_p").first().css("border-left-width");
                if (sBorder.indexOf("px") > 0) {
                    borderPx = sBorder.replace("px", "") * 1;
                }

                c.realWid = s.widthPx + s.marginPx * 2 + borderPx * 2;

                var widAll = 1 * c.realWid * s.nbVisible;

                $this.find(".paginator_p").css("width", s.widthPx + "px");
                $this.find(".paginator_p").css("margin", "0 " + s.marginPx + "px 0 " + s.marginPx + "px");

                $this.find(".paginator_p_wrap").css("width", widAll + "px");
                $this.find(".paginator_slider").css("width", widAll + "px");

                c.cInfMax = s.nbPages * c.realWid - (s.nbVisible * c.realWid);

                // init selected page
                s.selectedPage = Math.min(s.selectedPage, s.nbPages);
                goToSelectedPage();

                if (s.selectedPage)
                    $($this.find(".paginator_p_bloc .paginator_p").get(s.selectedPage - c.curNum + 1)).addClass("selected");

                $this.find(".paginator_p").bind('click.jPaginator', function() {
                    return onClickNum($(this));
                });

            }

        });
    };
})(jQuery);
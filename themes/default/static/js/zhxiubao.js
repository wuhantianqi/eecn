$(function() {
    var zxb = {
        init: function() {
             this.bindMenuEvent(), this.topBannerEvent()
        },
        bindMenuEvent: function() {
            $("#zxb-floor-menu").on("click", "li", function() {
                var a = $("#zxb-floor-menu").height(),
                    b = $(this).attr("data-id"),
                    c = $("#" + b).offset().top,
                    d = $(this);
                    
                c = c - a - 70, $("body,html").animate({
                    scrollTop: c
                }), d.addClass("cur").siblings().removeClass("cur")
            })
        },
        topBannerEvent: function() {
            function a() {
                c[g][1].stop(!0, !0).addClass("animate fadeOutRight").fadeOut(), c[g][0].stop(!0, !0).removeClass("animated fadeOut").addClass("animated fadeIn").show(), c[f][0].stop(!0, !0).removeClass("animated fadeIn").addClass("animated fadeOut").fadeOut(), c[f][1].stop(!0, !0).removeClass("animate fadeOutRight").addClass("animated fadeInRight").fadeIn(), g = f, ++f >= e && (f = 0)
            }
            var b, c = [
                [$(".banner-item-01"), $(".banner-item-01-hover")],
                [$(".banner-item-02"), $(".banner-item-02-hover")],
                [$(".banner-item-03"), $(".banner-item-03-hover")],
                [$(".banner-item-04"), $(".banner-item-04-hover")],
                [$(".banner-item-05"), $(".banner-item-05-hover")]
            ],
                d = $("#banner-canves"),
                e = c.length,
                f = 1,
                g = 0,
                h = setInterval(a, 3e3);
            d.on("mouseenter", function() {
                h && clearInterval(h)
            }).on("mouseleave", function() {
                h = setInterval(a, 3e3)
            }), d.on("click mouseenter", ".banner-item-prev", function() {
                h && clearInterval(h);
                var c = $(this).attr("data-index");
                b && clearTimeout(b), b = setTimeout(function() {
                    f = c, a()
                }, 100)
            })
        }
    };
    zxb.init();
});
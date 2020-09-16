var m_swiper = {
    init: function () {
        this.banner()
    }, banner: function () {
        new Swiper(".m_swiper", {pagination: ".swiper-pagination", paginationClickable: !0, loop: !0, autoplay: 5e3})
    }
};
m_swiper.init();
var m_info = {
    init: function () {
        this.marquee()
    }, marquee: function () {
        var i = {delay: 2e3, easing: "linear", items: 1, duration: .07, timeoutDuration: 0, pauseOnHover: "immediate"};
        $(".ticker-1").carouFredSel({
            width: 1060,
            align: !1,
            items: {width: "variable", height: 40, visible: 1},
            scroll: i
        })
    }
};
m_info.init();
var m_info = {
    init: function () {
        this.m_marquee()
    }, m_marquee: function () {
        new Swiper(".swiper-containers", {slidesPerView: 3, paginationClickable: !0, spaceBetween: 20})
    }
};
m_info.init(), $(function () {
    jQuery("#leaders").slide({mainCell: ".bd ul", autoPlay: !0, effect: "leftMarquee", vis: 6, interTime: 20})
});
var m_links = {
    init: function () {
        this.m_links()
    }, m_links: function () {
        new Swiper(".swiper-containers", {slidesPerView: 3, paginationClickable: !0, spaceBetween: 20})
    }
};
m_links.init(), jQuery("#friendlink").slide({
    mainCell: ".bd ul",
    autoPlay: !0,
    effect: "leftMarquee",
    vis: 5,
    interTime: 20
});
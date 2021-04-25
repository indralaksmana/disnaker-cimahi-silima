jQuery(document).ready(function ($) {
    "use strict";
    function screenClass() {
        if ($(window).innerWidth() < 768) {
            $($('.search-desktop').contents()).appendTo('.search-mobile')
        } else {
            $($('.search-mobile').contents()).appendTo('.search-desktop')
        }
    }
    // Fire.
    screenClass();
    // And recheck if window gets resized.
    $(window).bind('resize', function () {
        screenClass();
    });

    /* 1. Menu Mobile */
    var $body = $('body'),
        $menu = $('.menu-list'),
        $btnMenu = $('.menu-mobile'),
        back = '<li class="back"><a href="#"><i class="icon-left-open"></i>Back</a></li>';
    $('ul', $menu).prepend(back);
    $('.menu-item-has-children > a', $menu).on('click', function (event) {
        var $item = $(event.target).closest('.menu-item-has-children'),
            ww = $(window).width();
        if (ww <= 991) {
            event.preventDefault();
            $('> .sub-menu', $item).addClass('active');
        }
    });
    $menu.on('click', '.back', function (event) {
        event.preventDefault();
        $(this).closest('.sub-menu').removeClass('active');
    });
    $menu.on('open', function () {
        $menu.addClass('active');
        $body.addClass('menu-active');
    });
    $menu.on('close', function () {
        $menu.removeClass('active');
        $body.removeClass('menu-active');
    });
    $btnMenu.on('click', function () {
        $menu.trigger('open');
    });
    $('.hide-menu').on('click', function () {
        $menu.trigger('close');
    });
    $(document).on('keydown', function (event) {
        if (event.keyCode === 27) {
            $menu.trigger('close');
        }
    });

    // Coment
    (function(){
    $(document).on('click', '.reply-btn', function(e){
        e.preventDefault();
        if ($(".reply"+$(this).data('commentid')).is(":visible")) {
            $(".reply"+$(this).data('commentid')).hide();
        }else{
            $(".reply"+$(this).data('commentid')).show();
        }
        $("body").getNiceScroll().resize();
    });

    var hasError = 0;
    $(".has-error").each(function(){
       hasError = 1;
    })

    if (hasError) {
        $('html, body').animate({
            scrollTop: ($('.has-error').first().offset().top -175)
        },500);
    }
    // $('#status').bootstrapToggle({
    //   on: 'Published',
    //   off: 'Draft',
    //   width: '100%'
    // });

    // $('.select').select2({
    //     tags: true,
    //     width: '100%'
    // });
})();

});

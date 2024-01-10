'use strict';


document.addEventListener('DOMContentLoaded', () => {

    //  активное меню
        active_menu();

    //  Toggle the side navigation
        $('#sidebarToggle, #sidebarToggleTop').on('click', function(e) {
            $('body').toggleClass('sidebar-toggled');
            $('.sidebar').toggleClass('toggled');
            if ($('.sidebar').hasClass('toggled')) {
                $('.sidebar .collapse').collapse('hide');
            };
        });

    //  управление видимостью активных пунктов меню
        $(window).resize(function() {
            if ($(window).width() < 768) $('.sidebar .collapse').collapse('hide');
            else active_menu();
        });

    //  Prevent the content wrapper from scrolling when the fixed side navigation hovered over
        $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
            if ($(window).width() > 768) {
                let e0 = e.originalEvent,
                delta = e0.wheelDelta || -e0.detail;
                this.scrollTop += (delta < 0 ? 1 : -1) * 30;
                e.preventDefault();
            }
        });

    //  видимость кнопки прокрутки наверх
        $(document).on('scroll', function() {
            let scrollDistance = $(this).scrollTop();
            if (scrollDistance > 100) $('.scroll-to-top').fadeIn();
            else $('.scroll-to-top').fadeOut();
        });

    //  действие кнопки прокрутки наверх
        $(document).on('click', 'a.scroll-to-top', function(e) {
            let $anchor = $(this);
            $('html, body').stop().animate({
                scrollTop: ($($anchor.attr('href')).offset().top)
            }, 555, 'easeInOutExpo');
            e.preventDefault();
        });

});


//  активное меню
    function active_menu() {
        let href = location.origin + location.pathname;
        $('#accordionSidebar a').each((i, e) => {
            if (e.href == href) {
                $(e).addClass('active');
                let item = $(e).closest('li');
                item.addClass('active');
                if ($(e).closest('.collapse').length && $(window).width() >= 768) {
                    item.find('a.collapsed').removeClass('collapsed');
                    item.find('div.collapse').addClass('show');
                }
            }
        });
    }


//  ajax - запрос
    function query(param, callback) {
        //  подготовка данных
            let timeout = 111;
            let data = new FormData();
            for (let i in param) data.append(i, param[i]);
            data.append('@API', '');
        //  запрос
            fetch('', {
                'method': 'POST',
                'body': data
            }).then(
                res => {
                    if (res.status == 200) {
                        res.json().then(
                            res => setTimeout(() => {
                                if ('@' in res) location.href = location.origin;
                                else callback({res});
                            }, timeout),
                            () => setTimeout(() => callback({
                                res: {
                                    error: 101
                                }
                            }), timeout)
                        );                    
                    }
                    else setTimeout(() => callback({
                        res: {
                            error: 'message', 
                            error_message: res.status + ' : ' + res.statusText
                        }
                    }), timeout);
                },
                () => setTimeout(() => callback({
                    res: {
                        error: 100
                    }
                }), timeout)
            );
    } 
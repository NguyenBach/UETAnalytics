/*!
    
 =========================================================
 * Paper Dashboard - v1.1.2
 =========================================================
 
 * Product Page: http://www.creative-tim.com/product/paper-dashboard
 * Copyright 2017 Creative Tim (http://www.creative-tim.com)
 * Licensed under MIT (https://github.com/creativetimofficial/paper-dashboard/blob/master/LICENSE.md)
 
 =========================================================
 
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 
 */


var fixedTop = false;
var transparent = true;
var navbar_initialized = false;

$(document).ready(function () {
    window_width = $(window).width();

    // Init navigation toggle for small screens
    if (window_width <= 991) {
        pd.initRightMenu();
    }

    //  Activate the tooltips
    $('[rel="tooltip"]').tooltip();

    $('.message-btn,.notify-btn').click(function () {
        $("input[name='msgsubject']").val('');
        $("textarea[name='msgmessage']").val('');
        if ($(this).attr('class') == 'notify-btn') {
            $('#message-title').html(notify);
            $("#subject").hide();
            $('#send').attr('data-type', 'notify');
        } else {
            $('#message-title').html(message);
            $("#subject").show();
            $('#send').attr('data-type', 'message');

        }
        var from = $(this).attr('data-from');
        var to = $(this).attr('data-to');
        $("input[name='from']").val(from);
        $("input[name='to']").val(to);
        $.ajax({
            url: siteUrl+'/mod/uetanalytics/ajax.php/',
            method: 'post',
            async: true,
            data: {'action': 'messageinfo', 'from': from, 'to': to},
            dataType: 'json',
            success: function (data) {
                $('#message-from').val(data.from);
                $('#message-to').val(data.to);
            }
        });
        $('#message-popup').fadeIn(300);
        $('body').append('<div id="over">');
        $('#over').fadeIn(300);
        return false;
    });

    $(document).on('click', "#main, #over, #cancel ", function () {
        $('#over, .message-popup').fadeOut(300, function () {
            $('#over').remove();
        });
        return false;
    });

    $('#send').click(function () {
        var type = $(this).attr('data-type');
        var data;
        if (type == 'message') {
            var data = {
                'action': 'sendmessage',
                'from': $("input[name='from']").val(),
                'to': $("input[name='to']").val(),
                'msgsubject': $("input[name='msgsubject']").val(),
                'msgmessage': $("textarea[name='msgmessage']").val(),
                'courseid': $("input[name='courseid']").val()
            };

        }
        if(type == 'notify'){
            var data = {
                'action': 'notify',
                'from': $("input[name='from']").val(),
                'to': $("input[name='to']").val(),
                'notification': $("textarea[name='msgmessage']").val(),
                'courseid': $("input[name='courseid']").val()
            };
        }
        $.ajax({
            url: siteUrl+ '/mod/uetanalytics/ajax.php/',
            method: 'post',
            async: true,
            data: data,
            dataType: 'json',
            success: function (data) {
                if(data.sent){
                    alert('Sent');
                    $('#over, .message-popup').fadeOut(300, function () {
                        $('#over').remove();
                    });
                }
            }
        });
    });
    // $.each(student,function (key, value) {
    //     $.ajax({
    //         url: 'http://moodle.local/mod/uetanalytics/ajax.php/',
    //         method: 'post',
    //         async: true,
    //         data: {'action':'student','courseid':coursid,'studentid':value},
    //         dataType: 'json',
    //         success: function (data) {
    //             data.predict = {w7:"A",w15:"B"};
    //             var assigment = 'S: '+data.assignment.submitted+'/' + data.assignment.total+
    //                             'L: '+data.assignment.late+'/' + data.assignment.submitted+
    //                             'N: '+data.assignment.not+'/' + data.assignment.total;
    //             var te = '<tr>' +
    //                 '<td>'+key+'</td>' +
    //                 '<td>'+data.fullname+'</td>' +
    //                 '<td>'+data.view+'</td>' +
    //                 '<td>'+data.post+'</td>' +
    //                 '<td> View: '+data.forumview+' Post: '+data.forumpost+'</td>' +
    //                 '<td>'+data.forumpost+'</td>' +
    //                 '<td>'+assigment+'</td>' +
    //                 '<td> Mid: '+data.predict.w7+'Fin: '+data.predict.w15+'</td>' +
    //                 '<td> Mid: '+data.grade.mid+'Fin: '+data.grade.final+'</td>' +
    //                 '</tr>';
    //             $('#table-statis').append(te);
    //
    //         }
    //     });
    // });


});


// activate collapse right menu when the windows is resized
$(window).resize(function () {
    if ($(window).width() <= 991) {
        pd.initRightMenu();
    }
});

pd = {
    misc: {
        navbar_menu_visible: 0
    },
    checkScrollForTransparentNavbar: debounce(function () {
        if ($(document).scrollTop() > 381) {
            if (transparent) {
                transparent = false;
                $('.navbar-color-on-scroll').removeClass('navbar-transparent');
                $('.navbar-title').removeClass('hidden');
            }
        } else {
            if (!transparent) {
                transparent = true;
                $('.navbar-color-on-scroll').addClass('navbar-transparent');
                $('.navbar-title').addClass('hidden');
            }
        }
    }),
    initRightMenu: function () {
        if (!navbar_initialized) {
            $off_canvas_sidebar = $('nav').find('.navbar-collapse').first().clone(true);

            $sidebar = $('.sidebar');
            sidebar_bg_color = $sidebar.data('background-color');
            sidebar_active_color = $sidebar.data('active-color');

            $logo = $sidebar.find('.logo').first();
            logo_content = $logo[0].outerHTML;

            ul_content = '';

            // set the bg color and active color from the default sidebar to the off canvas sidebar;
            $off_canvas_sidebar.attr('data-background-color', sidebar_bg_color);
            $off_canvas_sidebar.attr('data-active-color', sidebar_active_color);

            $off_canvas_sidebar.addClass('off-canvas-sidebar');

            //add the content from the regular header to the right menu
            $off_canvas_sidebar.children('ul').each(function () {
                content_buff = $(this).html();
                ul_content = ul_content + content_buff;
            });

            // add the content from the sidebar to the right menu
            content_buff = $sidebar.find('.nav').html();
            ul_content = ul_content + '<li class="divider"></li>' + content_buff;

            ul_content = '<ul class="nav navbar-nav">' + ul_content + '</ul>';

            navbar_content = logo_content + ul_content;
            navbar_content = '<div class="sidebar-wrapper">' + navbar_content + '</div>';

            $off_canvas_sidebar.html(navbar_content);

            $('body').append($off_canvas_sidebar);

            $toggle = $('.navbar-toggle');

            $off_canvas_sidebar.find('a').removeClass('btn btn-round btn-default');
            $off_canvas_sidebar.find('button').removeClass('btn-round btn-fill btn-info btn-primary btn-success btn-danger btn-warning btn-neutral');
            $off_canvas_sidebar.find('button').addClass('btn-simple btn-block');

            $toggle.click(function () {
                if (pd.misc.navbar_menu_visible == 1) {
                    $('html').removeClass('nav-open');
                    pd.misc.navbar_menu_visible = 0;
                    $('#bodyClick').remove();
                    setTimeout(function () {
                        $toggle.removeClass('toggled');
                    }, 400);

                } else {
                    setTimeout(function () {
                        $toggle.addClass('toggled');
                    }, 430);

                    div = '<div id="bodyClick"></div>';
                    $(div).appendTo("body").click(function () {
                        $('html').removeClass('nav-open');
                        pd.misc.navbar_menu_visible = 0;
                        $('#bodyClick').remove();
                        setTimeout(function () {
                            $toggle.removeClass('toggled');
                        }, 400);
                    });

                    $('html').addClass('nav-open');
                    pd.misc.navbar_menu_visible = 1;

                }
            });
            navbar_initialized = true;
        }

    }
}


// Returns a function, that, as long as it continues to be invoked, will not
// be triggered. The function will be called after it stops being called for
// N milliseconds. If `immediate` is passed, trigger the function on the
// leading edge, instead of the trailing.

function debounce(func, wait, immediate) {
    var timeout;
    return function () {
        var context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function () {
            timeout = null;
            if (!immediate) func.apply(context, args);
        }, wait);
        if (immediate && !timeout) func.apply(context, args);
    };
};

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

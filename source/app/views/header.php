<?php
/**
 * SMG Design MVC Template 2014
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo $title; ?></title>
        <link href="/css/admin.css" type="text/css" rel="stylesheet" />
        <link href="/css/jquery-ui.css" type="text/css" rel="stylesheet" />
        <script type="text/javascript" src="/js/jquery-2.1.0.min.js"></script>
        <script type="text/javascript" src="/js/jquery-ui.min.js"></script>
        <script type="text/javascript" src="/js/jquery-ui-timepicker.js"></script>
        <?php
        foreach ($this->headIncludes as $include) {
            echo $include;
        }
        ?>
        <script type="text/javascript">
        Object.equals = function( x, y ) {
            if ( x === y ) return true;
            // if both x and y are null or undefined and exactly the same

            if ( ! ( x instanceof Object ) || ! ( y instanceof Object ) ) return false;
            // if they are not strictly equal, they both need to be Objects

            if ( x.constructor !== y.constructor ) return false;
            // they must have the exact same prototype chain, the closest we can do is
            // test there constructor.

            for ( var p in x ) {
                if ( ! x.hasOwnProperty( p ) ) continue;
                  // other properties were tested using x.constructor === y.constructor

                if ( ! y.hasOwnProperty( p ) ) return false;
                  // allows to compare x[ p ] and y[ p ] when set to undefined

                if ( x[ p ] === y[ p ] ) continue;
                  // if they have the same strict value or identity then they are equal

                if ( typeof( x[ p ] ) !== "object" ) return false;
                  // Numbers, Strings, Functions, Booleans must be strictly equal

                if ( ! Object.equals( x[ p ],  y[ p ] ) ) return false;
                  // Objects and Arrays must be tested recursively
            }

            for ( p in y ) {
                if ( y.hasOwnProperty( p ) && ! x.hasOwnProperty( p ) ) return false;
                // allows x[ p ] to be set to undefined
            }
            return true;
        }
        function loader(text) {
            return $('<div class="loader_centre"><p class="large_text">'+text+'</p><div id="movingBallG"><div class="movingBallLineG"></div><div id="movingBallG_1" class="movingBallG"></div></div>');
        }
        var dataCache = {'array': [], 'object': {}, 'removed': {}};
        function autoRefresh() {
            $.ajax({
                "url": "/api/request/getall/json",
                "type": "post",
                "dataType": "json",
                "data": {"api_key": "9AdwAbXRB0D34ue4lN1G", "admin_request": true},
                "success": function(i) {
                    if (i.status.code === 3 || i.status.code === 2) {
                        dataCache.array = [];
                        var change = false;
                        for (var z in i.data) {
                            if (typeof dataCache.object[z] === 'undefined' || !Object.equals(i.data[z], dataCache.object[z])) {
                                change = true;
                                dataCache.object[z] = i.data[z];
                            }
                            /*
                            if (
                                    typeof dataCache[z] === 'undefined' ||
                                    dataCache[z].rating !== i.data[z].rating ||
                                    dataCache[z].status !== i.data[z].status ||
                                    dataCache[z].artist !== i.data[z].artist ||
                                    dataCache[z].title !== i.data[z].title
                            ) {
                                dataCache[z] = i.data[z];
                            }*/
                        }
                        for (var y in dataCache.object) {
                            if (typeof i.data[y] === 'undefined') {
                                // means its been removed \\
                                change = true;
                                dataCache.removed[y] = dataCache.object[y];
                                delete(dataCache.object[y]);
                            }
                        }
                        if (typeof z === 'undefined') {
                            // means its empty \\
                            $(".requests").find('.request:not(.add_request)').empty().remove();
                        } else {
                            if (change) {
                                for (var y in dataCache.object) {
                                    dataCache.array.push(dataCache.object[y]);
                                }
                            }
                            dataCache.array.sort(sortRequestsRating);
                            dataCache.array = dataCache.array.reverse();
                            for (var j in dataCache.removed) {
                                $("#request_"+j).empty().remove();
                            }
                            for (var i=0; i<dataCache.array.length; i++) {
                                if ($("#request_"+dataCache.array[i].id).length === 0) {
                                    $(".requests").append($('<li class="request" id="request_'+dataCache.array[i].id+'" />'));
                                }
                                $("#request_"+dataCache.array[i].id).html(
                                        (
                                            (
                                                dataCache.array[i].dedicate !== null &&
                                                dataCache.array[i].dedicate.length > 0
                                            ) ?
                                                '<span class="dedicate">'+dataCache.array[i].dedicate.join('<br />')+'</span>' :
                                                ''
                                         )+
                                         (
                                            (
                                                dataCache.array[i].comment !== null &&
                                                dataCache.array[i].comment.length > 0
                                            ) ?
                                                '<span class="comment">'+dataCache.array[i].comment.join('<br />')+'</span>' :
                                                ''
                                         )+
                                         '<span class="text"><span class="artist">'+dataCache.array[i].artist+'</span> - <span class="title">'+dataCache.array[i].title+'</span></span><div class="icons">'+((dataCache.array[i].status == 1) ? '<span class="'+
                                         (
                                            (
                                                dataCache.array[i].dedicate === null ||
                                                dataCache.array[i].dedicate.length === 0
                                            ) ?
                                                'inactive' :
                                                'active'
                                         )+
                                         ' dedicate_icon"></span><span class="'+
                                         (
                                            (
                                                dataCache.array[i].comment === null ||
                                                dataCache.array[i].comment.length === 0
                                            ) ?
                                                'inactive' :
                                                'active'
                                          )+
                                          ' comment_icon"></span><span class="active play_icon"></span>' :
                                          '<span class="active cancel"></span><span class="active confirm"></span>')+'</div>'
                                );
                                $(".requests").prepend($("#request_"+dataCache.array[i].id));
                            }
                        }
                    }
                    nowPlaying();
                }
            });
        }
        function nowPlaying() {
            $.ajax({
                "url": "/api/request/nowplaying",
                "type": "post",
                "dataType": "json",
                "data": {
                    "api_key": "9AdwAbXRB0D34ue4lN1G"
                },
                "success": function(i) {
                    if (i.status.code === 3) {
                        $(".nowplaying .text").html(i.data.artist+' - '+i.data.title);
                    }
                }
            });
        }
        function sortRequestsRating(a,b) {
            if (a.rating < b.rating) {
                return 1;
            } else if (a.rating > b.rating) {
                return -1;
            } else {
                return 0;
            }
        }
        $(document).ready(function() {
            var hght = $(window).height();
            $(".requests").height(hght-200);
            $(".auto_comp").keyup(function() {
                if ($(this).val().length > 2) {
                    var tgt = $(this).prop('id').split('_list')[0],
                        $this = $(this),
                        tgtResults;
                    $.ajax({
                        "type": "post",
                        "dataType": "json",
                        "data": {"submitted": true, "qry": $this.val()},
                        "url": "/api/complete/"+tgt,
                        "success": function(i) {
                            if (i.status.code === 3) {
                                tgtResults = $('<ul class="comp_box"></ul>');
                                var x = 0,
                                    dataCnt = i.data.length;
                                while (x < dataCnt) {
                                    tgtResults.append($('<li>'+i.data[x].title+'</li>'));
                                    x++;
                                }
                                $("li", tgtResults).click(function() {
                                    var text = $(this).text();
                                    $this.val(text);
                                    tgtResults.empty().remove();
                                });
                            }
                            $this.next('.comp_box').empty().remove().end().after(tgtResults);
                        }
                    });
                } else {
                    $(this).next('.comp_box').empty().remove();
                }
            }).blur(function() {
                var $this = $(this);
                setTimeout(function() {
                    $this.next('.comp_box').empty().remove();
                }, 200);
            });
            $(".requests").on('click', ".request .dedicate_icon, .request .comment_icon", function() {
                $(".request .dedicate, .request .comment").fadeOut();
                var tgt = $(this).prop('class').split('_icon')[0].split('active ')[1];
                $(this).parent().parent().find('.'+tgt).fadeIn();
                $(this).parent().parent().find('.'+tgt).mouseout(function() {
                    $(this).fadeOut();
                    $(this).unbind('mouseout');
                });
            });
            $(".requests").on('click', ".text .artist, .text .title", function() {
                if (!$(this).parent().parent().hasClass('editing')) {
                    $(this).attr('old-val', $(this).text());
                    if ($(this).hasClass('artist')) {
                        $(this).html('<input type="text" name="artist" value="'+$(this).text()+'" placeholder="Artist" />');
                    } else if ($(this).hasClass('title')) {
                        $(this).html('<input type="text" name="title" value="'+$(this).text()+'" placeholder="Title" />');
                    }
                    if ($(this).parent().parent().find('.icons .cancel').length > 0) {
                        $(this).parent().parent().attr('status', 0);
                    } else {
                        $(this).parent().parent().find('.icons').hide();
                        $(this).parent().parent().append($('<div class="icons editing" />').html('<span class="active cancel"></span><span class="active confirm"></span>'));
                    }
                    $(this).parent().parent().addClass('editing');
                }
            });
            $(".requests").on('click', ".play_icon", function() {
                var top = $(this).parent().parent(),
                    ID = top.attr('id').split('request_')[1];
                if (ID !== '') {
                    $.ajax({
                        "url": "/api/admin/play/"+ID,
                        "type": "post",
                        "dataType": "json",
                        "data": {"api_key": "9AdwAbXRB0D34ue4lN1G"},
                        "success": function(i) {
                            autoRefresh();
                            nowPlaying();
                        }
                    });
                }
            });
            $(".requests").on('click', ".add", function() {
                $(this).before('<li class="request add_request"><input type="text" name="artist" placeholder="Artist" /> - <input type="text" name="title" placeholder="Title" /><a href="#cancel" class="cancel"></a><a href="#add" class="add_list confirm"></a></li>');
                $(".add_request a.add_list").click(function(ev) {
                    ev.preventDefault();
                    var tgt = $(this).parent(),
                        artist = $("input[name='artist']", tgt),
                        title = $("input[name='title']", tgt),
                        cont = true;
                    artist.removeClass('input_err');
                    title.removeClass('input_err');
                    if (artist.val() === '') {
                        cont = false;
                        artist.addClass('input_err');
                    }
                    if (title.val() === '') {
                        cont = false;
                        title.addClass('input_err');
                    }
                    if (cont) {
                        $.ajax({
                            "url": "/api/request/submit",
                            "type": "post",
                            "dataType": "json",
                            "data": {
                                "submitted": true,
                                "artist": artist.val(),
                                "title": title.val(),
                                "status": 1,
                                "api_key": "9AdwAbXRB0D34ue4lN1G"
                            },
                            "success": function(i) {
                                if (i.status.code === 4) {
                                    artist.val('');
                                    title.val('');
                                    autoRefresh();
                                }
                            }
                        });
                    }
                });
                $(".add_request a.cancel").click(function(ev) {
                    ev.preventDefault();
                    $(this).parent().empty().remove();
                });
            });
            $(".requests").on('click', ".icons .confirm, .icons .cancel", function() {
                var top = $(this).parent().parent(),
                    ID = top.attr('id').split('request_')[1];
                if (top.hasClass('editing')) {
                    if ($(this).hasClass('confirm')) {
                        $.ajax({
                            "url": "/api/admin/update",
                            "type": "post",
                            "dataType": "json",
                            "data": {
                                "submitted": true,
                                "id": ID,
                                "artist": top.find('.artist input').val(),
                                "title": top.find('.title input').val(),
                                "api_key": "9AdwAbXRB0D34ue4lN1G"
                            },
                            "success": function(i) {
                                top.removeClass('editing');
                                if (!top.hasClass('status')) {
                                    // means we need to change to the comments stuff \\
                                    top.find('.icons.editing').empty().remove();
                                    top.find('.icons').show();
                                    top.find('.artist').html(top.find('.artist input').val());
                                    top.find('.title').html(top.find('.title input').val());
                                }
                                autoRefresh();
                            }
                        });
                    } else if ($(this).hasClass('cancel')) {
                        top.removeClass('editing');
                        if (!top.hasClass('status')) {
                            // means we need to change to the comments stuff \\
                            top.find('.icons.editing').empty().remove();
                            top.find('.icons').show();
                            top.find('.artist').html(top.find('.artist').attr('old-val'));
                            top.find('.title').html(top.find('.title').attr('old-val'));
                        }
                    }
                } else {
                    if ($(this).hasClass('confirm')) {
                        $.ajax({
                            "url": "/api/request/confirm",
                            "type": "post",
                            "dataType": "json",
                            "data": {
                                "submitted": true,
                                "id": ID,
                                "api_key": "9AdwAbXRB0D34ue4lN1G"
                            },
                            "success": function(i) {
                                autoRefresh();
                            }
                        });
                    } else if ($(this).hasClass('cancel')) {
                        $.ajax({
                            "url": "/api/request/cancel",
                            "type": "post",
                            "dataType": "json",
                            "data": {
                                "submitted": true,
                                "id": ID,
                                "api_key": "9AdwAbXRB0D34ue4lN1G"
                            },
                            "success": function(i) {
                                autoRefresh();
                            }
                        });
                    }
                }
            });
            $(".event").click(function() {
                var evID = $(this).prop('id').split('event_')[1],
                    $this = this;
                if (!$(this).hasClass('editing')) {
                    $(this).addClass('editing');
                    if (evID !== '-1') {
                        // this is an existing event \\
                        var name = $(".text", this).text(),
                            date = $(".date", this).attr('date'),
                            utcDate = $(".date", this).attr('utc-date'),
                            endDate = $(".date", this).attr('end-date'),
                            utcEndDate = $(".date", this).attr('utc-end-date');
                        $(".text", this).html('<input type="text" name="event_title" placeholder="Event name" value="'+name+'" /><a href="#add" class="confirm"></a><a href="#cancel" class="cancel"></a>');
                        $(".date", this).html('<input type="text" name="event_date" id="event_date" placeholder="Event date" class="datepicker" value="'+date+'" /><input type="hidden" name="event_date_hidden" id="event_date_hidden" value="'+utcDate+'" /><input type="hidden" name="event_end_date_hidden" id="event_end_date_hidden" value="'+utcEndDate+'" /><input type="text" name="event_end_date" id="event_end_date" placeholder="Event end date" class="datepicker" value="'+endDate+'" />');
                    } else {
                        $(".text", this).html('<input type="text" name="event_title" placeholder="Event name" /><a href="#add" class="confirm"></a><a href="#cancel" class="cancel"></a>');
                        $(".date", this).html('<input type="text" name="event_date" id="event_date" placeholder="Event date" class="datepicker" /><input type="hidden" name="event_date_hidden" id="event_date_hidden" /><input type="hidden" name="event_end_date_hidden" id="event_end_date_hidden" /><input type="text" name="event_end_date" id="event_end_date" placeholder="Event end date" class="datepicker" />');
                    }
                    $("#event_date").datetimepicker({
                        "altField": "#event_date_hidden",
                        "altFormat": "yy-mm-dd",
                        "dateFormat": "dd/mm/yy",
                        "altFieldTimeOnly": false
                    });
                    $("#event_end_date").datetimepicker({
                        "altField": "#event_end_date_hidden",
                        "altFormat": "yy-mm-dd",
                        "dateFormat": "dd/mm/yy",
                        "altFieldTimeOnly": false
                    });
                    $(".confirm", this).click(function(ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        $($this).removeClass('editing');
                        var cont = true,
                            tgt = $(this).parent(),
                            name = $("input[name='event_title']", tgt).val(),
                            date = $("input[name='event_date_hidden']", tgt.next()).val(),
                            dateVis = $("input[name='event_date']", tgt.next()).val(),
                            dateObj = new Date(date+':00'),
                            endDate = $("input[name='event_end_date_hidden']", tgt.next()).val(),
                            endDateObj = new Date(endDate+':00');
                        $("input[name='event_date'], input[name='event_end_date']", tgt.next()).css({'border-color': '#444444'});
                        if (isNaN(dateObj.getTime())) {
                            $("input[name='event_date']", tgt.next()).css({'border-color': '#cc0202'});
                            cont = false;
                        }
                        if (isNaN(endDateObj.getTime())) {
                            $("input[name='event_end_date']", tgt.next()).css({'border-color': '#cc0202'});
                            cont = false;
                        }
                        if (name === '') {
                            name = dateObj.getDate()+'/'+(dateObj.getMonth()+1)+'/'+dateObj.getFullYear();
                        }
                        if (cont) {
                            $.ajax({
                                "url": "/api/admin/event/"+evID,
                                "type": "post",
                                "dataType": "json",
                                "data": {
                                    "submitted": true,
                                    "api_key": "9AdwAbXRB0D34ue4lN1G",
                                    "name": name,
                                    "date": date,
                                    "end_date": endDate
                                },
                                "success": function(i) {
                                    if (i.status.code === 4) {
                                        $($this).attr('id', 'event_'+i.id);
                                        $(".text", $this).html(name);
                                        $(".date", $this).html('');
                                        $(".date", $this).attr('date', dateVis);
                                        $(".date", $this).attr('utc-date', dateObj.getFullYear()+'-'+(((dateObj.getMonth() < 9) ? '0' : '')+(dateObj.getMonth()+1))+'-'+(((dateObj.getDate() <= 9) ? '0' : '')+dateObj.getDate())+' '+(((dateObj.getHours() <= 9) ? '0' : '')+dateObj.getHours())+':'+(((dateObj.getMinutes() <= 9) ? '0' : '')+dateObj.getMinutes()));
                                        $(".date", $this).attr('end-date', (((endDateObj.getDate() <= 9) ? '0' : '')+endDateObj.getDate())+'/'+(((endDateObj.getMonth() < 9) ? '0' : '')+(endDateObj.getMonth()+1))+'/'+endDateObj.getFullYear()+' '+((endDateObj.getHours() <= 9) ? '0' : '')+endDateObj.getHours()+':'+(((endDateObj.getMinutes() <= 9) ? '0' : '')+endDateObj.getMinutes()));
                                        $(".date", $this).attr('utc-end-date', endDateObj.getFullYear()+'-'+(((endDateObj.getMonth() < 9) ? '0' : '')+(endDateObj.getMonth()+1))+'-'+(((endDateObj.getDate() <= 9) ? '0' : '')+endDateObj.getDate())+' '+((endDateObj.getHours() <= 9) ? '0' : '')+endDateObj.getHours()+':'+(((endDateObj.getMinutes() <= 9) ? '0' : '')+endDateObj.getMinutes()));
                                        if ($(".requests .add").length === 0) {
                                            $(".requests").append('<li class="add"><span class="icon"></span><span class="text">Add to list</span></li>');
                                        }
                                    } else {
                                        $($this).prepend(i['return']+'<br />');
                                    }
                                }
                            });
                        }
                    });
                    $(".cancel", this).click(function(ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        $($this).removeClass('editing');
                        if (evID !== '-1') {
                            $(".text", $this).html(name);
                            $(".date", $this).html('');
                        } else {
                            $(".text", $this).html('Create new event');
                            $(".date", $this).html('');
                        }
                    });
                }
            });
            setInterval(function() {
                autoRefresh();
            }, 5000);
        });
        </script>
    </head>
    <body>
        <h1 class="logo"><strong>Trax</strong>Selector<!--img src="/img/logo.png" alt="TraxSelector" width="300" /--></h1>
        

         
    
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
        <link href="/css/styles.css" type="text/css" rel="stylesheet" />
        <link href="/css/jquery-ui.css" type="text/css" rel="stylesheet" />
        <script type="text/javascript" src="/js/jquery-2.1.0.min.js"></script>
        <script type="text/javascript" src="/js/jquery-ui.min.js"></script>
        <?php
        foreach ($this->headIncludes as $include) {
            echo $include;
        }
        ?>
        <script type="text/javascript">
        var voteLog = {};
        function loader(text) {
            return $('<div class="loader_centre"><p class="large_text">'+text+'</p><div id="movingBallG"><div class="movingBallLineG"></div><div id="movingBallG_1" class="movingBallG"></div></div>');
        }
        function autoRefresh() {
            $.ajax({
                "url": "/api/request/getall/html",
                "type": "post",
                "dataType": "json",
                "data": {"api_key": "9AdwAbXRB0D34ue4lN1G"},
                "success": function(i) {
                    if (i.status.code === 3 || i.status.code === 2) {
                        $("#requests .list").html(i.html);
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
                "data": {"api_key": "9AdwAbXRB0D34ue4lN1G"},
                "success": function(i) {
                    if (i.status.code === 3) {
                        var newPlaying = i.data.artist+' - '+i.data.title;
                        if ($("#nowplaying").text() !== newPlaying) {
                            $("#nowplaying").animate({
                                "top": "-200px"
                            }, function() {
                                $(this).html(newPlaying).animate({
                                    "top": "59px"
                                });
                            });
                        }
                        
                    }
                }
            });
        }
        function autoComp() {
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
                            tgtResults.width($this.width()+20);
                            var pos = $this.offset();
                            tgtResults.css({'left': pos.left-11});
                            var x = 0,
                                dataCnt = i.data.length;
                            while (x < dataCnt) {
                                tgtResults.append($('<li>'+i.data[x].title+'</li>'));
                                x++;
                            }
                            $("li", tgtResults).click(function() {
                                $this.val($(this).text());
                                tgtResults.empty().remove();
                                if ($this.next('input, textarea').length === 0 || $this.next('input:hidden').length > 0) {
                                    $this.siblings().filter('input:first').focus();
                                } else {
                                    $this.next('input, textarea').focus();
                                }
                            });
                        }
                        $this.next('.comp_box').empty().remove().end().after(tgtResults);
                    }
                });
            } else {
                $(this).next('.comp_box').empty().remove();
            }
        }
        $(document).ready(function() {
            var hasFocus = $("#artist_list"),
                shift = false;
            $("body").append('<div id="modal_vote"><p><span class="ui-icon ui-icon-check" style="float:left;"></span><span class="message"></span></p></div>');
            $("#modal_vote").dialog({
                "autoOpen": false,
                "modal": true,
                "buttons": {
                    "Ok": function() {
                        $(this).dialog("close")
                    }
                }
            });
            $(".glass").bind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd", function() {
                if ($(this).hasClass('up')) {
                    document.getElementById('artist_list').focus();
                } else {
                    document.getElementById('artist_list').blur();
                }
            });
            $("h1.toggle, input.button[name='cancel']").mousedown(function() {
                var overlay = document.querySelector('.glass');
                if (overlay.className === 'glass down') {
                    overlay.className = 'glass up';
                    $("#requests").addClass('requests_hidden').removeClass('requests_visible');
                } else {
                    overlay.className = 'glass down';
                    $("#requests").addClass('requests_visible').removeClass('requests_hidden');
                }
            });
            $("#request_form form input, #request_form form textarea").focus(function() {
                hasFocus = $(this);
            });
            $("#request_form form").submit(function(ev) {
                ev.preventDefault();
                var $this = $(this),
                    cont = true;
                $(".error, .success", $this).empty().remove();
                if ($("[name='artist']", $this).val() === '') {
                    cont = false;
                    $("[name='artist']", $this).css({'background-color': '#ff5555'});
                } else {
                    $("[name='artist']", $this).css({'background-color': '#ffffff'});
                }
                if ($("[name='title']", $this).val() === '') {
                    cont = false;
                    $("[name='title']", $this).css({'background-color': '#ff5555'});
                } else {
                    $("[name='title']", $this).css({'background-color': '#ffffff'});
                }
                if (cont) {
                    var loading = loader('Sending your request');
                    $this.prepend(loading.fadeIn());
                    $.ajax({
                        "url": $this.prop('action'),
                        "type": $this.prop('method'),
                        "data": {
                            "artist": $("[name='artist']", $this).val(),
                            "title": $("[name='title']", $this).val(),
                            "dedicate": $("[name='dedicate']", $this).val(),
                            "message": $("[name='message']", $this).val(),
                            "submitted": true,
                            "api_key": "9AdwAbXRB0D34ue4lN1G"
                        },
                        "dataType": "json",
                        "success": function(i) {
                            loading.fadeOut(function() {
                                if (i.status.code === 4) {
                                    // means success \\
                                    $this.prepend('<p class="success">'+i.status.message+'</p>');
                                    $this[0].reset();
                                    autoRefresh();
                                } else {
                                    // means an error \\
                                    $this.prepend('<p class="error">'+i['return']+'</p>');
                                }
                                setTimeout(function() {
                                    $this.find('.success, .error').empty().remove();
                                }, 2000);
                            });
                        }
                    });
                }
            });
            $(".auto_comp").keyup(autoComp).blur(function() {
                var $this = $(this);
                setTimeout(function() {
                    if (!$this.is(':focus')) {
                        $this.next('.comp_box').empty().remove();
                    }
                }, 100);
            });
            setInterval(function() {
                autoRefresh();
            }, 5000);
            $("#request_form_kb div.kb_row div").click(function() {
                hasFocus.focus();
                if ($(this).hasClass('kb_letter')) {
                    // just use the text for it \\
                    var newVal = (shift) ? $(this).text() : $(this).text().toLowerCase();
                    hasFocus.val(hasFocus.val()+newVal);
                } else if ($(this).hasClass('kb_btn')) {
                    switch ($(this).attr('id')) {
                        case "kb_backspace":
                            hasFocus.val(hasFocus.val().slice(0, -1));
                            break;
                        case "kb_shift":
                            if (shift) {
                                shift = false;
                                $(this).removeClass('kb_active');
                            } else {
                                shift = true;
                                $(this).addClass('kb_active');
                            }
                            break;
                        case "kb_next":
                            if (hasFocus.next('input, textarea').length === 0 || hasFocus.next('input:hidden').length > 0) {
                                hasFocus.siblings().filter('input:first').focus();
                            } else {
                                hasFocus.next('input, textarea').focus();
                            }
                            break;
                        case "kb_prev":
                            if (hasFocus.prev('input, textarea').length === 0 || hasFocus.prev('input:hidden').length > 0) {
                                hasFocus.siblings().filter('textarea:last').focus();
                            } else {
                                hasFocus.prev('input, textarea').focus();
                            }
                            break;
                    }
                } else if ($(this).hasClass('kb_space')) {
                    hasFocus.val(hasFocus.val()+' ');
                }
                autoComp.apply(hasFocus);
            });
            $("#requests").on('click', '.vote_up', function() {
                var id = $(this).parent().attr('id');
                if (typeof voteLog[id] === 'undefined' || (new Date().getSeconds() > parseInt(voteLog[id])+5 || new Date().getSeconds() < parseInt(voteLog[id])-5)) {
                    $.ajax({
                        "url": "/api/request/rate",
                        "type": "post",
                        "dataType": "json",
                        "data": {
                            "api_key": "9AdwAbXRB0D34ue4lN1G",
                            "id": id,
                            "mode": "up",
                            "submitted": true
                        },
                        "success": function(i) {
                            if (i.status.code === 4) {
                                $("#modal_vote p .message").html("Thank you for your vote");
                                $("#modal_vote").dialog("open");
                            }
                        }
                    });
                } else {
                    $("#modal_vote p .message").html("You cannot vote for the same item twice.");
                    $("#modal_vote").dialog("open");
                }
                voteLog[id] = new Date().getSeconds();
            });
            $("#requests").on('click', '.vote_down', function() {
                var id = $(this).parent().attr('id');
                if (typeof voteLog[id] === 'undefined' || (new Date().getSeconds() > parseInt(voteLog[id])+5 || new Date().getSeconds() < parseInt(voteLog[id])-5)) {
                    $.ajax({
                        "url": "/api/request/rate",
                        "type": "post",
                        "dataType": "json",
                        "data": {
                            "api_key": "9AdwAbXRB0D34ue4lN1G",
                            "id": id,
                            "mode": "down",
                            "submitted": true
                        },
                        "success": function(i) {
                            if (i.status.code === 4) {
                                $("#modal_vote p .message").html("Thank you for your vote");
                                $("#modal_vote").dialog("open");
                            }
                        }
                    });
                } else {
                    $("#modal_vote p .message").html("You cannot vote for the same item twice.");
                    $("#modal_vote").dialog("open");
                }
                voteLog[id] = new Date().getSeconds();
            });
        });
        </script>
    </head>
    <body>
        <h1 class="logo"><strong>Trax</strong>Selector<!--img src="/img/logo.png" alt="TraxSelector" width="300" /--></h1>
        

         
    
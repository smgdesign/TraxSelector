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
        <script type="text/javascript" src="/js/jquery-2.1.0.min.js"></script>
        <?php
        foreach ($this->headIncludes as $include) {
            echo $include;
        }
        ?>
        <script type="text/javascript">
        function toggle() {
            var button = document.querySelector('.toggle');
            var overlay = document.querySelector('.glass');
            if (overlay.className === 'glass down') {
                overlay.className = 'glass up';
            } else {
                overlay.className = 'glass down';
            }
        }
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
                        console.log(i.html);
                        $("#requests .list").html(i.html);
                    }
                }
            });
        }
        $(document).ready(function() {
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
                            });
                        }
                    });
                }
            });
            $(".auto_comp").keyup(function() {
                var tgt = $(this).prop('id').split('_list')[0],
                    $this = $(this);
                $.ajax({
                    "url": "/api/complete/"+tgt
                });
            });
            setInterval(function() {
                autoRefresh();
            }, 3000);
        });
        </script>
    </head>
    <body>
        <h1 class="logo"><strong>Trax</strong>Selector<!--img src="/img/logo.png" alt="TraxSelector" width="300" /--></h1>
        

         
    
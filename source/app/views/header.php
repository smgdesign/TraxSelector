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
        $(document).ready(function() {
            
        });
        </script>
    </head>
    <body>
        <h1 class="logo"><strong>Trax</strong>Selector<!--img src="/img/logo.png" alt="TraxSelector" width="300" /--></h1>
        

         
    
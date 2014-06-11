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
        $(document).ready(function() {
            
        });
        </script>
    </head>
    <body>
        <div id="head">
            <img src="/img/logo.png" alt="TraxSelector" />
        </div>

         
    
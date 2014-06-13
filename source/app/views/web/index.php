<div id="requests">
    <table cellpadding="5" cellspacing="0">
    <?php
    if (count($requests) > 0) {
        foreach ($requests as $id=>$request) {
            ?>
        <tr id="<?php echo $id; ?>">
            <td><?php echo $request['artist']; ?></td>
            <td><?php echo $request['title']; ?></td>
        </tr>
            <?php
        }
    }
    ?>
    </table>
</div>
<div id="request_form"class="glass down">
            <h1 class="toggle" onclick="toggle();">Request Song</h1>
            <p>Request your song filling the list below</p>
</div>
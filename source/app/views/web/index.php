<div id="requests">
    <table cellpadding="0" cellspacing="0" class="list">
    <?php
    if (count($requests) > 0) {
        foreach ($requests as $id=>$request) {
            ?>
        <tr id="<?php echo $id; ?>">
            <td><?php echo $request['artist']; ?></td>
            <td><?php echo $request['title']; ?></td>
            <td width="25">Up</td>
            <td width="25">Down</td>
        </tr>
            <?php
        }
    }
    ?>
    </table>
</div>
<div id="request_form"class="glass down">
    <h1 class="toggle" onclick="toggle();">Request Song</h1>
    <div class="glass_content">
        <h2>Fill in the form to request your song</h2>
        <form action="/api/request/submit" method="post">
            <input type="text" name="artist" class="text auto_comp" id="artist_list" placeholder="Artist" />
            <input type="text" name="title" class="text auto_comp" id="title_list" placeholder="Title" />
            <input type="submit" name="send" value="Send Request" />
            <input type="hidden" name="submitted" value="TRUE" />
        </form>
    </div>
</div>
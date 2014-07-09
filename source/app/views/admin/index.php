<div class="split_box">
<h1>Requests</h1>
<ul class="requests">
<?php
if (!is_null($requests)) {
    foreach ($requests as $request) {
        $comments = array();
        $dedicate = array();
        if (isset($request['comments']) && !empty($request['comments'])) {
            foreach ($request['comments'] as $comment) {
                if (!empty($comment['comment'])) {
                    $comments[] = $comment['comment'];
                }
                if (!empty($comment['dedicate'])) {
                    $dedicate[] = $comment['dedicate'];
                }
            }
        }
        ?>
    <li class="request <?php echo ($request['status'] === 0) ? 'pending' : ''; ?>" id="request_<?php echo $request['id']; ?>">
        <?php echo ((!empty($dedicate)) ? '<span class="dedicate">'.implode('<br />', $dedicate).'</span>' : '').
                   ((!empty($comments)) ? '<span class="comment">'.implode('<br />', $comments).'</span>' : ''); ?>
                   <span class="text">
                       <span class="artist"><?php echo $request['artist']; ?></span> - 
                       <span class="title"><?php echo $request['title']; ?></span>
                   </span>
                   <div class="icons">
                       <?php
                       if ($request['status'] === 1) {
                       ?>
                       <span class="<?php echo (empty($dedicate)) ? 'inactive' : 'active'; ?> dedicate_icon"></span>
                       <span class="<?php echo (empty($comments)) ? 'inactive' : 'active'; ?> comment_icon"></span>
                       <span class="active play_icon"></span>
                       <?php
                       } else {
                       ?>
                       <span class="active cancel"></span>
                       <span class="active confirm"></span>
                       <?php
                       }
                       ?>
                   </div>
    </li>
        <?php
    }
    ?>
<li class="add"><span class="icon"></span><span class="text">Add to list</span></li>
<?php
}
?>
</ul>
</div>
<div class="split_box">
    <h1>Event</h1>
    <?php
    if (!is_null($event)) {
        $date  = new DateTime($event['date']);
        $endDate = new DateTime($event['end_date']);
        ?>
        <div class="event" id="event_<?php echo $event['id']; ?>"><span class="icon_edit"></span><span class="text"><?php echo $event['title'].'</span> <span class="date" date="'.$date->format('d/m/Y H:i').'" utc-date="'.$date->format('Y-m-d H:i').'" end-date="'.$endDate->format('d/m/Y H:i').'" utc-end-date="'.$endDate->format('Y-m-d H:i').'">'; ?></span></div>
        <?php
    } else {
        ?>
        <div class="event" id="event_-1"><span class="icon_edit"></span><span class="text">Create new event</span> <span class="date"></span></div>
        <?php
    }
    ?>
    <h1>Now Playing</h1>
    <?php
    if (!is_null($nowplaying)) {
        ?>
        <div class="nowplaying" id="nowplaying_<?php echo $nowplaying['id']; ?>">
            <span class="icon_edit"></span><span class="text"><?php echo $nowplaying['artist'].' - '.$nowplaying['title']; ?></span>
            <div class="dedicate">
                <span class="dedicate_msg"><?php echo implode('<br />', $nowplaying['dedicate']); ?></span><br />
                <span class="comment_msg"><?php echo implode('<br />', $nowplaying['comment']); ?></span>
            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="nowplaying" id="nowplaying_-1"><span class="icon_edit"></span><span class="text">Nothing is currently playing</span></div>
        <?php
    }
    ?>
</div>
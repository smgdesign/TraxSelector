<?php
/**
 * Bar App - 2014
 */
class Api extends Model {
    public function requests($venueID=0, $eventID=0, $ordering=false) {
        global $db;
        $list = array();
        if ($venueID != 0 && $eventID != 0) {
            $order = "";
            if ($ordering) {
                $order = "ORDER BY r.rating DESC";
            }
            $requests = $db->dbResult($db->dbQuery("SELECT r.*, a.artist, t.title FROM tbl_request AS r
                                                    INNER JOIN tbl_artist AS a ON a.id=r.artist_id
                                                    INNER JOIN tbl_title AS t ON t.id=r.title_id
                                                    WHERE r.venue_id=$venueID AND r.event_id=$eventID $order"));
            if ($requests[1] > 0) {
                foreach ($requests[0] as $request) {
                    $list[$request['id']] = $request;
                }
            }
        }
        return $list;
    }
    public function getLinks($venueID=0) {
        global $db;
        $list = array();
        if ($venueID != 0) {
            $links = $db->dbResult($db->dbQuery("SELECT link FROM tbl_items WHERE venue_id=$venueID"));
            if ($links[1] > 0) {
                foreach ($links[0] as $link) {
                    $list[] = $link['link'];
                }
            }
        }
        return $list;
    }
}
?>

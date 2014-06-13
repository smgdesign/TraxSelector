<?php
/**
 * Bar App - 2014
 */
class Api extends Model {
    public function requests($venueID=0, $eventID=0) {
        global $db;
        $list = array();
        if ($venueID != 0 && $eventID != 0) {
            $requests = $db->dbResult($db->dbQuery("SELECT * FROM tbl_request WHERE venue_id=$venueID AND event_id=$eventID ORDER BY rating DESC"));
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

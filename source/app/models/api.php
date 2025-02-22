<?php
/**
 * Bar App - 2014
 */
class Api extends Model {
    public function requests($venueID=0, $eventID=0, $ordering=false, $full=false) {
        global $db;
        $list = array();
        if ($venueID != 0 && $eventID != 0) {
            $order = "";
            if ($ordering) {
                $order = "ORDER BY r.status ASC, r.rating DESC";
            }
            $cols = ", c.id AS comment_id, c.dedicate, c.comment";
            $join = "LEFT JOIN tbl_comments AS c ON c.request_id=r.id";
            $cond = " AND r.status < 2";
            if (!$full) {
                $cond = " AND r.status=1";
            }
            $requests = $db->dbResult($db->dbQuery("SELECT r.*, a.artist, t.title $cols FROM tbl_request AS r
                                                    INNER JOIN tbl_artist AS a ON a.id=r.artist_id
                                                    INNER JOIN tbl_title AS t ON t.id=r.title_id
                                                    $join
                                                    WHERE r.venue_id=$venueID AND r.event_id=$eventID$cond $order"));
            if ($requests[1] > 0) {
                foreach ($requests[0] as $i=>$request) {
                    $request['status'] = (int)$request['status'];
                    if (!isset($list[$request['id']])) {
                        $list[$request['id']] = $request;
                        if ($full) {
                            $list[$request['id']]['comments'] = array();
                        }
                    }
                    if ($full) {
                        $list[$request['id']]['comments'][] = array('dedicate'=>$request['dedicate'], 'comment'=>$request['comment']);
                    }
                }
            }
        }
        return $list;
    }
    public function nowPlaying($venueID=0, $eventID=0) {
        global $db;
        $data = $db->dbResult($db->dbQuery("SELECT n.id, a.artist, t.title, c.dedicate, c.comment FROM tbl_now_playing AS n
                                            LEFT JOIN tbl_request AS r ON r.id=n.request_id
                                            LEFT JOIN tbl_artist AS a ON a.id=r.artist_id
                                            LEFT JOIN tbl_title AS t ON t.id=r.title_id
                                            LEFT JOIN tbl_comments AS c ON c.request_id=r.id
                                            WHERE n.venue_id=$venueID AND n.event_id=$eventID ORDER BY n.date_played DESC"));
        if ($data[1] > 0) {
            $nowPlaying = array();
            foreach ($data[0] as $i=>$comment) {
                if ($i == 0) {
                    $nowPlaying = $comment;
                    $nowPlaying['dedicate'] = array();
                    $nowPlaying['comment'] = array();
                }
                if (!empty($comment['dedicate'])) {
                    $nowPlaying['dedicate'][] = $comment['dedicate'];
                }
                if (!empty($comment['comment'])) {
                    $nowPlaying['comment'][] = $comment['comment'];
                }
            }
            return $nowPlaying;
        }
        return null;
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
    public function getArtist($id=0) {
        global $db;
        if (is_int($id) && $id > 0) {
            $artist = $db->dbResult($db->dbQuery("SELECT artist FROM tbl_artist WHERE id=$id"));
            if ($artist[1] > 0) {
                return $artist[0][0]['artist'];
            }
        }
        return null;
    }
    public function getTitle($id=0) {
        global $db;
        if (is_int($id) && $id > 0) {
            $title = $db->dbResult($db->dbQuery("SELECT title FROM tbl_title WHERE id=$id"));
            if ($title[1] > 0) {
                return $title[0][0]['title'];
            }
        }
        return null;
    }
    public function getArtistByName($name='') {
        global $db;
        if (!empty($name)) {
            $artist = $db->dbResult($db->dbQuery("SELECT id FROM tbl_artist WHERE artist='$name'"));
            if ($artist[1] > 0) {
                return $artist[0][0]['id'];
            } else {
                return $db->dbQuery("INSERT INTO tbl_artist (artist) VALUES ('$name')", 'id');
            }
        }
        return null;
    }
    public function getTitleByName($name='') {
        global $db;
        if (!empty($name)) {
            $title = $db->dbResult($db->dbQuery("SELECT id FROM tbl_title WHERE title='$name'"));
            if ($title[1] > 0) {
                return $title[0][0]['id'];
            } else {
                return $db->dbQuery("INSERT INTO tbl_title (title) VALUES ('$name')", 'id');
            }
        }
        return null;
    }
    public function getEventByID($id=0) {
        global $db;
        if (is_int($id) && $id != 0) {
            $data = $db->dbResult($db->dbQuery("SELECT * FROM tbl_event WHERE id=$id"));
            if ($data[1] > 0) {
                return $data[0][0];
            }
        }
        return null;
    }
    public function getEventByDate($date='') {
        global $db;
        if (!empty($date)) {
            $tmpDate = new DateTime($date);
            $data = $db->dbResult($db->dbQuery("SELECT * FROM tbl_event WHERE (date <= '{$tmpDate->format('Y-m-d H:i:00')}' AND end_date >= '{$tmpDate->format('Y-m-d H:i:00')}') OR (date = '{$tmpDate->format('Y-m-d H:i:00')}')"));
            if ($data[1] > 0) {
                return $data[0][0];
            }
        }
        return null;
    }
    public function getPhotos($venueID=0, $eventID=0) {
        global $db;
        if ($venueID != 0 && $eventID != 0) {
            $photos = $db->dbResult($db->dbQuery("SELECT url, date_added FROM tbl_photo WHERE venue_id=$venueID AND event_id=$eventID"));
            if ($photos[1] > 0) {
                foreach ($photos[0] as &$photo) {
                    $photo['url'] = '/api/photo/display/'.$photo['url'];
                }
                return $photos[0];
            }
        }
        return array();
    }
    public function getPhoto($url, $mode) {
        if ($mode == 'thumb') {
            $url = $mode.'/'.$url;
        }
        if (file_exists(uploadDir.$url)) {
            $ext = pathinfo(uploadDir.$url);
            $img = file_get_contents(uploadDir.$url);
            switch (strtolower($ext['extension'])) {
                case "jpg":
                case "jpeg":
                    header ("Content-Type: image/jpeg");
                    break;
                case "gif":
                    header ("Content-Type: image/gif");
                    break;
                case "png":
                    header ("Content-Type: image/png");
                    break;
            }
            echo $img;
        }
    }
}
?>

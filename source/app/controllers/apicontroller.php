<?php

/**
 * Dettol / Lysol - 2013
 * 
    request/getall //returns all requests from current venue
    request/nowplaying //returns the request thats currently playing (if any)
    venue/init //sets up user, returns userId
    request/rate //posts a rating for a given request_id
    request/submit // posts a request
 */
class ApiController extends Controller {
    public function venue($request='init') {
        if (!empty($request)) {
            $method = 'venue_'.$request;
            if (method_exists($this, $method)) {
                $params = func_get_args();
                call_user_func_array(array(&$this, $method), array_slice($params, 1));
            } else {
                $this->json = array('status'=>  \errors\codes::$__ERROR, 'return'=>'Unknown method');
            }
        }
    }
    
    
    public function venue_init() {
        global $auth;
        if ($this->checkContinue()) {
            $device = $auth->checkDevice();
            if (is_array($device)) {
                if (!is_null($auth->config['event_id'])) {
                    $welcome = array(
                        'data'=>array(
                            'message'=>'Accepted to the TraxSelector network',
                            'functions'=>$this->commands(true),
                            'venue_id'=>$auth->config['venue_id'],
                            'venue_title'=>$auth->config['venue_title'],
                            'venue_location'=>$auth->config['venue_location'],
                            'venue_image'=>$auth->config['venue_image'],
                            'event_id'=>$auth->config['event_id'],
                            'event_title'=>$auth->config['event_title'],
                            'event_date'=>$auth->config['event_date'],
                            'event_end_date'=>$auth->config['event_end_date'],
                            'user_id'=>$device['id'],
                            'return'=>$device['status']
                        )
                    );
                    if ($device['status'] == 'new') {
                        $this->json = array_merge($welcome, array('status'=> \errors\codes::$__SUCCESS));
                    } else {
                        $this->json = array_merge($welcome, array('status'=>  \errors\codes::$__FOUND));
                    }
                } else {
                    $this->json = array('status'=>  \errors\codes::$__EMPTY, 'return'=>'no event');
                }
            } else {
                $this->json = array('status'=>  \errors\codes::$__FOUND, 'return'=>$device['status']);
            }
        } else {
            $this->json = array('status'=>  \errors\codes::$__ERROR, 'return'=>'Not allowed');
        }
    }
    
    public function venue_event($type='id', $cond=0) {
        global $auth;
        if ($this->checkContinue()) {
            $device = $auth->checkDevice();
            if (is_array($device)) {
                switch ($type) {
                    case "id":
                        $data = $this->Api->getEventByID($cond);
                        break;
                    case "date":
                        $data = $this->Api->getEventByDate($cond);
                        break;
                }
                if (!is_null($data)) {
                    $this->json = array('status'=>  \errors\codes::$__FOUND, 'data'=>$data);
                } else {
                    $this->json = array('status'=>  \errors\codes::$__EMPTY, 'return'=>'No events');
                }
            } else {
                $this->json = array('status'=>  \errors\codes::$__FOUND, 'return'=>$device['status']);
            }
        } else {
            $this->json = array('status'=>  \errors\codes::$__ERROR, 'return'=>'Not allowed');
        }
    }
    
    
    public function request($request='submit') {
        global $auth;
        if ($this->checkContinue()) {
            $device = $auth->checkDevice();
            if (is_array($device) && $device['status'] == 'existing') {
                if (!empty($request)) {
                    $method = 'request_'.$request;
                    if (method_exists($this, $method)) {
                        $params = func_get_args();
                        call_user_func_array(array(&$this, $method), array_slice($params, 1));
                    }
                }
            }
        }
    }
    
    public function request_getall($mode='html') {
        global $common, $auth;
        if ($common->getParam('admin_request')) {
            $requests = $this->Api->requests($auth->config['venue_id'], $auth->config['event_id'], true, true);
        } else {
            $requests = $this->Api->requests($auth->config['venue_id'], $auth->config['event_id']);
        }
        if ($mode == 'html') {
            $this->json = array(
                'html'=>$this->_template->listRequests($requests, $common->getParam('admin_request')),
                'status'=>  (count($requests) > 0) ? \errors\codes::$__FOUND : \errors\codes::$__EMPTY
            );
        } else {
            $this->json = array(
                'data'=>$this->_template->listRequests($requests, $common->getParam('admin_request'), 'json'),
                'status'=>(count($requests) > 0) ? \errors\codes::$__FOUND : \errors\codes::$__EMPTY
            );
        }
    }
    
    public function request_submit() {
        global $auth, $common, $db;
        if (!is_null($common->getParam('submitted'))) {
            $artist = $common->getParam('artist');
            $title = $common->getParam('title');
            $dedicate = $common->getParam('dedicate');
            $message = $common->getParam('message');
            $status = $common->getParam('status', 'post', 0);
            $cont = true;
            if (is_null($artist)) {
                $cont = false;
            }
            if (is_null($title)) {
                $cont = false;
            }
            if ($cont) {
                // get the artist ID - if empty then create new one \\
                $artistID = $this->Api->getArtistByName(ucwords(strtolower($artist)));
                $titleID = $this->Api->getTitleByName(ucwords(strtolower($title)));
                // select the request - if it exists then add to the rating \\
                $exist = $db->dbResult($db->dbQuery("SELECT id, rating FROM tbl_request WHERE title_id=$titleID AND artist_id=$artistID AND venue_id={$auth->config['venue_id']} AND event_id={$auth->config['event_id']}"));
                if ($exist[1] > 0) {
                    $rating = $exist[0][0]['rating']+1;
                    $id = $exist[0][0]['id'];
                    // now update the rating +1 and update the db \\
                    $db->dbQuery("UPDATE tbl_request SET rating=$rating WHERE id=$id");
                } else {
                    $id = $db->dbQuery("INSERT INTO tbl_request (title_id, artist_id, date_requested, venue_id, event_id, rating, status) VALUES ($titleID, $artistID, NOW(), {$auth->config['venue_id']}, {$auth->config['event_id']}, 1, $status)", 'id');
                }
                if (!is_null($dedicate) || !is_null($message)) {
                    // insert a row for the dedication / message \\
                    $db->dbQuery("INSERT INTO tbl_comments (request_id, dedicate, comment) VALUES ($id, '$dedicate', '$message')");
                }
                $this->json = array(
                    'status'=>  \errors\codes::$__SUCCESS,
                    'data'=>array(
                        'id'=>$id
                    )
                );
            } else {
                $this->json = array(
                    'status'=>  \errors\codes::$__ERROR,
                    'return'=>'Artist and Title are required'
                );
            }
        } else {
            $this->json = array(
                'status'=>  \errors\codes::$__ERROR,
                'return'=>'Malformed form submission'
            );
        }
        
    }
    
    public function request_confirm() {
        global $common, $db;
        if (!is_null($common->getParam('submitted'))) {
            $id = $common->getParam('id');
            if (!is_null($id)) {
                $db->dbQuery("UPDATE tbl_request SET status=1 WHERE id=$id");
                $this->json = array(
                    "status"=>  \errors\codes::$__SUCCESS,
                    'return'=>'Successfully confirmed'
                );
            } else {
                $this->json = array(
                    "status"=>  \errors\codes::$__ERROR,
                    'return'=>'ID is required'
                );
            }
        } else {
            $this->json = array(
                "status"=>  \errors\codes::$__ERROR,
                'return'=>'Malformed form submission'
            );
        }
    }
    
    public function request_cancel() {
        global $common, $db;
        if (!is_null($common->getParam('submitted'))) {
            $id = $common->getParam('id');
            if (!is_null($id)) {
                $db->dbQuery("DELETE FROM tbl_request WHERE id=$id");
                $this->json = array(
                    "status"=>  \errors\codes::$__SUCCESS,
                    'return'=>'Successfully confirmed'
                );
            } else {
                $this->json = array(
                    "status"=>  \errors\codes::$__ERROR,
                    'return'=>'ID is required'
                );
            }
        } else {
            $this->json = array(
                "status"=>  \errors\codes::$__ERROR,
                'return'=>'Malformed form submission'
            );
        }
    }
    
    public function request_rate() {
        global $common, $db;
        if (!is_null($common->getParam('submitted'))) {
            $id = $common->getParam('id');
            $mode = $common->getParam('mode');
            $val = ($mode === 'up') ? 1 : -1;
            $db->dbQuery("UPDATE tbl_request SET rating=rating+$val WHERE id=$id");
            $this->json = array(
                'status'=>  \errors\codes::$__SUCCESS,
                'return'=>'Successfully rated'
            );
        } else {
            $this->json = array(
                'status'=>  \errors\codes::$__ERROR,
                'return'=>'Malformed form submission'
            );
        }
    }
    public function request_nowplaying() {
        global $auth, $db;
        $playing = $this->Api->nowPlaying($auth->config['venue_id'], $auth->config['event_id']);
        if (!is_null($playing)) {
            $this->json = array(
                'data'=>$playing,
                'status'=>  \errors\codes::$__FOUND,
                'return'=>'Item is now playing'
            );
        } else {
            $this->json = array(
                'status'=>  \errors\codes::$__EMPTY,
                'return'=>'No item is playing'
            );
        }
    }
    
    public function admin($request='event') {
        global $auth;
        $this->level = 1;
        if ($this->checkContinue()) {
            $device = $auth->checkDevice();
            if (is_array($device) && $device['status'] == 'existing') {
                if (!empty($request)) {
                    $method = 'admin_'.$request;
                    if (method_exists($this, $method)) {
                        $params = func_get_args();
                        call_user_func_array(array(&$this, $method), array_slice($params, 1));
                    }
                }
            }
        }
    }
    public function admin_event($id=-1) {
        global $auth, $common, $db;
        if ($id == -1) {
            // is an insert \\
            if (!is_null($common->getParam('submitted'))) {
                $name = $common->getParam('name');
                $date = $common->getParam('date');
                $endDate = $common->getParam('end_date');
                $dateObj = new DateTime($date);
                $endDateObj = new DateTime($endDate);
                if ($dateObj !== false && $endDateObj !== false) {
                    $id = $db->dbQuery("INSERT INTO tbl_event (venue_id, title, poster, date, end_date) VALUES ({$auth->config['venue_id']}, '$name', '', '{$dateObj->format('Y-m-d H:i:s')}', '{$endDateObj->format('Y-m-d H:i:s')}')", 'id');
                    $this->json = array(
                        'status'=>  \errors\codes::$__SUCCESS,
                        'return'=>'Successfully inserted',
                        'id'=>$id
                    );
                } else {
                    $this->json = array(
                        'status'=>  \errors\codes::$__ERROR,
                        'return'=>'Invalid date'
                    );
                }
            } else {
                $this->json = array(
                    'status'=>  \errors\codes::$__ERROR,
                    'return'=>'Malformed submission'
                );
            }
        } else {
            // is an update \\
            if (!is_null($common->getParam('submitted'))) {
                $name = $common->getParam('name');
                $date = $common->getParam('date');
                $endDate = $common->getParam('end_date');
                $dateObj = new DateTime($date);
                $endDateObj = new DateTime($endDate);
                if ($dateObj !== false && $endDateObj !== false) {
                    $db->dbQuery("UPDATE tbl_event SET venue_id={$auth->config['venue_id']}, title='$name', poster='', date='{$dateObj->format('Y-m-d H:i:s')}', end_date='{$endDateObj->format('Y-m-d H:i:s')}' WHERE id=$id");
                    $this->json = array(
                        'status'=>  \errors\codes::$__SUCCESS,
                        'return'=>'Successfully updated',
                        'id'=>$id
                    );
                } else {
                    $this->json = array(
                        'status'=>  \errors\codes::$__ERROR,
                        'return'=>'Invalid date'
                    );
                }
            } else {
                $this->json = array(
                    'status'=>  \errors\codes::$__ERROR,
                    'return'=>'Malformed submission'
                );
            }
        }
    }
    
    public function admin_update() {
        global $common, $db;
        if (!is_null($common->getParam('submitted'))) {
            // get the ID of the artist or title \\
            $IDs = $db->dbResult($db->dbQuery("SELECT artist_id, title_id FROM tbl_request WHERE id={$common->getParam('id')}"));
            if ($IDs[1] > 0) {
                if (!is_null($common->getParam('artist'))) {
                    $db->dbQuery("UPDATE tbl_artist SET artist='{$common->getParam('artist')}', status=1 WHERE id={$IDs[0][0]['artist_id']}");
                }
                if (!is_null($common->getParam('title'))) {
                    $db->dbQuery("UPDATE tbl_title SET title='{$common->getParam('title')}', status=1 WHERE id={$IDs[0][0]['title_id']}");
                }
                $this->json = array(
                    'status'=>  \errors\codes::$__SUCCESS,
                    'return'=>'Successfully updated'
                );
            } else {
                $this->json = array(
                    'status'=>  \errors\codes::$__ERROR,
                    'return'=>'Item not found'
                );
            }
        } else {
            $this->json = array(
                'status'=>  \errors\codes::$__ERROR,
                'return'=>'Malformed submission'
            );
        }
    }
    
    public function admin_play($id=-1) {
        global $db;
        if ($id != -1) {
            $req = $db->dbResult($db->dbQuery("SELECT venue_id, event_id FROM tbl_request WHERE id=$id"));
            if ($req[1] > 0) {
                $db->dbQuery("INSERT INTO tbl_now_playing (request_id, venue_id, event_id) VALUES ($id, {$req[0][0]['venue_id']}, {$req[0][0]['event_id']})");
                $db->dbQuery("UPDATE tbl_request SET status=2 WHERE id=$id");
                $this->json = array(
                    'status'=>  \errors\codes::$__SUCCESS,
                    'return'=>'Now playing'
                );
            } else {
                $this->json = array(
                    'status'=>  \errors\codes::$__NOTFOUND,
                    'return'=>'Request not found'
                );
            }
        } else {
            $this->json = array(
                    'status'=>  \errors\codes::$__NOTFOUND,
                    'return'=>'ID is required'
                );
        }
    }
    
    public function complete($tgt='') {
        if (!empty($tgt)) {
            global $common;
            if (!is_null($common->getParam('submitted'))) {
                if (!is_null($common->getParam('qry'))) {    
                    switch ($tgt) {
                        case "venue":
                            $tbl = array('v'=>'tbl_venue');
                            $selItems = \data\collection::buildQuery("SELECT", $tbl, array(), array(
                                "v"=>array("id", "title")
                            ), array(
                                "v"=>array(
                                        array(
                                            array(
                                                "operand"=>"LIKE",
                                                "col"=>"title",
                                                "value"=>"'%{$common->getParam('qry')}%'"
                                            )
                                        )
                                )
                            ));
                            break;
                        case "artist":
                            $tbl = array('a'=>'tbl_artist');
                            $selItems = \data\collection::buildQuery("SELECT", $tbl, array(), array(
                                "a"=>array("id", "artist AS title")
                            ), array(
                                "a"=>array(
                                        array(
                                            "join"=>"AND",
                                            array(
                                                "operand"=>"LIKE",
                                                "col"=>"artist",
                                                "value"=>"'%{$common->getParam('qry')}%'"
                                            ),
                                            array(
                                                "operand"=>"=",
                                                "col"=>"status",
                                                "value"=>1
                                            )
                                        )
                                )
                            ));
                            break;
                        case "title":
                            $tbl = array('t'=>'tbl_title');
                            $selItems = \data\collection::buildQuery("SELECT", $tbl, array(), array(
                                "t"=>array("id", "title")
                            ), array(
                                "t"=>array(
                                        array(
                                            "join"=>"AND",
                                            array(
                                                "operand"=>"LIKE",
                                                "col"=>"title",
                                                "value"=>"'%{$common->getParam('qry')}%'"
                                            ),
                                            array(
                                                "operand"=>"=",
                                                "col"=>"status",
                                                "value"=>1
                                            )
                                        )
                                )
                            ));
                            break;
                    }
                    if ($selItems[1] > 0) {
                        $this->json = array(
                            'data'=>$selItems[0],
                            'status'=>  \errors\codes::$__FOUND
                        );
                    }
                }
            }
        }
    }
    
}

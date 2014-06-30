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
        global $auth;
        if ($mode == 'html') {
            $requests = $this->Api->requests($auth->config['venue_id'], $auth->config['event_id']);
            $this->json = array(
                'html'=>$this->_template->listRequests($requests),
                'status'=>  (count($requests) > 0) ? \errors\codes::$__FOUND : \errors\codes::$__EMPTY
            );
        } else {
            $this->json = array(
                'data'=>$this->Api->requests($auth->config['venue_id'], $auth->config['event_id']),
                'status'=>(count($requests) > 0) ? \errors\codes::$__FOUND : \errors\codes::$__EMPTY
            );
        }
    }
    
    public function request_nowplaying() {
        
    }
    
    public function request_submit() {
        global $auth, $common, $db;
        if (!is_null($common->getParam('submitted'))) {
            $artist = $common->getParam('artist');
            $title = $common->getParam('title');
            $dedicate = $common->getParam('dedicate');
            $message = $common->getParam('message');
            $cont = true;
            if (is_null($artist)) {
                $cont = false;
            }
            if (is_null($title)) {
                $cont = false;
            }
            if ($cont) {
                // get the artist ID - if empty then create new one \\
                $artistID = $this->Api->getArtistByName(ucwords($artist));
                $titleID = $this->Api->getTitleByName(ucwords($title));
                // select the request - if it exists then add to the rating \\
                $exist = $db->dbResult($db->dbQuery("SELECT id, rating FROM tbl_request WHERE title_id=$titleID AND artist_id=$artistID AND venue_id={$auth->config['venue_id']} AND event_id={$auth->config['event_id']}"));
                if ($exist[1] > 0) {
                    $rating = $exist[0][0]['rating']+1;
                    $id = $exist[0][0]['id'];
                    // now update the rating +1 and update the db \\
                    $db->dbQuery("UPDATE tbl_request SET rating=$rating WHERE id=$id");
                } else {
                    $id = $db->dbQuery("INSERT INTO tbl_request (title_id, artist_id, date_requested, venue_id, event_id, rating) VALUES ($titleID, $artistID, NOW(), {$auth->config['venue_id']}, {$auth->config['event_id']}, 1)", 'id');
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
    
    public function request_rate() {
        
    }
    
}

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
            if (method_exists($this, $request)) {
                $params = func_get_args();
                $this->venue_{$request}(array_slice($params, 1));
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
                            'event_end_date'=>$auth->config['event_end_date']
                        )
                    );
                    if ($device['status'] == 'new') {
                        $this->json = array_merge($welcome, array('status'=> \errors\codes::$__SUCCESS, 'user_id'=>$device['id'], 'return'=>$device['status']));
                    } else {
                        $this->json = array_merge($welcome, array('status'=>  \errors\codes::$__FOUND, 'user_id'=>$device['id'], 'return'=>$device['status']));
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
                    if (method_exists($this, $request)) {
                        $params = func_get_args();
                        $this->request_{$request}(array_slice($params, 1));
                    }
                }
            }
        }
    }
    
    public function request_getall() {
        
    }
    
    public function request_nowplaying() {
        
    }
    
    public function request_submit() {
        global $common;
        if (!is_null($common->getParam('submitted'))) {
            $artist = $common->getParam('artist');
            $title = $common->getParam('title');
            $dedicate = $common->getParam('dedicate');
            $message = $common->getParam('message');
            if (!is_null($artist)) {
                
            }
            if (!is_null($title)) {
                
            }
            if (!is_null($dedicate)) {
                
            }
            if (!is_null($message)) {
                
            }
        }
        
    }
    
    public function request_rate() {
        
    }
    
}

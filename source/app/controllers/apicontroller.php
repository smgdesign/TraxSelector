<?php

/**
 * Dettol / Lysol - 2013
 */
class ApiController extends Controller {
    public function venue($request='init') {
        if (!empty($request)) {
            if (method_exists($this, $request)) {
                $params = func_get_args();
                $this->{$request}(array_slice($params, 1));
            }
        }
    }
    
    
    public function init() {
        global $auth;
        if ($this->checkContinue()) {
            $device = $auth->checkDevice();
            if (is_array($device)) {
                $welcome = array(
                    'message'=>'Accepted to the TraxSelector network',
                    'functions'=>$this->commands(true),
                    'venue_id'=>$auth->config['venue_id'],
                    'event_id'=>$auth->config['event_id'],
                    'event_date'=>$auth->config['event_date']
                );
                if ($device['status'] == 'new') {
                    $this->json = array_merge($welcome, array('status'=> \errors\codes::$__FOUND, 'user_id'=>$device['id'], 'return'=>$device['status']));
                } else {
                    $this->json = array_merge($welcome, array('status'=>  \errors\codes::$__SUCCESS, 'return'=>$device['status']));
                }
            } else {
                $this->json = array('status'=>  \errors\codes::$__SUCCESS, 'return'=>$device['status']);
            }
        } else {
            $this->json = array('status'=>  \errors\codes::$__ERROR, 'return'=>'Not allowed');
        }
    }
    
    
    public function request($request='add') {
        
    }
    public function add() {
        
    }
}

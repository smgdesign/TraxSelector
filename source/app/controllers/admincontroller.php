<?php

/**
 * Dettol / Lysol - 2013
 */
class AdminController extends Controller {
    protected $img;
    protected $imageResized;
    protected $height;
    protected $width;
    var $api = null;
    public function __construct($model, $controller, $action) {
        global $common;
        parent::__construct($model, $controller, $action);
        $this->isJSON = false;
        $this->_template->xhr = false;
        $this->api = $common->loadController('api');
        $common->isPage = true;
    }
    public function index() {
        global $auth;
        $this->level = 1;
        // index can't have parameters otherwise they'd become the view \\
        $this->set('title', 'TraxSelector Admin');
        // this is where we view the requests \\
        if (is_null($auth->config['event_id'])) {
            $this->set('requests', null);
            $this->set('event', NULL);
            $this->set('nowplaying', NULL);
        } else {
            $this->set('requests', $this->api->Api->requests($auth->config['venue_id'], $auth->config['event_id'], true, true));
            $this->set('event', $this->api->Api->getEventByID($auth->config['event_id']));
            $this->set('nowplaying', $this->api->Api->nowPlaying($auth->config['venue_id'], $auth->config['event_id']));
        }
    }
    public function super($action='login') {
        $this->level = 2;
        // this is where we create everything for a venue \\
    }
    public function super_login() {
        
    }
}

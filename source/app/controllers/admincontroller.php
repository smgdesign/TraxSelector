<?php

/**
 * Dettol / Lysol - 2013
 */
class AdminController extends Controller {
    protected $img;
    protected $imageResized;
    protected $height;
    protected $width;
    public function index() {
        $this->level = 1;
        // index can't have parameters otherwise they'd become the view \\
        $this->set('title', 'TraxSelector Admin');
        // this is where we view the requests \\
    }
    public function super($action='login') {
        $this->level = 2;
        // this is where we create everything for a venue \\
    }
    public function super_login() {
        
    }
}

<?php

/**
 * Dettol / Lysol - 2013
 */
class AuthController extends Controller {
    public function __construct($model, $controller, $action) {
        global $common;
        parent::__construct($model, $controller, $action);
        $this->isJSON = false;
        $this->_template->xhr = false;
        $common->isPage = true;
    }
    public function login($prevURL='', $msg='') {
        global $auth, $common;
        $this->set('title', 'Login');
        if (!empty($msg)) {
            $this->set('msg', $msg);
        }
        if (!is_null($common->getParam('submitted'))) {
            //$common->isPage = false;
            $loginScript = $auth->login($common->getParam('venue'), $common->getParam('password'), '/admin');
            $this->set('errors', $loginScript);
        }
    }
    public function logout() {
        global $auth;
        $auth->logout();
    }
}

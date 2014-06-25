<?php

/**
 * Dettol / Lysol - 2013
 */

/**
 * Description of auth
 *
 * @author richard
 */
class authentication {
    var $loggedIn = false;
    var $level = 0;
    var $config = array();
    public function __construct() {
        global $session, $db, $common;
        require_once ROOT . DS . 'lib' . DS . '/enc/__init.php';
        $this->encrypt = new Bcrypt();
        $venue = $db->dbResult($db->dbQuery("SELECT value FROM tbl_config WHERE name='{$common->getMacAddr()}'"));
        if ($venue[1] > 0) {
            $this->config['venue_id'] = $venue[0][0]['value'];
        }
        $event = $db->dbResult($db->dbQuery("SELECT id, title, date, end_date FROM tbl_event WHERE venue_id={$this->config['venue_id']} AND date <= NOW() AND end_date >= NOW() ORDER BY date DESC LIMIT 1"));
        if ($event[1] > 0) {
            $this->config['event_id'] = $event[0][0]['id'];
            $this->config['event_title'] = $event[0][0]['title'];
            $this->config['event_date'] = $event[0][0]['date'];
            $this->config['event_end_date'] = $event[0][0]['end_date'];
            if (!is_null($session->getVar('id')) && $session->getVar('user_agent') == md5($common->getParam('HTTP_USER_AGENT', 'server'))) {
                $cur = new DateTime();
                $this->loggedIn = true;
                $this->level = $session->getVar('level');
                $session->addVar('last_action', $cur->getTimestamp());
            }
        } else {
            $this->config['event_id'] = null;
        }
    }
    public function setAPI($key='') {
        $this->config['api_key'] = $key;
    }
    public function login($username='', $pass='', $nextURL='') {
        global $common, $db, $session;
        if (empty($nextURL)) {
            $nextURL = '/';
        }
        if (!empty($username) && !empty($pass)) {
            $passSalt = $this->encrypt->generateSalt($username);
            $passEnc = $this->encrypt->generateHash($passSalt, $pass);
            $user = $db->dbResult($db->dbQuery("SELECT u.id, u.username, u.location_id, u.level, v.parent_id FROM tbl_users AS u
                                                LEFT JOIN tbl_venue AS v ON v.id=u.location_id
                                                WHERE u.username='$username' AND u.password='$passEnc'"));
            if ($user[1] > 0) {
                $session->addVar('id', $user[0][0]['id']);
                $session->addVar('username', $user[0][0]['username']);
                $session->addVar('location_id', $user[0][0]['location_id']);
                $session->addVar('venue_id', $user[0][0]['parent_id']);
                $session->addVar('level', $user[0][0]['level']);
                $cur = new DateTime();
                $session->addVar('last_action', $cur->getTimestamp());
                $session->addVar('user_agent', md5($common->getParam('HTTP_USER_AGENT', 'server')));
                header("Location: $nextURL");
                exit();
            } else {
                $err['system'][] = 'Unfortunately your details could not be found.';
            }
        } else {
            $common->isPage = true;
            $err = array('fields'=>array());
            if (empty($username)) {
                $err['fields'][] = 'Please enter a username.';
            }
            if (empty($pass)) {
                $err['fields'][] = 'Please enter a password.';
            }
        }
        return $err;
    }
    public function register() {
        global $common, $db;
        $err = array('fields'=>array());
        if (!$this->loggedIn) {
            $locationID = $common->getParam('location_id');
            if (!is_null($locationID)) {
                if (!is_null($common->getParam('submitted'))) {
                    $username = $common->getParam('username');
                    $pass = $common->getParam('password');
                    if (is_null($username)) {
                        $err['fields'][] = 'Please enter a username.';
                    } else {
                        $exists = $db->dbResult($db->dbQuery("SELECT id FROM tbl_users WHERE username='$username'"));
                        if ($exists[1] > 0) {
                            $err['fields'][] = 'Your username already exists.';
                        }
                    }
                    if (is_null($pass)) {
                        $err['fields'][] = 'Please enter a password.';
                    }
                    if ($pass != $common->getParam('conf-password')) {
                        $err['fields'][] = 'Your passwords must match.';
                    }
                    if (empty($err['fields'])) {
                        $passSalt = $this->encrypt->generateSalt($username);
                        $passEnc = $this->encrypt->generateHash($passSalt, $pass);
                        $ins = $db->dbQuery("INSERT INTO tbl_users (username, password, location_id, level) VALUES ('$username', '$passEnc', $locationID, 1)", 'id');
                        if (is_int($ins)) {
                            $this->login($username, $pass);
                        }
                    }
                }
            } else {
                $err['system'] = 'You appear to have followed an incorrect link.';
            }
        } else {
            $err['system'] = 'already logged in';
        }
        $common->isPage = true;
        return $err;
    }
    
    public function checkDevice() {
        global $common, $db, $session;
        $UUID = $common->getParam('UUID');
        $exists = $db->dbResult($db->dbQuery("SELECT id FROM tbl_mobile_device WHERE UDID='$UUID'"));
        if ($exists[1] > 0) {
            // this exists \\
            return array('id'=>$exists[0][0]['id'], 'status'=>'existing');
        } else {
            $ins = $db->dbQuery("INSERT INTO tbl_mobile_device (UDID) VALUES ('$UUID')", 'id');
            if ($ins) {
                return array('id'=>$ins, 'status'=>'new');
            } else {
                return array('status'=>'error');
            }
        }
    }
    
    public function getDevice() {
        global $common, $db, $session;
        if (!is_null($session->getVar('UUID'))) {
            $UUID = $session->getVar('UUID');
        }
        if (!is_null($common->getParam('UUID'))) {
            $UUID = $common->getParam('UUID');
            
        }
        if (isset($UUID)) {
            $id = $db->dbResult($db->dbQuery("SELECT id, name, last_sync, venue_id FROM tbl_device WHERE UUID='$UUID'"));
            if ($id[1] > 0) {
                return $id[0][0];
            }
        }
        return false;
    }
    
    public function logout($msg='') {
        global $session;
        $session->destroySession();
        if (!empty($msg)) {
            header ("Location: /auth/login/$msg");
            exit();
        }
        header("Location: /auth/login");
        exit();
    }
    private function generatePassword($level=5,$length=10) {
	$chars[1] = "1234567890";
	$chars[2] = "abcdefghijklmnopqrstuvwxyz";
	$chars[3] = "0123456789abcdefghijkmnopqrstuvwxyz";
	$chars[4] = "0123456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";
	$chars[5] = "0123456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ!@#$%^&*?=+-_";
	$i = 0;
	$str = "";
	while ($i<=$length) {
            $str .= $chars[$level][mt_rand(0,strlen($chars[$level])-1)];
            $i++;
	}
	return $str;
}
}

?>

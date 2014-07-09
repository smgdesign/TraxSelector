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
    protected $device = array();
    public function __construct() {
        global $session, $db, $common;
        require_once ROOT . DS . 'lib' . DS . '/enc/__init.php';
        $this->encrypt = new Bcrypt();
        if (is_null($common->getParam('UUID'))) {
            // this is a static device \\
            $ip = $common->getParam('REMOTE_ADDR', 'server');
            $venue = $db->dbResult($db->dbQuery("SELECT c.value, d.id, v.type FROM tbl_config AS c
                                                 LEFT JOIN tbl_device AS d ON d.IP=INET_ATON('$ip')
                                                 LEFT JOIN tbl_device_venues AS v ON v.venue_id=c.value AND v.device_id=d.id AND v.type='static'
                                                 WHERE c.name='{$common->getMacAddr()}'"));
        } else {
            // this is a mobile device \\
            $venue = $db->dbResult($db->dbQuery("SELECT c.value, m.id, v.type FROM tbl_config AS c
                                                 LEFT JOIN tbl_mobile_device AS m ON m.UDID='{$common->getParam('UUID')}'
                                                 LEFT JOIN tbl_device_venues AS v ON v.venue_id=c.value AND v.device_id=m.id AND v.type='mobile'
                                                 WHERE c.name='{$common->getMacAddr()}'"));
        }
        if ($venue[1] > 0) {
            $this->config['venue_id'] = (int)$venue[0][0]['value'];
            if (isset($venue[0][0]['id'])) {
                $this->device['id'] = (int)$venue[0][0]['id'];
                $this->device['status'] = 'existing';
            } else if (!is_null($common->getParam('UUID'))) {
                $this->device['id'] = $this->addDevice($common->getParam('UUID'));
                $this->device['status'] = 'new';
            }
            if (isset($venue[0][0]['type'])) {
                $this->device['type'] = $venue[0][0]['type'];
            } else if (!is_null($common->getParam('UUID'))) {
                $this->device['type'] = 'mobile';
                $this->addDeviceHook($this->device['id'], $this->config['venue_id']);
            } else {
                // means something isn't right \\
                die("You are trying to access using an unregistered static device: $ip");
            }
        } else {
            // means something isn't right \\
            die("You are trying to access using an unregistered static device: {$common->getMacAddr()}");
        }
        $event = $db->dbResult($db->dbQuery("SELECT id, title, date, end_date FROM tbl_event WHERE venue_id={$this->config['venue_id']} AND date <= NOW() AND end_date >= NOW() ORDER BY date DESC LIMIT 1"));
        if ($event[1] > 0) {
            $this->config['event_id'] = (int)$event[0][0]['id'];
            $this->config['event_title'] = $event[0][0]['title'];
            $this->config['event_date'] = $event[0][0]['date'];
            $this->config['event_end_date'] = $event[0][0]['end_date'];
        } else {
            $this->config['event_id'] = null;
        }
        if ($session->getVar('level') > 0) {
            if (!is_null($session->getVar('id')) && $session->getVar('user_agent') == md5($common->getParam('HTTP_USER_AGENT', 'server'))) {
                $cur = new DateTime();
                $this->loggedIn = true;
                $this->level = $session->getVar('level');
                $session->addVar('last_action', $cur->getTimestamp());
            }
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
            $passEnc = md5($pass);
            $user = $db->dbResult($db->dbQuery("SELECT v.id, v.location, v.title, v.image FROM tbl_venue AS v
                                                WHERE v.title='$username' AND v.password='$passEnc'"));
            if ($user[1] > 0) {
                $session->addVar('id', $user[0][0]['id']);
                $session->addVar('location', $user[0][0]['location']);
                $session->addVar('title', $user[0][0]['title']);
                $session->addVar('image', $user[0][0]['image']);
                $session->addVar('level', 1);
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
                $err['fields'][] = 'Please enter a location.';
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
        return $this->device;
    }
    
    public function addDevice($UUID) {
        global $db;
        return $db->dbQuery("INSERT INTO tbl_mobile_device (UDID) VALUES ('$UUID')", 'id');
    }
    public function addDeviceHook($deviceID, $venueID) {
        global $db;
        return $db->dbQuery("INSERT INTO tbl_device_venues (device_id, venue_id, type, last_visit) VALUES ($deviceID, $venueID, '{$this->device['type']}', NOW())", 'id');
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

<?php

/**
 * Bar App - 2014
 */
class Template {
    var $statuses = array('Order Placed', 'In Progress', 'Serving', 'Completed', 'Problem with Order');
    protected $variables = array();
    protected $_controller;
    protected $_action;
    protected $_model;
    var $headIncludes = array();
    var $xhr = true;
    function __construct($controller,$action, $model) {
        $this->_controller = $controller;
        $this->_action = $action;
        $this->_model = $model;
    }
 
    /** Set Variables **/
 
    function set($name,$value) {
        $this->variables[$name] = $value;
    }
 
    /** Display Template **/
     
    function render() {
        global $auth, $common, $session;
        extract($this->variables);
        header('Content-Type:text/html; charset=UTF-8');
        if (!$this->xhr) {
            if (file_exists(ROOT . DS . 'app' . DS . 'views' . DS . $this->_controller . DS . 'header.php')) {
                include (ROOT . DS . 'app' . DS . 'views' . DS . $this->_controller . DS . 'header.php');
            } else {
                include (ROOT . DS . 'app' . DS . 'views' . DS . 'header.php');
            }
        }
        include (ROOT . DS . 'app' . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php');       
        if (!$this->xhr) {
            if (file_exists(ROOT . DS . 'app' . DS . 'views' . DS . $this->_controller . DS . 'footer.php')) {
                include (ROOT . DS . 'app' . DS . 'views' . DS . $this->_controller . DS . 'footer.php');
            } else {
                include (ROOT . DS . 'app' . DS . 'views' . DS . 'footer.php');
            }
        }
    }
    
    public function orderStatus($status) {
        if (isset($this->statuses[$status])) {
            return $this->statuses[$status];
        }
        return $this->statuses[0];
    }
    public function orderStatuses() {
        return $this->statuses;
    }
    public function selectList($list=array(), $sel='') {
        $output = '';
        if (!empty($list)) {
            foreach ($list as $val=>$item) {
                $output .= '<option value="'.$val.'" '.(($val == $sel) ? 'selected="selected"' : '').'>'.$item.'</option>';
            }
        }
        return $output;
    }
    public function radioList($name='', $list=array(), $sel='') {
        $output = '';
        if (!empty($list)) {
            $UID = uniqid('item_');
            $i = 0;
            foreach ($list as $val=>$item) {
                $output .= '<label for="'.$UID.'_'.$i.'">'.$item.'</label><input name="'.$name.'" type="radio" id="'.$UID.'_'.$i.'" value="'.$val.'" '.(($val == $sel) ? 'checked="checked"' : '').' />';
                $i++;
            }
        }
        return $output;
    }
    public function listRequests($requests, $isAdmin=null, $json=false) {
        global $common;
        $output = '';
        $jsonResp = array();
        if (!is_null($requests) && count($requests) > 0) {
            foreach ($requests as $id=>$request) {
                if ($isAdmin) {
                    $comments = array();
                    $dedicate = array();
                    if (isset($request['comments']) && !empty($request['comments'])) {
                        foreach ($request['comments'] as $comment) {
                            if (!empty($comment['comment'])) {
                                $comments[] = $comment['comment'];
                            }
                            if (!empty($comment['dedicate'])) {
                                $dedicate[] = $comment['dedicate'];
                            }
                        }
                    }
                    $request['dedicate'] = $dedicate;
                    $request['comment'] = $comments;
                    $tmpOutput = '<li class="request" id="request_'.$id.'">
                        '.((!empty($dedicate)) ? '<span class="dedicate">'.implode('<br />', $dedicate).'</span>' : '').
                        ((!empty($comments)) ? '<span class="comment">'.implode('<br />', $comments).'</span>' : '').
                        '<span class="text">'.$request['artist'].' - '.$request['title'].'</span>
                        <div class="icons">
                        '.(($request['status'] == 1) ? '
                            <span class="'.((empty($dedicate)) ? 'inactive' : 'active').' dedicate_icon"></span>
                            <span class="'.((empty($comments)) ? 'inactive' : 'active').' comment_icon"></span>
                            <span class="active play_icon"></span>' :
                        '
                            <span class="active cancel"></span>
                            <span class="active confirm"></span>
                        ').'
                        </div>
                    </li>';
                } else {
                    $tmpOutput = '<tr id="'.$id.'">
                        <td>'.$request['artist'].'</td>
                        <td>'.$request['title'].'</td>
                        <td width="25" class="vote vote_up"><div class="icon"></div></td>
                        <td width="25" class="vote vote_down"><div class="icon"></div></td>
                    </tr>';
                }
                if (!$json) {
                    $output .= $tmpOutput;
                } else {
                    $jsonResp[$id] = $request;
                }
            }
        }
        if (!$json) {
            return $output;
        } else {
            return $jsonResp;
        }
    }
}
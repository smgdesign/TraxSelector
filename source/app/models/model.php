<?php

/**
 * Dettol / Lysol - 2013
 */
class Model {
    protected $_model;
    var $_controller;
    public function __construct() {
        $this->_model = get_class($this);
    }
    public function orders($mode='list', $id=0) {
        global $common;
        switch ($mode) {
            case "list":
                global $session;
                $setSync = false;
                if (!is_null($session->getVar('location_id'))) {
                    $tbl = array(
                        'o'=>'tbl_order'
                    );
                    $joins = array(
                        array('table'=>'tbl_order_item', 'as'=>'oi', 'on'=>array('oi.order_id', '=', 'o.id')),
                        array('table'=>'tbl_menu', 'as'=>'m', 'on'=>array('m.id', '=', 'oi.menu_id')),
                        array('table'=>'tbl_table', 'as'=>'t', 'on'=>array('t.id', '=', 'o.table_id'))
                    );
                    $cols = array(
                        'o'=>array('*'),
                        'oi'=>array('status AS item_status'),
                        'm'=>array('id AS menu_id', 'title', 'price'),
                        't'=>array('name')
                    );
                    $cond = array(
                        't'=>array(
                            'join'=>'AND',
                            array(
                                'col'=>'location_id',
                                'operand'=>'=',
                                'value'=>$session->getVar('location_id')
                            )
                        )
                    );
                    if (!is_null($common->getParam('last'))) {
                        $last = ($common->getParam('last') == 'first') ? new DateTime() : new DateTime($common->getParam('last'));
                        if ($last === false) {
                            $last = new DateTime();
                        }
                        $cond['o'] = array(
                            'join'=>'AND',
                            array(
                                'col'=>'time_ordered',
                                'operand'=>'>=',
                                'value'=>"'{$last->format('Y-m-d H:i:s')}'"
                            )
                        );
                        $setSync = true;
                    }
                    $order = array('ORDER BY o.time_ordered DESC');
                    $data = \data\collection::buildQuery("SELECT", $tbl, $joins, $cols, $cond, $order);
                    if ($data[1] > 0) {
                        $orders = array();
                        foreach ($data[0] as $item) {
                            if (!isset($orders[$item['id']])) {
                                $orders[$item['id']] = array('status'=>$item['status'], 'instruction'=>$item['instructions'], 'time_ordered'=>$item['time_ordered'], 'time_completed'=>$item['time_completed'], 'table'=>$item['name'], 'items'=>array(), 'total'=>0);
                            }
                            if (!empty($item['menu_id'])) {
                                $orders[$item['id']]['items'][] = array('id'=>$item['menu_id'], 'title'=>$item['title'], 'price'=>$item['price'], 'status'=>$item['item_status']);
                                $orders[$item['id']]['total'] = $orders[$item['id']]['total']+$item['price'];
                            }
                        }
                        if ($setSync) {
                            return array('data'=>$orders, 'lastSync'=>new DateTime());
                        } else {
                            return $orders;
                        }
                    } else {
                        if ($setSync) {
                            return array('data'=>array(), 'lastSync'=>new DateTime());
                        }
                        return array();
                    }
                }
                break;
            case "get":
                if ($id !== 0) {
                    $tbl = array(
                        'o'=>'tbl_order'
                    );
                    $joins = array(
                        array('table'=>'tbl_order_item', 'as'=>'oi', 'on'=>array('oi.order_id', '=', 'o.id')),
                        array('table'=>'tbl_menu', 'as'=>'m', 'on'=>array('m.id', '=', 'oi.menu_id')),
                        array('table'=>'tbl_table', 'as'=>'t', 'on'=>array('t.id', '=', 'o.table_id'))
                    );
                    $cols = array(
                        'o'=>array('*'),
                        'oi'=>array('status AS item_status'),
                        'm'=>array('id AS menu_id', 'title', 'price'),
                        't'=>array('name')
                    );
                    $cond = array(
                        'o'=>array(
                            'join'=>'AND',
                            array(
                                'col'=>'id',
                                'operand'=>'=',
                                'value'=>$id
                            )
                        )
                    );
                    $order = array('ORDER BY time_ordered DESC');
                    $data = \data\collection::buildQuery("SELECT", $tbl, $joins, $cols, $cond, $order);
                    if ($data[1] > 0) {
                        $orders = array();
                        foreach ($data[0] as $item) {
                            if (empty($orders)) {
                                $orders = array('id'=>$item['id'], 'status'=>$item['status'], 'instruction'=>$item['instructions'], 'time_ordered'=>$item['time_ordered'], 'time_completed'=>$item['time_completed'], 'table'=>$item['name'], 'items'=>array(), 'total'=>0);
                            }
                            if (!empty($item['menu_id'])) {
                                if (isset($orders['items'][$item['menu_id']])) {
                                    $orders['items'][$item['menu_id']]['qty'] = $orders['items'][$item['menu_id']]['qty']+1;
                                } else {
                                    $orders['items'][$item['menu_id']] = array('id'=>$item['menu_id'], 'title'=>$item['title'], 'price'=>$item['price'], 'status'=>$item['item_status'], 'qty'=>1);
                                }
                                
                                $orders['total'] = $orders['total']+$item['price'];
                            }
                        }
                        return $orders;
                    } else {
                        return array();
                    }
                }
                break;
        }
    }
    public function menu($mode='list') {
        global $session;
        switch ($mode) {
            case "list":
                $tbl = array('m'=>'tbl_menu');
                $cols = array(
                    'm'=>array('id', 'title')
                );
                $cond = array(
                    'm'=>array(
                        'join'=>'OR',
                        array(
                            'col'=>'location_id',
                            'operand'=>'=',
                            'value'=>$session->getVar('location_id')
                        ),
                        array(
                            'col'=>'location_id',
                            'operand'=>'=',
                            'value'=>$session->getVar('venue_id')
                        )
                    )
                );
                $additional = array("ORDER BY m.title ASC");
                $data = \data\collection::buildQuery("SELECT", $tbl, array(), $cols, $cond, $additional);
                if ($data[1] > 0) {
                    return $data[0];
                }
                return array();
                break;
        }
    }
    public function ingredients($mode='list') {
        switch ($mode) {
            case "list":
                $tbl = array(
                    'i'=>'tbl_ingredient'
                );
                $cols = array(
                    'i'=>array('*')
                );
                $data = \data\collection::buildQuery("SELECT", $tbl, array(), $cols);
                return $data[0];
                break;
        }
    }
    public function categories($mode='list') {
        switch ($mode) {
            case "list":
                $tbl = array(
                    'c'=>'tbl_category'
                );
                $cols = array(
                    'c'=>array('*')
                );
                $data = \data\collection::buildQuery("SELECT", $tbl, array(), $cols);
                return $data[0];
                break;
        }
    }
    public function venue($mode='list', $index='id') {
        switch ($mode) {
            case "list":
                $tbl = array(
                    'v'=>'tbl_venue'
                );
                $cols = array(
                    'v'=>array('*')
                );
                $cond = array(
                    'v'=>array(
                        'join'=>'AND',
                        array(
                            'col'=>'parent_id',
                            'operand'=>'=',
                            'value'=>0
                        )
                    )
                );
                $data = \data\collection::buildQuery("SELECT", $tbl, array(), $cols, $cond);
                $output = $data[0];
                if ($index === true) {
                    $output = array();
                    foreach ($data[0] as $venue) {
                        $output[$venue['id']] = $venue['title'];
                    }
                }
                return $output;
                break;
        }
    }
    public function location($mode='list') {
        global $auth, $session;
        switch ($mode) {
            case "list":
                if ($auth->level < 2) {
                    $tbl = array(
                        'l'=>'tbl_venue'
                    );
                    $cols = array(
                        'l'=>array('*')
                    );
                    $join = array(
                        array('table'=>'tbl_venue', 'as'=>'v', 'on'=>array('v.id', '=', 'l.parent_id'))
                    );
                    $cond = array(
                        'l'=>array(
                            'join'=>'AND',
                            array(
                                'col'=>'parent_id',
                                'operand'=>'=',
                                'value'=>$session->getVar('venue_id')
                            )
                        )
                    );
                } else {
                    $tbl = array(
                        'l'=>'tbl_venue'
                    );
                    $cols = array(
                        'l'=>array('*'),
                        'v'=>array('id AS venue_id', 'title AS venue_title')
                    );
                    $join = array(
                        array('table'=>'tbl_venue', 'as'=>'v', 'on'=>array('v.id', '=', 'l.parent_id'))
                    );
                    $cond = array(
                        'l'=>array(
                            'join'=>'AND',
                            array(
                                'col'=>'parent_id',
                                'operand'=>'!=',
                                'value'=>0
                            )
                        )
                    );
                }
                $data = \data\collection::buildQuery("SELECT", $tbl, $join, $cols, $cond);
                if ($data[1] > 0) {
                    $venues = array();
                    foreach ($data[0] as $location) {
                        if (!isset($venues[$location['venue_id']])) {
                            $venues[$location['venue_id']] = array('venue_title'=>$location['venue_title'], 'locations'=>array());
                        }
                        $venues[$location['venue_id']]['locations'][$location['id']] = $location['title'];
                    }
                }
                return $venues;
                break;
        }
    }
    public function table($mode='list') {
        global $auth, $session;
        switch ($mode) {
            case "list":
                if ($auth->level < 2) {
                    $tbl = array(
                        't'=>'tbl_table'
                    );
                    $cols = array(
                        't'=>array('*'),
                        'l'=>array('id AS location_id', 'title AS location_title'),
                        'v'=>array('id AS venue_id', 'title AS venue_title')
                    );
                    $join = array(
                        array('table'=>'tbl_venue', 'as'=>'l', 'on'=>array('l.id', '=', 't.location_id')),
                        array('table'=>'tbl_venue', 'as'=>'v', 'on'=>array('v.id', '=', 'l.parent_id'))
                    );
                    $cond = array(
                        'v'=>array(
                            'join'=>'AND',
                            array(
                                'col'=>'id',
                                'operand'=>'=',
                                'value'=>$session->getVar('venue_id')
                            )
                        )
                    );
                } else {
                    $tbl = array(
                        't'=>'tbl_table'
                    );
                    $cols = array(
                        't'=>array('*'),
                        'l'=>array('id AS location_id', 'title AS location_title'),
                        'v'=>array('id AS venue_id', 'title AS venue_title')
                    );
                    $join = array(
                        array('table'=>'tbl_venue', 'as'=>'l', 'on'=>array('l.id', '=', 't.location_id')),
                        array('table'=>'tbl_venue', 'as'=>'v', 'on'=>array('v.id', '=', 'l.parent_id'))
                    );
                    $cond = array(
                        'l'=>array(
                            'join'=>'AND',
                            array(
                                'col'=>'parent_id',
                                'operand'=>'!=',
                                'value'=>0
                            )
                        )
                    );
                }
                $data = \data\collection::buildQuery("SELECT", $tbl, $join, $cols, $cond);
                if ($data[1] > 0) {
                    $venues = array();
                    foreach ($data[0] as $location) {
                        if (!isset($venues[$location['venue_id']])) {
                            $venues[$location['venue_id']] = array('venue_title'=>$location['venue_title'], 'locations'=>array());
                        }
                        if (!isset($venues[$location['venue_id']]['locations'][$location['location_id']])) {
                            $venues[$location['venue_id']]['locations'][$location['location_id']] = array('location_title'=>$location['location_title'], 'tables'=>array());
                        }
                        $venues[$location['venue_id']]['locations'][$location['location_id']]['tables'][$location['id']] = $location['name'];
                    }
                }
                return $venues;
                break;
        }
    }
}

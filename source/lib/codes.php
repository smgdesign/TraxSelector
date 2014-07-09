<?php
namespace errors;

class codes {
    /**
     *
     * @__NOTFOUND type Code 1
     */
    static $__NOTFOUND = array('code'=>1, 'message'=>'The item you have requested could not be found');
    /**
     *
     * @__EMPTY type Code 2
     */
    static $__EMPTY = array('code'=>2, 'message'=>'The resource you have called upon is empty');
    /**
     *
     * @__FOUND type Code 3
     */
    static $__FOUND = array('code'=>3, 'message'=>'The item you have requested was found');
    /**
     *
     * @__SUCCESS type Code 4
     */
    static $__SUCCESS = array('code'=>4, 'message'=>'The action was successful');
    /**
     *
     * @__ERROR type Code 5
     */
    static $__ERROR = array('code'=>5, 'message'=>'An error occurred processing the last action');
}
?>

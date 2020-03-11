<?php

if (!defined('BASEPATH')) exit('No Direct Script Access Allowed');

class M_menustan extends CI_Model {

        function __construct() {
        }
        
        function ambildata($table,$where){		
            return $this->db->get_where($table,$where);
	}
        
}

<?php

if (!defined('BASEPATH')) exit('No Direct Script Access Allowed');

class M_login extends CI_Model{	
    
        function __construct() {
            parent::__construct();
        }
        
	function cek_login($table,$where){		
            return $this->db->get_where($table,$where);
	}	
}

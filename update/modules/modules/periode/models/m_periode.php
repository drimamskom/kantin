<?php

if (!defined('BASEPATH')) exit('No Direct Script Access Allowed');

class M_periode extends CI_Model {

        function __construct() {
        }
        
        function ambildata($table,$where){		
                return $this->db->get_where($table,$where);
	}
        
        function datetosqldate($string){
                $arr = explode("/", $string);
                return $arr[2]."-".$arr[1]."-".$arr[0];
        }
        
        function sqldatetodate($string){
                $arr = explode("-", $string);
                return $arr[2]."/".$arr[1]."/".$arr[0];
        }

        function datetimepicker_tosqldate($string){
                $arr1 = explode(" ", $string);
                $date = $arr1[0];
                $time = $arr1[1];
                $arr2 = explode("/", $date);
                return $arr2[2]."-".$arr2[1]."-".$arr2[0]." ".$time;
        }

        function sqldate_todatetimepicker($string){
                $arr1 = explode(" ", $string);
                $date = $arr1[0];
                $time = $arr1[1];
                $arr2 = explode("-", $date);
                return $arr2[2]."/".$arr2[1]."/".$arr2[0]." ".$time;
        }

}

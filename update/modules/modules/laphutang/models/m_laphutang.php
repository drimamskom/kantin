<?php

if (!defined('BASEPATH')) exit('No Direct Script Access Allowed');

class M_laphutang extends CI_Model {

        function __construct() {
        }
        
        function ambildata($table,$where){		
            return $this->db->get_where($table,$where);
	}
        
        function ambilnomorbaru(){
                $today = date('Y-m-d');
                $exp   = explode("-",$today);
                $yyyy  = substr($exp[0],-3);
                $mm    = $exp[1];
                $query = $this->db->query("select max(SUBSTRING_INDEX(kode_retur,'.',-1)*1) + 1 as new_count from trns_retur where SUBSTRING_INDEX(kode_retur,'.',1)='R". $yyyy. $mm ."'");
                $row   = $query->first_row();
                $x     = intval($row->new_count);
                //jika belum ada transaksi maka no urut dimulai dari 1
                if($x == 0 || $x == NULL){
                    $x = 1;
                }
                //untuk menjaga agar jumlah digit no urut tetap 4 digit
                if(strlen($x) == 1){
                    $x = "00" . $x;
                }elseif(strlen($x) == 2){
                    $x = "0" . $x;
                }
                $no_next = "F". $yyyy. $mm ."." .$x;

                return $no_next;
	}
        
        function tglmanusia($string){
            $namabln = array("KS", "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
                             "Juli", "Agustus", "September", "Oktober", "November", "Desember");
            $arr = explode("-", $string);
            $blnx = intval($arr[1]);
            return $arr[2]." ".$namabln[$blnx]." ".$arr[0];
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

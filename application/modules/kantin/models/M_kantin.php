<?php

if (!defined('BASEPATH')) exit('No Direct Script Access Allowed');

class M_kantin extends CI_Model{	
    
        function __construct() {
            parent::__construct();
        }
        
	function cek_login($table,$where){		
            return $this->db->get_where($table,$where);
	}
        
    function ambilnomorbaru(){
            $today = date('Y-m-d');
            $exp   = explode("-",$today);
            $yyyy  = substr($exp[0],-3);
            $mm    = $exp[1];
            $query = $this->db->query("select max(SUBSTRING_INDEX(kode_trns_pemesanan,'.',-1)*1) + 1 as new_count from trns_pemesanan where SUBSTRING_INDEX(kode_trns_pemesanan,'.',1)='K". $yyyy. $mm ."'");
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
            $no_next = "K". $yyyy. $mm ."." .$x;

            return $no_next;
	}
}

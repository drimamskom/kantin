<?php

if (!defined('BASEPATH')) exit('No Direct Script Access Allowed');

class M_pembayaran extends CI_Model {

        function __construct() {
        }
        
        function ambildata($table,$where){		
                return $this->db->get_where($table,$where);
	}
        
        function ambilnomorbaru($jenis_pembayaran){
                if(strlen($jenis_pembayaran) == 1){
                    $no_jenis = "00" . $jenis_pembayaran;
                }elseif(strlen($jenis_pembayaran) == 2){
                    $no_jenis = "0" . $jenis_pembayaran;
                }else{
                    $no_jenis = $jenis_pembayaran;
                }
                $today = date('Y-m-d');
                $exp   = explode("-",$today);
                $yyyy  = substr($exp[0],-3);
                $mm    = $exp[1];
                $query = $this->db->query("select max(SUBSTRING_INDEX(kode_pembayaran,'.',-1)*1) + 1 as new_count from m_pembayaran where SUBSTRING_INDEX(kode_pembayaran,'.',2)='".$no_jenis .".". $yyyy. $mm ."'");
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
                $no_next = $no_jenis .".". $yyyy. $mm ."." .$x;

                return $no_next;
	}
        
        function buattagihan($next_num,$pembayaran,$kelas,$periode_text,$reguler){
            $datetime = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            $sukses=0; 
            $gagal=0;
            $query = $this->db->query("SELECT nis FROM m_siswa where id_kelas='$kelas' AND id_periode='$periode_text' AND reguler='$reguler' ");
            foreach ($query->result_array() as $data){
                $nis = $data['nis'];
                $inser = $this->db->query("INSERT INTO tagihan_siswa 
                        (nama_tagihan, nis, kode_pembayaran, created_date, created_by)
                        VALUES
                        ('$pembayaran', '$nis', '$next_num', '$datetime', '$userr') ");
                if($inser){
                    $sukses++;
                }else{
                    $gagal++;
                }
            }
            //return json_encode(array('status'=>'success:'.$sukses.", gagal:".$gagal));
            return true;
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

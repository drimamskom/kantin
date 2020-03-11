<?php

if (!defined('BASEPATH')) exit('No Direct Script Access Allowed');

class M_trnskasir extends CI_Model {

        function __construct() {
        }
        
        function ambildata($table,$where){		
                return $this->db->get_where($table,$where);
	}
        
        function getalldata($id){
            $array['header'] = array();
            $query = $this->db->query("SELECT ph.*, c.nama nama_customer
                                    FROM trns_penjualan ph
                                    LEFT JOIN tb_customer c ON c.kode=ph.kode_customer
                                    WHERE ph.kode_trns_penjualan='$id' LIMIT 1 ");
            foreach ($query->result_array() as $data){
                $data['tgl'] = $this->sqldatetodate($data['tgl']);
                array_push($array['header'], $data);
            }
            
            $array['detail'] = array();
            $query2 = $this->db->query("SELECT pd.*, ob.nama_barang
                                        FROM trns_penjualan_detail pd
                                        LEFT JOIN tb_stok ob ON ob.kode_barang=pd.kode_barang                              
                                        WHERE pd.kode_trns_penjualan='$id' ");
            foreach ($query2->result_array() as $data2){
                array_push($array['detail'], $data2);
            }
            return $array;
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

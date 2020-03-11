<?php

if (!defined('BASEPATH')) exit('No Direct Script Access Allowed');

class M_barang extends CI_Model {

        function __construct() {
        }
        
        function ambildata($table,$where){		
            return $this->db->get_where($table,$where);
	}
        
        public function upload_data($filename,$periode_text){
            $sukses=0; 
            $gagal=0; 
            $total=0;
            $date = date('Y-m-d');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            ini_set('memory_limit', '-1');
            $inputFileName = './assets/uploads/'.$filename;
            try {
                $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
            } catch(Exception $e) {
                die('Error loading file :' . $e->getMessage());
            }
            
            $worksheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
            $numRows = count($worksheet);

            for ($i=2; $i < ($numRows+1) ; $i++) {                 
                $kode_barang = (!empty($worksheet[$i]["B"])) ? strtoupper($worksheet[$i]["B"]) : '';
                $nama_barang = (!empty($worksheet[$i]["C"])) ? strtoupper($worksheet[$i]["C"]) : '';
                $satuan      = (!empty($worksheet[$i]["D"])) ? strtoupper($worksheet[$i]["D"]) : '';
                $supplier    = (!empty($worksheet[$i]["E"])) ? strtoupper($worksheet[$i]["E"]) : '';
                $jenis       = (!empty($worksheet[$i]["F"])) ? strtoupper($worksheet[$i]["F"]) : '';
                $tempat      = (!empty($worksheet[$i]["G"])) ? strtoupper($worksheet[$i]["G"]) : '';
                $stan        = (!empty($worksheet[$i]["H"])) ? strtoupper($worksheet[$i]["H"]) : '';
                $harga_beli  = (!empty($worksheet[$i]["I"])) ? strtoupper($worksheet[$i]["I"]) : '';
                $harga_jual  = (!empty($worksheet[$i]["J"])) ? strtoupper($worksheet[$i]["J"]) : '';
                $min_stok    = (!empty($worksheet[$i]["K"])) ? strtoupper($worksheet[$i]["K"]) : '';
                if(!empty($kode_barang)){
                    $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_stok WHERE nama_barang='$nama_barang' OR kode_barang='$kode_barang' ");
                    $row1 = $query1->first_row();
                    $jmle = intval($row1->jml);
                    if($jmle==0){       
                        $ins = array(
                            "jenis"         => $jenis,
                            "kode_barang"   => $kode_barang,
                            "nama_barang"   => $nama_barang,
                            "satuan"        => $satuan,
                            "harga_beli"    => $harga_beli,
                            "harga_jual"    => $harga_jual,
                            "min_stok"      => $min_stok,
                            "tempat"        => $tempat,
                            "no_stan"       => $stan,
                            "supplier"      => $supplier,
                            "created_date"  => $date,
                            "created_by"    => $userr
                        );
                        $this->db->insert('tb_stok', $ins);                           
                        $sukses++;
                    }else{
                        $gagal++;
                    }
                    $total++;
                }
            }
            $hasil = array('status'=>'success', 'txt'=>'Total Upload Barang:'.$total.', Berhasil:'.$sukses.', Gagal:'.$gagal);
            return $hasil;
        }
        
}

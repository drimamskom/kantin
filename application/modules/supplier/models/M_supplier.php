<?php

if (!defined('BASEPATH')) exit('No Direct Script Access Allowed');

class M_supplier extends CI_Model {

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
                $nama_supplier   = (!empty($worksheet[$i]["B"])) ? strtoupper($worksheet[$i]["B"]) : '';
                $alamat_supplier = (!empty($worksheet[$i]["C"])) ? strtoupper($worksheet[$i]["C"]) : '';
                $kota_supplier   = (!empty($worksheet[$i]["D"])) ? strtoupper($worksheet[$i]["D"]) : '';
                $tlp_supplier    = (!empty($worksheet[$i]["E"])) ? strtoupper($worksheet[$i]["E"]) : '';
                $tempat          = (!empty($worksheet[$i]["F"])) ? strtoupper($worksheet[$i]["F"]) : '';
                if(!empty($nama_supplier)){
                    $query = $this->db->query("SELECT nama_supplier FROM tb_supplier WHERE nama_supplier='$nama_supplier' ");
                    $jum = $query->num_rows();
                    if($jum==0){  
                        $code = strtoupper(substr($nama_supplier,0,1))."-";
                        $query2 = $this->db->query("select max(right(kode_supplier,6)*1) as new_count from tb_supplier where left(kode_supplier,2)='$code' ");
                        $row2 = $query2->first_row();
                        $new_count = intval($row2->new_count)+1;
                        $new_code = $code.str_pad($new_count,6,"0",STR_PAD_LEFT);

                        $ins = array(
                            "kode_supplier"   => $new_code,
                            "nama_supplier"   => $nama_supplier,
                            "alamat_supplier" => $alamat_supplier,
                            "kota_supplier"   => $kota_supplier,
                            "tlp_supplier"    => $tlp_supplier,
                            "tempat"          => $tempat,
                            "created_date"  => $date,
                            "created_by"    => $userr
                        );
                        $this->db->insert('tb_supplier', $ins);
                        $sukses++;
                    }else{
                        $gagal++;
                    }
                    $total++;
                }
            }
            $hasil = array('status'=>'success', 'txt'=>'Total Upload Supplier:'.$total.', Berhasil:'.$sukses.', Gagal:'.$gagal);
            return $hasil;
        }
        
}

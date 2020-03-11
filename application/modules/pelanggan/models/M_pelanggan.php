<?php

if (!defined('BASEPATH')) exit('No Direct Script Access Allowed');

class M_pelanggan extends CI_Model {

        function __construct() {
        }
        
        function ambildata($table,$where){		
            return $this->db->get_where($table,$where);
	}

        public function upload_data($filename){
            $sukses=0; 
            $gagal=0; 
            $total=0;
            $arr_gagal=array();
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
                $kode   = (!empty($worksheet[$i]["B"])) ? strtoupper($worksheet[$i]["B"]) : '';
                $kode2  = (!empty($worksheet[$i]["C"])) ? strtoupper($worksheet[$i]["C"]) : '';
                $nama   = (!empty($worksheet[$i]["D"])) ? strtoupper($worksheet[$i]["D"]) : '';
                $alamat = (!empty($worksheet[$i]["E"])) ? $worksheet[$i]["E"] : '';
                $kota   = (!empty($worksheet[$i]["F"])) ? strtoupper($worksheet[$i]["F"]) : '';
                $grup   = (!empty($worksheet[$i]["G"])) ? strtoupper($worksheet[$i]["G"]) : '';
                $agama  = (!empty($worksheet[$i]["H"])) ? strtoupper($worksheet[$i]["H"]) : '';
                $jk     = (!empty($worksheet[$i]["I"])) ? strtoupper($worksheet[$i]["I"]) : '';
                if(!empty($kode)){
                    $query = $this->db->query("SELECT kode FROM tb_customer WHERE kode='$kode' OR kode2='$kode2'  ");
                    $jum = $query->num_rows();
                    if($jum==0){  
                        $jenis_kelamin = preg_replace('/\s+/', '', $jk);
                        $ins = array(
                            "kode"   => $kode,
                            "kode2"  => $kode2,
                            "grup"  => $grup,
                            "nama"  => addslashes($nama),
                            "alamat"=> $alamat,
                            "kota"  => $kota,
                            "agama" => $agama,
                            "jenis_kelamin" => $jenis_kelamin,
                            "created_date"  => $date,
                            "created_by"    => $userr
                        );
                        $this->db->insert('tb_customer', $ins);
                        $this->db->query("INSERT INTO tb_card 
                                            (grup, kode, card, pin, akses, created_date, created_by)
                                          VALUES
                                            ('$grup', '$kode', '$kode2', '1234', 'customer', '$date', '$userr')");
                    }else{
                        $gagal++;
                        $arr_gagal[] = $kode." (duplikat)";
                    }
                    $total++;
                }
            }
            $info  = implode(", ", $arr_gagal);
            $hasil = array('status'=>'success', 'txt'=>'Total Upload :'.$total.', Berhasil:'.$sukses.', Gagal:'.$gagal.'<br>alasan:'.$info);
            return $hasil;
        }
        
}

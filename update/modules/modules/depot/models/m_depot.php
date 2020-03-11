<?php

if (!defined('BASEPATH')) exit('No Direct Script Access Allowed');

class M_depot extends CI_Model {

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
                $nama_depot   = strtoupper($worksheet[$i]["B"]);
                $alamat_depot = strtoupper($worksheet[$i]["C"]);
                $tlp_depot    = strtoupper($worksheet[$i]["D"]);
                if(!empty($nama_depot)){
                    $query = $this->db->query("SELECT nama_depot FROM tb_depot WHERE nama_depot='$nama_depot' ");
                    $jum = $query->num_rows();
                    if($jum==0){  
                        $code = "DPT-".strtoupper(substr($nama_depot,0,1));
                        $query2 = $this->db->query("select max(right(kode_depot,5)*1) as new_count from tb_depot where left(kode_depot,5)='$code' ");
                        $row2 = $query2->first_row();
                        $new_count = intval($row2->new_count)+1;
                        $new_code = $code.str_pad($new_count,5,"0",STR_PAD_LEFT);

                        $ins = array(
                            "kode_depot"   => $new_code,
                            "nama_depot"   => $nama_depot,
                            "alamat_depot" => $alamat_depot,
                            "tlp_depot"    => $tlp_depot,
                            "created_date"  => $date,
                            "created_by"    => $userr
                        );
                        $this->db->insert('tb_depot', $ins);
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

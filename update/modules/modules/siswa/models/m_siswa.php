<?php

if (!defined('BASEPATH')) exit('No Direct Script Access Allowed');

class M_siswa extends CI_Model {

        function __construct() {
        }
        
        function ambildata($table,$where){		
            return $this->db->get_where($table,$where);
	}

        public function upload_data($filename,$thn_ajaran){
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
                $nis     = (!empty($worksheet[$i]["B"])) ? strtoupper($worksheet[$i]["B"]) : '';
                $nisn    = (!empty($worksheet[$i]["C"])) ? strtoupper($worksheet[$i]["C"]) : '';
                $nama    = (!empty($worksheet[$i]["D"])) ? strtoupper($worksheet[$i]["D"]) : '';
                $alamat  = (!empty($worksheet[$i]["E"])) ? strtoupper($worksheet[$i]["E"]) : '';
                $kota    = (!empty($worksheet[$i]["F"])) ? strtoupper($worksheet[$i]["F"]) : '';
                $agama   = (!empty($worksheet[$i]["G"])) ? strtoupper($worksheet[$i]["G"]) : '';
                $jk      = (!empty($worksheet[$i]["H"])) ? strtoupper($worksheet[$i]["H"]) : '';
                $kelas   = (!empty($worksheet[$i]["I"])) ? strtoupper($worksheet[$i]["I"]) : '';
                $jurusan = (!empty($worksheet[$i]["J"])) ? strtoupper($worksheet[$i]["J"]) : '';
                $ke      = (!empty($worksheet[$i]["K"])) ? strtoupper($worksheet[$i]["K"]) : '';
                if(!empty($nis)){
                    $query = $this->db->query("SELECT nis FROM tb_siswa WHERE nis='$nis' ");
                    $jum = $query->num_rows();
                    $query1 = $this->db->query("SELECT id_kelas FROM tb_kelas WHERE kelas='$kelas' AND jurusan='$jurusan' AND kelompok='$ke' ");
                    $jum1 = $query1->num_rows();
                    if($jum==0 && $jum1>0){
                        $row1 = $query1->first_row();
                        $id_kelas = $row1->id_kelas;   
                        $jenis_kelamin = preg_replace('/\s+/', '', $jk);
                        $ins = array(
                            "nis"   => $nis,
                            "nisn"  => $nisn,
                            "nama"  => $nama,
                            "alamat"=> $alamat,
                            "kota"  => $kota,
                            "agama" => $agama,
                            "jenis_kelamin" => $jenis_kelamin,
                            "id_kelas"      => $id_kelas,
                            "id_thnajaran"  => $thn_ajaran,
                            "golongan"      => "reguler",
                            "created_date"  => $date,
                            "created_by"    => $userr
                        );
                        $this->db->insert('tb_siswa', $ins);
                    }else{
                        $gagal++;
                        $arr_gagal[] = $nis." (duplikat)";
                    }
                    $total++;
                }
            }
            $info  = implode(", ", $arr_gagal);
            $hasil = array('status'=>'success', 'txt'=>'Total Upload Siswa:'.$total.', Berhasil:'.$sukses.', Gagal:'.$gagal.'<br>alasan:'.$info);
            return $hasil;
        }
        
}

<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Siswa extends CI_Controller {
    
        public $nama_tabel = 'm_siswa';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_siswa');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('siswa');
            $this->load->view('footer');
	}
        
	public function kenaikan(){
            $this->load->view('header');
            $this->load->view('kenaikan');
            $this->load->view('footer');
	}
        
	public function kelulusan(){
            $this->load->view('header');
            $this->load->view('kelulusan');
            $this->load->view('footer');
	}
        
	public function pindah_gol(){
            $this->load->view('header');
            $this->load->view('pindah_gol');
            $this->load->view('footer');
	}
        
	public function pindah(){
            $this->load->view('header');
            $this->load->view('pindah');
            $this->load->view('footer');
	}
        
	public function upload(){
            $this->load->view('header');
            $this->load->view('upload');
            $this->load->view('footer');
	}
        
        public function do_upload(){
            $thn_ajaran = $this->input->post('thn_ajaran');
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = 'xlsx|xls';
		
            $this->load->library('upload', $config);

            if ( ! $this->upload->do_upload('upfiles')){
                $hasil = array('status'=>'failed', 'txt'=> $this->upload->display_errors());
            }else{
                $data = array('upload_data' => $this->upload->data());
                $upload_data = $this->upload->data(); //Returns array of containing all of the data related to the file you uploaded.
                $filename = $upload_data['file_name'];
                $hasil = $this->m_siswa->upload_data($filename,$thn_ajaran);
                unlink('./assets/uploads/'.$filename);
            }
              
            if($hasil['status'] == 'success') {
                $data['pesan'] = '<div class="alert alert-success alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-check"></i> Berhasil!</h4>'.$hasil['txt'].'</div>';
            }else if($hasil['status'] == 'failed') {
                $data['pesan'] = '<div class="alert alert-danger alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-ban"></i> Gagal!</h4> '.$hasil['txt'].'</div>';
            }else{
                $data['pesan'] = '';
            }
            $this->load->view('header');
            $this->load->view('upload', $data, FALSE);
            $this->load->view('footer');
	}
        
        public function data(){
            $draw = $this->input->post('draw');
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $kata = $this->input->post('cari');
            $kelas = $this->input->post('kelas');
            $thn_ajaran = $this->input->post('thn_ajaran');
            
            $arr_jk = array("L" => "Laki - Laki", "P" => "Perempuan");
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND s.nis LIKE '%$kata%' OR s.nama LIKE '%$kata%' ";
            }
            if(count($kelas)>0){                
                $kls_list = "'".implode("','", $kelas)."'";
                $where2=" AND s.id_kelas IN ($kls_list) ";
            }else{
                $where2="";
            }
            if(empty($thn_ajaran)){
                $where3="";
            }else{
                $where3=" AND s.id_thnajaran='$thn_ajaran' ";
            }
            $query1 = $this->db->query("SELECT COUNT(*) as total FROM tb_siswa s WHERE s.id_kelas!='0' $where1 $where2 $where3 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT s.*, k.nama_kelas, p.nama_thnajaran
                            FROM tb_siswa s
                            LEFT JOIN tb_kelas k ON k.id_kelas=s.id_kelas
                            LEFT JOIN tb_thnajaran p ON p.id_thnajaran=s.id_thnajaran
                            WHERE s.id_kelas!='0' $where1 $where2 $where3
                            ORDER BY s.nis LIMIT $start, $length 
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $row['jenis_kelamin'] = $arr_jk[$row['jenis_kelamin']];
                $data[$i] = $row;
                $data[$i]['button'] = '<button title="Edit Siswa" idnex="'.$data[$i]['nis'].'" class="btn btn-info btn-xs btnedit" ><i class="fa fa-pencil"></i> Edit</button> 
                                       <button title="Delete Siswa" idnex="'.$data[$i]['nis'].'" namenex="'.$data[$i]['nama'].'" class="btn btn-danger btn-xs btnhapus" ><i class="fa fa-remove"></i> Del</button>';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function datanaik(){
            $draw = $this->input->post('draw');
            $thn_ajaran = $this->input->post('thn_ajaran');
            $kelas = $this->input->post('kelas');
            
            if(empty($thn_ajaran)||empty($kelas)){
                $tot = 0;
                $data = array();
            }else{
                $kelas=intval($kelas);
                $query1 = $this->db->query("SELECT COUNT(*) as total FROM tb_siswa s WHERE s.id_kelas='$kelas' AND s.id_thnajaran='$thn_ajaran'  ");
                $row1 = $query1->first_row();
                $tot = $row1->total;

                $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                        FROM (
                                SELECT s.* FROM tb_siswa s
                                WHERE s.id_kelas='$kelas' AND s.id_thnajaran='$thn_ajaran' 
                                ORDER BY s.nis 
                        ) AS t, 
                        (SELECT @rownum := 0) r");  
                $i=0;
                $data = array();
                foreach ($query->result_array() as $row){
                    $data[$i] = $row;
                    $data[$i]['cekbok'] = '<input type="checkbox" name="'.$row['nis'].'">';
                    $i++;
                }
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function cari(){
            $id = $this->input->post('id');
            $array = array();
            $query = $this->db->query("SELECT * FROM tb_siswa where nis='$id'");
            foreach ($query->result_array() as $data){
                    array_push($array,$data);
            }
            echo json_encode(array('data'=>$array[0]));
        }
        
        public function hapus(){
            $id = $this->input->post('id');
            $date = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            // fungsi cek delete kalau id nya mempengaruhi tabel lain            
            $insert = $this->db->query("INSERT INTO tb_siswa_hist (nis, nama, alamat, jenis_kelamin, id_kelas, id_thnajaran, aksi, perubahan, updated_date, updated_by)  
                                SELECT nis, nama, alamat, jenis_kelamin, id_kelas, id_thnajaran, 'HAPUS', '', '$date', '$userr'
                                FROM tb_siswa WHERE nis = '$id' ");
            if($insert) { 
                $delete = $this->db->query("DELETE FROM tb_siswa where nis='$id'");
                if($delete){
                    echo json_encode(array('status'=>'success'));
                }else{
                    echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa delete data!'));
                }
            }else{
                echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa delete data!'));
            }
        }
        
        public function save(){
            $idne = $this->input->post('idne');
            $code = $this->input->post('code');
            $siswa = $this->input->post('siswa');
            $jenis_kelamin = $this->input->post('jenis_kelamin');
            $alamat = $this->input->post('alamat');
            $id_kelas = $this->input->post('kelas');
            $id_thnajaran = $this->input->post('thn_ajaran');
            $crud = $this->input->post('crud');
            $date = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if($crud=='N'){
                $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_siswa WHERE nis='$code' ");
                $row1 = $query1->first_row();
                $jmle = intval($row1->jml);
                if($jmle>0){
                    echo json_encode(array('status'=>'failed', 'txt'=>'NIS sudah ada!'));
                }else{
                    $inser = $this->db->query("INSERT INTO tb_siswa 
                            (nis, nama, alamat, jenis_kelamin, id_kelas, id_thnajaran, golongan, created_date, created_by)
                            VALUES
                            ('$code', '$siswa', '$alamat', '$jenis_kelamin', '$id_kelas', '$id_thnajaran', 'reguler', '$date', '$userr')");
                    if($inser){ 
                        echo json_encode(array('status'=>'success'));
                    }else{
                        echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                    };
                }
            }else if($crud == 'E'){
                $update = $this->db->query("UPDATE tb_siswa SET
                                            nama = '$siswa' , 
                                            jenis_kelamin = '$jenis_kelamin' , 
                                            alamat = '$alamat' ,                                                     
                                            updated_date = '$date' , 
                                            updated_by = '$userr'
                                         WHERE
                                            nis = '$idne' ");
                if($update){
                    echo json_encode(array('status'=>'success'));
                }else{
                    echo json_encode(array('status'=>'failed'));
                };
            }else if($crud == 'T'){
                $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_siswa WHERE nis='$code' ");
                $row1 = $query1->first_row();
                $jmle = intval($row1->jml);
                if($jmle>0){
                    echo json_encode(array('status'=>'failed', 'txt'=>'NIS sudah ada!'));
                }else{
                    $inser = $this->db->query("INSERT INTO tb_siswa 
                            (nis, nama, alamat, jenis_kelamin, id_kelas, id_thnajaran, golongan, created_date, created_by)
                            VALUES
                            ('$code', '$siswa', '$alamat', '$jenis_kelamin', '$id_kelas', '$id_thnajaran', 'reguler', '$date', '$userr')");
                    if($inser){  
                        echo json_encode(array('status'=>'success'));
                    }else{
                        echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                    };
                }
            }else{
                echo json_encode(array('status'=>'failed'));
            }
        }
        
        public function naikturun_kelas(){                
            $sukses=0; 
            $gagal=0;
            $tipe = $this->input->post('tipe');
            $noww = $this->input->post('noww');
            $tapel = $this->input->post('tapel');
            $kelas = $this->input->post('kelas');
            $tapel2 = $this->input->post('tapel2');
            $kelas2 = $this->input->post('kelas2');         
            $id = $this->input->post('id');
            $id_arr = explode("-", $id);
            $id_list = "'".implode("','", $id_arr)."'";
            
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            if($tipe=='naik'){
                $kelas_tujuan = $kelas2;
                $tapel_tujuan = $tapel2;
                $info = "NAIK_KELAS";
            }else if($tipe=='tinggal'){
                $kelas_tujuan = $kelas;
                $tapel_tujuan = $tapel;
                $info = "TINGGAL_KELAS";                
            }else if($tipe=='lulus'){
                $kelas_tujuan = '0';
                $tapel_tujuan = $tapel2;
                $info = "LULUS";
            }else if($tipe=='ulang'){
                $kelas_tujuan = $kelas;
                $tapel_tujuan = $tapel;
                $info = "ULANG_KELAS";                
            }else if($tipe=='pindah'){
                $kelas_tujuan = $kelas2;
                $tapel_tujuan = $tapel2;
                $info = "PINDAH_KELAS";
            }else if($tipe=='pindah2'){
                $kelas_tujuan = $kelas;
                $tapel_tujuan = $tapel;
                $info = "PINDAH_KELAS"; 
            }
            $perubahan = "Kelas:".$kelas_tujuan.", Tapel:".$tapel_tujuan;
            foreach ($id_arr as $idne) {
                // fungsi cek Perubahan kalau id nya mempengaruhi tabel lain
                $insert = $this->db->query("INSERT INTO tb_siswa_hist (nis, nama, alamat, jenis_kelamin, id_kelas, id_thnajaran, aksi, perubahan, updated_date, updated_by)  
                                    SELECT nis, nama, alamat, jenis_kelamin, id_kelas, id_thnajaran, '$info', '$perubahan', '$noww', '$userr'
                                    FROM tb_siswa WHERE nis = '$idne' ");
                if($insert){
                    $update = $this->db->query("UPDATE tb_siswa SET 
                                                    id_kelas = '$kelas_tujuan' , 
                                                    id_thnajaran = '$tapel_tujuan' ,
                                                    updated_date = '$noww' , 
                                                    updated_by = '$userr'
                                                WHERE
                                                    nis = '$idne' ");
                    $sukses++;
                }else{
                    $gagal++;
                }
            } 
            echo json_encode(array('status'=>'success', 'txt'=>'sukses:'.$sukses.' gagal:'.$gagal.'!'));
        }
        
}

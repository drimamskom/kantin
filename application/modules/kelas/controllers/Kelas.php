<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kelas extends CI_Controller {
    
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            //$this->load->model('m_kelas');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('kelas');
            $this->load->view('footer');
	}
        
        public function data(){
            //$result = $this->m_kelas->ambildata("m_kelas",$where);
            $draw = $this->input->post('draw');
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $search = $this->input->post('search');
            $kata = $search['value'];
            if(empty($kata)){
                $where="";
            }else{
                $where=" AND kelas LIKE '%$kata%' OR jurusan LIKE '%$kata%' OR nama_kelas LIKE '%$kata%' ";
            }

            $query1 = $this->db->query("SELECT COUNT(*) as total FROM tb_kelas WHERE aktif='1' $where");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT * FROM tb_kelas WHERE aktif='1' $where ORDER BY nama_kelas LIMIT $start, $length 
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $data[$i] = $row;
                $data[$i]['button'] = '<button title="Edit Kelas" idnex="'.$data[$i]['id_kelas'].'" class="btn btn-sm btn-info btnedit" ><i class="fa fa-pencil"></i> Edit</button> 
                                       <button title="Delete Kelas" idnex="'.$data[$i]['id_kelas'].'" namenex="'.$data[$i]['nama_kelas'].'" class="btn btn-sm btn-danger btnhapus"><i class="fa fa-remove"></i> Del</button>';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function cari(){
            $id = $this->input->post('id');
            $array = array();
            $query = $this->db->query("SELECT * FROM tb_kelas where id_kelas='$id' and aktif='1' ");
            foreach ($query->result_array() as $data){
                    array_push($array,$data);
            }
            echo json_encode(array('data'=>$array[0]));
        }
        
        public function hapus(){
            $id = $this->input->post('id');
            // fungsi cek delete kalau id nya mempengaruhi tabel lain
            $query1 = $this->db->query("SELECT DISTINCT id_kelas FROM tb_siswa WHERE id_kelas='$id' ");
            $num = $query1->num_rows();
            if($num==0){
                $delete = $this->db->query("UPDATE tb_kelas SET aktif = '0' where id_kelas='$id'");
                if($delete){
                    echo json_encode(array('status'=>'success'));
                }else{
                    echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa delete data!'));
                }
            }else{
                echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa delete data, Data masih digunakan!'));
            }
        }
        
        public function save(){
            $idne = $this->input->post('idne');
            $code = $this->input->post('code');
            $kelas = $this->input->post('kelas');
            $jurusan = $this->input->post('jurusan');
            $kelompok = $this->input->post('kelompok');
            $crud = $this->input->post('crud');
            $date = date('Y-m-d');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if($crud=='N'){
                    $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_kelas WHERE id_kelas='$idne' and aktif='1' ");
                    $row1 = $query1->first_row();
                    $jmle = intval($row1->jml);
                    if($jmle>0){
                        echo json_encode(array('status'=>'failed', 'txt'=>'Kelas sudah ada!'));
                    }else{
                        $inser = $this->db->query("INSERT INTO tb_kelas 
                                (kelas, jurusan, kelompok, nama_kelas, created_date, created_by)
                                VALUES
                                ('$kelas', '$jurusan', '$kelompok', '$code', '$date', '$userr')");
                        if($inser){
                            echo json_encode(array('status'=>'success'));
                        }else{
                            echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                        };
                    }
            }else if($crud == 'E'){
                        $update = $this->db->query("UPDATE tb_kelas SET
                                                    kelas = '$kelas' , 
                                                    jurusan = '$jurusan' , 
                                                    kelompok = '$kelompok' , 
                                                    nama_kelas = '$code' , 
                                                    updated_date = '$date' , 
                                                    updated_by = '$userr'
                                                  WHERE
                                                    id_kelas = '$idne' ");
                    if($update){
                        echo json_encode(array('status'=>'success'));
                    }else{
                        echo json_encode(array('status'=>'failed'));
                    };
            }else{
                    echo json_encode(array('status'=>'failed'));
            }
        }
        
}

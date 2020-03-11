<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
    
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            //$this->load->model('m_siswa');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('user');
            $this->load->view('footer');
	}
        
	public function changepsw(){ 
            $this->load->view('header');
            $this->load->view('change');
            $this->load->view('footer');
	}
        
	public function changepin(){ 
            $this->load->view('header');
            $this->load->view('changepin');
            $this->load->view('footer');
	}
        
        public function data(){
            //$result = $this->m_siswa->ambildata("tb_user",$where);
            $draw = $this->input->post('draw');
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $search = $this->input->post('search');
            $kata = $search['value'];
            if(empty($kata)){
                $where="";
            }else{
                $where=" WHERE username LIKE '%$kata%' OR nomor_induk LIKE '%$kata%' OR fullname LIKE '%$kata%' ";
            }

            $query1 = $this->db->query("SELECT COUNT(*) as total FROM tb_user $where");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT * FROM tb_user $where ORDER BY user_id LIMIT $start, $length 
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $data[$i] = $row;
                $data[$i]['button'] = '<button title="Edit User" idnex="'.$data[$i]['user_id'].'" class="btn btn-xs btn-info btnedit" ><i class="fa fa-pencil"></i> Edit</button> 
                                       <button title="Delete User" idnex="'.$data[$i]['user_id'].'" namenex="'.$data[$i]['nomor_induk'].'" class="btn btn-xs btn-danger btnhapus" ><i class="fa fa-remove"></i> Del</button>';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function cari(){
            $id = $this->input->post('id');
            $array = array();
            $query = $this->db->query("SELECT * FROM tb_user where user_id='$id'");
            foreach ($query->result_array() as $data){
                    array_push($array,$data);
            }
            echo json_encode(array('data'=>$array[0]));
        }
        
        public function info(){
            $kode = $this->input->post('kode');
            $array = array();
            if($kode=="kantin"){
                $query = $this->db->query("SELECT * FROM tb_supplier ");
                foreach ($query->result_array() as $data){
                    $row['id'] = $data['kode_supplier'];
                    $row['text'] = $data['nama_supplier'];
                    array_push($array,$row);
                }
            }else if($kode=="customer"){
                $query = $this->db->query("SELECT * FROM tb_customer ");
                foreach ($query->result_array() as $data){
                    $row['id'] = $data['kode'];
                    $row['text'] = $data['nama'];
                    array_push($array,$row);
                }
            }else if($kode=="tenant"){
                $query = $this->db->query("SELECT * FROM tb_stan WHERE no_stan>0");
                foreach ($query->result_array() as $data){
                    $row['id'] = $data['no_stan'];
                    $row['text'] = $data['nama_stan'];
                    array_push($array,$row);
                }
            }            
            echo json_encode(array('data'=>$array));
        }
        
        public function hapus(){
            $id = $this->input->post('id');
            $sesi = $this->session->get_userdata();
            $userid = $sesi['user_id'];
            if($id==$userid){
		echo json_encode(array('status'=>'failed', 'txt'=>'User masih digunakan, logout dahulu!'));
            }else{
		$delete = $this->db->query("DELETE FROM tb_user where user_id='$id'");
		if($delete){
			echo json_encode(array('status'=>'success'));
		}else{
			echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa delete data!'));
		};
            }
        }
        
        public function act_change(){
            //$id = $this->input->post('id');
            $newpassword = md5($this->input->post('newpassword'));
            $date = date('Y-m-d');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            $userid = $sesi['user_id'];

            $qcekk = $this->db->query("SELECT COUNT(*) AS jml FROM tb_user WHERE username='$userr' ");
            $cekk = $qcekk->first_row();
            $jmle = intval($cekk->jml);
            if($jmle==0){
                echo json_encode(array('status'=>'failed', 'txt'=>'username tidak ada!'));
            }else{
                $update = $this->db->query("UPDATE tb_user SET
                                                password = '$newpassword' ,
                                                updated_date = '$date' , 
                                                updated_by = '$userr'
                                        WHERE
                                                user_id = '$userid' ");
                if($update){
                    echo json_encode(array('status'=>'success'));
                }else{
                    echo json_encode(array('status'=>'failed'));
                };
            }
            
        }
        
        public function act_change_pin(){
            $idcard = $this->input->post('idcard');
            $newpin = md5($this->input->post('newpin'));
            $date = date('Y-m-d');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            $userid = $sesi['user_id'];

            $qcekk = $this->db->query("SELECT COUNT(*) AS jml FROM tb_card WHERE card='$idcard' ");
            $cekk = $qcekk->first_row();
            $jmle = intval($cekk->jml);
            if($jmle==0){
                echo json_encode(array('status'=>'failed', 'txt'=>'IDCARD tidak ada!'));
            }else{
                $update = $this->db->query("UPDATE tb_card SET
                                                pin = '$newpin' ,
                                                updated_date = '$date' , 
                                                updated_by = '$userr'
                                        WHERE
                                                user_id = '$userid' ");
                if($update){
                    echo json_encode(array('status'=>'success'));
                }else{
                    echo json_encode(array('status'=>'failed'));
                };
            }
            
        }
        
        public function save(){
            $idne = $this->input->post('idne');
            $code = $this->input->post('code');
            $karyawan = $this->input->post('karyawan');
            $akses = $this->input->post('akses');
            $username = $this->input->post('username');
            $password = md5($this->input->post('password'));
            $crud = $this->input->post('crud');
            $date = date('Y-m-d');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if($crud=='N'){
                    $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_user 
                                                WHERE nomor_induk='$code' AND username='$username' ");
                    $row1 = $query1->first_row();
                    $jmle = intval($row1->jml);
                    if($jmle>0){
                        echo json_encode(array('status'=>'failed', 'txt'=>'data sudah ada!'));
                    }else{
                        $inser = $this->db->query("INSERT INTO tb_user 
                                            (username, PASSWORD, nomor_induk, fullname,  akses, created_date, created_by)
                                            VALUES
                                            ('$username', '$password', '$code', '$karyawan', '$akses', '$date', '$userr')");
                        if($inser){
                                echo json_encode(array('status'=>'success'));
                        }else{
                                echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                        };
                    }
            }else if($crud == 'E'){
                    if(empty($_POST['password'])){ $inupass = ""; }else{ $inupass = " PASSWORD = '$password' ,  "; }
                        $update = $this->db->query("UPDATE tb_user SET
                                                        username = '$username' ,
                                                        ".$inupass."					
                                                        nomor_induk 	= '$code' , 
                                                        fullname 	= '$karyawan' , 
                                                        akses 		= '$akses' , 
                                                        updated_date = '$date' , 
                                                        updated_by = '$userr'
                                                    WHERE
                                                        user_id = '$idne' ");
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

<?php if(!defined('BASEPATH')) exit ('No Direct Script Access Allowed');

class Login extends CI_Controller {

	function __construct(){
            parent::__construct();		
            $this->load->model('m_login');
	}

	function index(){
            $this->load->view('v_login');
	}

	function card_login(){
            $idcard = $this->input->post('idcard');
            $car = "0007877555";
            $pin    = $this->input->post('pin');
            //$where  = array( 'aktif' => '1', 'card' => $idcard, 'pin' => $pin );
            // $where  = array( 'aktif' => '1', 'card' => $idcard );
            $where  = array( 'aktif' => '1', 'card' => $car );
            $result = $this->m_login->cek_login("tb_card",$where);
            $cek = $result->num_rows();
            if($cek > 0){
                $row = $result->first_row();
                if($row->akses=='customer'){
                    $sql = "SELECT kode AS nomor_induk, nama AS fullname FROM tb_customer WHERE kode='".$row->kode."' ";
                }else if($row->akses=='kantin'){
                    $sql = "SELECT kode_supplier AS nomor_induk, nama_supplier AS fullname FROM tb_supplier WHERE kode_supplier='".$row->kode."' ";
                }
                
                $query = $this->db->query($sql);
                $data  = $query->first_row();
                $data_session = array(
                        'username' => $data->nomor_induk,
                        'fullname' => $data->fullname,
                        'nomor_induk' => $data->nomor_induk,
                        'user_id' => $data->nomor_induk,
                        'akses' => $row->akses,
                        'status' => "mart"
                        );
                $this->session->set_userdata($data_session);
                redirect(base_url());
            }else{
                $this->load->view('v_login',array("info"=>"gagal", "text"=>"ID Card atau PIN salah !"));
            }
        }

	function aksi_login(){
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            $where = array(
                    'username' => $username,
                    'password' => md5($password)
                    );
            $result = $this->m_login->cek_login("tb_user",$where);
            $cek = $result->num_rows();
            if($cek > 0){
                $row = $result->first_row();
                $data_session = array(
                        'username' => $username,
                        'fullname' => $row->fullname,
                        'nomor_induk' => $row->nomor_induk,
                        'user_id' => $data->nomor_induk,
                        'akses' => $row->akses,
                        'status' => "mart"
                        );
                $this->session->set_userdata($data_session);
                redirect(base_url());
            }else{
                $this->load->view('v_login',array("info"=>"gagal", "text"=>"Username atau password salah !"));
            }
        }

	function logout(){
            $this->session->sess_destroy();
            redirect(base_url());
	}
}
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stan extends CI_Controller {
    
        public $nama_tabel = 'tb_stan';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }else{
                if($this->session->userdata('akses') != "admin"){
                    redirect(base_url('login'));
                }
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_stan');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('stan');
            $this->load->view('footer');
	}
        
        public function data(){
            $draw = $this->input->post('draw');
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $kata = $this->input->post('cari');
            
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( s.nama_stan LIKE '%$kata%' ) ";
            }
            
            $query1 = $this->db->query("SELECT COUNT(*) as total FROM tb_stan s WHERE s.no_stan!='0' $where1 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT s.*
                            FROM tb_stan s
                            WHERE s.no_stan!='0' $where1
                            ORDER BY s.no_stan LIMIT $start, $length 
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $data[$i] = $row;
                $data[$i]['button'] = '<button title="Edit" idnex="'.$data[$i]['no_stan'].'" class="btn btn-info btn-xs btnedit" ><i class="fa fa-pencil"></i> Edit</button> 
                                       <button title="Delete" idnex="'.$data[$i]['no_stan'].'" namenex="'.$data[$i]['nama_stan'].'" class="btn btn-danger btn-xs btnhapus" ><i class="fa fa-remove"></i> Del</button>';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function cari(){
            $id = $this->input->post('id');
            $array = array();
            $query = $this->db->query("SELECT * FROM tb_stan where no_stan='$id'");
            foreach ($query->result_array() as $data){
                    array_push($array,$data);
            }
            echo json_encode(array('data'=>$array[0]));
        }
        
        public function hapus(){
            $id = $this->input->post('id');
            // fungsi cek delete kalau id nya mempengaruhi tabel lain            
            $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_stok WHERE no_stan='$id' ");
            $row1 = $query1->first_row();
            $jml = $row1->jml;
            if($jml=='0') { 
                $delete = $this->db->query("DELETE FROM tb_stan where no_stan='$id'");
                if($delete){
                    echo json_encode(array('status'=>'success'));
                }else{
                    echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa delete data!'));
                }
            }else{
                echo json_encode(array('status'=>'failed', 'txt'=>'Stan masih digunakan di Stok, tdk bisa delete data!'));
            }
        }
        
        public function save(){
            $idne = $this->input->post('idne');
            $stan = strtoupper($this->input->post('stan'));
            $crud = $this->input->post('crud');
            $date = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if($crud=='N'){
                    $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_stan WHERE nama_stan='$stan' ");
                    $row1 = $query1->first_row();
                    $jmle = intval($row1->jml);
                    if($jmle>0){
                        echo json_encode(array('status'=>'failed', 'txt'=>'Nama Stan sudah ada!'));
                    }else{                        
                        $inser = $this->db->query("INSERT INTO tb_stan 
                                (nama_stan, created_date, created_by )
                                VALUES
                                ('$stan', '$date', '$userr' ) ");
                        if($inser){
                            echo json_encode(array('status'=>'success'));
                        }else{
                            echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                        };
                    }
            }else if($crud == 'E'){
                    $update = $this->db->query("UPDATE tb_stan SET
                                                    nama_stan = '$stan' ,                                                       
                                                    updated_date = '$date' , 
                                                    updated_by = '$userr'
                                                WHERE
                                                    no_stan = '$idne' ");
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

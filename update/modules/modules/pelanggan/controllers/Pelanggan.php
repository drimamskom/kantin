<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pelanggan extends CI_Controller {
    
        public $nama_tabel = 'm_pelanggan';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_pelanggan');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('pelanggan');
            $this->load->view('footer');
	}
        
	public function upload(){
            $this->load->view('header');
            $this->load->view('upload');
            $this->load->view('footer');
	}
        
        public function do_upload(){
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = 'xlsx|xls';
		
            $this->load->library('upload', $config);

            if ( ! $this->upload->do_upload('upfiles')){
                $hasil = array('status'=>'failed', 'txt'=> $this->upload->display_errors());
            }else{
                $data = array('upload_data' => $this->upload->data());
                $upload_data = $this->upload->data(); //Returns array of containing all of the data related to the file you uploaded.
                $filename = $upload_data['file_name'];
                $hasil = $this->m_pelanggan->upload_data($filename);
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
            $grup = $this->input->post('grup');
            
            $arr_jk = array("L" => "Laki - Laki", "P" => "Perempuan");
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND c.kode LIKE '%$kata%' OR c.nama LIKE '%$kata%' ";
            }
            if(empty($grup)){
                $where2="";
            }else{
                $where2=" AND c.grup='$grup' ";
            }
            $query1 = $this->db->query("SELECT COUNT(*) as total FROM tb_customer c WHERE c.id!='0' $where1 $where2 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT c.*, g.grup nama_grup
                            FROM tb_customer c
                            LEFT JOIN tb_grup g ON g.kode=c.grup
                            WHERE c.id!='0' $where1 $where2
                            ORDER BY c.grup desc, c.nama 
                            LIMIT $start, $length 
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $row['jenis_kelamin'] = $arr_jk[$row['jenis_kelamin']];
                $data[$i] = $row;
                $data[$i]['button'] = '<button title="Edit" idnex="'.$data[$i]['kode'].'" class="btn btn-info btn-xs btnedit" ><i class="fa fa-pencil"></i> Edit</button> 
                                       <button title="Delete" idnex="'.$data[$i]['kode'].'" namenex="'.$data[$i]['nama'].'" class="btn btn-danger btn-xs btnhapus" ><i class="fa fa-remove"></i> Del</button>';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function cari(){
            $id = $this->input->post('id');
            $array = array();
            $query = $this->db->query("SELECT * FROM tb_customer where kode='$id'");
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
            $insert = $this->db->query("INSERT INTO tb_customer_hist 
                                              (kode, kode2, grup, nama, alamat, kota, agama, jenis_kelamin, id_kelas, id_thnajaran, golongan, aksi, perubahan, updated_date, updated_by)
                                        SELECT kode, kode2, grup, nama, alamat, kota, agama, jenis_kelamin, id_kelas, id_thnajaran, golongan, 'HAPUS', '', '$date', '$userr'
                                        FROM tb_customer WHERE kode = '$id' ");
            if($insert) { 
                $delete = $this->db->query("DELETE FROM tb_customer where kode='$id'");
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
            $idcard = $this->input->post('idcard');
            $pelanggan = addslashes($this->input->post('pelanggan'));
            $jenis_kelamin = $this->input->post('jenis_kelamin');
            $alamat = $this->input->post('alamat');
            $agama = $this->input->post('agama');
            $grup = $this->input->post('grup');
            $crud = $this->input->post('crud');
            $date = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if($crud=='N'){
                $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_customer WHERE kode='$code' OR kode2='$idcard' ");
                $row1 = $query1->first_row();
                $jmle = intval($row1->jml);
                if($jmle>0){
                    echo json_encode(array('status'=>'failed', 'txt'=>'Kode sudah ada!'));
                }else{
                    $inser = $this->db->query("INSERT INTO tb_customer 
                            (kode, kode2, grup, nama, alamat, agama, jenis_kelamin, created_date, created_by)
                            VALUES
                            ('$code', '$idcard', '$grup', '$pelanggan', '$alamat', '$agama', '$jenis_kelamin', '$date', '$userr')");
                    if($inser){ 
                        $inser = $this->db->query("INSERT INTO tb_card 
                                                    (grup, kode, card, pin, akses, created_date, created_by)
                                                   VALUES
                                                    ('$grup', '$code', '$idcard', '1234', 'customer', '$date', '$userr')");
                        echo json_encode(array('status'=>'success'));
                    }else{
                        echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                    }
                }
            }else if($crud == 'E'){
                $update = $this->db->query("UPDATE tb_customer SET
                                            grup = '$grup' , 
                                            nama = '$pelanggan' , 
                                            alamat = '$alamat' , 
                                            agama = '$agama' , 
                                            jenis_kelamin = '$jenis_kelamin' ,                                      
                                            updated_date = '$date' , 
                                            updated_by = '$userr'
                                         WHERE
                                            kode = '$idne' ");
                if($update){
                    echo json_encode(array('status'=>'success'));
                }else{
                    echo json_encode(array('status'=>'failed'));
                }
            }else{
                echo json_encode(array('status'=>'failed'));
            }
        }        
}

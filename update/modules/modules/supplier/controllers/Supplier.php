<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Supplier extends CI_Controller {
    
        public $nama_tabel = 'tb_supplier';
        
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
            $this->load->model('m_supplier');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('supplier');
            $this->load->view('footer');
	}
        
	public function upload(){
            $this->load->view('header');
            $this->load->view('upload_sup');
            $this->load->view('footer');
	}
        
        public function do_upload(){
            $periode_text = $this->input->post('periode_text');
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = 'xlsx|xls';
		
            $this->load->library('upload', $config);

            if ( ! $this->upload->do_upload('upfiles')){
                $hasil = array('status'=>'failed', 'txt'=> $this->upload->display_errors());
            }else{
                $data = array('upload_data' => $this->upload->data());
                $upload_data = $this->upload->data(); //Returns array of containing all of the data related to the file you uploaded.
                $filename = $upload_data['file_name'];
                $hasil = $this->m_supplier->upload_data($filename,$periode_text);
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
            $this->load->view('upload_sup', $data, FALSE);
            $this->load->view('footer');
	}
        
        public function data(){
            $draw = $this->input->post('draw');
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $kata = $this->input->post('cari');
            $tempat = $this->input->post('tempat');
            
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( s.kode_supplier LIKE '%$kata%' OR s.nama_supplier LIKE '%$kata%' OR s.alamat_supplier LIKE '%$kata%' OR s.kota_supplier LIKE '%$kata%' ) ";
            }
            if(empty($tempat)){
                $where2="";
            }else{
                $where2=" AND s.tempat='$tempat' ";
            }
            
            $query1 = $this->db->query("SELECT COUNT(*) as total FROM tb_supplier s WHERE s.kode_supplier!='0' $where1 $where2 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT s.*, t.tempat nama_tempat
                            FROM tb_supplier s
                            LEFT JOIN tb_tempat t ON t.kode=s.tempat
                            WHERE s.kode_supplier!='0' $where1 $where2
                            ORDER BY s.kode_supplier LIMIT $start, $length 
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $data[$i] = $row;
                $data[$i]['button'] = '<button title="Edit" idnex="'.$data[$i]['kode_supplier'].'" class="btn btn-info btn-xs btnedit" ><i class="fa fa-pencil"></i> Edit</button> 
                                       <button title="Delete" idnex="'.$data[$i]['kode_supplier'].'" namenex="'.$data[$i]['nama_supplier'].'" class="btn btn-danger btn-xs btnhapus" ><i class="fa fa-remove"></i> Del</button>';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function cari(){
            $id = $this->input->post('id');
            $array = array();
            $query = $this->db->query("SELECT * FROM tb_supplier where kode_supplier='$id'");
            foreach ($query->result_array() as $data){
                    array_push($array,$data);
            }
            echo json_encode(array('data'=>$array[0]));
        }
        
        public function hapus(){
            $id = $this->input->post('id');
            // fungsi cek delete kalau id nya mempengaruhi tabel lain            
            $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM trns_pembelian WHERE kode_supplier='$id' ");
            $row1 = $query1->first_row();
            $jml = $row1->jml;
            if($jml=='0') { 
                $delete = $this->db->query("DELETE FROM tb_supplier where kode_supplier='$id'");
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
            $supplier = strtoupper($this->input->post('supplier'));
            $alamat = $this->input->post('alamat');
            $kota = $this->input->post('kota');
            $telp = $this->input->post('telp');
            $crud = $this->input->post('crud');
            $date = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if($crud=='N'){
                    $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_supplier WHERE nama_supplier='$supplier' ");
                    $row1 = $query1->first_row();
                    $jmle = intval($row1->jml);
                    if($jmle>0){
                        echo json_encode(array('status'=>'failed', 'txt'=>'Nama Supplier sudah ada!'));
                    }else{
                        $code = strtoupper(substr($supplier,0,1))."-";
                        $query2 = $this->db->query("select max(right(kode_supplier,6)*1) as new_count from tb_supplier where left(kode_supplier,2)='$code' ");
                        $row2 = $query2->first_row();
                        $new_count = intval($row2->new_count)+1;
                        $new_code = $code.str_pad($new_count,6,"0",STR_PAD_LEFT);
                        
                        $inser = $this->db->query("INSERT INTO tb_supplier 
                                (kode_supplier, nama_supplier, alamat_supplier, kota_supplier, tlp_supplier, created_date, created_by )
                                VALUES
                                ('$new_code', '$supplier', '$alamat', '$kota', '$telp', '$date', '$userr' ) ");
                        if($inser){
                            echo json_encode(array('status'=>'success'));
                        }else{
                            echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                        };
                    }
            }else if($crud == 'E'){
                    $update = $this->db->query("UPDATE tb_supplier SET
                                                nama_supplier = '$supplier' , 
                                                alamat_supplier = '$alamat' , 
                                                kota_supplier = '$kota' , 
                                                tlp_supplier = '$telp' ,                                                      
                                                updated_date = '$date' , 
                                                updated_by = '$userr'

                                                WHERE
                                                kode_supplier = '$idne' ");
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

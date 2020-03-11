<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Depot extends CI_Controller {
    
        public $nama_tabel = 'tb_depot';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_depot');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('depot');
            $this->load->view('footer');
	}
        
	public function upload(){
            $this->load->view('header');
            $this->load->view('upload_depot');
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
                $hasil = $this->m_depot->upload_data($filename,$periode_text);
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
            $this->load->view('upload_depot', $data, FALSE);
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
                $where1=" AND ( s.kode_depot LIKE '%$kata%' OR s.nama_depot LIKE '%$kata%' OR s.alamat_depot LIKE '%$kata%' ) ";
            }
            
            $query1 = $this->db->query("SELECT COUNT(*) as total FROM tb_depot s WHERE s.kode_depot!='0' $where1 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT s.*
                            FROM tb_depot s
                            WHERE s.kode_depot!='0' $where1
                            ORDER BY s.kode_depot LIMIT $start, $length 
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $data[$i] = $row;
                $data[$i]['button'] = '<button title="Edit" idnex="'.$data[$i]['kode_depot'].'" class="btn btn-info btn-xs btnedit" ><i class="fa fa-pencil"></i> Edit</button> 
                                       <button title="Delete" idnex="'.$data[$i]['kode_depot'].'" namenex="'.$data[$i]['nama_depot'].'" class="btn btn-danger btn-xs btnhapus" ><i class="fa fa-remove"></i> Del</button>';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function cari(){
            $id = $this->input->post('id');
            $array = array();
            $query = $this->db->query("SELECT * FROM tb_depot where kode_depot='$id'");
            foreach ($query->result_array() as $data){
                    array_push($array,$data);
            }
            echo json_encode(array('data'=>$array[0]));
        }
        
        public function hapus(){
            $id = $this->input->post('id');
            // fungsi cek delete kalau id nya mempengaruhi tabel lain            
//            $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM trns_pembelian WHERE kode_depot='$id' ");
//            $row1 = $query1->first_row();
//            $jml = $row1->jml;
//            if($jml=='0') { 
                $delete = $this->db->query("DELETE FROM tb_depot where kode_depot='$id'");
                if($delete){
                    echo json_encode(array('status'=>'success'));
                }else{
                    echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa delete data!'));
                }
//            }else{
//                echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa delete data!'));
//            }
        }
        
        public function save(){
            $idne = $this->input->post('idne');
            $depot = $this->input->post('depot');
            $alamat = $this->input->post('alamat');
            $telp = $this->input->post('telp');
            $crud = $this->input->post('crud');
            $date = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if($crud=='N'){
                $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_depot WHERE nama_depot='$depot' ");
                $row1 = $query1->first_row();
                $jmle = intval($row1->jml);
                if($jmle>0){
                    echo json_encode(array('status'=>'failed', 'txt'=>'Nama Depot sudah ada!'));
                }else{
                    $code = "DPT-".strtoupper(substr($depot,0,1));
                    $query2 = $this->db->query("select max(right(kode_depot,5)*1) as new_count from tb_depot where left(kode_depot,5)='$code' ");
                    $row2 = $query2->first_row();
                    $new_count = intval($row2->new_count)+1;
                    $new_code = $code.str_pad($new_count,5,"0",STR_PAD_LEFT);

                    $inser = $this->db->query("INSERT INTO tb_depot 
                            (kode_depot, nama_depot, alamat_depot, tlp_depot, created_date, created_by )
                            VALUES
                            ('$new_code', '$depot', '$alamat', '$telp', '$date', '$userr' ) ");
                    if($inser){
                        echo json_encode(array('status'=>'success'));
                    }else{
                        echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                    };
                }
            }else if($crud == 'E'){
                $update = $this->db->query("UPDATE tb_depot SET
                                            nama_depot = '$depot' , 
                                            alamat_depot = '$alamat' , 
                                            tlp_depot = '$telp' ,                                                      
                                            updated_date = '$date' , 
                                            updated_by = '$userr'

                                            WHERE
                                            kode_depot = '$idne' ");
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

<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Barang extends CI_Controller {
    
        public $nama_tabel = 'm_barang';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }else{
                if($this->session->userdata('akses') != "admin"){
                    redirect(base_url('login'));
                }
            }
            $this->load->library("Role");
            $this->load->library("PHPExcel");
            $this->load->model('m_barang');
	}
        
	public function index(){     
            if(!$this->role->getAkses()){
                $this->load->view('header');
                $this->load->view('error');
                $this->load->view('footer');
            }else{
                $this->load->view('header');
                $this->load->view('barang');
                $this->load->view('footer');                
            }
	}
        
	public function upload(){
            $this->load->view('header');
            $this->load->view('upload');
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
                $hasil = $this->m_barang->upload_data($filename,$periode_text);
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
            $stan = $this->input->post('stan');
            $tempat = $this->input->post('tempat');
            $supplier = $this->input->post('supplier');
            
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( s.kode_barang LIKE '%$kata%' OR s.nama_barang LIKE '%$kata%' ) ";
            }
            if(count($stan)>0){                
                $stan_list = "'".implode("','", $stan)."'";
                $where2=" AND s.no_stan IN ($stan_list) ";
            }else{
                $where2="";
            }
            if(empty($tempat)){
                $where3="";
            }else{
                $where3=" AND s.tempat='$tempat' ";
            }
            if(empty($supplier)){
                $where4="";
            }else{
                $where4=" AND s.supplier='$supplier' ";
            }
            $query1 = $this->db->query("SELECT COUNT(*) as total FROM tb_stok s WHERE s.aktif='1' $where1 $where2 $where3 $where4 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT s.*, sup.nama_supplier, t.tempat nama_tempat, st.nama_stan 
                            FROM tb_stok s
                            LEFT JOIN tb_supplier sup ON sup.kode_supplier=s.supplier
                            LEFT JOIN tb_tempat t ON t.kode=s.tempat
                            LEFT JOIN tb_stan st ON st.no_stan=s.no_stan
                            WHERE s.aktif='1' $where1 $where2 $where3 $where4 
                            ORDER BY s.tempat, s.nama_barang 
                            LIMIT $start, $length 
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $row['harga_beli'] = "Rp, ".number_format($row['harga_beli'], 0, ".", ".");
                $row['harga_jual'] = "Rp, ".number_format($row['harga_jual'], 0, ".", ".");
                $data[$i] = $row;
                $data[$i]['button'] = '<button title="Edit Barang" idnex="'.$data[$i]['kode_barang'].'" class="btn btn-info btn-xs btnedit" ><i class="fa fa-pencil"></i> Edit</button> 
                                       <button title="Delete Barang" idnex="'.$data[$i]['kode_barang'].'" namenex="'.$data[$i]['nama_barang'].'" class="btn btn-danger btn-xs btnhapus" ><i class="fa fa-remove"></i> Del</button>';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function cari(){
            $id = $this->input->post('id');
            $array = array();
            $query = $this->db->query("SELECT * FROM tb_stok where kode_barang='$id'");
            foreach ($query->result_array() as $data){
                    array_push($array,$data);
            }
            echo json_encode(array('data'=>$array[0]));
        }
        
        public function hapus(){
            $id = $this->input->post('id');
            // fungsi cek delete kalau id nya mempengaruhi tabel lain            
            $query1 = $this->db->query("SELECT SUM(qty) AS jml FROM tb_stok_moves WHERE kode_barang='$id' ");
            $row1 = $query1->first_row();
            $jml = intval($row1->jml);
            if($jml=='0') { 
                $delete = $this->db->query("UPDATE tb_stok SET aktif='0' WHERE kode_barang='$id' ");
                if($delete){
                    echo json_encode(array('status'=>'success'));
                }else{
                    echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa delete data!'));
                }
            }else{
                echo json_encode(array('status'=>'failed', 'txt'=>'Stok Masih ada, tdk bisa delete data!'));
            }
        }
        
        public function save(){
            $idne = $this->input->post('idne');
            $barang = $this->input->post('barang');
            $kode_barang = $this->input->post('kode_barang');
            $jenis = $this->input->post('jenis');
            $satuan = $this->input->post('satuan');
            $tempat = $this->input->post('tempat');
            $stan = $this->input->post('stan');
            $supplier = $this->input->post('supplier');
            $stok_minimal = $this->input->post('stok_minimal');
            $harga_beli = $this->input->post('harga_beli');
            $harga_jual = $this->input->post('harga_jual');
            $crud = $this->input->post('crud');
            $date = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if($crud=='N'){
                $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_stok WHERE kode_barang='$kode_barang' ");
                $row1 = $query1->first_row();
                $jmle = intval($row1->jml);
                if($jmle>0){
                    echo json_encode(array('status'=>'failed', 'txt'=>'Nama barang/kode barang sudah ada!'));
                }else{
                    $inser = $this->db->query("INSERT INTO tb_stok 
                            (jenis, kode_barang, nama_barang, satuan, harga_beli, harga_jual, min_stok, tempat, no_stan, supplier, created_date, created_by )
                            VALUES
                            ('$jenis', '$kode_barang', '$barang','$satuan', '$harga_beli', '$harga_jual', '$stok_minimal', '$tempat', '$stan', '$supplier', '$date', '$userr' ) ");
                    if($inser){
                        echo json_encode(array('status'=>'success'));
                    }else{
                        echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                    };
                }
            }else if($crud == 'E'){
                $update = $this->db->query("UPDATE tb_stok SET
                                            jenis = '$jenis' , 
                                            kode_barang = '$kode_barang' , 
                                            nama_barang = '$barang' , 
                                            satuan = '$satuan' , 
                                            harga_beli = '$harga_beli' , 
                                            harga_jual = '$harga_jual' , 
                                            min_stok = '$stok_minimal' , 
                                            tempat = '$tempat' , 
                                            no_stan = '$stan' , 
                                            supplier = '$supplier' ,                                                    
                                            updated_date = '$date' , 
                                            updated_by = '$userr'
                                          WHERE
                                            id = '$idne' ");
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

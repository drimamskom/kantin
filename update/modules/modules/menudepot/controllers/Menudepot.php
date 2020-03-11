<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Menudepot extends CI_Controller {
    
        public $nama_tabel = 'm_menudepot';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	            
            $this->load->library("Role");
            $this->load->model('m_menudepot');
	}
        
	public function index(){ 
            if(!$this->role->getAkses()){
                $this->load->view('header');
                $this->load->view('error');
                $this->load->view('footer');
            }else{
                $this->load->view('header');
                $this->load->view('menudepot');
                $this->load->view('footer');                
            }
	}
        
        public function data(){
            $sesi = $this->session->get_userdata();
            $nomor_induk = $sesi['nomor_induk'];
            $akses = $sesi['akses'];
            //$today = date('Y-m-d');
            $draw = $this->input->post('draw');
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $kata = $this->input->post('cari');
            $kategori = $this->input->post('kategori');
            
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( s.kode_menu LIKE '%$kata%' OR s.nama_menu LIKE '%$kata%' ) ";
            }
            if(empty($kategori)){
                $where2="";
            }else{
                $where2=" AND s.kategori = '$kategori' ";
            }  
            if($akses=="admin"){
                $where3="";
            }else{
                $where3=" AND s.kode_depot = '$nomor_induk' ";
            }  
            $query1 = $this->db->query("SELECT COUNT(*) as total FROM tb_depot_menu s WHERE s.id!='0' $where1 $where2 $where3 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT s.*, d.nama_depot, k.nama_kategori
                            FROM tb_depot_menu s
                            LEFT JOIN tb_depot_kategori k ON k.kode_kategori=s.kategori
                            LEFT JOIN tb_depot d ON d.kode_depot=s.kode_depot
                            WHERE s.id!='0' $where1 $where2 $where3
                            ORDER BY s.kode_menu LIMIT $start, $length 
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $row['harga'] = "Rp, ".number_format($row['harga'], 0, ".", ".");
                $data[$i] = $row;
                $data[$i]['button'] = '<button title="Edit Menu" idnex="'.$data[$i]['kode_menu'].'" class="btn btn-info btn-xs btnedit" ><i class="fa fa-pencil"></i> Edit</button> 
                                       <button title="Delete Menu" idnex="'.$data[$i]['kode_menu'].'" namenex="'.$data[$i]['nama_menu'].'" class="btn btn-danger btn-xs btnhapus" ><i class="fa fa-remove"></i> Del</button>';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function cari(){
            $id = $this->input->post('id');
            $array = array();
            $query = $this->db->query("SELECT * FROM tb_depot_menu where kode_menu='$id'");
            foreach ($query->result_array() as $data){
                    array_push($array,$data);
            }
            echo json_encode(array('data'=>$array[0]));
        }
        
        public function hapus(){
            $id = $this->input->post('id');
            // fungsi cek delete kalau id nya mempengaruhi tabel lain            
            $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM trns_pemesanan_detail WHERE kode_menu='$id' ");
            $row1 = $query1->first_row();
            $jml = $row1->jml;
            if($jml=='0') { 
                $delete = $this->db->query("DELETE FROM tb_depot_menu WHERE kode_menu='$id' ");
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
            $menu = $this->input->post('menu');
            $kategori = $this->input->post('kategori');
            $kode_depot = $this->input->post('depot');
            $harga = $this->input->post('harga');
            $crud = $this->input->post('crud');
                
            $today = date('Y-m-d');
            $date = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if($crud=='N'){
                $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_depot_menu WHERE nama_menu='$menu' AND kode_depot='$kode_depot' ");
                $row1 = $query1->first_row();
                $jmle = intval($row1->jml);
                if($jmle>0){
                    echo json_encode(array('status'=>'failed', 'txt'=>'Nama Menu sudah ada!'));
                }else{
                    $code = strtoupper($kategori)."-";
                    $query2 = $this->db->query("select max(right(kode_menu,4)*1) as new_count FROM tb_depot_menu where left(kode_menu,4)='$code' ");
                    $row2 = $query2->first_row();
                    $new_count = intval($row2->new_count)+1;
                    $new_code = $code.str_pad($new_count,4,"0",STR_PAD_LEFT);

                    $inser = $this->db->query("INSERT INTO tb_depot_menu 
                            (kode_menu, nama_menu, kategori, kode_depot, harga, created_date, created_by )
                            VALUES
                            ('$new_code', '$menu', '$kategori', '$kode_depot', '$harga',  '$date', '$userr' ) ");
                    if($inser){  
                        echo json_encode(array('status'=>'success'));
                    }else{
                        echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                    }
                }
            }else if($crud == 'E'){
                $update = $this->db->query("UPDATE tb_depot_menu SET
                                            nama_menu = '$menu' , 
                                            kategori = '$kategori' ,
                                            kode_depot = '$kode_depot' , 
                                            harga = '$harga' ,                                                     
                                            updated_date = '$date' , 
                                            updated_by = '$userr'
                                          WHERE
                                            kode_menu = '$idne' ");
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

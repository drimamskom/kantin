<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Isidepot extends CI_Controller {
    
        public $nama_tabel = 'm_isidepot';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_isidepot');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('isidepot');
            $this->load->view('footer');
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
            $tgl_cari = $this->input->post('tgl_cari');
            
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
            if(empty($tgl_cari)){
                $tanggal = date('Y-m-d');
            }else{
                $tanggal = $this->m_isidepot->datetosqldate($tgl_cari);
            }
            $query1 = $this->db->query("SELECT COUNT(*) as total FROM tb_depot_menu s WHERE s.id!='0' $where1 $where2 $where3 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT s.*, d.nama_depot, k.nama_kategori, stok.tanggal, stok.tersedia, stok.terpakai
                            FROM tb_depot_menu s
                            LEFT JOIN tb_depot_kategori k ON k.kode_kategori=s.kategori
                            LEFT JOIN tb_depot_menu_stok stok ON stok.kode_menu=s.kode_menu AND stok.tanggal='$tanggal'
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
                $data[$i]['button'] = '<button title="Edit Menu" idnex="'.$data[$i]['kode_menu'].'" class="btn btn-info btn-xs btnedit" ><i class="fa fa-pencil"></i> Pengisian</button>';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function cari(){
            $id = $this->input->post('id');
            $today = date('Y-m-d');
            $array = array();
            $query = $this->db->query("SELECT s.*, d.nama_depot, k.nama_kategori, stok.tanggal, stok.tersedia, stok.terpakai
                                       FROM tb_depot_menu s
                                       LEFT JOIN tb_depot_kategori k ON k.kode_kategori=s.kategori
                                       LEFT JOIN tb_depot_menu_stok stok ON stok.kode_menu=s.kode_menu AND stok.tanggal='$today'
                                       LEFT JOIN tb_depot d ON d.kode_depot=s.kode_depot
                                       WHERE s.kode_menu='$id'");
            foreach ($query->result_array() as $data){
                array_push($array,$data);
            }
            echo json_encode(array('data'=>$array[0]));
        }
        
        public function save(){
            $idne = $this->input->post('idne');
            //$menu = $this->input->post('menu');
            //$kode_depot = $this->input->post('depot');
            $tersedia = $this->input->post('tersedia');
            $tanggal = $this->m_isidepot->datetosqldate($this->input->post('tanggal'));
            $crud = $this->input->post('crud');
                
            $date = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if($crud == 'E'){
                $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_depot_menu_stok WHERE kode_menu = '$idne' AND tanggal='$tanggal' ");
                $row1 = $query1->first_row();
                $jmle = intval($row1->jml);
                if($jmle>0){
                    //KALAU SUDAH ADA MAKA DI UPDATE
                    $update = $this->db->query("UPDATE tb_depot_menu_stok SET
                                            tersedia = '$tersedia' ,                                            
                                            updated_date = '$date' , 
                                            updated_by = '$userr'
                                          WHERE
                                            kode_menu = '$idne'
                                            AND tanggal='$tanggal' ");
                    if($update){
                        echo json_encode(array('status'=>'success'));       
                    }else{
                        echo json_encode(array('status'=>'failed'));
                    }
                }else{
                    //KALAU SUDAH ADA MAKA DI INSERT
                    $inser = $this->db->query("INSERT INTO tb_depot_menu_stok 
                            (kode_menu, tanggal, tersedia, terpakai, created_date, created_by )
                            VALUES
                            ('$idne', '$tanggal', '$tersedia', '0', '$date', '$userr' ) ");
                    if($inser){  
                        echo json_encode(array('status'=>'success'));
                    }else{
                        echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                    }
                }
                
            }else{
                echo json_encode(array('status'=>'failed'));
            }
        }
        
}

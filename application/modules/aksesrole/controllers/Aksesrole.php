<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Aksesrole extends CI_Controller {
    
        public $nama_tabel = 'm_role';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_aksesrole');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('aksesrole');
            $this->load->view('footer');
	}
        
        public function data(){
            $draw  = $this->input->post('draw');
            $kata  = $this->input->post('cari');
            
            $kolom  = array();
            $query1 = $this->db->query("SELECT akses FROM tb_akses WHERE aktif='1' ");
            foreach ($query1->result_array() as $datax){
                array_push($kolom, $datax['akses']);
            }

            if(empty($kata)){
                $where="";
            }else{
                $where=" AND ( ur.akses LIKE '%$kata%' OR m.judul_menu LIKE '%$kata%' ) ";
            }
            
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT dt.aksesnya, m.*
                            FROM tb_menu m
                            LEFT JOIN (
				SELECT menu, GROUP_CONCAT(akses SEPARATOR ',') AS aksesnya
				FROM tb_user_role GROUP BY menu
                            ) AS dt ON dt.menu=m.id
                            WHERE m.id IS NOT NULL AND m.aktif='1' $where 
                            ORDER BY m.parent, m.urut
                    ) AS t, 
                    (SELECT @rownum := 0) r ");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $aksesnya = explode(',', $row['aksesnya']);
                foreach ($kolom as $akses){
                    if (in_array($akses, $aksesnya)){
                        $data[$i][$akses] = '<center><input type="checkbox" value="1" name="hsl['.$akses.']['.$row['id'].'][]" class="'.$akses.'" checked="checked"></center>';
                    }else{
                        $data[$i][$akses] = '<center><input type="checkbox" value="1" name="hsl['.$akses.']['.$row['id'].'][]" class="'.$akses.'"></center>';
                    }
                }
                $data[$i]['urutan'] = $row['urutan']; 
                $data[$i]['nama']   = $row['judul_menu'];  
                $data[$i]['parent'] = $row['parent'];  
                $data[$i]['urut']   = $row['urut'];                
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => 0,  "recordsFiltered" => 0, "data" => $data);
            echo json_encode($datax);            
        }        
        
        public function save(){
            $hsl  = $this->input->post('hsl');
            
            $arr_akses = array();
            $query1 = $this->db->query("SELECT akses FROM tb_akses WHERE aktif='1' ");
            foreach ($query1->result_array() as $datax){
                $akses = $datax['akses'];
                if (array_key_exists($akses,$hsl)){
                    $data  = $hsl[$akses];
                    if(count($data)>0){
                        foreach ($data as $idmenu => $value) {
                            $arr_akses[] = " ('$idmenu', '$akses' ) ";
                        }
                    }
                }
            }
            //INSERT MENU VS AKSES  & RESET DATA
            if(count($arr_akses)>0){
                $in_akses = implode(",", $arr_akses);
                $reset = $this->db->query("TRUNCATE TABLE tb_user_role "); 
                $inser = $this->db->query("INSERT INTO tb_user_role (menu, akses) VALUES ".$in_akses." ");   
                if($inser){
                    echo json_encode(array('status'=>'success'));
                }else{
                    echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                }
            }else{
                echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
            }
        }
        
}

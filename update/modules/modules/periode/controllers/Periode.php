<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Periode extends CI_Controller {
    
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->model('m_periode');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('periode');
            $this->load->view('footer');
	}
        
        public function data(){
            $draw = $this->input->post('draw');
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $search = $this->input->post('search');
            $kata = $search['value'];
            if(empty($kata)){
                $where="";
            }else{
                $where=" WHERE nama_thnajaran LIKE '%$kata%' OR tgl_mulai LIKE '%$kata%' OR tgl_selesai LIKE '%$kata%' ";
            }

            $query1 = $this->db->query("SELECT COUNT(*) as total FROM tb_thnajaran $where");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT * FROM tb_thnajaran $where ORDER BY tgl_mulai DESC LIMIT $start, $length 
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                if($row['aktif']=='1'){ $chek='checked disabled'; }else{ $chek=''; }  
                $data[$i] = $row;                
                $data[$i]['tgl_mulai'] = $this->m_periode->sqldatetodate($row['tgl_mulai']);
                $data[$i]['tgl_selesai'] = $this->m_periode->sqldatetodate($row['tgl_selesai']);
                $data[$i]['cekbok'] = '<input type="checkbox" class="toggle-aktif" '.$chek.' name="'.$data[$i]['id_thnajaran'].'" data-toggle="toggle" data-on="Aktif" data-off="Non-Aktif" data-onstyle="success" data-offstyle="danger" data-size="mini">';
                $data[$i]['button'] = '<button title="Edit" idnex="'.$data[$i]['id_thnajaran'].'" class="btn btn-sm btn-info btnedit" ><i class="fa fa-pencil"></i> Edit</button> 
                                       <button title="Delete" idnex="'.$data[$i]['id_thnajaran'].'" namenex="'.$data[$i]['nama_thnajaran'].'" class="btn btn-sm btn-danger btnhapus" ><i class="fa fa-remove"></i> Del</button>';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function setaktif(){
            $id = $this->input->post('id');
            $act = $this->input->post('act');
            $date = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            if($act){
                $update = $this->db->query("UPDATE tb_thnajaran SET aktif = '0' ");
                if($update){
                    $update2 = $this->db->query("UPDATE tb_thnajaran SET aktif = '1' , 
                                                    updated_date = '$date' , 
                                                    updated_by = '$userr'

                                                    WHERE
                                                    id_thnajaran = '$id' ");
                    echo json_encode(array('status'=>'success'));
                }else{
                    echo json_encode(array('status'=>'failed'));
                }
            }else{
                echo json_encode(array('status'=>'failed'));
            };
        }
        
        public function cari(){
            $id = $this->input->post('id');
            $array = array();
            $query = $this->db->query("SELECT * FROM tb_thnajaran where id_thnajaran='$id'");
            foreach ($query->result_array() as $data){
                $data['tgl_mulai'] = $this->m_periode->sqldatetodate($data['tgl_mulai']);
                $data['tgl_selesai'] = $this->m_periode->sqldatetodate($data['tgl_selesai']);
                array_push($array,$data);
            }
            echo json_encode(array('data'=>$array[0]));
        }
        
        public function hapus(){
            $id = $this->input->post('id');
            // fungsi cek delete kalau id nya mempengaruhi tabel lain
            $query1 = $this->db->query("SELECT DISTINCT id_thnajaran FROM tb_siswa WHERE id_thnajaran='$id' ");
            $num = $query1->num_rows();
            if($num==0){
                $delete = $this->db->query("DELETE FROM tb_thnajaran where id_thnajaran='$id'");
                if($delete){
                    echo json_encode(array('status'=>'success'));
                }else{
                    echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa delete data!'));
                }
            }else{
                echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa delete data, Data masih digunakan!'));
            }
        }
        
        public function save(){
            $idne = $this->input->post('idne');
            $code = $this->input->post('code');
            $tgl_mulai = $this->m_periode->datetosqldate($this->input->post('tgl_mulai'));
            $tgl_selesai = $this->m_periode->datetosqldate($this->input->post('tgl_selesai'));
            $crud = $this->input->post('crud');
            $date = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if($crud=='N'){
                    $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_thnajaran WHERE id_thnajaran='$idne' ");
                    $row1 = $query1->first_row();
                    $jmle = intval($row1->jml);
                    if($jmle>0){
                        echo json_encode(array('status'=>'failed', 'txt'=>'Kelas sudah ada!'));
                    }else{
                        $inser = $this->db->query("INSERT INTO tb_thnajaran 
                                (nama_thnajaran, tgl_mulai, tgl_selesai, created_date, created_by)
                                VALUES
                                ('$code', '$tgl_mulai', '$tgl_selesai', '$date', '$userr')");
                        if($inser){
                            echo json_encode(array('status'=>'success'));
                        }else{
                            echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                        };
                    }
            }else if($crud == 'E'){
                        $update = $this->db->query("UPDATE tb_thnajaran SET
                                                    nama_thnajaran = '$code' , 
                                                    tgl_mulai = '$tgl_mulai' , 
                                                    tgl_selesai = '$tgl_selesai' , 
                                                    updated_date = '$date' , 
                                                    updated_by = '$userr'
                                                  WHERE
                                                    id_thnajaran = '$idne' ");
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

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Card extends CI_Controller {
    
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('card');
            $this->load->view('footer');
	}
        
	public function changepin(){ 
            $this->load->view('header');
            $this->load->view('changepin');
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
                $where=" AND ( cus.nama LIKE '%$kata%' OR c.kode LIKE '%$kata%' OR g.grup LIKE '%$kata%' ) ";
            }

            $query1 = $this->db->query("SELECT COUNT(*) as total FROM tb_card c
                                        LEFT JOIN tb_customer cus ON cus.kode=c.kode
                                        LEFT JOIN tb_grup g ON g.kode=c.grup
                                        WHERE c.aktif='1' $where");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT c.*, cus.nama, g.grup nama_grup
                            FROM tb_card c
                            LEFT JOIN tb_customer cus ON cus.kode=c.kode
                            LEFT JOIN tb_grup g ON g.kode=c.grup
                            WHERE c.aktif='1' $where
                            ORDER BY c.grup DESC, cus.nama 
                            LIMIT $start, $length 
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $data[$i] = $row;
                $data[$i]['button'] = '<button title="Delete" idnex="'.$data[$i]['kode'].'" namenex="'.$data[$i]['nama'].'" class="btn btn-xs btn-danger btnhapus" ><i class="fa fa-remove"></i> Del</button>';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function cari(){
            $id = $this->input->post('id');
            $array = array();
            $query = $this->db->query("SELECT * FROM tb_card where kode='$id'");
            foreach ($query->result_array() as $data){
                    array_push($array,$data);
            }
            echo json_encode(array('data'=>$array[0]));
        }
        
        public function info(){
            $kode = $this->input->post('kode');
            $array = array();
            $query = $this->db->query("SELECT * FROM tb_customer WHERE grup='$kode' ");
            foreach ($query->result_array() as $data){
                $row['id'] = $data['kode'];
                $row['text'] = $data['nama'];
                array_push($array,$row);
            }       
            echo json_encode(array('data'=>$array));
        }
        
        public function hapus(){
            $id = $this->input->post('id');
            $sesi = $this->session->get_userdata();
            $userid = $sesi['user_id'];
            if($id==$userid){
		echo json_encode(array('status'=>'failed', 'txt'=>'User masih digunakan, logout dahulu!'));
            }else{
		$delete = $this->db->query("DELETE FROM tb_card where kode='$id'");
		if($delete){
			echo json_encode(array('status'=>'success'));
		}else{
			echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa delete data!'));
		};
            }
        }
        
        public function act_change_pin(){
            $idcard = $this->input->post('idcard');
            $newpin = md5($this->input->post('newpin'));
            $date = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            $userid = $sesi['user_id'];

            $qcekk = $this->db->query("SELECT COUNT(*) AS jml FROM tb_card WHERE card='$idcard' ");
            $cekk = $qcekk->first_row();
            $jmle = intval($cekk->jml);
            if($jmle==0){
                echo json_encode(array('status'=>'failed', 'txt'=>'IDCARD tidak ada!'));
            }else{
                $update = $this->db->query("UPDATE tb_card SET
                                                pin = '$newpin' ,
                                                updated_date = '$date' , 
                                                updated_by = '$userr'
                                        WHERE
                                                user_id = '$userid' ");
                if($update){
                    echo json_encode(array('status'=>'success'));
                }else{
                    echo json_encode(array('status'=>'failed'));
                };
            }
            
        }
        
        public function save(){
            $idne   = $this->input->post('idne');
            $code   = $this->input->post('code');
            $pin    = $this->input->post('pin');
            $idcard = $this->input->post('idcard');
            $akses  = $this->input->post('akses');
            $grup   = $this->input->post('grup');
            //$nama   = $this->input->post('nama');
            //$nama2  = $this->input->post('nama2');
            $crud   = $this->input->post('crud');
            $date   = date('Y-m-d');
            $sesi   = $this->session->get_userdata();
            $userr  = $sesi['username'];
            
            if($crud=='N'){
                    $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_card 
                                                WHERE card='$idcard' AND pin='$pin' ");
                    $row1 = $query1->first_row();
                    $jmle = intval($row1->jml);
                    if($jmle>0){
                        echo json_encode(array('status'=>'failed', 'txt'=>'ID CARD dan PIN sudah dipakai!'));
                    }else{
                        $inser = $this->db->query("INSERT INTO tb_card 
                                            (grup, kode, card, pin, akses, created_date, created_by)
                                            VALUES
                                            ('$grup', '$code', '$idcard', '$pin', '$akses', '$date', '$userr')");
                        if($inser){
                                echo json_encode(array('status'=>'success'));
                        }else{
                                echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                        }
                    }
            }else if($crud == 'E'){
                        $update = $this->db->query("UPDATE tb_user SET
                                                        kode = '$code' ,
                                                        grup  = '$grup' , 
                                                        card  = '$idcard' , 
                                                        pin   = '$pin' , 
                                                        akses = '$akses' ,  
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

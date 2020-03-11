<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Saldo extends CI_Controller {
    
        public $nama_tabel = 'm_menudepot';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_saldo');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('saldo');
            $this->load->view('footer');
	}
        
        public function data(){
            $draw = $this->input->post('draw');
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $kata = $this->input->post('cari');
            $tanggal_mulai = $this->input->post('tgl_mulai');
            $tanggal_selesai = $this->input->post('tgl_selesai');
            
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( s.no_pengisian LIKE '%$kata%' OR s.nama_pengisian LIKE '%$kata%' OR s.nis LIKE '%$kata%' OR sis.nama LIKE '%$kata%' ) ";
            }
            if(empty($tanggal_mulai)||empty($tanggal_selesai)){
                $where2="";
            }else{
                $d1 = $this->m_saldo->datetosqldate($tanggal_mulai);
                $d2 = $this->m_saldo->datetosqldate($tanggal_selesai);
                $where2=" AND (s.tanggal BETWEEN '$d1' AND '$d2') ";
            }
            $query1 = $this->db->query("SELECT COUNT(*) as total 
                                        FROM tb_saldo s 
                                        LEFT JOIN tb_siswa sis ON sis.nis=s.nis
                                        WHERE s.no_pengisian!='0' $where1 $where2 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT s.*, sis.nama
                            FROM tb_saldo s
                            LEFT JOIN tb_siswa sis ON sis.nis=s.nis
                            WHERE s.no_pengisian!='0' $where1 $where2
                            ORDER BY s.tanggal LIMIT $start, $length 
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $row['saldo'] = "Rp, ".number_format($row['saldo'], 0, ".", ".");
                $row['tanggal'] = $this->m_saldo->sqldatetodate($row['tanggal']);
                $data[$i] = $row;
                $data[$i]['button'] = '<button title="Edit" idnex="'.$data[$i]['no_pengisian'].'" class="btn btn-info btn-xs btnedit" ><i class="fa fa-pencil"></i> Edit</button> 
                                       <button title="Delete" idnex="'.$data[$i]['no_pengisian'].'" namenex="'.$data[$i]['no_pengisian'].'" class="btn btn-danger btn-xs btnhapus" ><i class="fa fa-remove"></i> Del</button>';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function cari(){
            $id = $this->input->post('id');
            $array = array();
            $query = $this->db->query("SELECT * FROM tb_saldo where no_pengisian='$id'");
            foreach ($query->result_array() as $data){
                $data['tanggal'] = $this->m_saldo->sqldatetodate($data['tanggal']);
                array_push($array,$data);
            }
            echo json_encode(array('data'=>$array[0]));
        }
        
        public function hapus(){
            $id = $this->input->post('id');
            // fungsi cek delete kalau id nya mempengaruhi tabel lain            
            $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_saldo_moves WHERE transaksi='$id' ");
            $row1 = $query1->first_row();
            $jml = $row1->jml;
            if($jml=='0') { 
                $delete = $this->db->query("DELETE FROM tb_saldo WHERE no_pengisian='$id' ");
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
            $namasaldo = $this->input->post('namasaldo');
            $tanggal = $this->m_saldo->datetosqldate($this->input->post('tanggal'));
            $nis = $this->input->post('nis');
            $saldo = $this->input->post('saldo');
            $crud = $this->input->post('crud');
                
            $date = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if($crud=='N'){
                $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_saldo WHERE tanggal='$tanggal' AND nis='$nis' AND saldo='$saldo' ");
                $row1 = $query1->first_row();
                $jmle = intval($row1->jml);
                if($jmle>0){
                    echo json_encode(array('status'=>'failed', 'txt'=>'Saldo '.$saldo.' sudah pernah dimasukkan, ganti tanggal aktif!'));
                }else{
                    $cdhr = date('Ymd');
                    $code = "S".$cdhr."-";
                    $query2 = $this->db->query("select max(right(no_pengisian,4)*1) as new_count FROM tb_saldo where left(no_pengisian,10)='$code' ");
                    $row2 = $query2->first_row();
                    $new_count = intval($row2->new_count)+1;
                    $new_code = $code.str_pad($new_count,4,"0",STR_PAD_LEFT);

                    $inser = $this->db->query("INSERT INTO tb_saldo 
                            (no_pengisian, nama_pengisian, tanggal, nis, saldo, created_date, created_by )
                            VALUES
                            ('$new_code', '$namasaldo', '$tanggal', '$nis', '$saldo', '$date', '$userr' ) ");
                    if($inser){  
                        $inser2 = $this->db->query("INSERT INTO tb_saldo_moves 
                                    (transaksi, nis, jumlah, created_date, created_by)
                                    VALUES
                                    ('$new_code', '$nis', '$saldo', '$date', '$userr') ");
                        echo json_encode(array('status'=>'success'));
                    }else{
                        echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                    }
                }
            }else if($crud == 'E'){
                $update = $this->db->query("UPDATE tb_saldo SET
                                            nama_pengisian = '$namasaldo' , 
                                            tanggal = '$tanggal' ,
                                            nis = '$nis' , 
                                            saldo = '$saldo' ,                                                    
                                            updated_date = '$date' , 
                                            updated_by = '$userr'
                                          WHERE
                                            no_pengisian = '$idne' ");
                if($update){
                    $delete = $this->db->query("DELETE FROM tb_saldo_moves WHERE transaksi='$idne' ");
                    if($delete){
                        $inser2 = $this->db->query("INSERT INTO tb_saldo_moves 
                                    (transaksi, nis, jumlah, created_date, created_by)
                                    VALUES
                                    ('$idne', '$nis', '$saldo', '$date', '$userr') ");
                        echo json_encode(array('status'=>'success'));
                    }else{
                        echo json_encode(array('status'=>'success'));
                    }                    
                }else{
                    echo json_encode(array('status'=>'failed'));
                }
            }else{
                echo json_encode(array('status'=>'failed'));
            }
        }
        
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pembayaran extends CI_Controller {
    
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->model('m_pembayaran');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('pembayaran');
            $this->load->view('footer');
	}
        
        public function data(){
            $draw = $this->input->post('draw');
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $kata = $this->input->post('cari');
            if(empty($kata)){
                $where="";
            }else{
                $where=" AND pm.kode_pembayaran LIKE '%$kata%' OR pm.nama_pembayaran LIKE '%$kata%' OR k.text LIKE '%$kata%' OR p.periode_text LIKE '%$kata%' OR jp.jenis_pembayaran LIKE '%$kata%' ";
            }

            $query1 = $this->db->query("SELECT COUNT(*) as total FROM m_pembayaran_detail pm2
                            LEFT JOIN m_pembayaran pm ON pm2.kode_pembayaran=pm.kode_pembayaran
                            LEFT JOIN m_kelas k ON k.id_kelas=pm2.id_kelas
                            LEFT JOIN m_periode p ON p.id_periode=pm2.id_periode
                            LEFT JOIN jenis_pembayaran jp ON jp.id_jenis_pembayaran=pm.id_jenis_pembayaran 
                            WHERE pm.id_jenis_pembayaran!='SPP'
                            $where");
            $row1 = $query1->first_row();
            $tot = $row1->total;
            
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT pm.*, k.text, pm2.id, pm2.reguler, p.periode_text, jp.jenis_pembayaran
                            FROM m_pembayaran_detail pm2
                            LEFT JOIN m_pembayaran pm ON pm2.kode_pembayaran=pm.kode_pembayaran
                            LEFT JOIN m_kelas k ON k.id_kelas=pm2.id_kelas
                            LEFT JOIN m_periode p ON p.id_periode=pm2.id_periode
                            LEFT JOIN jenis_pembayaran jp ON jp.id_jenis_pembayaran=pm.id_jenis_pembayaran
                            WHERE pm.id_jenis_pembayaran!='SPP'
                            $where ORDER BY pm.kode_pembayaran DESC LIMIT $start, $length 
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $row['rupiah'] = number_format($row['rupiah'], 0, ".", ".");
                $data[$i] = $row;                
                $data[$i]['tgl_mulai'] = $this->m_pembayaran->sqldatetodate($row['tgl_mulai']);
                $data[$i]['tgl_selesai'] = $this->m_pembayaran->sqldatetodate($row['tgl_selesai']);
                $data[$i]['button'] = '<input type="checkbox" name="'.$data[$i]['id'].'">';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function cari(){
            $id = $this->input->post('id');
            $array = array();
            $query = $this->db->query("SELECT * FROM m_pembayaran where id_jenis_pembayaran!='SPP' and kode_pembayaran='$id'");
            foreach ($query->result_array() as $data){
                $data['tgl_mulai'] = $this->m_pembayaran->sqldatetodate($data['tgl_mulai']);
                $data['tgl_selesai'] = $this->m_pembayaran->sqldatetodate($data['tgl_selesai']);
                array_push($array,$data);
            }
            echo json_encode(array('data'=>$array[0]));
        }
        
        public function hapus(){
            $sukses=0; $gagal=0;
            $id = $this->input->post('id');
            // fungsi cek delete kalau id nya mempengaruhi tabel lain
            $id_arr = explode("-", $id);
            $alert = "Sudah Pernah dibayar : \n";
            $id_list = "'".implode("','", $id_arr)."'";
            $query = $this->db->query("SELECT * FROM (
                                        SELECT p.kode_pembayaran , SUM(tp.rupiah) bayar
                                        FROM  m_pembayaran_detail p 
                                        LEFT JOIN t_penjualan_detail tp ON tp.kode_pembayaran=p.kode_pembayaran
                                        WHERE p.id IN ($id_list)
                                        GROUP BY p.kode_pembayaran
                                       ) dt WHERE dt.bayar IS NOT NULL ");
            foreach ($query->result_array() as $data){
                $alert .= $data['kode_pembayaran']." (Rp, ".number_format($data['bayar'], 0, ".", ".").")\n";
            }
            $alert .= "Tidak boleh dihapus";
            $count = $query->num_rows();
            if($count>0){
                echo json_encode(array('status'=>'failed', 'txt'=>$alert));
            }else{
                foreach ($id_arr as $value) {
                    $delete = $this->db->query("DELETE FROM m_pembayaran_detail where id='$value' ");
                    if($delete){
                        $sukses++;
                    }else{
                        $gagal++;
                    }
                } 
                echo json_encode(array('status'=>'success', 'txt'=>'sukses:'.$sukses.' gagal:'.$gagal.'!'));
            }
        }
        
        public function save(){
            $idne = $this->input->post('idne');
            $pembayaran = $this->input->post('pembayaran');
            $rupiah = $this->input->post('rupiah');
            $kelas = $this->input->post('kelas');
            $periode_text = $this->input->post('periode_text');
            $reguler = $this->input->post('reguler');
            $jenis_pembayaran = $this->input->post('jenis_pembayaran');
            $tgl_mulai = $this->m_pembayaran->datetosqldate($this->input->post('tgl_mulai'));
            $tgl_selesai = $this->m_pembayaran->datetosqldate($this->input->post('tgl_selesai'));
            $crud = $this->input->post('crud');
            $bulan = substr($tgl_mulai, 5, 2);
            $tahun = substr($tgl_mulai, 0, 4);
            $datetime = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if($crud=='N'){
                $cekk = $this->db->query("SELECT MAX(batch_id)+1 AS batch FROM m_pembayaran");
                $rowcek = $cekk->first_row();
                $batch = $rowcek->batch;
                $sukses=0; $gagal=0;
                $alert = "Duplicate : \n";
                $kls_list = "'".implode("','", $kelas)."'";
                $query = $this->db->query("SELECT s.*, k.text kelas, p.periode_text
                                           FROM m_pembayaran_detail s2
                                           LEFT JOIN m_pembayaran s ON s2.kode_pembayaran=s.kode_pembayaran
                                           LEFT JOIN m_kelas k ON k.id_kelas=s2.id_kelas
                                           LEFT JOIN m_periode p ON p.id_periode=s2.id_periode
                                           WHERE s.id_jenis_pembayaran!='SPP' and s.id_jenis_pembayaran='$jenis_pembayaran'
                                           AND s2.id_kelas IN ($kls_list) and s2.id_periode='$periode_text' and s2.reguler='$reguler'
                                           GROUP BY s2.id_kelas ");
                foreach ($query->result_array() as $data){
                    $alert .= "Kelas ".$data['kelas']." (".$data['reguler'].")\n";
                }
                $alert .= "Sudah Pernah diinput";
                $count = $query->num_rows();
                if($count>0){
                    echo json_encode(array('status'=>'failed', 'txt'=>$alert));
                }else{
                    if(count($kelas) > 0){                        
                        $next_num = $this->m_pembayaran->ambilnomorbaru($jenis_pembayaran);
                        $inser = $this->db->query("INSERT INTO m_pembayaran 
                                (kode_pembayaran, nama_pembayaran, uraian, rupiah, id_jenis_pembayaran,
                                 tgl_mulai, tgl_selesai, bulan, tahun, created_date, created_by)
                                VALUES
                                ('$next_num', '$pembayaran', '', '$rupiah', '$jenis_pembayaran',
                                 '$tgl_mulai', '$tgl_selesai', '$bulan', '$tahun', '$datetime', '$userr') ");
                        if($inser){
                            foreach ($kelas as $value) {
                                $inser2 = $this->db->query("INSERT INTO m_pembayaran_detail
                                        (kode_pembayaran, id_kelas, id_periode, reguler, created_date, created_by)
                                        VALUES
                                        ('$next_num', '$value', '$periode_text', '$reguler', '$datetime', '$userr') ");                                
                                $cret = $this->m_pembayaran->buattagihan($next_num,$pembayaran,$value,$periode_text,$reguler);
                                if($cret) { 
                                    $sukses++; 
                                }else{
                                    $gagal++;
                                }                              
                            }
                        }else{
                            $gagal++;
                        }  
                    }
                    echo json_encode(array('status'=>'success', 'txt'=>'sukses:'.$sukses.' gagal:'.$gagal.'!'));
                }
            }else{
                echo json_encode(array('status'=>'failed'));
            }
        }
        
}

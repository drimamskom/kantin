<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Userhist extends CI_Controller {
    
        public $nama_tabel = 'm_menudepot';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_userhist');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('userhist');
            $this->load->view('footer');
	}
        
        public function data(){
            $draw = $this->input->post('draw');
            $kode = $this->input->post('cari');
            
            $query1 = $this->db->query("SELECT COUNT(*) as total 
                                        FROM tb_deposit_moves s 
                                        WHERE s.kode='$kode' ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT s.*
                            FROM tb_deposit_moves s 
                            WHERE s.kode='$kode'
                            ORDER BY s.tanggal DESC
                    ) AS t, 
                    (SELECT @rownum := 0) r");  
            $i=0;
            $totalz=0;
            $data = array();
            $info = array('P'=>'Pembelian Mart', 'K'=>'Pembelian Kantin', 'S'=>'Deposit', 'M'=>'MIGRASI DATA'); 
            foreach ($query->result_array() as $row){
                $totalz += intval($row["jumlah"]);
                $fist = $row["transaksi"];
                $row['tanggal'] = $this->m_userhist->tglmanusia2($row['tanggal']); 
                $row['nama_transaksi'] = $info[strtoupper($fist[0])];           
                $row['jumlah'] = "Rp, ".number_format(intval($row['jumlah']), 0, ".", ".");
                $totalz += intval($row["jumlah"]);
                $data[$i] = $row;
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data, "totalz" => $totalz);
            echo json_encode($datax);
        }
        
        public function getcustomer(){
            $kode = $this->input->post('kode');
            $data = array();
            $query = $this->db->query("select cus.nama, c.kode
                                        from tb_card c
                                        left join tb_customer cus on cus.kode=c.kode
                                        where c.card='$kode' ");
            $jum = $query->num_rows();
            if($jum==0){ 
                $data['status'] = 'failed';
                $data['txt'] = 'IDCARD Belum Terdaftar';
            }else{
                $row = $query->first_row();
                $nomor_induk = $row->kode;
                $data['kode'] = $row->kode;
                $data['nama'] = $row->nama;
                $data['status'] = 'success';

                $que = $this->db->query("SELECT SUM(jumlah) userhist, kode FROM tb_deposit_moves 
                                         WHERE kode='$nomor_induk' GROUP BY kode");
                $jml = $que->num_rows();
                if($jml>0){
                    $roe = $que->first_row();
                    $data['userhist'] = $roe->userhist;
                }else{
                    $data['userhist'] = 0;
                }
            }
            echo json_encode($data);
        }
        
}

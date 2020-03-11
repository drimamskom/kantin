<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Isikantin extends CI_Controller {
    
        public $nama_tabel = 'm_isikantin';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_isikantin');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('isikantin');
            $this->load->view('footer');
	}
        
        public function data(){
            $draw = $this->input->post('draw');
            $supplier = $this->input->post('supplier');
            $tgl_isi  = $this->input->post('tgl_isi');   
            $tanggal = $this->m_isikantin->datetosqldate($tgl_isi); 
            $kata = $this->input->post('cari');
            
            $hasil = array();
            $cekqq = $this->db->query("SELECT p.kode_supplier, p.tgl, pd.* 
                                       FROM trns_pembelian p 
                                       LEFT JOIN trns_pembelian_detail pd ON pd.kode_faktur=p.kode_faktur
                                       WHERE p.tgl='$tanggal' AND p.kode_supplier='$supplier'");
            foreach ($cekqq->result_array() as $hsl){
                $kd = $hsl['kode_barang'];
                $hasil[$kd] = $hsl['qty'];
            }
            
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( s.kode_barang LIKE '%$kata%' OR s.nama_barang LIKE '%$kata%' ) ";
            }
            $query1 = $this->db->query("SELECT COUNT(*) as total FROM tb_stok s WHERE s.supplier='$supplier' $where1 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT s.* 
                            FROM tb_stok s
                            WHERE s.supplier='$supplier' AND s.aktif='1' $where1 
                            ORDER BY s.nama_barang
                    ) AS t, 
                    (SELECT @rownum := 0) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $kd = $row['kode_barang'];
                $row['harga'] = "Rp, ".number_format($row['harga_beli'], 0, ".", ".");
                if (array_key_exists($kd, $hasil)) {
                    $row['input'] = '<input type="number" name="kode['.$row['kode_barang'].']" min="0" class="form-control input-lg number-only inp-gede" value="'.$hasil[$kd].'" disabled="disabled">';
                }else{
                    $row['input'] = '<input type="number" name="kode['.$row['kode_barang'].']" min="0" class="form-control input-lg number-only inp-gede"/>
                                      <input type="hidden" name="harga['.$row['kode_barang'].']" value="'.$row['harga_beli'].'" />
                                      <input type="hidden" name="satuan['.$row['kode_barang'].']" value="'.$row['satuan'].'" /> ';
                }
                $data[$i] = $row;
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
            echo json_encode($datax);
        }
        
        public function save(){
            $kodearr = $this->input->post('kode');
            $hargaarr = $this->input->post('harga');
            $satuanarr = $this->input->post('satuan');
            
            $supplier = $this->input->post('supplier');
            $tgl_isi  = $this->input->post('tgl_isi');   
            $tanggal = $this->m_isikantin->datetosqldate($tgl_isi); 
                        
            $datetime = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if(count($kodearr)>0){                    
                $sukses=0; $gagal=0;
                $subtotal = 0;
                $next_num = $this->m_isikantin->ambilnomorbaru();
                $inser = $this->db->query("INSERT INTO trns_pembelian 
                        (kode_faktur, kode_supplier, pembayaran, tgl, jatuh_tempo, hari_jatuh_tempo, total, diskon, pajak, harga_ppn, subtotal, terbayar, created_date, created_by)
                        VALUES
                        ('$next_num', '$supplier', 'KANTIN', '$tanggal', '$tanggal', '0', '0', '0', 'Y', '0', '0', '0', '$datetime', '$userr') ");
                if($inser){
                    $arr_stok = array();
                    foreach ($kodearr as $kode => $value){  
                        if($value!=''){
                            $total = $hargaarr[$kode]*$value;
                            $subtotal += $total;
                            $subinser = $this->db->query("INSERT INTO trns_pembelian_detail 
                                    (kode_faktur, kode_barang, expired_date, qty, satuan, harga_satuan, total, diskon, diskon1, pajak, harga_ppn, subtotal, harga_hpp, created_date, created_by)
                                    VALUES
                                    ('$next_num', '$kode', '$tanggal', '$value', '$satuanarr[$kode]', '$hargaarr[$kode]', '$total', '0', '0', 'Y', '0', '$total', '$hargaarr[$kode]', '$datetime', '$userr') ");

                            $arr_stok[] = " ('$next_num', '$tanggal', '$kode', '$value', '$datetime', '$userr' ) ";

                            if($subinser){  $sukses++; }else{ $gagal++; }
                        }
                    }
                    //INSERT STOK BARANG
                    if(count($arr_stok)>0){
                        $in_stok = implode(",", $arr_stok);
                        $inser2 = $this->db->query("INSERT INTO tb_stok_moves 
                                                      (transaksi, tanggal, kode_barang, qty, created_date, created_by )
                                                    VALUES ".$in_stok." ");                                    
                    }
                    //UPDATE SUBTOTAL FAKTUR
                    $this->db->query("UPDATE trns_pembelian SET
                                        total   = '$subtotal',  
                                        subtotal= '$subtotal' 
                                      WHERE kode_faktur = '$next_num'");
                    echo json_encode(array('status'=>'success', 'txt'=>'sukses:'.$sukses.' gagal:'.$gagal.'!', 'nota'=>$next_num));
                }else{
                    echo json_encode(array('status'=>'failed', 'txt'=>"Gagal Insert data!"));
                }
            }
        }
        
}

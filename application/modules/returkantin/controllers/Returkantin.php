<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Returkantin extends CI_Controller {
    
        public $nama_tabel = 'm_returkantin';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_returkantin');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('returkantin');
            $this->load->view('footer');
	}
        
        public function data(){
            $draw = $this->input->post('draw');
            $supplier = $this->input->post('supplier');
            $tgl_isi  = $this->input->post('tgl_isi');   
            $tanggal = $this->m_returkantin->datetosqldate($tgl_isi); 
            $kata = $this->input->post('cari');
            
            $hasil = array();
            $cekqq = $this->db->query("SELECT p.kode_supplier, p.tgl, pd.* 
                                       FROM trns_retur p 
                                       LEFT JOIN trns_retur_detail pd ON pd.kode_retur=p.kode_retur
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
            $totl['jumstok'] = 0;
            $totl['jumlaku'] = 0;
            $totl['jumretur'] = 0;
            $totl['jumsisa'] = 0;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT s.*, dt.qty AS stok, (dt.qty*s.harga_beli) AS jumstok, dt2.jumlah AS laku, (dt2.jumlah*s.harga_beli) AS jumlaku, dt3.qty AS retur, (dt3.qty*s.harga_beli) AS jumretur
                            FROM tb_stok s
                            LEFT JOIN (
                                    SELECT p.kode_supplier, p.tgl, pd.kode_barang, SUM(pd.qty) AS qty
                                    FROM trns_pembelian p 
                                    LEFT JOIN trns_pembelian_detail pd ON pd.kode_faktur=p.kode_faktur
                                    WHERE p.tgl='$tanggal' AND p.kode_supplier='$supplier'
                                    GROUP BY pd.kode_barang
                            ) AS dt ON dt.kode_barang=s.kode_barang
                            LEFT JOIN (
                                    SELECT p.kode, p.tgl, pd.kode_menu, SUM(pd.jumlah) AS jumlah 
                                    FROM trns_pemesanan p 
                                    LEFT JOIN trns_pemesanan_detail pd ON pd.kode_trns_pemesanan=p.kode_trns_pemesanan
                                    WHERE p.tgl='$tanggal'
                                    GROUP BY pd.kode_menu
                            ) AS dt2 ON dt2.kode_menu=s.kode_barang
                            LEFT JOIN (
                                    SELECT p.kode_supplier, p.tgl, pd.kode_barang, SUM(pd.qty) AS qty
                                    FROM trns_retur p 
                                    LEFT JOIN trns_retur_detail pd ON pd.kode_retur=p.kode_retur
                                    WHERE p.tgl='$tanggal' AND p.kode_supplier='$supplier'
                                    GROUP BY pd.kode_barang
                            ) AS dt3 ON dt3.kode_barang=s.kode_barang
                            WHERE s.supplier='$supplier' $where1
                            ORDER BY s.nama_barang
                    ) AS t, 
                    (SELECT @rownum := 0) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){                
                $kd = $row['kode_barang'];
                
                $sisa = ( intval($row['stok'])-intval($row['laku']) )-intval($row['retur']);
                $jumsisa = ( intval($row['jumstok'])-intval($row['jumlaku']) )-intval($row['jumretur']);
                $totl['jumstok'] += intval($row['jumstok']);
                $totl['jumlaku'] += intval($row['jumlaku']);
                $totl['jumretur'] += intval($row['jumretur']);
                $totl['jumsisa'] += $jumsisa;
                
                $row['harga'] = "Rp, ".number_format($row['harga_beli'], 0, ".", ".");
                if (array_key_exists($kd, $hasil)) {
                    $row['input'] = '<input type="number" name="kode['.$row['kode_barang'].']" min="0" max="'.$sisa.'" class="form-control number-only input-lg inp-gede" value="'.$hasil[$kd].'" disabled="disabled">';
                }else{
                    $row['input'] = '<input type="number" name="kode['.$row['kode_barang'].']" min="0" max="'.$sisa.'" class="form-control number-only input-lg inp-gede"/>
                                      <input type="hidden" name="harga['.$row['kode_barang'].']" value="'.$row['harga_beli'].'" />
                                      <input type="hidden" name="satuan['.$row['kode_barang'].']" value="'.$row['satuan'].'" /> ';
                }
                $row['harga'] = "Rp, ".number_format($row['harga_beli'], 0, ".", ".");
                $row['stok'] = intval($row['stok']);
                $row['jumstok'] = "Rp, ".number_format($row['jumstok'], 0, ".", ".");
                $row['laku'] = intval($row['laku']);
                $row['jumlaku'] = "Rp, ".number_format($row['jumlaku'], 0, ".", ".");
                $row['retur'] = intval($row['retur']);
                $row['jumretur'] = "Rp, ".number_format($row['jumretur'], 0, ".", ".");
                $row['sisa'] = '<span id="kode['.$row['kode_barang'].']" >'.$sisa.'</span>';
                $row['jumsisa'] = "Rp, ".number_format($jumsisa, 0, ".", ".");
                $data[$i] = $row;
                $i++;
            }

            $datax = array("draw" => $draw , "data" => $data);
            echo json_encode($datax);
        }
        
        public function save(){
            $kodearr = $this->input->post('kode');
            $hargaarr = $this->input->post('harga');
            $satuanarr = $this->input->post('satuan');
            
            $supplier = $this->input->post('supplier');
            $tgl_isi  = $this->input->post('tgl_isi');   
            $tanggal = $this->m_returkantin->datetosqldate($tgl_isi); 
                        
            $datetime = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if(count($kodearr)>0){                    
                $sukses=0; $gagal=0;
                $subtotal = 0;
                $next_num = $this->m_returkantin->ambilnomorbaru();
                $inser = $this->db->query("INSERT INTO trns_retur 
                        (kode_retur, kode_supplier, pembayaran, tgl, jatuh_tempo, hari_jatuh_tempo, total, diskon, pajak, harga_ppn, subtotal, terbayar, created_date, created_by)
                        VALUES
                        ('$next_num', '$supplier', 'KANTIN', '$tanggal', '$tanggal', '0', '0', '0', 'Y', '0', '0', '0', '$datetime', '$userr') ");
                if($inser){
                    $arr_stok = array();
                    foreach ($kodearr as $kode => $value){  
                        $minval= 0-intval($value);
                        if($value!=''){
                            $total = $hargaarr[$kode]*$value;
                            $subtotal += $total;
                            $subinser = $this->db->query("INSERT INTO trns_retur_detail 
                                    (kode_retur, kode_barang, expired_date, qty, satuan, harga_satuan, total, diskon, diskon1, pajak, harga_ppn, subtotal, harga_hpp, created_date, created_by)
                                    VALUES
                                    ('$next_num', '$kode', '$tanggal', '$value', '$satuanarr[$kode]', '$hargaarr[$kode]', '$total', '0', '0', 'Y', '0', '$total', '$hargaarr[$kode]', '$datetime', '$userr') ");

                            $arr_stok[] = " ('$next_num', '$tanggal', '$kode', '$minval', '$datetime', '$userr' ) ";

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
                    $this->db->query("UPDATE trns_retur SET
                                        total   = '$subtotal',  
                                        subtotal= '$subtotal' 
                                      WHERE kode_retur = '$next_num'");
                    echo json_encode(array('status'=>'success', 'txt'=>'sukses:'.$sukses.' gagal:'.$gagal.'!', 'nota'=>$next_num));
                }else{
                    echo json_encode(array('status'=>'failed', 'txt'=>"Gagal Insert data!"));
                }
            }
        }
        
}

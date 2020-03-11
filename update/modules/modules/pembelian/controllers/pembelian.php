<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pembelian extends CI_Controller {
    
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_pembelian');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('pembelian');
            $this->load->view('footer');
	}
        
        public function getbarang(){
            $q = $this->input->post('q');
            $array = array();
            $query = $this->db->query("SELECT o.* FROM tb_stok o
                                       WHERE o.tempat='2'
                                       AND ( o.kode_barang LIKE '%$q%' OR o.nama_barang LIKE '%$q%' ) ");
            $count = $query->num_rows();
            foreach ($query->result_array() as $data){
                $datax['id'] = $data['kode_barang'];
                $datax['text'] = $data['nama_barang']." - ".$data['kode_barang'];
                array_push($array,$datax);
            }
            $hsl = array("total_count" => $count , "incomplete_results" => false, "items" => $array);
            echo json_encode($hsl);
        }
        
        public function info(){
            $kode = $this->input->post('kode'); 
            if(!empty($kode)){
                $query = $this->db->query("SELECT o.* FROM tb_stok o
                                           WHERE o.kode_barang='$kode' ");
                $row = $query->first_row();
                $data['harga_beli'] = $row->harga_beli;
                $data['satuan'] = $row->satuan;
                echo json_encode(array('data'=>$data));
            }else{
                echo json_encode(array('data'=>''));
            }
        }
        
        public function save(){
            $supplier = $this->input->post('supplier');
            $faktur = $this->input->post('faktur');
            $pembayaran = $this->input->post('pembayaran');
            $tanggal = $this->m_pembelian->datetosqldate($this->input->post('tanggal'));
            $tgl_jatuh_tempo = $this->m_pembelian->datetosqldate($this->input->post('tgl_jatuh_tempo'));
            $tempo = $this->input->post('tempo');
            
            $cbobarang = $this->input->post('cbobarang');
            $harga = $this->input->post('harga');
            $expiredx = $this->input->post('expired');
            $qty = $this->input->post('qty');
            $satuan = $this->input->post('satuan');
            $total = $this->input->post('total');
            $diskon = $this->input->post('diskon');
            $subtotal = $this->input->post('subtotal');
            
            $subtotal1 = $this->input->post('subtotal1');
            $diskon1 = floatval($this->input->post('diskon1'));
            //$subtotal2 = $this->input->post('subtotal2');
            //$diskon_cash = $this->input->post('diskon_cash');
            $ppn = floatval($this->input->post('ppn'));
            $bayar = $this->input->post('bayar');
            
            $datetime = date('Y-m-d H:i:s');
            $tanggalx = date('Y-m-d');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            $query = $this->db->query("SELECT kode_faktur FROM trns_pembelian WHERE kode_faktur='$faktur' ");
            $count = $query->num_rows();
            if($count>0){ 
                echo json_encode(array('status'=>'failed', 'txt'=>"No. Faktur Sudah ada!"));
            }else if(count($cbobarang)>0){                    
                $sukses=0; $gagal=0;
                $inser = $this->db->query("INSERT INTO trns_pembelian 
                            (kode_faktur, kode_supplier, pembayaran, tgl, jatuh_tempo, hari_jatuh_tempo, total, diskon, 
                             pajak, harga_ppn, subtotal, created_date, created_by )
                            VALUES
                            ('$faktur', '$supplier', '$pembayaran', '$tanggal', '$tgl_jatuh_tempo', '$tempo', '$subtotal1', '$diskon1',
                             'Y', '$ppn', '$bayar', '$datetime', '$userr' ) ");
                if($inser){
                    foreach ($cbobarang as $key => $barcode){
                        $querycek = $this->db->query("SELECT kode_barang,harga_beli FROM tb_stok WHERE kode_barang='$barcode' ");
                        $row1 = $querycek->first_row();
                        $kodebarang = $row1->kode_barang;
                        $harga_beli = $row1->harga_beli;
                        $jumlah_all = floatval($qty[$key]);
                        $harga_hpp  = floatval($subtotal[$key])/floatval($jumlah_all);
                        if(empty($expiredx[$key])){
                          $expired = "9999-12-30"; 
                        }else{
                          $expired = $this->m_pembelian->datetosqldate($expiredx[$key]);  
                        }
                        
                        $subinser = $this->db->query("INSERT INTO trns_pembelian_detail 
                                    (kode_faktur, kode_barang, expired_date, qty, satuan, harga_satuan, total, 
                                     diskon, diskon1, pajak, harga_ppn, subtotal, harga_hpp, created_date, created_by)
                                    VALUES
                                    ('$faktur', '$kodebarang', '$expired', '$qty[$key]', '$satuan[$key]', '$harga[$key]', '$total[$key]', 
                                     '".intval($diskon[$key])."', '$diskon1', 'Y', '$ppn', '$subtotal[$key]', '$harga_hpp', '$datetime', '$userr') ");
                        $subinser2 = $this->db->query("INSERT INTO tb_stok_moves 
                                    (transaksi, tanggal, kode_barang, qty, created_date, created_by)
                                    VALUES
                                    ('$faktur', '$tanggalx', '$kodebarang', '$qty[$key]', '$datetime', '$userr') ");  
						if($harga_beli!=$harga[$key]){
							$upd = $this->db->query("UPDATE tb_stok SET harga_beli='$harga[$key]' WHERE kode_barang='$kodebarang' ");  
						}
                        if($subinser){
                            $sukses++; 
                        }else{
                            $gagal++;
                        }
                    }
                    echo json_encode(array('status'=>'success', 'txt'=>'sukses:'.$sukses.' gagal:'.$gagal.'!!', 'nota'=>$faktur));
                }else{
                    echo json_encode(array('status'=>'failed', 'txt'=>"Gagal Insert data!"));
                }
            }
        }
        
        public function cetak($nonota=""){  
            if(!empty($nonota)){
                $result = $this->db->query("SELECT p.*, s.nama_supplier
                                            FROM trns_pembelian p
                                            LEFT JOIN tb_supplier s ON s.kode_supplier=p.kode_supplier
                                            WHERE p.kode_faktur='$nonota' ");
                $datax = $result->first_row();
                $nama_supplier = $datax->nama_supplier; 
                
                $line  = 23;
                $line2 = 24;
                $left0 = 10;
                $left1 = 300;
                $left2 = 350;
                $left3 = 460;
                $max = 560;
                $lines = 0;
                //$p = printer_open("CutePDF Writer"); 
                $p = printer_open("RP80 Printer"); 
                printer_set_option($p, PRINTER_MODE, "RAW"); // mode disobek (gak ngegulung kertas)
                printer_start_doc($p, "Tes Laporan"); 
                printer_start_page($p);
                $font = printer_create_font("Arial", 24, 11, PRINTER_FW_NORMAL, false, false, false, 0);
                $font_k = printer_create_font("Arial", 23, 10, PRINTER_FW_NORMAL, false, false, false, 0);
                $pen = printer_create_pen(PRINTER_PEN_SOLID, 1, "000000"); 
                //info bon
                printer_draw_bmp ($p, APPPATH.'../assets/img/smanema.bmp', $left0, $lines);
                $lines += $line2;
                printer_select_font($p, $font);
                printer_draw_text($p, "SMANEMART",$left0+80, $lines);
                printer_select_font($p, $font_k);
                printer_draw_text($p, date("d/m/Y H:i:s"),$max-190,$lines);
                $lines += $line2;
                printer_draw_text($p, "SMA Negeri 1 Manyar",$left0+80, $lines);
                $lines += $line;
                printer_draw_text($p, "Jl. Kayu Raya Pongangan Indah Manyar Gresik",$left0+80, $lines);
                $lines += $line+3;
                printer_select_pen($p, $pen);
                printer_draw_line($p, $left0, $lines, $max, $lines);
                $lines += $line-8;
                //judul bon
                printer_select_font($p, $font);
                printer_draw_text($p, "STRUK PEMBELIAN SMANEMART",101,$lines);
                $lines += $line2;
                printer_select_font($p, $font_k);
                printer_draw_text($p, "Nota : ".$nonota,180,$lines);
                $lines += $line*2;
                printer_draw_text($p, "Supplier : ".$nama_supplier,$left0,$lines);
                $lines += $line+3;
                // Header Bon
                printer_draw_line($p, $left0, $lines, $max, $lines);
                $lines += 3;
                printer_draw_text($p, "Produk Pembelian", $left0,  $lines);
                printer_draw_text($p, "Qty", $left1-5, $lines);
                printer_draw_text($p, "Harga", $left2+25, $lines);
                printer_draw_text($p, "Total", $left3+25, $lines);
                $lines += $line;
                printer_draw_line($p, $left0, $lines, $max, $lines);
                $lines += 5;                
                //LOAD DATA ROW
                $totalz = 0;                
                $query = $this->db->query("SELECT d.nama_barang, pd.* 
                                           FROM trns_pembelian_detail pd
                                           LEFT JOIN tb_stok d ON d.kode_barang=pd.kode_barang
                                           WHERE pd.kode_faktur='$nonota' ");
                foreach ($query->result_array() as $rowx){
                    printer_draw_text($p, ucwords(strtolower($rowx["nama_barang"])), $left0, $lines);
                    printer_draw_text($p, $rowx["qty"], $left1+5, $lines);
                    printer_draw_text($p, $this->right_numbering($rowx["harga_satuan"]), $left2, $lines);
                    printer_draw_text($p, $this->right_numbering($rowx["total"]), $left3, $lines);
                    $lines += $line;
                    $totalz += $rowx["total"];
                }
                
                //Footer bon
                $lines += 5;
                printer_draw_line($p, $left0, $lines, $max, $lines);
                $lines += 3;
                printer_draw_text($p, "Total", $left1-5, $lines);
                printer_draw_text($p, $this->right_numbering($totalz), $left3, $lines);
                $lines += $line;
                printer_draw_line($p, $left0, $lines, $max, $lines);
                $lines += 7;
                //cetak terimakasi atau potong disini
                $lines += $line*2;
                printer_draw_text($p, "Terima Kasih Atas Kunjungan Anda", 120, $lines);                
                //end cetak
                printer_delete_font($font);
                printer_end_page($p);
                printer_end_doc($p);
                printer_close($p);
                redirect(base_url('pembelian'));
            }
        } 
        
        function right_numbering($number){
            $new = number_format($number, 0, ".", ".");            
            $len = strlen($new);
            $jum = substr_count($new, ".");
            if(substr_count($new, "1")>0){
                if(substr($new, 0, 1)!='1'){
                    $jum += substr_count($new, "1");
                }
            }           
            $pad = (15-$len)+$jum;
            $result = str_pad($new, $pad, " ", STR_PAD_LEFT);
            return $result;
        }

}
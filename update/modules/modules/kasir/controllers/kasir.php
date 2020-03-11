<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kasir extends CI_Controller {
    
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_kasir');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('kasir');
            $this->load->view('footer');
	}
        
        public function getdata(){
            $barcode = $this->input->post('barcode');
            $kodex = str_replace(" ", "", $barcode);
            $kode1 = explode("-", $kodex);
            $kode2 = $kode1[0];
            $query = $this->db->query("SELECT s.*, m.jum
                                        FROM tb_stok s
                                        LEFT JOIN (
                                            SELECT SUM(qty) AS jum, kode_barang
                                            FROM tb_stok_moves
                                            GROUP BY kode_barang
                                        ) m ON m.kode_barang=s.kode_barang
                                       WHERE s.kode_barang='$kode2'
                                       LIMIT 1 ");
            $jum = $query->num_rows();
            if($jum==0){ 
                $data['status'] = 'failed';
                $data['txt'] = 'Kode Barang Belum Terdaftar';
            }else{
                $row = $query->first_row();
                $data['status'] = 'success';
                $data['kode_barang'] = $row->kode_barang;
                $data['nama_barang'] = $row->nama_barang;
                $data['harga']  = $row->harga_jual;
                $data['satuan'] = $row->satuan;    
                $data['stok']   = (intval($row->jum)>0) ? intval($row->jum) : 0;   
            }
            echo json_encode(array('data'=>$data));
        }
        
        public function caribarang(){
            $draw = $this->input->post('draw');
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $kata = $this->input->post('cari');
            $tempat = $this->input->post('tempat');
            
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( s.kode_barang LIKE '%$kata%' OR s.nama_barang LIKE '%$kata%' ) ";
            }
            if(empty($tempat)){
                $where2="";
            }else{
                $where2=" AND s.tempat='$tempat' ";
            }
            $query1 = $this->db->query("SELECT COUNT(*) as total
                                        FROM tb_stok s
                                        LEFT JOIN tb_stan t ON t.no_stan=s.no_stan
                                        LEFT JOIN (
                                            SELECT SUM(qty) AS jum, kode_barang
                                            FROM tb_stok_moves
                                            GROUP BY kode_barang
                                        ) m ON m.kode_barang=s.kode_barang
                                        WHERE m.jum IS NOT NULL AND m.jum>0
                                        AND s.tempat='2'
                                        $where1 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT s.*, t.nama_stan, m.jum
                            FROM tb_stok s
                            LEFT JOIN tb_stan t ON t.no_stan=s.no_stan
                            LEFT JOIN (
                                SELECT SUM(qty) AS jum, kode_barang
                                FROM tb_stok_moves
                                GROUP BY kode_barang
                            ) m ON m.kode_barang=s.kode_barang
                            WHERE m.jum IS NOT NULL AND m.jum>0
                            AND s.tempat='2' $where1
                            ORDER BY s.nama_barang
                            LIMIT $start, $length 
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $row['stok']   = (intval($row['jum'])>0) ? intval($row['jum']) : 0;    
                $row['harga_jual'] = "Rp, ".number_format($row['harga_jual'], 0, ".", ".");
                $data[$i] = $row;
                $data[$i]['button'] = '<button title="Pilih" idnex="'.$data[$i]['kode_barang'].'" class="btn btn-info btn-xs btnpilih" ><i class="fa fa-check"></i></button>';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data);
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
                $data['status'] = 'success';
                $data['kode'] = $row->kode;
                $data['nama'] = $row->nama;

                $que = $this->db->query("SELECT SUM(jumlah) deposit, kode FROM tb_deposit_moves 
                                         WHERE kode='$nomor_induk' GROUP BY kode");
                $jjj = $que->num_rows();
                if($jjj>0){
                    $roe = $que->first_row();
                    $data['deposit'] = $roe->deposit;
                }else{
                    $data['deposit'] = 0;
                }
            }
            echo json_encode($data);
        }
        
        public function save(){
            $kodebarang = $this->input->post('kodebarang');
            $harga = $this->input->post('harga');
            $jumlah = $this->input->post('jumlah');
            $satuan = $this->input->post('satuan');
            $total = $this->input->post('total');
            
            $subtotal = $this->input->post('subtotal');
            $biaya = $this->input->post('biaya');
            $bayar = $this->input->post('bayar');
            $kembali = $this->input->post('kembali');
            $customer = $this->input->post('customer');
            
            $datetime = date('Y-m-d H:i:s');
            $tanggal  = date('Y-m-d');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            if(count($kodebarang)>0){                    
                $sukses=0; $gagal=0;
                $biaya_all = 0-floatval($subtotal);   
                $next_num = $this->m_kasir->ambilnomorbaru();
                $inser = $this->db->query("INSERT INTO trns_penjualan 
                        (kode_trns_penjualan, tgl, kasir, kode_customer, subtotal, biaya, bayar, kembali, created_date, created_by)
                        VALUES
                        ('$next_num', '$tanggal', '$userr', '$customer', '$subtotal', '$biaya', '$bayar', '$kembali', '$datetime', '$userr') ");
                        $subinser3 = $this->db->query("INSERT INTO tb_deposit_moves 
                                    (transaksi, tanggal, kode, jumlah, created_date, created_by)
                                    VALUES
                                    ('$next_num', '$tanggal', '$customer', '$biaya_all', '$datetime', '$userr') "); 
                if($inser){
                    foreach ($kodebarang as $key => $kode){
                        $jumlah_all = 0-floatval($jumlah[$key]);     
                        $hargax = str_replace(".", "", $harga[$key]);
                        $totalx = str_replace(".", "", $total[$key]);                        
                        $subinser = $this->db->query("INSERT INTO trns_penjualan_detail 
                                (kode_trns_penjualan, kode_barang, jumlah, satuan, harga, total, created_date, created_by)
                                VALUES
                                ('$next_num', '$kode', '$jumlah[$key]', '$satuan[$key]', '$hargax', '$totalx', '$datetime', '$userr') ");
                        $subinser2 = $this->db->query("INSERT INTO tb_stok_moves 
                                    (transaksi, tanggal, kode_barang, qty, created_date, created_by)
                                    VALUES
                                    ('$next_num', '$tanggal', '$kode', '$jumlah_all', '$datetime', '$userr') "); 
                        if($subinser){
                            $sukses++; 
                        }else{
                            $gagal++;
                        }
                    }
                    echo json_encode(array('status'=>'success', 'txt'=>'sukses:'.$sukses.' gagal:'.$gagal.'!', 'nota'=>$next_num));
                }else{
                    echo json_encode(array('status'=>'failed', 'txt'=>"Gagal Insert data!"));
                }
            }
        } 
        
        public function cetak($nonota=""){  
            if(!empty($nonota)){
                $result = $this->db->query("SELECT t.*, c.nama 
                                            FROM trns_penjualan t
                                            LEFT JOIN tb_customer c ON c.kode=t.kode_customer
                                            WHERE t.kode_trns_penjualan='$nonota' ");
                $datax = $result->first_row();
                $kasir = strtolower($datax->kasir); 
                $cust  = $datax->kode_customer; 
                $nama  = strtolower($datax->nama); 
                
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
                printer_draw_text($p, "STRUK PENJUALAN SMANEMART",101,$lines);
                $lines += $line2;
                printer_select_font($p, $font_k);
                printer_draw_text($p, "Nota : ".$nonota,180,$lines);
                $lines += $line*2;
                printer_draw_text($p, "Kasir : ".ucwords($kasir),$left0,$lines);
                $lines += $line;
                printer_draw_text($p, "Pembeli : ".ucwords($nama),$left0,$lines);
                $lines += $line+3;
                // Header Bon
                printer_draw_line($p, $left0, $lines, $max, $lines);
                $lines += 3;
                printer_draw_text($p, "Produk Penjualan", $left0,  $lines);
                printer_draw_text($p, "Qty", $left1-5, $lines);
                printer_draw_text($p, "Harga", $left2+30, $lines);
                printer_draw_text($p, "Total", $left3+30, $lines);
                $lines += $line;
                printer_draw_line($p, $left0, $lines, $max, $lines);
                $lines += 5;                
                //LOAD DATA ROW
                $totalz = 0;                
                $query = $this->db->query("SELECT d.nama_barang, pd.* 
                                           FROM trns_penjualan_detail pd
                                           LEFT JOIN tb_stok d ON d.kode_barang=pd.kode_barang
                                           WHERE pd.kode_trns_penjualan='$nonota' ");
                foreach ($query->result_array() as $rowx){
                    printer_draw_text($p, ucwords(strtolower($rowx["nama_barang"])), $left0, $lines);
                    printer_draw_text($p, $rowx["jumlah"], $left1+5, $lines);
                    printer_draw_text($p, $this->right_numbering($rowx["harga"]), $left2, $lines);
                    printer_draw_text($p, $this->right_numbering($rowx["harga"]*$rowx["jumlah"]), $left3, $lines);
                    $lines += $line;
                    $totalz += $rowx["harga"]*$rowx["jumlah"];
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
                
                $que = $this->db->query("SELECT SUM(jumlah) deposit, kode FROM tb_deposit_moves 
                                         WHERE kode='$cust' GROUP BY kode");
                $jml = $que->num_rows();
                if($jml>0){
                    $roe = $que->first_row();
                    $deposit = $roe->deposit;
                }else{
                    $deposit = 0;
                }
                
                //Footer sisa deposit
                printer_draw_text($p, "Saldo Akhir", $left1-5, $lines);
                printer_draw_text($p, $this->right_numbering($deposit), $left3, $lines);
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
                redirect(base_url('kasir'));
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
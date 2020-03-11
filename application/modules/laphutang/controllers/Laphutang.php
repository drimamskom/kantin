<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Laphutang extends CI_Controller {
    
        public $nama_tabel = 'm_laphutang';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_laphutang');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('laphutang');
            $this->load->view('footer');
	}
        
        public function data(){
            $umpan  = $this->input->post('umpan');
            $draw = $this->input->post('draw');
            $supplier = $this->input->post('supplier');
            $tgl_isi  = $this->input->post('tgl_isi');   
            $tanggal = $this->m_laphutang->datetosqldate($tgl_isi); 
            $kata = $this->input->post('cari');
            
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
                $sisa = ( intval($row['stok'])-intval($row['laku']) )-intval($row['retur']);
                $jumsisa = ( intval($row['jumstok'])-intval($row['jumlaku']) )-intval($row['jumretur']);
                $totl['jumstok'] += intval($row['jumstok']);
                $totl['jumlaku'] += intval($row['jumlaku']);
                $totl['jumretur'] += intval($row['jumretur']);
                $totl['jumsisa'] += $jumsisa;
                                
                $row['harga_beli2'] = $row['harga_beli'];
                $row['jumstok2']    = $row['jumstok'];
                $row['jumlaku2']    = $row['jumlaku'];
                $row['jumretur2']   = $row['jumretur'];
                $row['jumsisa2']    = $jumsisa;
                
                $row['harga_beli']  = "Rp, ".number_format($row['harga_beli'], 0, ".", ".");
                $row['stok'] = intval($row['stok']);
                $row['jumstok'] = "Rp, ".number_format($row['jumstok'], 0, ".", ".");
                $row['laku'] = intval($row['laku']);
                $row['jumlaku'] = "Rp, ".number_format($row['jumlaku'], 0, ".", ".");
                $row['retur'] = intval($row['retur']);
                $row['jumretur'] = "Rp, ".number_format($row['jumretur'], 0, ".", ".");
                $row['sisa'] = $sisa;
                $row['jumsisa'] = "Rp, ".number_format($jumsisa, 0, ".", ".");
                $data[$i] = $row;
                $i++;
            }

            $datax = array("draw" => $draw, "data" => $data, "totalz" => $totl);
            if(empty($umpan)){
                echo json_encode($datax);
            }else{
                return $datax;
            }
        }
        
        public function excel(){  
            $supplier = $this->input->post('supplier');
            $tgl_isi  = $this->input->post('tgl_isi');   
            $tgl     = $this->m_laphutang->datetosqldate($tgl_isi); 
            $tglm    = $this->m_laphutang->tglmanusia($tgl);
            $tanggal = $this->m_laphutang->sqldatetodate($tgl);
            
            $query1 = $this->db->query("SELECT nama_supplier FROM tb_supplier WHERE kode_supplier='$supplier' ");
            $row1 = $query1->first_row();
            $nama_supplier = $row1->nama_supplier;
            
            $data = $this->data();
            $judul = "Laporan Pembayaran Hutang ".$nama_supplier." Per tanggal ".$tglm;
            //membuat objek
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, $judul);
                        
            // Nama Field Baris Pertama                                    
            $fields = array("urutan" => "No" ,
                            "nama_barang" => "Nama Menu" ,
                            "satuan" => "Satuan" ,
                            "harga_beli" => "Harga Beli" ,
                            "stok" => "Stok" ,
                            "jumstok" => "Jml Stok" ,
                            "laku" => "Laku" ,
                            "jumlaku" => "Jml Laku" ,
                            "retur" => "Retur" ,
                            "jumretur" => "Jml Retur" ,
                            "sisa" => "Sisa" ,
                            "jumsisa" => "Jml Sisa" );                            
            $col = 0;
            foreach ($fields as $key => $value) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 2, $value);
                $col++;
            }
	 
            // Mengambil Data
            $row = 3;
            foreach($data["data"] as $datax){
                $col = 0;
                foreach ($fields as $key => $value) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $datax[$key]);
                    $col++;
                }
                $row++;
            }
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, "Total");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, "Rp. ".number_format($data["totalz"]["jumstok"], 0, ".", "."));
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, "Total");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $row, "Rp. ".number_format($data["totalz"]["jumlaku"], 0, ".", "."));
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $row, "Total");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $row, "Rp. ".number_format($data["totalz"]["jumretur"], 0, ".", "."));
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $row, "Total");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $row, "Rp. ".number_format($data["totalz"]["jumsisa"], 0, ".", "."));
            $objPHPExcel->setActiveSheetIndex(0);

            //Set Title
            $objPHPExcel->getActiveSheet()->setTitle('Lap Supplier');
 
            //Save ke .xlsx, kalau ingin .xls, ubah 'Excel2007' menjadi 'Excel5'
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
 
            //Header
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            //Nama File
            header('Content-Disposition: attachment;filename="Lap_Supplier('.$tanggal.').xlsx"');

            //Download
            $objWriter->save("php://output"); 
        }    
        
        public function cetak(){  
            $supplier = $this->input->post('supplier');
            $tgl_isi  = $this->input->post('tgl_isi');   
            $tgl     = $this->m_laphutang->datetosqldate($tgl_isi); 
            $tglm    = $this->m_laphutang->tglmanusia($tgl);
            $tanggal = $this->m_laphutang->sqldatetodate($tgl);
            
            $query1 = $this->db->query("SELECT nama_supplier FROM tb_supplier WHERE kode_supplier='$supplier' ");
            $row1 = $query1->first_row();
            $nama_supplier = $row1->nama_supplier;
            
            $data = $this->data();
            $judul = "Laporan Pembayaran Hutang ".$nama_supplier." Per tanggal ".$tglm;
            
            $line  = 22;
            $line2 = 24;
            $left0 = 10;
            $left1 = 300;
            $left2 = 345;
            $left3 = 455;
            $max = 560;
            $lines = 0;
            //$p = printer_open("CutePDF Writer"); 
            $p = printer_open("RP80 Printer"); 
            printer_set_option($p, PRINTER_MODE, "RAW"); // mode disobek (gak ngegulung kertas)
            printer_start_doc($p, "Tes Laporan"); 
            printer_start_page($p);
            $font = printer_create_font("Arial", 24, 11, PRINTER_FW_NORMAL, false, false, false, 0);
            $font_k = printer_create_font("Arial", 22, 11, PRINTER_FW_NORMAL, false, false, false, 0);
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
            printer_draw_text($p, "STRUK PEMBAYARAN HUTANG",101,$lines);
            $lines += $line2;
            printer_select_font($p, $font_k);
            printer_draw_text($p, "Supplier : ".$nama_supplier,170,$lines);
            $lines += $line;
            printer_draw_text($p, "Tgl : ".$tglm,160,$lines);
            $lines += $line+3;
            // Header Bon
            printer_draw_line($p, $left0, $lines, $max, $lines);
            $lines += 3;
            printer_draw_text($p, "Produk Penjualan", $left0,  $lines);
            printer_draw_text($p, "Qty", $left1-5, $lines);
            printer_draw_text($p, "Harga Beli", $left2, $lines);
            printer_draw_text($p, "Jumlah", $left3+20, $lines);
            $lines += $line;
            printer_draw_line($p, $left0, $lines, $max, $lines);
            $lines += 5;
            //loop per row bon
            foreach ($data['data'] as $rowx) {
                printer_draw_text($p, substr(ucwords(strtolower($rowx["nama_barang"])), 0, 23), $left0, $lines);
                printer_draw_text($p, $rowx["laku"], $left1, $lines);
                printer_draw_text($p, $this->right_numbering($rowx["harga_beli2"]), $left2, $lines);
                printer_draw_text($p, $this->right_numbering($rowx["jumlaku2"]), $left3, $lines);
                $lines += $line;
            }
            //Footer bon
            $lines += 5;
            printer_draw_line($p, $left0, $lines, $max, $lines);
            $lines += 3;
            printer_draw_text($p, "Total", $left1-5, $lines);
            printer_draw_text($p, $this->right_numbering($data["totalz"]["jumlaku"]), $left3, $lines);
            $lines += $line;
            printer_draw_line($p, $left0, $lines, $max, $lines);
            $lines += 7;
            //cetak terimakasi atau potong disini                
            $lines += $line*2;
            printer_draw_text($p, "Terima Kasih Atas Kunjungan Anda", 150, $lines);            
            //end cetak
            printer_delete_font($font);
            printer_end_page($p);
            printer_end_doc($p);
            printer_close($p);
            redirect(base_url('laphutang'));
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

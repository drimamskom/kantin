<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Trnskasir extends CI_Controller {
    
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_trnskasir');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('trnskasir');
            $this->load->view('footer');
	}
        
        public function data(){            
            $umpan = $this->input->post('umpan');
            $draw = $this->input->post('draw');
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $kata = $this->input->post('cari');
            $tgl_mulai = $this->input->post('tgl_mulai');
            $tgl_selesai = $this->input->post('tgl_selesai');
            $kasir = $this->input->post('kasir');
            
            if(empty($length)){ $limit_txt=""; }else{ $limit_txt=" LIMIT $start, $length "; }
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( p.kode_trns_penjualan LIKE '%$kata%' OR p.subtotal LIKE '%$kata%' OR p.tgl LIKE '%$kata%' ) ";
            }
            if(empty($tgl_mulai)||empty($tgl_selesai)){
                $where2="";
            }else{
                $d1 = $this->m_trnskasir->datetosqldate($tgl_mulai);
                $d2 = $this->m_trnskasir->datetosqldate($tgl_selesai);
                $where2=" AND (p.tgl BETWEEN '$d1' AND '$d2' ) ";
            }
            if(empty($kasir)){
                $where3="";
            }else{
                $where3=" AND p.kasir = '$kasir' ";
            }

            $query1 = $this->db->query("SELECT COUNT(*) AS total, SUM(subtotal) AS jumm FROM trns_penjualan p WHERE p.kode_trns_penjualan IS NOT NULL $where1 $where2 $where3 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
            $totalz = $row1->jumm;
            
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT p.*, c.nama nama_customer
                            FROM trns_penjualan p
                            LEFT JOIN tb_customer c ON c.kode=p.kode_customer
                            WHERE p.kode_trns_penjualan IS NOT NULL $where1 $where2 $where3
                            ORDER BY p.kode_trns_penjualan DESC $limit_txt
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;            
            $totalx['subtotal'] = 0;
            $totalx['biaya']    = 0;
            $totalx['bayar']    = 0;
            $totalx['kembali']  = 0;
            $data = array();
            foreach ($query->result_array() as $row){
                $row['tot'] = $row['subtotal'];
                $totalx['subtotal'] += $row['subtotal'];
                $totalx['biaya']    += $row['biaya'];
                $totalx['bayar']    += $row['bayar'];
                $totalx['kembali']  += $row['kembali'];
                $row['subtotal'] = "Rp, ".number_format($row['subtotal'], 0, ".", ".");
                $row['biaya'] = "Rp, ".number_format($row['biaya'], 0, ".", ".");
                $row['bayar'] = "Rp, ".number_format($row['bayar'], 0, ".", ".");
                $row['kembali'] = "Rp, ".number_format($row['kembali'], 0, ".", ".");
                $data[$i] = $row;                
                $data[$i]['tgl'] = $this->m_trnskasir->sqldatetodate($row['tgl']);
                $data[$i]['button'] = '<button title="View Detail" idnex="'.$data[$i]['kode_trns_penjualan'].'" class="btn btn-info btn-xs btnview" ><i class="fa fa-get-pocket"></i> Detail</button>
                                       <button title="Delete" idnex="'.$data[$i]['kode_trns_penjualan'].'" namenex="'.$data[$i]['kode_trns_penjualan'].'" class="btn btn-danger btn-xs btnhapus" ><i class="fa fa-remove"></i> Del</button>';
                $i++;
            }
            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data, "totalz" => $totalx, "tot" => $totalz);
            if(empty($umpan)){
                echo json_encode($datax);
            }else{
                return $datax;
            }
        }
        
        public function cari(){
            $id = $this->input->post('id'); // kode_trans_penjualan
            $array = $this->m_trnskasir->getalldata($id);
            echo json_encode($array);
        }
        
        public function hapus(){
            $datetime = date('Y-m-d H:i:s');
            $tanggal  = date('Y-m-d');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            $id = $this->input->post('id');
            // cari data masih ada         
            $query1 = $this->db->query("SELECT * FROM trns_penjualan WHERE kode_trns_penjualan='$id' ");
            $jml = $query1->num_rows(); 
            if($jml!='0') { 
                $row1 = $query1->first_row();
                $usr = $row1->kode_customer;
                $inser = $this->db->query("INSERT INTO trns_hapus 
                                              (kode_trns, pelanggan, kode_barang, jumlah, harga, total, created_date, created_by)
                                           SELECT kode_trns_penjualan, '$usr' AS usr, kode_barang, jumlah, harga, total, '$datetime' AS dt, '$userr' AS us
                                           FROM trns_penjualan_detail WHERE kode_trns_penjualan='$id' ");
                
                $delete1 = $this->db->query("DELETE FROM trns_penjualan WHERE kode_trns_penjualan='$id' ");
                $delete2 = $this->db->query("DELETE FROM trns_penjualan_detail WHERE kode_trns_penjualan='$id' ");
                $delete3 = $this->db->query("DELETE FROM tb_deposit_moves WHERE transaksi='$id' "); 
                $delete4 = $this->db->query("DELETE FROM tb_stok_moves WHERE transaksi='$id' "); 
                if($delete1){
                    echo json_encode(array('status'=>'success'));
                }else{
                    echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa delete data!'));
                }
            }else{
                echo json_encode(array('status'=>'failed', 'txt'=>'Stok Masih ada, tdk bisa delete data!'));
            }
        }
        
        public function export(){
            $report = $this->input->post('report');	
            $tgl_mulai = $this->input->post('tgl_mulai');
            $tgl_selesai = $this->input->post('tgl_selesai');
            $data = $this->data();
            //membuat objek
            $objPHPExcel = new PHPExcel();
            if($report=='report'){
                // Nama Field Baris Pertama
                $fields = array("urutan" => "No" ,
                                "kode_trns_penjualan" => "No. Nota" ,
                                "tgl" => "Tgl" ,
                                "kasir" => "Kasir" ,
                                "nama_customer" => "Pelanggan" ,
                                "subtotal" => "Subtotal" ,
                                "bayar" => "Bayar" );
                $col = 0;
                foreach ($fields as $key => $value) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $value);
                    $col++;
                }

                // Mengambil Data
                $row = 2;
                foreach($data["data"] as $datax){
                    $col = 0;
                    foreach ($fields as $key => $value) {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $datax[$key]);
                        $col++;
                    }                
                    $row++;
                }

                $totalz=$data["totalz"];
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, "Totals");
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, "Rp, ".number_format($totalz['subtotal'], 0, ".", "."));
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, "Rp, ".number_format($totalz['bayar'], 0, ".", "."));
                $objPHPExcel->setActiveSheetIndex(0);

                //Set Title
                $objPHPExcel->getActiveSheet()->setTitle('Data Trans Kasir');

                //Save ke .xlsx, kalau ingin .xls, ubah 'Excel2007' menjadi 'Excel5'
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

                //Header
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                header("Cache-Control: no-store, no-cache, must-revalidate");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

                //Nama File
                header('Content-Disposition: attachment;filename="Trans_Kasir.xlsx"');

                //Download
                $objWriter->save("php://output"); 
            }else{
                //Header Excel
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, "PENJUALAN SMANEMART");
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 2, "Tgl ".$tgl_mulai." s/d ".$tgl_selesai);
                // Nama Field Baris Pertama
                $fields = array("urutan" => "No" ,
                                "tgl" => "Tanggal" ,
                                "jumlah" => "Jumlah" ,
                                "total" => "Total");
                $col = 0;
                foreach ($fields as $key => $value) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 4, $value);
                    $col++;
                }
                
                // Mengambil Data
                $listtgl = array();
                $newdata = array();
                foreach($data["data"] as $datax){
                    $tgl = $datax['tgl'];
                    $jml = $datax['tot'];
                    $newdata[$tgl][] = $jml;   
                    array_push($listtgl, $tgl);
                }
                // MenCetak Data
                $listtgl = array_unique($listtgl);
                $row = 5;
                $urut = 1;
                $tot = 0;
                foreach ($listtgl as $key => $tgl) {
                    $jumlah = array_sum($newdata[$tgl]);
                    $tot += $jumlah;
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $urut);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $tgl);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, "Rp, ".number_format($jumlah, 0, ".", "."));
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, "Rp, ".number_format($tot, 0, ".", "."));
                    $urut++;        
                    $row++;
                }
                
                //Set Title
                $objPHPExcel->getActiveSheet()->setTitle('Data Trans Kasir');

                //Save ke .xlsx, kalau ingin .xls, ubah 'Excel2007' menjadi 'Excel5'
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

                //Header
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                header("Cache-Control: no-store, no-cache, must-revalidate");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

                //Nama File
                header('Content-Disposition: attachment;filename="Trans_Kasir.xlsx"');

                //Download
                $objWriter->save("php://output"); 
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
                redirect(base_url('trnskasir'));
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
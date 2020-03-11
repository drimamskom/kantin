<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lapkantin extends CI_Controller {
    
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_lapkantin');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('lapkantin');
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
            $stan = $this->input->post('stan');
            
            if(empty($length)){ $limit_txt=""; }else{ $limit_txt=" LIMIT $start, $length "; }
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( p.kode_trns_pemesanan LIKE '%$kata%' OR pp.kode LIKE '%$kata%' OR s.nama LIKE '%$kata%' OR dm.nama_barang LIKE '%$kata%' ) ";
            }
            if(empty($tgl_mulai)||empty($tgl_selesai)){
                $where2="";
            }else{
                $d1 = $this->m_lapkantin->datetosqldate($tgl_mulai);
                $d2 = $this->m_lapkantin->datetosqldate($tgl_selesai);
                $where2=" AND (pp.tgl BETWEEN '$d1' AND '$d2' ) ";
            }
            if(empty($stan)){
                $where3="";
            }else{
                $where3=" AND dm.no_stan = '$stan' ";
            }

            $query1 = $this->db->query("SELECT COUNT(*) AS total, SUM(total) AS jumm 
                                        FROM trns_pemesanan_detail p
                                        LEFT JOIN trns_pemesanan pp ON pp.kode_trns_pemesanan=p.kode_trns_pemesanan
                                        LEFT JOIN tb_customer s ON s.kode=pp.kode
                                        LEFT JOIN tb_stok dm ON dm.kode_barang=p.kode_menu
                                        WHERE p.kode_trns_pemesanan IS NOT NULL $where1 $where2 $where3 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
            $totalz = $row1->jumm;
            
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT p.*, pp.tgl, pp.kode kode_customer, s.nama nama_customer, dm.nama_barang, dm.no_stan, d.nama_stan
                            FROM trns_pemesanan_detail p
                            LEFT JOIN trns_pemesanan pp ON pp.kode_trns_pemesanan=p.kode_trns_pemesanan
                            LEFT JOIN tb_customer s ON s.kode=pp.kode
                            LEFT JOIN tb_stok dm ON dm.kode_barang=p.kode_menu
                            LEFT JOIN tb_stan d ON d.no_stan=dm.no_stan
                            WHERE p.kode_trns_pemesanan IS NOT NULL $where1 $where2 $where3
                            ORDER BY p.kode_trns_pemesanan DESC $limit_txt
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;            
            $data = array();
            foreach ($query->result_array() as $row){
                $row['tot']   = $row['total'];
                $row['link']  = '<a class="link">'.$row['kode_trns_pemesanan'].'</a>';
                $row['harga'] = "Rp, ".number_format($row['harga'], 0, ".", ".");
                $row['total'] = "Rp, ".number_format($row['total'], 0, ".", ".");
                $row['tgl']   = $this->m_lapkantin->sqldatetodate($row['tgl']);
                $row['button'] = '<button title="Delete" idnex="'.$row['kode_trns_pemesanan'].'" namenex="'.$row['kode_trns_pemesanan'].'" class="btn btn-danger btn-xs btnhapus" ><i class="fa fa-remove"></i> Del</button>';
                $data[$i] = $row;  
                $i++;
            }
            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data, "totalz" => $totalz);
            if(empty($umpan)){
                echo json_encode($datax);
            }else{
                return $datax;
            }
        }
        
        public function hapus(){
            $datetime = date('Y-m-d H:i:s');
            $tanggal  = date('Y-m-d');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            $id = $this->input->post('id');
            // cari data masih ada         
            $query1 = $this->db->query("SELECT * FROM trns_pemesanan WHERE kode_trns_pemesanan='$id' ");
            $jml = $query1->num_rows(); 
            if($jml!='0') { 
                $row1 = $query1->first_row();
                $usr = $row1->kode;
                $inser = $this->db->query("INSERT INTO trns_hapus 
                                              (kode_trns, pelanggan, kode_barang, jumlah, harga, total, created_date, created_by)
                                           SELECT kode_trns_pemesanan, '$usr' AS usr, kode_menu, jumlah, harga, total, '$datetime' AS dt, '$userr' AS us
                                           FROM trns_pemesanan_detail WHERE kode_trns_pemesanan='$id' ");
                
                $delete1 = $this->db->query("DELETE FROM trns_pemesanan WHERE kode_trns_pemesanan='$id' ");
                $delete2 = $this->db->query("DELETE FROM trns_pemesanan_detail WHERE kode_trns_pemesanan='$id' ");
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
            $data = $this->data();
            //membuat objek
            $objPHPExcel = new PHPExcel();
                        
            // Nama Field Baris Pertama
            $fields = array("urutan" => "No" ,
                            "kode_trns_pemesanan" => "No. Pesanan" ,
                            "tgl" => "Tgl" ,
                            "kode_customer" => "Kode" ,
                            "nama_customer" => "Pelanggan" ,
                            "nama_stan" => "Stan" ,
                            "nama_barang" => "Nama Menu" ,
                            "jumlah" => "Jumlah" ,
                            "harga" => "Harga" ,
                            "total" => "Total");
            $col = 0;
            foreach ($fields as $key => $value) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $value);
                $col++;
            }
	 
            // Mengambil Data
            $total = 0;
            $row = 2;
            foreach($data["data"] as $datax){
                $col = 0;
                foreach ($fields as $key => $value) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $datax[$key]);
                    $col++;
                }     
                $total+=$datax["tot"];           
                $row++;
            }
            
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $row, "Totals");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $row, "Rp, ".number_format($total, 0, ".", "."));
            
            //Set Title
            $objPHPExcel->getActiveSheet()->setTitle('Data Pemesanan Kantin');
 
            //Save ke .xlsx, kalau ingin .xls, ubah 'Excel2007' menjadi 'Excel5'
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
 
            //Header
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            //Nama File
            header('Content-Disposition: attachment;filename="Lap_Kantin.xlsx"');

            //Download
            $objWriter->save("php://output"); 
        }    
        
        public function cetak(){  
            $nonota = $this->input->post('nonota');
            $line  = 23;
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
            $font_k = printer_create_font("Arial", 23, 10, PRINTER_FW_NORMAL, false, false, false, 0);
            $pen = printer_create_pen(PRINTER_PEN_SOLID, 1, "000000");
            
            $result = $this->db->query("SELECT * FROM trns_pemesanan WHERE kode_trns_pemesanan='$nonota' ");
            $row = $result->first_row();
            $cekq  = $this->db->query("SELECT kode AS nomor_induk, nama AS fullname FROM tb_customer WHERE kode='".$row->kode."' ");
            $datax = $cekq->first_row();
            $nama  = strtolower($datax->fullname); 
            $hasil = array();
            $query = $this->db->query("SELECT sup.nama_stan, s.nama_barang, pd.*
                                       FROM trns_pemesanan_detail pd
                                       LEFT JOIN tb_stok s ON s.kode_barang=pd.kode_menu
                                       LEFT JOIN tb_stan sup ON sup.no_stan=s.no_stan
                                       WHERE pd.kode_trns_pemesanan='$nonota' ");
            foreach ($query->result_array() as $data){
                $nama_stan = $data["nama_stan"];
                $hasil[$nama_stan][] = $data;
            }
            $ke=0;
            $jml_depot = count($hasil);
            foreach ($hasil as $nama_stan => $subarr) {
                $ke++;
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
                printer_draw_text($p, "STRUK PENJUALAN KANTIN",101,$lines);
                $lines += $line2;
                printer_select_font($p, $font_k);
                printer_draw_text($p, "Stan : ".$nama_stan,170,$lines);
                $lines += $line*2;
                printer_draw_text($p, "Nota : ".$nonota,$left0,$lines);
                $lines += $line;
                printer_draw_text($p, "Pemesan : ".ucwords($nama),$left0,$lines);
                $lines += $line+3;
                // Header Bon
                printer_draw_line($p, $left0, $lines, $max, $lines);
                $lines += 3;
                printer_draw_text($p, "Produk Penjualan", $left0,  $lines);
                printer_draw_text($p, "Qty", $left1-5, $lines);
                printer_draw_text($p, "Harga", $left2+35, $lines);
                printer_draw_text($p, "Total", $left3+30, $lines);
                $lines += $line;
                printer_draw_line($p, $left0, $lines, $max, $lines);
                $lines += 5;
                $totalz = 0;
                //loop per row bon                
                foreach ($subarr as $rowx) {
                    printer_draw_text($p, substr(ucwords(strtolower($rowx["nama_barang"])), 0,23), $left0, $lines);
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
                                         WHERE kode='$row->kode' GROUP BY kode");
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
                printer_select_font($p, $font_k);
                if($ke!=$jml_depot){
                    $lines += $line*2;
                    printer_draw_text($p, "------------------------------ Potong disini ------------------------------", $left0, $lines);
                    $lines += $line*2;
                }else{
                    $lines += $line*2;
                    printer_draw_text($p, "Terima Kasih Atas Kunjungan Anda", 120, $lines);    
                }
            }
            //end cetak
            printer_delete_font($font);
            printer_end_page($p);
            printer_end_doc($p);
            printer_close($p);
            redirect(base_url('lapkantin'));
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
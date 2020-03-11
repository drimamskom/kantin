<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Trnspembelian extends CI_Controller {
    
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_trnspembelian');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('trnspembelian');
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
            $supplier = $this->input->post('supplier');
            
            if(empty($length)){ $limit_txt=""; }else{ $limit_txt=" LIMIT $start, $length "; }
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( p.kode_faktur LIKE '%$kata%' OR p.kode_supplier LIKE '%$kata%' OR sp.nama_supplier LIKE '%$kata%' OR p.subtotal LIKE '%$kata%' OR p.pembayaran LIKE '%$kata%' ) ";
            }
            if(empty($tgl_mulai)||empty($tgl_selesai)){
                $where2="";
            }else{
                $d1 = $this->m_trnspembelian->datetosqldate($tgl_mulai);
                $d2 = $this->m_trnspembelian->datetosqldate($tgl_selesai);
                $where2=" AND p.tgl BETWEEN '$d1' AND '$d2' ";
            }
            if(empty($supplier)){
                $where3="";
            }else{
                $where3=" AND p.kode_supplier = '$supplier' ";
            }

            $query1 = $this->db->query("SELECT COUNT(*) AS total, SUM(subtotal) AS jumm 
                                        FROM trns_pembelian p 
                                        LEFT JOIN tb_supplier sp ON sp.kode_supplier=p.kode_supplier
                                        WHERE sp.tempat='2' $where1 $where2 $where3 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
            $totalz = $row1->jumm;
            
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT p.*, sp.nama_supplier
                            FROM trns_pembelian p
                            LEFT JOIN tb_supplier sp ON sp.kode_supplier=p.kode_supplier
                            WHERE sp.tempat='2' $where1 $where2 $where3
                            ORDER BY p.id_header DESC $limit_txt
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $row['sub'] = $row['subtotal'];
                $row['subtotal'] = "Rp, ".number_format($row['subtotal'], 0, ".", ".");
                $data[$i] = $row;                
                $data[$i]['tgl'] = $this->m_trnspembelian->sqldatetodate($row['tgl']);
                $data[$i]['jatuh_tempo'] = $this->m_trnspembelian->sqldatetodate($row['jatuh_tempo']);
                $data[$i]['button'] = '<button title="View Detail" idnex="'.$data[$i]['kode_faktur'].'" class="btn btn-info btn-xs btnview" ><i class="fa fa-get-pocket"></i> Detail</button>
                                       <button title="Retur" idnex="'.$data[$i]['kode_faktur'].'" class="btn btn-warning btn-xs btnretur" ><i class="fa fa-exchange"></i> Retur</button>';
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data, "totalz" => $totalz);
            if(empty($umpan)){
                echo json_encode($datax);
            }else{
                return $datax;
            }
        }
        
        public function cari(){
            $id = $this->input->post('id'); // kode_trans_pembelian
            $array = $this->m_trnspembelian->getalldata($id);
            echo json_encode($array);
        }
        
        public function save(){
            $kodearr = $this->input->post('kode');
            $hargaarr = $this->input->post('harga');
            $satuanarr = $this->input->post('satuan');
            
            $nofaktur = $this->input->post('nofaktur');  
            $supplier = $this->input->post('supplier');
            
            $datetime = date('Y-m-d H:i:s');
            $tanggal  = date('Y-m-d');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if(count($kodearr)>0){                    
                $sukses=0; $gagal=0;
                $subtotal = 0;
                $next_num = $this->m_trnspembelian->ambilnomorbaru();
                $inser = $this->db->query("INSERT INTO trns_retur 
                        (kode_retur, kode_supplier, pembayaran, tgl, jatuh_tempo, hari_jatuh_tempo, total, diskon, pajak, harga_ppn, subtotal, terbayar, created_date, created_by)
                        VALUES
                        ('$next_num', '$supplier', '$nofaktur', '$tanggal', '$tanggal', '0', '0', '0', 'Y', '0', '0', '0', '$datetime', '$userr') ");
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
        
        public function export(){
            $data = $this->data();
            //membuat objek
            $objPHPExcel = new PHPExcel();
                        
            // Nama Field Baris Pertama
            $fields = array("urutan" => "No" ,
                            "kode_faktur" => "No. Faktur" ,
                            "nama_supplier" => "Supplier" ,
                            "pembayaran" => "Pembayaran" ,
                            "tgl" => "Tgl" ,
                            "jatuh_tempo" => "Jatuh Tempo" ,
                            "hari_jatuh_tempo" => "Hari" ,
                            "subtotal" => "Subtotal");
            $col = 0;
            foreach ($fields as $key => $value) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $value);
                $col++;
            }
	 
            // Mengambil Data
            $subtotal = 0;
            $row = 2;
            foreach($data["data"] as $data){
                $col = 0;
                foreach ($fields as $key => $value) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $data[$key]);
                    $col++;
                }
                $subtotal+=$data["sub"];
                $row++;
            }
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, "Totals");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $row, "Rp, ".number_format($subtotal, 0, ".", "."));
            
            //Set Title
            $objPHPExcel->getActiveSheet()->setTitle('Data Trans Pembelian Faktur');
 
            //Save ke .xlsx, kalau ingin .xls, ubah 'Excel2007' menjadi 'Excel5'
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
 
            //Header
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            //Nama File
            header('Content-Disposition: attachment;filename="Trans_Pembelian.xlsx"');

            //Download
            $objWriter->save("php://output"); 
        }    

}
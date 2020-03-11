<?php if(!defined('BASEPATH')) exit ('No Direct Script Access Allowed');

class Supplierinfo extends CI_Controller {

	function __construct(){
            parent::__construct();		
            $this->load->model('m_supplierinfo');
	}

	function index(){
            if($this->session->userdata('status') != "mart"){
                $this->session->sess_destroy();
                $this->load->view('v_supplierinfo',array("info"=>"gagal"));
            }else{
                if($this->session->userdata('akses') != "kantin"){
                    redirect(base_url());
                }else{
                    $this->load->view('v_supplierinfo',array("info"=>"success"));
                }
            }
            
	}
        
        public function data(){
            $sesi = $this->session->get_userdata();
            $nomor_induk = $sesi['nomor_induk'];
            $tanggal  = date('Y-m-d');
            $draw = $this->input->post('draw');
            $kata = $this->input->post('cari');
            
            $hasil = array();
            $cekqq = $this->db->query("SELECT p.kode_supplier, p.tgl, pd.* 
                                       FROM trns_pembelian p 
                                       LEFT JOIN trns_pembelian_detail pd ON pd.kode_faktur=p.kode_faktur
                                       WHERE p.tgl='$tanggal' AND p.kode_supplier='$nomor_induk'");
            foreach ($cekqq->result_array() as $hsl){
                $kd = $hsl['kode_barang'];
                $hasil[$kd] = $hsl['qty'];
            }
            
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( s.kode_barang LIKE '%$kata%' OR s.nama_barang LIKE '%$kata%' ) ";
            }
            $query1 = $this->db->query("SELECT COUNT(*) as total FROM tb_stok s WHERE s.supplier='$nomor_induk' $where1 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT s.* 
                            FROM tb_stok s
                            WHERE s.supplier='$nomor_induk' $where1 
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
                        
            $datetime = date('Y-m-d H:i:s');
            $tanggal  = date('Y-m-d');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            $kodeinduk = $sesi['nomor_induk'];
            
            if(count($kodearr)>0){                    
                $sukses=0; $gagal=0;
                $subtotal = 0;
                $next_num = $this->m_supplierinfo->ambilnomorbaru();
                $inser = $this->db->query("INSERT INTO trns_pembelian 
                        (kode_faktur, kode_supplier, pembayaran, tgl, jatuh_tempo, hari_jatuh_tempo, total, diskon, pajak, harga_ppn, subtotal, terbayar, created_date, created_by)
                        VALUES
                        ('$next_num', '$kodeinduk', 'KANTIN', '$tanggal', '$tanggal', '0', '0', '0', 'Y', '0', '0', '0', '$datetime', '$userr') ");
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
        
        public function data2(){
            $sesi = $this->session->get_userdata();
            $nomor_induk = $sesi['nomor_induk'];
            $umpan  = $this->input->post('umpan');
            $draw = $this->input->post('draw');
            $kata = $this->input->post('cari');
            $tgl_cari = $this->input->post('tgl_cari');
            
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( s.kode_barang LIKE '%$kata%' OR s.nama_barang LIKE '%$kata%' ) ";
            }
            if(empty($tgl_cari)){
                $tanggal = date('Y-m-d');
            }else{
                $tanggal = $this->m_supplierinfo->datetosqldate($tgl_cari);
            }
            $totl['jumstok'] = 0;
            $totl['jumlaku'] = 0;
            $totl['jumretur'] = 0;
            $totl['jumsisa'] = 0;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT s.*, dt.qty AS stok, dt.total AS jumstok, dt2.jumlah AS laku, dt2.total AS jumlaku, dt3.qty AS retur, dt3.total AS jumretur
                            FROM tb_stok s
                            LEFT JOIN (
                                    SELECT p.kode_supplier, p.tgl, pd.* 
                                    FROM trns_pembelian p 
                                    LEFT JOIN trns_pembelian_detail pd ON pd.kode_faktur=p.kode_faktur
                                    WHERE p.tgl='$tanggal' AND p.kode_supplier='$nomor_induk'
                            ) AS dt ON dt.kode_barang=s.kode_barang
                            LEFT JOIN (
                                    SELECT p.kode, p.tgl, pd.* 
                                    FROM trns_pemesanan p 
                                    LEFT JOIN trns_pemesanan_detail pd ON pd.kode_trns_pemesanan=p.kode_trns_pemesanan
                                    WHERE p.tgl='$tanggal'
                            ) AS dt2 ON dt2.kode_menu=s.kode_barang
                            LEFT JOIN (
                                    SELECT p.kode_supplier, p.tgl, pd.* 
                                    FROM trns_retur p 
                                    LEFT JOIN trns_retur_detail pd ON pd.kode_retur=p.kode_retur
                                    WHERE p.tgl='$tanggal' AND p.kode_supplier='$nomor_induk'
                            ) AS dt3 ON dt3.kode_barang=s.kode_barang
                            WHERE s.supplier='$nomor_induk' $where1
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
                
                $row['harga'] = "Rp, ".number_format($row['harga_beli'], 0, ".", ".");
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
            
            $datax = array("draw" => $draw, "tgl" => $tanggal, "data" => $data, "totalz" => $totl);
            if(empty($umpan)){
                echo json_encode($datax);
            }else{
                return $datax;
            }
        }
        
        public function export(){  
            $sesi = $this->session->get_userdata();
            $fullname = $sesi['fullname'];
            
            $data = $this->data2();
            $tgl  = $data['tgl'];
            $tglm = $this->m_supplierinfo->tglmanusia($tgl);
            $tanggal = $this->m_supplierinfo->sqldatetodate($tgl);
            $judul = "Laporan Supplier ".$fullname." Per tanggal ".$tglm;
            //membuat objek
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, $judul);
                        
            // Nama Field Baris Pertama                                    
            $fields = array("urutan" => "No" ,
                            "nama_barang" => "Nama Menu" ,
                            "satuan" => "Satuan" ,
                            "harga" => "Harga" ,
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
         
}
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lapomset extends CI_Controller {
    
        public $nama_tabel = 'm_siswa';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_lapomset');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('lapomset');
            $this->load->view('footer');
	}
        
        public function data(){
            $umpan  = $this->input->post('umpan');
            $draw   = $this->input->post('draw');
            $kata   = $this->input->post('cari');
            $jenis  = $this->input->post('jenis');
            $kategori = $this->input->post('kategori');
            $satuan = $this->input->post('satuan');
            
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( ob.kode_obat LIKE '%$kata%' OR ob.barcode LIKE '%$kata%' OR ob.nama_obat LIKE '%$kata%' ) ";
            }
            if(count($jenis)>0){                
                $jenis_list = "'".implode("','", $jenis)."'";
                $where2=" AND ob.jenis_obat IN ($jenis_list) ";
            }else{
                $where2="";
            }
            if(count($kategori)>0){                
                $kategori_list = "'".implode("','", $kategori)."'";
                $where3=" AND ob.id_kategori IN ($kategori_list) ";
            }else{
                $where3="";
            }
            if(count($satuan)>0){                
                $satuan_list = "'".implode("','", $satuan)."'";
                $where4=" AND ob.id_satuan IN ($satuan_list) ";
            }else{
                $where4="";
            }         
            
            $totalz['stok_awal'] = 0;
            $totalz['stok']      = 0;
            $totalz['total1']    = 0;
            $totalz['qty1']      = 0;
            $totalz['total2']    = 0;
            $totalz['qty2']      = 0;
            $totalz['omset']     = 0;
                
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT ob.*, a.stok, b.total1, b.qty1, c.total2, c.qty2
                            FROM m_obat ob
                            LEFT JOIN ( SELECT kode_obat, SUM(qty_satuan_awal) stok 
                                        FROM stok_moves GROUP BY kode_obat ) AS a ON a.kode_obat=ob.kode_obat
                            LEFT JOIN ( SELECT pd.kode_obat, SUM(pd.total) total1, SUM(sm.qty_satuan_awal) qty1
                                        FROM t_penjualan_detail pd
                                        LEFT JOIN stok_moves sm ON sm.transaksi=pd.kode_trans_penjualan AND sm.kode_obat=pd.kode_obat
                                        GROUP BY pd.kode_obat ) AS b ON b.kode_obat=ob.kode_obat
                            LEFT JOIN ( SELECT pd2.kode_obat, SUM(pd2.subtotal) total2, SUM(sm2.qty_satuan_awal) qty2
                                        FROM t_pembelian_detail pd2
                                        LEFT JOIN stok_moves sm2 ON sm2.transaksi=pd2.kode_faktur AND sm2.kode_obat=pd2.kode_obat
                                        GROUP BY pd2.kode_obat ) AS c ON c.kode_obat=ob.kode_obat
                            WHERE ob.kode_obat IS NOT NULL $where1 $where2 $where3 $where4
                            ORDER BY ob.kode_obat
                    ) AS t, 
                    (SELECT @rownum := 0) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $totalz['stok_awal'] += $row['stok_awal'];
                $totalz['stok']      += $row['stok'];
                $totalz['total1']    += $row['total1'];
                $totalz['qty1']      += abs($row['qty1']);
                $totalz['total2']    += $row['total2'];
                $totalz['qty2']      += $row['qty2'];
                $row['qty1']   = abs($row['qty1']);
                $row['total1'] = "Rp, ".  number_format($row['total1'], 0, ".", ".");
                $row['total2'] = "Rp, ".  number_format($row['total2'], 0, ".", ".");
                $data[$i] = $row;
                $i++;
            }           
            
            $totalz['omset'] = floatval($totalz['total1'])-floatval($totalz['total2']);
            $datax = array("draw" => $draw, "data" => $data, "totalz" => $totalz);
            if(empty($umpan)){
                echo json_encode($datax);
            }else{
                return $datax;
            }
        }
        
        public function export(){
            $data = $this->data();
            //membuat objek
            $objPHPExcel = new PHPExcel();
                        
            // Nama Field Baris Pertama                                       
            $fields = array("urutan" => "No" ,
                            "kode_obat" => "Kode" ,
                            "barcode" => "Barcode" ,
                            "nama_obat" => "Nama Obat" ,
                            "jenis_obat" => "Jenis Obat" ,
                            "id_kategori" => "Kategori" ,
                            "id_satuan" => "Satuan" ,
                            "stok_awal" => "Stok Awal" ,
                            "qty2" => "Stok Beli" ,
                            "total2" => "Total beli" ,
                            "qty1" => "Stok Jual" ,
                            "total1" => "Total Jual" );
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
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, "Totals");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $data["totalz"]["stok_awal"]);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $data["totalz"]["qty2"]);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $row, "Rp. ".number_format($data["totalz"]["total2"], 0, ".", "."));
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $row, $data["totalz"]["qty1"]);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $row, "Rp. ".number_format($data["totalz"]["total1"], 0, ".", "."));
            $objPHPExcel->setActiveSheetIndex(0);

            //Set Title
            $objPHPExcel->getActiveSheet()->setTitle('Data Omset');
 
            //Save ke .xlsx, kalau ingin .xls, ubah 'Excel2007' menjadi 'Excel5'
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
 
            //Header
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            //Nama File
            header('Content-Disposition: attachment;filename="Data_Omset.xlsx"');

            //Download
            $objWriter->save("php://output"); 
        }
        
}

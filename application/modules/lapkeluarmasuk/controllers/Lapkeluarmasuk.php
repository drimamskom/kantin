<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lapkeluarmasuk extends CI_Controller {
    
        public $nama_tabel = 'm_siswa';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_lapkeluarmasuk');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('lapkeluarmasuk');
            $this->load->view('footer');
	}
        
        public function data(){
            $umpan  = $this->input->post('umpan');
            $draw   = $this->input->post('draw');
            $kata = $this->input->post('cari');
            $tgl_mulai = $this->input->post('tgl_mulai');
            $tgl_selesai = $this->input->post('tgl_selesai');
            
            $i=0;
            $data = array();            
            $totalz['masuk']  = 0;
            $totalz['keluar'] = 0;
            $totalz['total']  = 0;
            
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( dt.ket LIKE '%$kata%' ) ";
            }
            if(empty($tgl_mulai)||empty($tgl_selesai)){
                $where2="";
            }else{
                $d1 = $this->m_lapkeluarmasuk->datetosqldate($tgl_mulai);
                $d2 = $this->m_lapkeluarmasuk->datetosqldate($tgl_selesai);
                $where2  = " AND ( dt.tgl BETWEEN '$d1' AND '$d2' ) ";
                $kemarin = date('Y-m-d', strtotime("-1 day", strtotime($d1)));
                $query1  = $this->db->query("SELECT 'Sisa Kas Kemarin' AS ket, '1' AS urut, SUM(nilai) AS jumall
                                            FROM (
                                                SELECT dt.*,
                                                CASE dt.info
                                                  WHEN 'K' THEN 0-dt.jumlah
                                                  WHEN 'M' THEN dt.jumlah
                                                 ELSE '0'
                                                 END AS nilai
                                                FROM (
                                                SELECT tgl, 'Pembelian Menu Kantin' AS ket, '1' AS urut, SUM(total) AS jumlah, 'K' AS info FROM trns_pembelian WHERE pembayaran='KANTIN' GROUP BY tgl
                                                UNION ALL
                                                SELECT tgl, 'Pembelian Barang Mart' AS ket, '2' AS urut, SUM(total) AS jumlah, 'K' AS info FROM trns_pembelian WHERE pembayaran!='KANTIN' GROUP BY tgl
                                                UNION ALL
                                                SELECT tgl, 'Penghasilan Kantin' AS ket, '3' AS urut, SUM(subtotal) AS jumlah, 'M' AS info FROM trns_pemesanan GROUP BY tgl
                                                UNION ALL
                                                SELECT tgl, 'Penjualan Mart' AS ket, '4' AS urut, SUM(subtotal) AS jumlah, 'M' AS info FROM trns_penjualan GROUP BY tgl
                                                UNION ALL
                                                SELECT tgl, 'Retur Kantin' AS ket, '5' AS urut, SUM(subtotal) AS jumlah, 'M' AS info FROM trns_retur GROUP BY tgl
                                                UNION ALL
                                                SELECT tanggal AS tgl, 'Pengisian Deposit' AS ket, '6' AS urut, SUM(jumlah) AS jumlah, 'M' AS info FROM tb_deposit GROUP BY tanggal
                                            ) dt 
                                            WHERE dt.tgl IS NOT NULL AND dt.tgl<'$d1' $where1
                                            ORDER BY dt.tgl, dt.urut
                                        ) AS dt2 ");
                foreach ($query1->result_array() as $row1){
                    $row1['urutan'] = $i+1;
                    $row1['masuk'] = "Rp, ".number_format(0, 0, ".", "."); 
                    $row1['keluar'] = "Rp, ".number_format(0, 0, ".", "."); 
                    $row1['total']   = "Rp, ".number_format($row1['jumall'], 0, ".", "."); 
                    $row1['tanggal'] = $this->m_lapkeluarmasuk->tglmanusia($kemarin);
                    $row1['tgl'] = $this->m_lapkeluarmasuk->sqldatetodate($kemarin);
                    $data[$i] = $row1;           
                    $totalz['total'] += intval($row1['jumall']);
                    $i++;
                }
            }       
                
            $query = $this->db->query("SELECT dt.* FROM (
                                            SELECT tgl, 'Pembelian Menu Kantin' AS ket, '1' AS urut, SUM(total) AS jumlah, 'K' AS info FROM trns_pembelian WHERE pembayaran='KANTIN' GROUP BY tgl
                                            UNION ALL
                                            SELECT tgl, 'Pembelian Barang Mart' AS ket, '2' AS urut, SUM(total) AS jumlah, 'K' AS info FROM trns_pembelian WHERE pembayaran!='KANTIN' GROUP BY tgl
                                            UNION ALL
                                            SELECT tgl, 'Penghasilan Kantin' AS ket, '3' AS urut, SUM(subtotal) AS jumlah, 'M' AS info FROM trns_pemesanan GROUP BY tgl
                                            UNION ALL
                                            SELECT tgl, 'Penjualan Mart' AS ket, '4' AS urut, SUM(subtotal) AS jumlah, 'M' AS info FROM trns_penjualan GROUP BY tgl
                                            UNION ALL
                                            SELECT tgl, 'Retur Kantin' AS ket, '5' AS urut, SUM(subtotal) AS jumlah, 'M' AS info FROM trns_retur GROUP BY tgl
                                            UNION ALL
                                            SELECT tanggal AS tgl, 'Pengisian Deposit' AS ket, '6' AS urut, SUM(jumlah) AS jumlah, 'M' AS info FROM tb_deposit GROUP BY tanggal
                                        ) dt 
                                        WHERE dt.tgl IS NOT NULL $where1 $where2
                                        ORDER BY dt.tgl, dt.urut ");  
            foreach ($query->result_array() as $row){
                $row['urutan'] = $i+1;
                if($row['info']=="M"){
                    $row['masuk'] = "Rp, ".number_format($row['jumlah'], 0, ".", "."); 
                    $row['keluar'] = "Rp, ".number_format(0, 0, ".", "."); 
                    $totalz['masuk'] += $row['jumlah'];  
                    $totalz['keluar'] += 0;   
                    $totalz['total']  +=  intval($row['jumlah'])-0;  
                }else{
                    $row['masuk'] = "Rp, ".number_format(0, 0, ".", "."); 
                    $row['keluar'] = "Rp, ".number_format($row['jumlah'], 0, ".", "."); 
                    $totalz['masuk']  += 0;
                    $totalz['keluar'] += $row['jumlah'];    
                    $totalz['total']  +=  0-intval($row['jumlah']);                
                }
                $row['total']   = "Rp, ".number_format($totalz['total'], 0, ".", "."); 
                $row['tanggal'] = $this->m_lapkeluarmasuk->tglmanusia($row['tgl']);
                $row['tgl'] = $this->m_lapkeluarmasuk->sqldatetodate($row['tgl']);
                $data[$i] = $row;                
                $i++;
            }           
            
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
                            "tgl" => "Tanggal" ,
                            "ket" => "Keterangan" ,
                            "masuk" => "Pemasukan" ,
                            "keluar" => "Pengeluaran" );
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
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, "Total");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, "Rp. ".number_format($data["totalz"]["masuk"], 0, ".", "."));
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, "Rp. ".number_format($data["totalz"]["keluar"], 0, ".", "."));
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row+1, "Grand Total");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $row+1, "Rp. ".number_format($data["totalz"]["sisa"], 0, ".", "."));
            $objPHPExcel->setActiveSheetIndex(0);

            //Set Title
            $objPHPExcel->getActiveSheet()->setTitle('Data Keluar Masuk');
 
            //Save ke .xlsx, kalau ingin .xls, ubah 'Excel2007' menjadi 'Excel5'
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
 
            //Header
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            //Nama File
            header('Content-Disposition: attachment;filename="Data_Keluar_Masuk.xlsx"');

            //Download
            $objWriter->save("php://output"); 
        }
        
}

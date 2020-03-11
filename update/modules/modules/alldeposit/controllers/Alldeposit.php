<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Alldeposit extends CI_Controller {
    
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_alldeposit');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('alldeposit');
            $this->load->view('footer');
	}
        
        public function data(){            
            $umpan = $this->input->post('umpan');
            $draw = $this->input->post('draw');
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $kata = $this->input->post('cari');
            
            if(empty($length)){ $limit_txt=""; }else{ $limit_txt=" LIMIT $start, $length "; }
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( d.kode LIKE '%$kata%' OR s.nama LIKE '%$kata%' ) ";
            }

            $query1 = $this->db->query("SELECT COUNT(*) AS total, SUM(jumlah) AS jumm 
                                        FROM tb_customer s
                                        LEFT JOIN tb_deposit_moves d ON s.kode=d.kode
                                        WHERE d.jumlah IS NOT NULL $where1  ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
            $totalz = $row1->jumm;
            
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT SUM(jumlah) jumlah, d.kode kode_customer, s.nama nama_customer, s.grup
                            FROM tb_customer s
                            LEFT JOIN tb_deposit_moves d ON s.kode=d.kode
                            WHERE d.jumlah IS NOT NULL $where1 
                            GROUP BY d.kode				
                            order by s.grup desc, s.nama asc	
                            $limit_txt
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;            
            $data = array();
            foreach ($query->result_array() as $row){
                $row['tot']  = $row['jumlah'];
                $row['jumlah'] = number_format($row['jumlah'], 0, ".", ".");
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
        
        public function export(){
            $data = $this->data();
            //membuat objek
            $objPHPExcel = new PHPExcel();
                        
            // Nama Field Baris Pertama                                
            $fields = array("urutan" => "No" ,
                            "kode_customer" => "Kode" ,
                            "nama_customer" => "Pelanggan" ,
                            "tot" => "Jumlah" );
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
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, "Totals");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, number_format($total, 0, ".", "."));
            
            //Set Title
            $objPHPExcel->getActiveSheet()->setTitle('Semua Deposit');
 
            //Save ke .xlsx, kalau ingin .xls, ubah 'Excel2007' menjadi 'Excel5'
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
 
            //Header
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            //Nama File
            header('Content-Disposition: attachment;filename="Lap_Deposit.xlsx"');

            //Download
            $objWriter->save("php://output"); 
        }    

}
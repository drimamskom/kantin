<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lapstok extends CI_Controller {
    
        //public $nama_tabel = 'm_siswa';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_lapstok');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('lapstok');
            $this->load->view('footer');
	}
        
        public function data(){
            $umpan  = $this->input->post('umpan');
            $draw   = $this->input->post('draw');
            $kata   = $this->input->post('cari');
            $stan   = $this->input->post('stan');
            
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( s.kode_barang LIKE '%$kata%' OR s.nama_barang LIKE '%$kata%' ) ";
            }
            if(strlen($stan)==0){
                $where2="";
            }else{
                $where2=" AND s.no_stan='$stan' ";
            }
            $query1 = $this->db->query("SELECT COUNT(*) as total
                                        FROM tb_stok s
                                        LEFT JOIN tb_stan t ON t.no_stan=s.no_stan
                                        LEFT JOIN (
                                            SELECT SUM(qty) AS jum, kode_barang
                                            FROM tb_stok_moves
                                            GROUP BY kode_barang
                                        ) m ON m.kode_barang=s.kode_barang
                                        WHERE s.aktif='1'
                                        $where1 $where2 ");
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
                            WHERE s.aktif='1'
                            $where1 $where2
                    ) AS t, 
                    (SELECT @rownum := 0) r
                     ORDER BY t.no_stan, t.nama_barang ");  
            $i=0;
            $totalz=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $row['stok']   = (intval($row['jum'])>0) ? intval($row['jum']) : 0;    
                $row['harga_beli'] = "Rp, ".number_format($row['harga_beli'], 0, ".", ".");
                $row['harga_jual'] = "Rp, ".number_format($row['harga_jual'], 0, ".", ".");
                $row['input']      = '<input type="number" name="kode['.$row['kode_barang'].']" min="0" class="form-control input-lg number-only inp-gede"/>
                                      <input type="hidden" name="stok['.$row['kode_barang'].']" value="'.$row['stok'].'" /> ';
                $data[$i] = $row;
                $totalz+=$row['stok'];
                $i++;
            }

            $datax = array("draw" => $draw , "recordsTotal" => $tot,  "recordsFiltered" => $tot, "data" => $data, "totalz" => $totalz );
            if(empty($umpan)){
                echo json_encode($datax);
            }else{
                return $datax;
            }
        }
        
        public function save(){
            $kodearr = $this->input->post('kode');
            $stokarr = $this->input->post('stok');
                        
            $datetime = date('Y-m-d H:i:s');
            $tanggal  = date('Y-m-d');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if(count($kodearr)>0){                    
                $sukses=0; $gagal=0;
                $n=0;
                $batch = $this->m_lapstok->batchbaru();
                $arr_ins = array();
                foreach ($kodearr as $kode => $value){  
                    if($value!=''){
                        $stok = intval($stokarr[$kode]);
                        $nulled = $stok <= 0 ? abs($stok) : 0-$stok ;
                        $arr_ins[] = " ('BATCH:".$batch."', '$tanggal', '$kode', '$nulled', '$datetime', '$userr' ) ";
                        $arr_ins[] = " ('REBATCH:".$batch."', '$tanggal', '$kode', '$value', '$datetime', '$userr' ) ";
                        $sukses++;
                    }
                }
                //INSERT STOK BARANG
                if(count($arr_ins)>0){
                    $in_stok = implode(",", $arr_ins);
                    $this->db->query("INSERT INTO tb_stok_moves 
                                        (transaksi, tanggal, kode_barang, qty, created_date, created_by )
                                      VALUES ".$in_stok." ");                                    
                }
                echo json_encode(array('status'=>'success', 'txt'=>'sukses:'.$sukses.' gagal:'.$gagal.'!', 'batch'=>$batch));
            }
        }
        
        public function export(){
            $data = $this->data();
            //membuat objek
            $objPHPExcel = new PHPExcel();
            
            // Nama Field Baris Pertama                                   
            $fields = array("urutan" => "No" ,
                            "nama_stan" => "Stan" ,
                            "kode_barang" => "Kode Barang" ,
                            "nama_barang" => "Nama Barang" ,
                            "satuan" => "Satuan" ,
                            "harga_beli" => "Harga Beli",
                            "harga_jual" => "Harga Jual",
                            "stok" => "Stok"  );
            $col = 0;
            foreach ($fields as $key => $value) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $value);
                $col++;
            }
	 
            // Mengambil Data
            $row = 2;
            foreach($data["data"] as $dataz){
                $col = 0;
                foreach ($fields as $key => $value) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $dataz[$key]);
                    $col++;
                }
                $row++;
            }
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, "Total");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $row, number_format($data["totalz"], 0, ".", "."));
            $objPHPExcel->setActiveSheetIndex(0);

            //Set Title
            $objPHPExcel->getActiveSheet()->setTitle('Lap Stok');
 
            //Save ke .xlsx, kalau ingin .xls, ubah 'Excel2007' menjadi 'Excel5'
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
 
            //Header
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            //Nama File
            header('Content-Disposition: attachment;filename="Lap_Stok.xlsx"');

            //Download
            $objWriter->save("php://output"); 
        }
        
}

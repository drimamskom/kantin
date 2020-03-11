<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Deposit extends CI_Controller {
    
        public $nama_tabel = 'm_menudepot';
        
	function __construct(){
            parent::__construct();
            if($this->session->userdata('status') != "mart"){
                redirect(base_url('login'));
            }	
            $this->load->library("PHPExcel");
            $this->load->model('m_deposit');
	}
        
	public function index(){ 
            $this->load->view('header');
            $this->load->view('deposit');
            $this->load->view('footer');
	}
        
        public function data(){
            $draw = $this->input->post('draw');
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $kata = $this->input->post('cari');
            $tgl_cari = $this->input->post('tgl_cari');
            $tgl = $this->m_deposit->datetosqldate($tgl_cari);
            
            if(empty($kata)){
                $where1="";
            }else{
                $where1=" AND ( s.no_deposit LIKE '%$kata%' OR cus.nama LIKE '%$kata%' OR s.kode LIKE '%$kata%' OR s.jumlah LIKE '%$kata%' ) ";
            }
            if(empty($tgl_cari)){
                $where2="";
            }else{
                $where2=" AND s.tanggal='$tgl' ";
            }
            $query1 = $this->db->query("SELECT COUNT(*) as total 
                                        FROM tb_deposit s 
                                        LEFT JOIN tb_customer cus ON cus.kode=s.kode
                                        WHERE s.no_deposit!='0' $where1 $where2 ");
            $row1 = $query1->first_row();
            $tot = $row1->total;
           
            $query = $this->db->query("SELECT @rownum := @rownum + 1 AS urutan, t.*
                    FROM (
                            SELECT s.*, cus.nama
                            FROM tb_deposit s 
                            LEFT JOIN tb_customer cus ON cus.kode=s.kode
                            WHERE s.no_deposit!='0' $where1 $where2
                            ORDER BY s.created_date DESC
                            LIMIT $start, $length 
                    ) AS t, 
                    (SELECT @rownum := $start) r");  
            $i=0;
            $data = array();
            foreach ($query->result_array() as $row){
                $row['link']  = '<a class="link">'.$row['no_deposit'].'</a>';
                $row['deposit'] = "Rp, ".number_format(intval($row['jumlah']), 0, ".", ".");
                $data[$i] = $row;
                $data[$i]['button'] = '<button title="Delete" idnex="'.$data[$i]['no_deposit'].'" namenex="'.$data[$i]['no_deposit'].'" class="btn btn-danger btn-xs btnhapus" ><i class="fa fa-remove"></i> Del</button>';
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
                $data['kode'] = $row->kode;
                $data['nama'] = $row->nama;
                $data['status'] = 'success';

                $que = $this->db->query("SELECT SUM(jumlah) deposit, kode FROM tb_deposit_moves 
                                         WHERE kode='$nomor_induk' GROUP BY kode");
                $jml = $que->num_rows();
                if($jml>0){
                    $roe = $que->first_row();
                    $data['deposit'] = $roe->deposit;
                }else{
                    $data['deposit'] = 0;
                }
            }
            echo json_encode($data);
        }
        
        public function hapus(){
            $id = $this->input->post('id');
            $delete = $this->db->query("DELETE FROM tb_deposit WHERE no_deposit='$id' ");
            $delete2 = $this->db->query("DELETE FROM tb_deposit_moves WHERE transaksi='$id' ");
            if($delete2){
                echo json_encode(array('status'=>'success'));
            }else{
                echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa delete data!'));
            }
        }
        
        public function save(){
            $tanggal = date('Y-m-d');
            $sama = $this->input->post('sama');
            $idne = $this->input->post('idne');
            $kode = $this->input->post('kode');
            $idcard = $this->input->post('idcard');
            $deposit = $this->input->post('deposit');
            $crud = $this->input->post('crud');
                
            $date = date('Y-m-d H:i:s');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            
            if($crud=='N'){
                $query1 = $this->db->query("SELECT COUNT(*) AS jml FROM tb_deposit WHERE tanggal='$tanggal' AND kode='$kode' AND jumlah='$deposit' ");
                $row1 = $query1->first_row();
                $jmle = intval($row1->jml);
                if( ($jmle>0)&&($sama=='no') ){
                    echo json_encode(array('status'=>'duplicate', 'txt'=>'Deposit Rp, '.number_format($deposit, 0, ".", ".").' sudah pernah dimasukkan, Tetap Lanjut??'));
                }else{
                    $cdhr = date('Ymd');
                    $code = "S".$cdhr."-";
                    $query2 = $this->db->query("select max(right(no_deposit,4)*1) as new_count FROM tb_deposit where left(no_deposit,10)='$code' ");
                    $row2 = $query2->first_row();
                    $new_count = intval($row2->new_count)+1;
                    $new_code = $code.str_pad($new_count,4,"0",STR_PAD_LEFT);

                    $inser = $this->db->query("INSERT INTO tb_deposit 
                            (no_deposit, tanggal, kode, jumlah, created_date, created_by )
                            VALUES
                            ('$new_code', '$tanggal', '$kode', '$deposit', '$date', '$userr' ) ");
                    if($inser){  
                        $inser2 = $this->db->query("INSERT INTO tb_deposit_moves 
                                    (transaksi, tanggal, kode, jumlah, created_date, created_by)
                                    VALUES
                                    ('$new_code', '$tanggal', '$kode', '$deposit', '$date', '$userr') ");
                        echo json_encode(array('status'=>'success', 'nota'=>$new_code));
                    }else{
                        echo json_encode(array('status'=>'failed', 'txt'=>'tdk bisa save data!'));
                    }
                }
            }else{
                echo json_encode(array('status'=>'failed'));
            }
        }
        
        public function cetak($nonota=""){  
            if(!empty($nonota)){
                $result = $this->db->query("SELECT d.*, c.nama 
                                            FROM tb_deposit d
                                            LEFT JOIN tb_customer c ON c.kode=d.kode
                                            WHERE no_deposit='$nonota' ");
                $datax = $result->first_row();
                $kasir = strtolower($datax->created_by); 
                $nama  = strtolower($datax->nama); 
                $kode  = $datax->kode; 
                
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
                printer_draw_text($p, "TAMBAH SALDO DEPOSIT",136,$lines);
                $lines += $line2;
                printer_select_font($p, $font_k);
                printer_draw_text($p, "Nota : ".$nonota,175,$lines);
                $lines += $line*2;
                printer_draw_text($p, "Kasir  : ".ucwords($kasir),$left0,$lines);
                $lines += $line;
                printer_draw_text($p, "Nama : ".ucwords($nama),$left0,$lines);
                $lines += $line+5; 
                printer_draw_text($p, "Riwayat 5 Transaksi Terakhir : ",$left0,$lines);
                $lines += $line+5;    
                
                $info=array('P'=>'Pembelian Mart', 'K'=>'Pembelian Kantin', 'S'=>'Deposit');                
                $query = $this->db->query("(SELECT * FROM tb_deposit_moves WHERE kode='$kode' ORDER BY id_saldo DESC LIMIT 5) ORDER BY id_saldo ");
                foreach ($query->result_array() as $rowx){
                    $fist = $rowx["transaksi"];
                    printer_draw_text($p, $info[strtoupper($fist[0])], $left0, $lines);
                    printer_draw_text($p, $this->m_deposit->tglmanusia2($rowx["tanggal"]), $left1, $lines);
                    printer_draw_text($p, $this->right_numbering($rowx["jumlah"]), $left3-20, $lines);
                    $lines += $line;
                }
                
                $que = $this->db->query("SELECT SUM(jumlah) deposit, kode FROM tb_deposit_moves 
                                         WHERE kode='$kode' GROUP BY kode");
                $jml = $que->num_rows();
                if($jml>0){
                    $roe = $que->first_row();
                    $deposit = $roe->deposit;
                }else{
                    $deposit = 0;
                }
                
                //Footer bon
                $lines += 5;
                printer_draw_line($p, $left0, $lines, $max, $lines);
                $lines += 3;
                printer_draw_text($p, "Saldo Akhir", $left1-5, $lines);
                printer_draw_text($p, $this->right_numbering($deposit), $left3-20, $lines);
                $lines += $line;
                //cetak terimakasi atau potong disini
                $lines += $line*2;
                printer_draw_text($p, "Note :", $left0, $lines); 
                $lines += $line;
                printer_draw_text($p, "Struk ini sudah sah untuk dijadikan bukti transaksi.", $left0, $lines);                
                //end cetak
                printer_delete_font($font);
                printer_end_page($p);
                printer_end_doc($p);
                printer_close($p);
                redirect(base_url('deposit'));
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

<?php if(!defined('BASEPATH')) exit ('No Direct Script Access Allowed');

class Kantin extends CI_Controller {

	function __construct(){
            parent::__construct();		
            $this->load->model('m_kantin');
	}

	function index(){
            if($this->session->userdata('status') != "mart"){
                $this->session->sess_destroy();
                $this->load->view('v_kantin',array("info"=>"gagal"));
            }else{
                if($this->session->userdata('akses') != "customer"){
                    redirect(base_url());
                }else{
                    $this->load->view('v_kantin',array("info"=>"success"));
                }                
            }
            
	}
        
        public function stan_menu(){
            $tanggal  = date('Y-m-d');
            $stan  = $this->input->post('stan');
            $cari  = $this->input->post('cari');
            if(!empty($stan)){
                $w1=" AND s.no_stan='$stan' ";
            }else{
                $w1="";
            }
            $array = array();
            $query = $this->db->query("SELECT s.*, t.nama_stan, m.jum
                                        FROM tb_stok s
                                        LEFT JOIN tb_stan t ON t.no_stan=s.no_stan
                                        LEFT JOIN (
                                            SELECT SUM(qty) AS jum, kode_barang
                                            FROM tb_stok_moves
                                            WHERE tanggal='$tanggal'
                                            GROUP BY kode_barang
                                        ) m ON m.kode_barang=s.kode_barang
                                        WHERE s.tempat='1' AND m.jum IS NOT NULL AND m.jum>0
                                        AND s.nama_barang LIKE '%$cari%' ".$w1."
                                        ORDER BY s.nama_barang ");
            foreach ($query->result_array() as $data){
                array_push($array, $data);
            }
            echo json_encode($array);
        }
        
        public function save(){
            $subtotal = $this->input->post('subtotal');
            
            $kodearr = $this->input->post('kode');
            $jumlah = $this->input->post('jumlah');
            $harga = $this->input->post('harga');
            $total = $this->input->post('total');
                        
            $datetime = date('Y-m-d H:i:s');
            $tanggal  = date('Y-m-d');
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            $kodeinduk = $sesi['nomor_induk'];
            
            if(count($kodearr)>0){  
                $stok_ks = array();
                $kd_ar = "'".implode("','", $kodearr)."'";
                $cekqq = $this->db->query("SELECT dt.*, s.nama_barang
                                           FROM (
                                                SELECT SUM(qty) AS jum, kode_barang
                                                FROM tb_stok_moves
                                                WHERE tanggal='2017-03-14'
                                                AND kode_barang IN ($kd_ar)
                                                GROUP BY kode_barang
                                           ) AS dt
                                           LEFT JOIN tb_stok s ON s.kode_barang=dt.kode_barang");
                foreach ($cekqq->result_array() as $dtcek){
                    if($dtcek["jum"]=="0"){
                        $stok_ks[$dtcek["kode_barang"]] = $dtcek["nama_barang"];
                    }
                }
                //cek apakah ada yg stoknya habis
                if(count($stok_ks)>0){ 
                    $txt = "Menu Habis:\n";
                    foreach ($stok_ks as $kd => $nama){ 
                        $txt .= $nama."\n";
                    }
                    $txt .= "Sudah terpesan oleh pelanggan lain!";
                    echo json_encode(array('status'=>'failed', 'txt'=>$txt));
                }else{
                    $sukses=0; $gagal=0;
                    $next_num = $this->m_kantin->ambilnomorbaru();
                    $kurang = 0-intval($subtotal);
                    $inser = $this->db->query("INSERT INTO trns_pemesanan 
                            (kode_trns_pemesanan, tgl, kode, subtotal, created_date, created_by)
                            VALUES
                            ('$next_num', '$tanggal', '$kodeinduk', '$subtotal', '$datetime', '$userr') ");
                    $inser2 = $this->db->query("INSERT INTO tb_deposit_moves 
                                (transaksi, tanggal, kode, jumlah, created_date, created_by )
                                VALUES
                                ('$next_num', '$tanggal', '$kodeinduk', '$kurang', '$datetime', '$userr') ");  
                    if($inser){
                        $arr_stok = array();
                        foreach ($kodearr as $key => $kode){                         
                            $subinser = $this->db->query("INSERT INTO trns_pemesanan_detail 
                                    (kode_trns_pemesanan, kode_menu, jumlah, harga, total, stat, created_date, created_by)
                                    VALUES
                                    ('$next_num', '$kode', '$jumlah[$key]', '$harga[$key]', '$total[$key]', 'proses', '$datetime', '$userr') ");

                            $terjual    = 0-intval($jumlah[$key]);
                            $arr_stok[] = " ('$next_num', '$tanggal', '$kode', '$terjual', '$datetime', '$userr' ) ";

                            if($subinser){  $sukses++; }else{ $gagal++; }
                        }
                        //INSERT STOK BARANG
                        if(count($arr_stok)>0){
                            $in_stok = implode(",", $arr_stok);
                            $inser2 = $this->db->query("INSERT INTO tb_stok_moves 
                                                          (transaksi, tanggal, kode_barang, qty, created_date, created_by )
                                                        VALUES ".$in_stok." ");                                   
                        }
                        echo json_encode(array('status'=>'success', 'txt'=>'sukses:'.$sukses.' gagal:'.$gagal.'!', 'nota'=>$next_num));
                    }else{
                        echo json_encode(array('status'=>'failed', 'txt'=>"Gagal Insert data!"));
                    }
                }
            }
        }

	function logout(){
            $this->session->sess_destroy();
            redirect(base_url());
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
            redirect(base_url('kantin'));
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
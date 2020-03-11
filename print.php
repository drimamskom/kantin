<?php
// phpinfo();
$tanggal = date("d-m-Y");
$jam = date("H:i:s");
$line  = 23;
$space = 15;
// printer_draw_text($p, 't',10, 100);
// printer_draw_text($p, 'e',25, 100);
// printer_draw_text($p, 's',40, 100);
// printer_draw_text($p, 't',500, 100);
// printer_draw_text($p, 'e',500, 120);
// printer_draw_text($p, 's',500, 140);
$left0 = 10;
$left1 = 310;
$left2 = 390;
$left3 = 440;
$max = 560;
$row = 0;
//$p = printer_open("CutePDF Writer"); 
$p = printer_open("POS80"); 
printer_set_option($p, PRINTER_MODE, "RAW"); // mode disobek (gak ngegulung kertas)
printer_start_doc($p, "Tes Printer"); 
printer_start_page($p);
$font_k = printer_create_font("Arial", 20, 11, PRINTER_FW_NORMAL, false, false, false, 0);
$font = printer_create_font("Arial", 23, 12, PRINTER_FW_NORMAL, false, false, false, 0);
printer_select_font($p, $font_k);
printer_draw_text($p, date("Y/m/d H:i:s"),350, 0);

$lines = $line+5;
printer_select_font($p, $font);
printer_draw_text($p, ".: KANTIN SMANEMA :.",120,$lines);
$lines = $lines+$line;
printer_draw_text($p, "DEPOT BU MUS",150,$lines);

$lines = ($lines+$line)+25;
printer_draw_text($p, "Pesan", $left0, $lines);
printer_draw_text($p, ":",90, $lines);
printer_draw_text($p, 'Muhammad Yunus Firmansyah',110, $lines);

// Header Bon
$lines = ($lines+$line)+10;
$pen = printer_create_pen(PRINTER_PEN_SOLID, 1, "000000");
printer_select_pen($p, $pen);
printer_draw_line($p, $left0, $lines, $max, $lines);
$lines = ($lines+$line)-15;
printer_draw_text($p, "TRANSAKSI", $left0,  $lines);
printer_draw_text($p, "QTY", 300, $lines);
printer_draw_text($p, "PRICE", 400, $lines);
$lines = ($lines+$line)+5;
printer_draw_line($p, $left0, $lines, $max, $lines);

$lines = ($lines+$line)-10;
printer_draw_text($p, "NASI GORENG", $left0, $lines);
printer_draw_text($p, "10", $left1, $lines);
printer_draw_text($p, "Rp.", $left2, $lines);
printer_draw_text($p, "6.000", $left3, $lines);

$lines = $lines+$line;
printer_draw_text($p, "NASI GORENG", $left0, $lines);
printer_draw_text($p, "10", $left1, $lines);
printer_draw_text($p, "Rp.", $left2, $lines);
printer_draw_text($p, "6.000", $left3, $lines);

$lines = $lines+$line;
printer_draw_text($p, "NASI GORENG", $left0, $lines);
printer_draw_text($p, "10", $left1, $lines);
printer_draw_text($p, "Rp.", $left2, $lines);
printer_draw_text($p, "6.000", $left3, $lines);

$lines = ($lines+$line)+10;
printer_draw_line($p, $left0, $lines, $max, $lines);
$lines = ($lines+$line)-15;
printer_draw_text($p, "Total", $left1-19, $lines);
printer_draw_text($p, "Rp.", $left2, $lines);
printer_draw_text($p, "10.000", $left3, $lines);
$lines = ($lines+$line)+5;
printer_draw_line($p, $left0, $lines, $max, $lines);

$lines = $lines+50;
printer_draw_text($p, "Terima Kasih Atas Kunjungan Anda", 50, $lines);

printer_delete_font($font);
printer_end_page($p);
printer_end_doc($p);
printer_close($p);
?>
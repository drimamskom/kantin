<?php          
$sesi = $this->session->get_userdata();
$userr = $sesi['username'];
$fullname = $sesi['fullname'];
$akses = $sesi['akses'];
?>
<!-- Bootsrtap-select -->
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/bootstrap-select/bootstrap-select.css" type="text/css"/>
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/bootstrap-select/bootstrap-select.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/bootstrap-select/i18n/defaults-en_US.js"></script>
<script type="text/javascript">   
	$(function(){ 
            $('#table_cust').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": false,
                "ordering": false,
                "info": false,
                "autoWidth": false,
                "pageLength": 25,
                "processing": true,
                "serverSide": true,
	        "ajax": {
	            "url": "<?php echo base_url('trnspengisian/data'); ?>",
                    "data": function( d ) {
                                var send = $('#formFilter').serializeArray();
                                $.each(send, function(i, v) {
                                    d[v.name] = v.value;         
                                });
                            },      
                    "dataType": "json",
                    "type": "POST"
	        },
                "initComplete": function( settings, json ) {
                        $('#all_subtotal').html("Rp, "+addPeriod(json.totalz));
                    },
	        "columnDefs": [
		        {"className": "dt-center", "targets": [0,8]}
		    ],
	        "columns": [
		        { "data": "urutan" },
		        { "data": "kode_faktur" },
		        { "data": "nama_supplier" },
		        { "data": "pembayaran" },
		        { "data": "tgl" },
		        { "data": "jatuh_tempo" },
		        { "data": "hari_jatuh_tempo" },
		        { "data": "subtotal" },
		        { "data": "button" },
	        ]
	    });		
                
            $('.selectpicker').selectpicker({
                size: 10
            });

            $(document).on("click","#btncari",function(){
                    var table = $('#table_cust').DataTable(); 
                    table.ajax.reload( function ( json ) {
                        $('#all_subtotal').html("Rp, "+addPeriod(json.totalz));
                    });
            });
                    
	    $(document).on("click","#btnexport",function(){
                    var cari = $("#txtcari").val();
                    var tgl_mulai = $("#tgl_mulai").val();
                    var tgl_selesai = $("#tgl_selesai").val();
                    var supplier = $("#supplier").val();
                    $("#carian").val(cari);
                    $("#tgl_mulaix").val(tgl_mulai);
                    $("#tgl_selesaix").val(tgl_selesai);
                    $("#supplierx").val(supplier);
                    $('#export_form').submit();
	    });

            $(document).on("click",".btnview",function(){
                var id = $(this).attr("idnex");
                var value = { id:id };
                resetForm();
                $.ajax({
                    url: "<?php echo base_url('trnspengisian/cari'); ?>",
                    type: "POST",
                    data : value,
                    dataType: "json",
			success: function(data){
                            var head = data.header[0];                            
                            $("#text_kdpembelian").html(head.kode_faktur);
                            $("#view_txtnota").html(head.kode_faktur);
                            $("#view_txtsupplier").html(head.nama_supplier);
                            $("#view_txtpembayaran").html(head.pembayaran);
                            $("#view_txttanggal").html(head.tgl);
                            $("#view_txtjatuhtempo").html(head.jatuh_tempo);
                            $("#view_txttempo").html(head.hari_jatuh_tempo+" Hari");
                            
                            $("#view_txtdiskon1").html(head.diskon);
                            $("#view_txtdiskon2").html(head.diskon2);
                            $("#view_txtsubtotal1").html(addPeriod(head.total));
                            $("#view_txtcash").html(head.diskon);
                            $("#view_txtppn").html(head.harga_ppn);
                            $("#view_txtbayar").html(addPeriod(head.subtotal));

                            var detail = data.detail;
                            var alltot = 0;
                            var alldis = 0;
                            var allsub = 0;
                            var allhpp = 0;
                            for (var i = 0; i < detail.length; i++) {
                                alltot = alltot+parseFloat(detail[i].total);
                                alldis = alldis+parseFloat(detail[i].diskon1);
                                allsub = allsub+parseFloat(detail[i].subtotal);
                                allhpp = allhpp+parseFloat(detail[i].harga_hpp);
                                var n = i+1;                                        
                                var tr = '<tr class="temp-row">'+  
                                        '<td align="center">'+ n +'</td>'+  
                                        '<td>'+ detail[i].nama_barang +' </td>'+ 
                                        '<td>Rp. '+ addPeriod(detail[i].harga_satuan) +'</td>'+ 
                                        '<td>'+ detail[i].qty +'</td>'+ 
                                        '<td>'+ detail[i].satuan +'</td>'+ 
                                        '<td>Rp. '+ addPeriod(detail[i].total) +'</td>'+ 
                                        '<td>'+ detail[i].diskon1 +'</td>'+ 
                                        '<td>Rp. '+ addPeriod(detail[i].subtotal) +'</td>'+ 
                                        '<td>Rp. '+ addPeriod(detail[i].harga_hpp) +'</td>'+ 
                                '</tr>';  
                                $('#list_infois2 > tbody').append(tr);
                            }
                    
                            var hasil = addPeriod(head.subtotal);
                            $('#view_total').html("Rp. "+addPeriod(alltot));
                            $('#view_diskon').html(alldis);
                            $('#view_subtotal').html("Rp. "+addPeriod(hasil));
                            $('#view_hpp').html("Rp. "+addPeriod(allhpp));
                            $("#modalcust2").modal('show');
			}
                });
	    });                
                
            //if the letter is not digit then don't type anything
            $(".number-only").keypress(function (e) {
                    if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                            return false;
                    }
            });               

            $('#formFilter').on('keyup keypress', function(e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) { 
                    e.preventDefault();
                    return false;
                }
            });

            $("#txtcari").keypress(function (e) {
                var key = e.which;
                if(key == 13){
                    e.preventDefault();
                    var table = $('#table_cust').DataTable(); 
                    table.ajax.reload( function ( json ) {
                        $('#all_subtotal').html("Rp, "+addPeriod(json.totalz));
                    });
                }
            });

	}); 
                
        function resetForm(){       
            $("#text_kdpembelian").html(" "); 
            $("#view_txtnota").html(" "); 
            $("#view_txtsupplier").html(" "); 
            $("#view_txttanggal").html(" ");
            $('#view_subtotal').html(" "); 
            $('.temp-row').remove();
	}
        
        function addPeriod(nStr){
            if(nStr==null){
                return "0";
            }else{
                nStr += '';
                x = nStr.split('.');
                x1 = x[0];
                x2 = x.length > 1 ? '.' + x[1] : '';
                var rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) {
                    x1 = x1.replace(rgx, '$1' + '.' + '$2');
                }
                return x1 + x2;
            }
        }
</script> 
<style>
th.dt-center, td.dt-center { text-align: center; }
.table {
    border: 1px solid #ccc;
}
.table thead > th{
    border: 1px solid #fff;
}
.table tbody > tr td {
    padding: 3px;
    /* border: 1px solid #ccc; */
}
.table tfoot > tr td {
    padding: 3px;
    /* border: 1px solid #ccc; */
}
.table tr td {
    border: 1px solid #ccc;
}
</style>
<div class="row">
    <div class="col-md-12">
        <!-- Default box -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Laporan Transaksi Pengisian Kantin</h3>
            </div>
            <div class="box-body"> 
                <form id="export_form" action="<?php echo base_url('trnspengisian/export'); ?>" method="post" target="_self">
                    <input type="hidden" id="carian" name="cari" value=""/>
                    <input type="hidden" id="tgl_mulaix" name="tgl_mulai" value=""/>
                    <input type="hidden" id="tgl_selesaix" name="tgl_selesai" value=""/>
                    <input type="hidden" id="supplierx" name="supplier" value=""/>
                    <input type="hidden" name="umpan" value="umpan"/>
                    <input type="hidden" name="draw" value="1"/>
                    <input type="hidden" name="start" value="0"/>
                    <input type="hidden" name="length" value=""/>
                </form>
                <p>
                <a href="<?php echo base_url('pembelian'); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Tambah Transksi</a>
                <button type="button" class="btn btn-success" id="btnexport"><i class="fa fa-file-excel-o"></i> Export Excel</button>
                </p>
                <form id="formFilter" class="form-horizontal">
                    <div class="col-sm-9" style="padding-left:0px">
                        <ul class="nav nav-pills">
                            <li>
                                <div class="input-group">
                                    <div class="input-group-addon label-warning">Tanggal</div>
                                    <input type="text" class="form-control pull-right datepickerr" name="tgl_mulai" id="tgl_mulai" placeholder="Tgl Mulai" style="width:100px;">
                                    <div class="input-group-addon">
                                        s/d
                                    </div>
                                    <input type="text" class="form-control pull-right datepickerr" name="tgl_selesai" id="tgl_selesai" placeholder="Tgl Selesai" style="width:100px;">
                                </div>
                            </li>
                            <li style="padding-left:10px;">
                                <div class="input-group">
                                    <div class="input-group-addon label-warning">Supplier</div>
                                    <select class="form-control selectpicker" name="supplier" id="supplier" data-width="200px" title="Pilih...">
                                        <option value="">All</option>
                                        <?php
                                          $query = $this->db->query("SELECT * FROM tb_supplier WHERE tempat='1' ");
                                          foreach ($query->result_array() as $data){
                                              echo "<option value='".$data['kode_supplier']."'>".$data['kode_supplier']." - ".$data['nama_supplier']."</option>";
                                          }
                                        ?>
                                    </select>
                                </div>
                            </li>
                        </ul>                
                    </div>
                    <div class="col-sm-3" style="padding-right:0px">
                        <div class="input-group">
                            <input type="text" class="form-control" id="txtcari" name="cari" placeholder="Pencarian"/>
                            <span class="input-group-btn">
                              <button type="button" class="btn btn-info" id="btncari"><i class="fa fa-search"></i> Cari</button>
                            </span>
                        </div><!-- /input-group -->
                    </div>
                </form>
                
                <table id="table_cust" width="100%" class="table table-bordered table-striped">
                    <thead class="label-primary">
                      <tr>
                            <th style="width:5%">No</th>
                            <th style="width:10%">No. Faktur</th>
                            <th style="width:20%">Supplier</th>
                            <th style="width:10%">Pembayaran</th>
                            <th style="width:10%">Tgl</th>
                            <th style="width:10%">Jatuh Tempo</th>
                            <th style="width:10%">Hari</th>
                            <th style="width:10%">Subtotal</th>
                            <th style="width:15%">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7"><b><span class="pull-right" style="margin-right:10px;">Totals</span></b></td>
                            <td><b><span id="all_subtotal"></span></b></td>
                            <td></td>
                        </tr>           
                    </tfoot>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col -->
</div><!-- /.row -->    
        
<div id="modalcust2" class="modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">Detail <small>#<span id="text_kdpembelian"></span></small></h4>
            </div>
            <!--modal body-->
            <div class="modal-body">
                <!-- info row -->
                <div class="row">
                    <div class="col-sm-12">
                        <table width="100%">
                            <tr>
                                <td width="11%">No. Faktur</td>
                                <td width="1%">:</td>
                                <td width="30%"><span id="view_txtnota"></span></td>
                                <td width="29%"></td>
                                <td width="11%">Tanggal</td>
                                <td width="1%">:</td>
                                <td width="17%"><span id="view_txttanggal"></span></td>
                            </tr>
                            <tr>
                                <td>Supplier</td>
                                <td>:</td>
                                <td><span id="view_txtsupplier"></span></td>
                                <td></td>
                                <td>Jatuh Tempo</td>
                                <td>:</td>
                                <td><span id="view_txtjatuhtempo"></span></td>
                            </tr>
                            <tr>
                                <td>Pembayaran</td>
                                <td>:</td>
                                <td><span id="view_txtpembayaran"></span></td>
                                <td></td>
                                <td>Tempo</td>
                                <td>:</td>
                                <td><span id="view_txttempo"></span></td>
                            </tr>
                        </table>
                    </div><!-- /.col -->
                </div><!-- /.row -->
                <br>
                <!-- Table row -->
                <div class="row">
                    <div class="col-xs-12 table-responsive">
                        <table id="list_infois2" width="100%" class="table table-striped">
                            <thead class="label-primary">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">Nama Barang</th>
                                    <th width="15%">Harga Satuan</th>
                                    <th width="5%">Qty</th>
                                    <th width="10%">Satuan</th>
                                    <th width="10%">Total</th>
                                    <th width="10%">Diskon(%)</th>
                                    <th width="10%">Subtotal</th>
                                    <th width="10%">HPP</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5"><b><span class="pull-right" style="margin-right:10px;">Subtotal</span></b></td>
                                    <td><b><span id="view_total"></span></b></td>
                                    <td><b><span id="view_diskon"></span></b></td>
                                    <td><b><span id="view_subtotal"></span></b></td>
                                    <td><b><span id="view_hpp"></span></b></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div><!-- /.col -->
                </div><!-- /.row --> 
                
                <!-- Footer row -->
                <div class="row">
                    <div class="col-sm-12">
                        <table width="100%">
                            <tr>
                                <td width="70%"></td>
                                <td width="12%">Discount 1</td>
                                <td width="1%">:</td>
                                <td width="17%"><span id="view_txtdiskon1"></span></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>Discount 2</td>
                                <td>:</td>
                                <td><span id="view_txtdiskon2"></span></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>Subtotals</td>
                                <td>:</td>
                                <td><span id="view_txtsubtotal1"></span></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>Cash Disc</td>
                                <td>:</td>
                                <td><span id="view_txtcash"></span></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>Netto PPN 1</td>
                                <td>:</td>
                                <td><span id="view_txtppn"></span></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>Harus Bayar</td>
                                <td>:</td>
                                <td><span id="view_txtbayar"></span></td>
                            </tr>
                        </table>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.body -->                
            <!--modal-footer-->
            <div class="modal-footer">
                <button type="button" class="btn btn-info" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
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
	            "url": "<?php echo base_url('trnskasir/data'); ?>",
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
                        RefresFooterTable(json.tot);
                    },
	        "columnDefs": [
		        {"className": "dt-center", "targets": [0,7]}
		    ],
	        "columns": [
		        { "data": "urutan" },
		        { "data": "kode_trns_penjualan" },
		        { "data": "tgl" },
		        { "data": "kasir" },
		        { "data": "nama_customer" },
		        { "data": "subtotal" },
		        { "data": "bayar" },
		        { "data": "button" },
	        ]
	    });		
                
            $('.selectpicker').selectpicker({
                size: 4
            });

            $(document).on("click","#btncari",function(){
                    var table = $('#table_cust').DataTable(); 
                    table.ajax.reload( function ( json ) {
                        RefresFooterTable(json.tot);
                    });
            });
                    
	    $(document).on("click","#btnexport",function(){
                    var cari = $("#txtcari").val();
                    var tgl_mulai = $("#tgl_mulai").val();
                    var tgl_selesai = $("#tgl_selesai").val();
                    var kasir = $("#kasir").val();
                    $("#carian").val(cari);
                    $("#tgl_mulaix").val(tgl_mulai);
                    $("#tgl_selesaix").val(tgl_selesai);
                    $("#kasirx").val(kasir);
                    $("#reportx").val("report");					
                    $('#export_form').submit();
	    });
		
	    $(document).on("click","#btnreport",function(){
                    var cari = $("#txtcari").val();
                    var tgl_mulai = $("#tgl_mulai").val();
                    var tgl_selesai = $("#tgl_selesai").val();
                    var kasir = $("#kasir").val();
                    if( (tgl_mulai.length==0) || (tgl_selesai.length==0) ){
                        swal('Peringatan',"Tanggal Harus diisi dengan bulanan...",'warning');
                    }else{
                        $("#carian").val(cari);
                        $("#tgl_mulaix").val(tgl_mulai);
                        $("#tgl_selesaix").val(tgl_selesai);
                        $("#kasirx").val(kasir);
                        $("#reportx").val("bulanan");
                        $('#export_form').submit();
                    }
	    });

            $(document).on("click",".btnview",function(){
                var id = $(this).attr("idnex");
                var value = { id:id };
                resetForm();
                $.ajax({
                    url: "<?php echo base_url('trnskasir/cari'); ?>",
                    type: "POST",
                    data : value,
                    dataType: "json",
			success: function(data){
                            var head = data.header[0];                            
                            $("#text_kdpembayaran").html(head.kode_trns_penjualan);
                            $("#view_txtnota").html(head.kode_trns_penjualan);                            
                            $("#view_txtpetugas").html(head.kasir);
                            $("#view_txtpelanggan").html(head.nama_customer);
                            $("#view_txttanggal").html(head.tgl);

                            var hasil = addPeriod(head.subtotal);
                            $('#view_subtotal').html("Rp. "+hasil);

                            var detail = data.detail;
                            for (var i = 0; i < detail.length; i++) {
                                var n = i+1;
                                var tr = '<tr class="temp-row">'+  
                                        '<td align="center">'+ n +'</td>'+  
                                        '<td>'+ detail[i].nama_barang +' - '+ detail[i].kode_barang +'</td>'+ 
                                        '<td>Rp. '+ addPeriod(detail[i].harga) +'</td>'+ 
                                        '<td>'+ detail[i].jumlah +'</td>'+ 
                                        '<td>'+ detail[i].satuan +'</td>'+ 
                                        '<td>Rp. '+ addPeriod(detail[i].total) +'</td>'+ 
                                '</tr>';  
                                $('#list_infois2 > tbody').append(tr);
                            }
                            $("#btncetakulang").attr('href', '<?php echo base_url();?>trnskasir/cetak/'+head.kode_trns_penjualan);
                            $("#modalcust2").modal('show');
			}
                });
	    });   
            
            $(document).on( "click",".btnhapus", function() {
                    var id = $(this).attr("idnex");
                    var name = $(this).attr("namenex");
                    swal({   
                        title: "Delete Data?",   
                        text: "Are you Sure Delete Data : "+name+" ?",   
                        type: "warning",   
                        showCancelButton: true,   
                        confirmButtonColor: "#DD6B55",   
                        confirmButtonText: "Delete",   
                        closeOnConfirm: true }, 
                        function(){   
                            var value = { id:id };
                            $.ajax({
                                url : "<?php echo base_url('trnskasir/hapus'); ?>",
                                type: "POST",
                                data : value,
                                dataType: "json",
                                success: function(data){
                                    if(data.status == 'success'){
                                        $.notify('Successfull delete Data');
                                        var table = $('#table_cust').DataTable(); 
                                        table.ajax.reload( function ( json ) {
                                            RefresFooterTable(json.tot);
                                        });
                                    }else{
                                        swal("Error","Can't delete Data, error : "+name,"error");
                                    }
                                }
                            });
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
                        RefresFooterTable(json.tot);
                    });
                }
            });

	}); 
            
        function RefresFooterTable(tot){
            $('#all_subtotal').html("Rp, "+addPeriod(tot));
            $('#all_bayar').html("Rp, "+addPeriod(tot));
        }   
        
        function resetForm(){       
            $("#text_kdpembayaran").html(" "); 
            $("#view_txtnota").html(" "); 
            $("#view_txtpetugas").html(" "); 
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
                <h3 class="box-title">Laporan Transaksi Kasir</h3>
            </div>
            <div class="box-body"> 
                <form id="export_form" action="<?php echo base_url('trnskasir/export'); ?>" method="post" target="_self">
                    <input type="hidden" id="carian" name="cari" value=""/>
                    <input type="hidden" id="tgl_mulaix" name="tgl_mulai" value=""/>
                    <input type="hidden" id="tgl_selesaix" name="tgl_selesai" value=""/>
                    <input type="hidden" id="kasirx" name="kasir" value=""/>
                    <input type="hidden" id="reportx" name="report" value=""/>
                    <input type="hidden" name="umpan" value="umpan"/>
                    <input type="hidden" name="draw" value="1"/>
                    <input type="hidden" name="start" value="0"/>
                    <input type="hidden" name="length" value=""/>
                </form>
                <p>
                <a href="<?php echo base_url('kasir'); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Tambah Transksi</a>
                <button type="button" class="btn btn-success" id="btnexport"><i class="fa fa-file-excel-o"></i> Export Excel</button>
                <button type="button" class="btn btn-warning" id="btnreport"><i class="fa fa-file-excel-o"></i> Report Blnan</button>
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
                                    <div class="input-group-addon label-warning">Kasir</div>
                                    <select class="form-control selectpicker" name="kasir" id="kasir" data-width="150px" title="Pilih...">
                                        <option value="">All</option>
                                        <?php
                                          $query = $this->db->query("SELECT * FROM tb_user WHERE akses='kasir' ");
                                          foreach ($query->result_array() as $data){
                                              echo "<option value='".$data['username']."'>".$data['username']."</option>";
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
                            <th style="width:3%">No</th>
                            <th style="width:10%">No. Nota</th>
                            <th style="width:8%">Tgl</th>
                            <th style="width:8%">Kasir</th>
                            <th style="width:18%">Pelanggan</th>
                            <th style="width:20%">Subtotal</th>
                            <th style="width:20%">Bayar</th>
                            <th style="width:12%">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5"><b><span class="pull-right" style="margin-right:10px;">Totals</span></b></td>
                            <td><b><span id="all_subtotal"></span></b></td>
                            <td><b><span id="all_bayar"></span></b></td>
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
                <h4 class="modal-title">Detail <small>#<span id="text_kdpembayaran"></span></small></h4>
            </div>
            <!--modal body-->
            <div class="modal-body">
              <!-- info row -->
                <div class="row">
                    <div class="col-sm-12">
                        <table width="100%" style="margin-left:5px;">
                            <tr>
                                <td width="9%">No. Nota</td>
                                <td width="1%">:</td>
                                <td width="90%"><span id="view_txtnota"></span></td>
                            </tr>
                            <tr>
                                <td>Petugas</td>
                                <td>:</td>
                                <td><span id="view_txtpetugas"></span></td>
                            </tr>
                            <tr>
                                <td>Pelanggan</td>
                                <td>:</td>
                                <td><span id="view_txtpelanggan"></span></td>
                            </tr>
                            <tr>
                                <td>Tanggal</td>
                                <td>:</td>
                                <td><span id="view_txttanggal"></span></td>
                            </tr>
                        </table>
                    </div><!-- /.col -->
                </div><!-- /.row -->

                <!-- Table row -->
                <div class="row">
                    <div class="col-xs-12 table-responsive">
                        <table id="list_infois2" width="100%" class="table table-striped">
                            <thead class="label-primary">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="40%">Nama Barang</th>
                                    <th width="15%">Harga</th>
                                    <th width="10%">Jumlah</th>
                                    <th width="10%">Satuan</th>
                                    <th width="20%">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5"><b><span class="pull-right" style="margin-right:10px;">Subtotal</span></b></td>
                                    <td><b><span id="view_subtotal"></span></b></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div><!-- /.col -->
                </div><!-- /.row --> 
            </div><!-- /.body -->                
            <!--modal-footer-->
            <div class="modal-footer">
                <a href="" target="_self" class="btn btn-danger" id="btncetakulang" ><i class="fa fa-print"></i>&nbsp; Cetak Ulang</a>
                <button type="button" class="btn btn-info" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
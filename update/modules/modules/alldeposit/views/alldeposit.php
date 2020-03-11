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
	            "url": "<?php echo base_url('alldeposit/data'); ?>",
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
		        {"className": "dt-center", "targets": [0,1]},
		        {"className": "dt-right", "targets": [3]}
		    ],
	        "columns": [
		        { "data": "urutan" },
		        { "data": "kode_customer" },
		        { "data": "nama_customer" },
		        { "data": "jumlah" },
	        ]
	    });		
                
            $('.selectpicker').selectpicker({
                size: 4
            });

            $(document).on("click","#btncari",function(){
                    var table = $('#table_cust').DataTable(); 
                    table.ajax.reload( function ( json ) {
                        $('#all_subtotal').html("Rp, "+addPeriod(json.totalz));
                    });
            });
                    
	    $(document).on("click","#btnexport",function(){
                    var cari = $("#txtcari").val();
                    var depot = $("#depot").val();
                    $("#carian").val(cari);
                    $("#depotx").val(depot);
                    $('#export_form').submit();
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
th.dt-right, td.dt-right { text-align: right; }
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
                <h3 class="box-title">Semua Deposit</h3>
            </div>
            <div class="box-body"> 
                <form id="export_form" action="<?php echo base_url('alldeposit/export'); ?>" method="post" target="_self">
                    <input type="hidden" id="carian" name="cari" value=""/>
                    <input type="hidden" id="depotx" name="depot" value=""/>
                    <input type="hidden" name="umpan" value="umpan"/>
                    <input type="hidden" name="draw" value="1"/>
                    <input type="hidden" name="start" value="0"/>
                    <input type="hidden" name="length" value=""/>
                </form>
                
                <form id="formFilter" class="form-horizontal">
                    <div class="col-sm-9" style="padding-left:0px">
                        <ul class="nav nav-pills">
                            <a href="<?php echo base_url('deposit'); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Tambah Transksi</a>
                            <button type="button" class="btn btn-success" id="btnexport"><i class="fa fa-file-excel-o"></i> Export Excel</button>
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
                            <th style="width:10%">Kode</th>
                            <th style="width:35%">Pelanggan</th>
                            <th style="width:40%">Sisa Deposit</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><b><span class="pull-right" style="margin-right:10px;">Totals</span></b></td>
                            <td><b><span id="all_subtotal"></span></b></td>
                        </tr>             
                    </tfoot>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col -->
</div><!-- /.row -->    
        
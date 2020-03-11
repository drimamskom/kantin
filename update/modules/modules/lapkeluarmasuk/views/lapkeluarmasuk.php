<!-- Bootsrtap-select -->
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/datatables/dataTables.rowsGroup.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/bootstrap-select/bootstrap-select.css" type="text/css"/>
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/bootstrap-select/bootstrap-select.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/bootstrap-select/i18n/defaults-en_US.js"></script>
<script type="text/javascript">  
	$(function(){ 
		$('#table_cust').DataTable({
                    "paging": false,
                    "lengthChange": false,
                    "searching": false,
                    "ordering": false,
                    "info": false,
                    "autoWidth": false,
                    "pageLength": 25,
                    "processing": true,
                    "serverSide": true,
                    "ajax": {
                        "url": "<?php echo base_url('lapkeluarmasuk/data'); ?>",
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
                        var datajson = json.totalz;
                        $('#footer1').html("Rp. "+addPeriod(datajson.masuk));
                        $('#footer2').html("Rp. "+addPeriod(datajson.keluar));
                        $('#footer3').html("Rp. "+addPeriod(datajson.total));
                    },
                    "rowsGroup": [1],
                    "columnDefs": [
                            {"className": "dt-center", "targets": [0,1]},
                        ],
                    "columns": [
                            { "data": "urutan" },
                            { "data": "tanggal" },
                            { "data": "ket" },
                            { "data": "masuk" },
                            { "data": "keluar" },
                            { "data": "total" },
                    ]
                });	
                
                $('.selectpicker').selectpicker({
                    size: 4
                });

                $(document).on("click","#btncari",function(){
                        var table = $('#table_cust').DataTable(); 
                        table.ajax.reload( function ( json ) {
                            var datajson = json.totalz;
                            $('#footer1').html("Rp. "+addPeriod(datajson.masuk));
                            $('#footer2').html("Rp. "+addPeriod(datajson.keluar));
                            $('#footer3').html("Rp. "+addPeriod(datajson.total));
                        });
                });        
                                       
                $(document).on("click","#btnexport",function(){
                        var cari = $("#txtcari").val();
                        var tgl_mulai = $("#tgl_mulai").val();
                        var tgl_selesai = $("#tgl_selesai").val();
                        $("#carian").val(cari);
                        $("#tgl_mulaix").val(tgl_mulai);
                        $("#tgl_selesaix").val(tgl_selesai);
                        $('#export_form').submit();
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
                        var table = $('#table_cust').DataTable(); 
                        table.ajax.reload( function ( json ) {
                            var datajson = json.totalz;                            
                            $('#footer1').html("Rp. "+addPeriod(datajson.masuk));
                            $('#footer2').html("Rp. "+addPeriod(datajson.keluar));
                            $('#footer3').html("Rp. "+addPeriod(datajson.total));
                        });
                    }
                });
	});
        
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
    padding: 1px;
    vertical-align: middle;
    /* border: 1px solid #ccc; */
}
.table tfoot > tr td {
    padding: 3px 1px;
    /* border: 1px solid #ccc; */
}
.table tr td {
    border: 1px solid #ccc;
}
.fot-style{
    font-weight:bold;
}
</style>
<div class="row">
    <div class="col-md-12">
        <!-- Default box -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Laporan Keluar Masuk</h3>
            </div>
            <div class="box-body"> 
                <form id="export_form" action="<?php echo base_url('lapkeluarmasuk/export'); ?>" method="post" target="_self">
                    <input type="hidden" id="carian" name="cari" value=""/>
                    <input type="hidden" id="tgl_mulaix" name="tgl_mulai" value=""/>
                    <input type="hidden" id="tgl_selesaix" name="tgl_selesai" value=""/>
                    <input type="hidden" name="umpan" value="umpan"/>
                    <input type="hidden" name="draw" value="1"/>
                    <input type="hidden" name="start" value="0"/>
                    <input type="hidden" name="length" value=""/>
                </form>  
                
                <div class="table-toolbarnya">
                    <form id="formFilter" class="form-horizontal">
                        <div class="col-sm-9" style="padding-left:0px">
                            <ul class="nav nav-pills">
                                <li><button type="button" class="btn btn-success" id="btnexport"><i class="fa fa-file-excel-o"></i> Export Excel</button></li>
                                <li style="padding-left:10px">
                                    <div class="input-group">
                                        <div class="input-group-addon label-warning">Tanggal</div>
                                        <input type="text" class="form-control pull-right datepickerr" name="tgl_mulai" id="tgl_mulai" placeholder="Tgl Mulai" style="width:100px;">
                                        <div class="input-group-addon">
                                            s/d
                                        </div>
                                        <input type="text" class="form-control pull-right datepickerr" name="tgl_selesai" id="tgl_selesai" placeholder="Tgl Selesai" style="width:100px;">
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
                </div><!-- /.table-toolbarnya-->

                <table id="table_cust" width="100%" class="table table-bordered table-striped">
                    <thead class="label-primary">
                      <tr>
                            <th style="width:5%">No</th>
                            <th style="width:10%">Tanggal</th>
                            <th style="width:30%">Keterangan</th>
                            <th style="width:15%">Pemasukan</th>
                            <th style="width:15%">Pengeluaran</th>
                            <th style="width:15%">Total</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align:right;padding-right:10px;"><span class="fot-style">Subtotal</span></td>
                            <td><span id="footer1" class="fot-style"></span></td>
                            <td><span id="footer2" class="fot-style"></span></td>
                            <td><span id="footer3" class="fot-style"></span></td>
                        </tr>           
                    </tfoot>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col -->
</div><!-- /.row -->    
<!-- Bootsrtap-select -->
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
                        "url": "<?php echo base_url('laphutang/data'); ?>",
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
                        addFooter(json);
                    },
                    "columnDefs": [
                        {"className": "dt-center", "targets": [0,5,6,8]}
                    ],
                    "columns": [
                            { "data": "urutan" },
                            { "data": "nama_barang" },
                            { "data": "satuan" },
                            { "data": "harga_beli" },
                            { "data": "stok" },
                            { "data": "jumstok" },
                            { "data": "laku" },
                            { "data": "jumlaku" },
                            { "data": "retur" },
                            { "data": "jumretur" },
                            { "data": "sisa" },
                            { "data": "jumsisa" },
                    ]
                }); 		
            
                $('.selectpicker').selectpicker({
                    size: 10
                });

                $(document).on("click","#btncari",function(){
                        var table = $('#table_cust').DataTable(); 
                        table.ajax.reload( function ( json ) {
                            addFooter(json);
                        });
                });

                $(document).on("click","#btnpilih",function(){
                        var table = $('#table_cust').DataTable(); 
                        table.ajax.reload( function ( json ) {
                            addFooter(json);
                        });
                });                

                $(document).on("click","#btncetak",function(){                        
                        var supplier = $('#supplier').val();
                        var tgl_isi  = $('#tgl_isi').val();                        
                        $('#txtsupplier').val(supplier);
                        $('#txttanggal').val(tgl_isi);
                        $("#myform").attr('action', '<?php echo base_url('laphutang/cetak');?>');
                        $("#myform").submit();
                });                

                $(document).on("click","#btnexcel",function(){                        
                        var supplier = $('#supplier').val();
                        var tgl_isi  = $('#tgl_isi').val();                        
                        $('#txtsupplier').val(supplier);
                        $('#txttanggal').val(tgl_isi);
                        $("#myform").attr('action', '<?php echo base_url('laphutang/excel');?>');
                        $("#myform").submit();
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
                        var table = $('#table_cust').DataTable(); 
                        table.ajax.reload( function ( json ) {
                            addFooter(json);
                        });
                    }
                });
	});            
        function addFooter(json){
            var datajson = json.totalz;
            $('#jumstok').html("Rp. "+addPeriod(datajson.jumstok));
            $('#jumlaku').html("Rp. "+addPeriod(datajson.jumlaku));
            $('#jumretur').html("Rp. "+addPeriod(datajson.jumretur));
            $('#jumsisa').html("Rp. "+addPeriod(datajson.jumsisa));
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
    vertical-align: middle;
    /* border: 1px solid #ccc; */
}
.table tfoot > tr td {    
    padding: 3px;
}
.table tr td {
    border: 1px solid #ccc;
}
.inp-gede{
    font-weight:bold;
    text-align:right;
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
                <h3 class="box-title">Laporan Hutang Kantin Per Hari</h3>
            </div>
            <div class="box-body">
                <div class="table-toolbarnya">
                    <form id="formFilter" class="form-horizontal">
                        <div class="col-sm-9" style="padding-left:0px">
                            <ul class="nav nav-pills">
                                <li>
                                    <div class="input-group">
                                        <div class="input-group-addon label-warning">Supplier</div>
                                        <select class="form-control selectpicker" name="supplier" id="supplier" data-width="150px" required>
                                            <option value="">Pilih...</option>
                                            <?php
                                              $query = $this->db->query("SELECT * FROM tb_supplier WHERE tempat='1' AND aktif='1' ");
                                              foreach ($query->result_array() as $data){
                                                  echo "<option value='".$data['kode_supplier']."'>".$data['nama_supplier']."</option>";
                                              }
                                            ?>
                                        </select>
                                    </div>
                                </li>
                            </ul>   
                            <ul class="nav nav-pills" style="padding-top:2px;">                                
                                <li>
                                    <div class="input-group">
                                        <div class="input-group-addon label-warning">Tanggal</div>
                                        <input type="text" class="form-control pull-right datepickerr" name="tgl_isi" id="tgl_isi" placeholder="Tgl Pengisian Stok" value="<?php echo date('d/m/Y');?>" style="width:150px;" required>
                                    </div>
                                </li>
                                <li style="padding-left:10px;">
                                    <button type="button" class="btn btn-primary" id="btnpilih"><i class="fa fa-check"></i> Pilih</button>
                                </li>
                                <li style="padding-left:5px;">
                                    <button type="button" class="btn btn-danger" id="btncetak"><i class="fa fa-print"></i> Cetak</button>
                                </li>
                                <li style="padding-left:5px;">
                                    <button type="button" class="btn btn-success" id="btnexcel"><i class="fa fa-file-excel-o"></i> Excel</button>
                                </li>
                            </ul>
                        </div>
                        <div class="col-sm-3" style="padding-right:0px;padding-top:37px;">
                            <div class="input-group">
                                <input type="text" class="form-control" id="txtcari" name="cari" placeholder="Pencarian"/>
                                <span class="input-group-btn">
                                  <button type="button" class="btn btn-info" id="btncari"><i class="fa fa-search"></i> Cari</button>
                                </span>
                            </div><!-- /input-group -->
                        </div>
                    </form>
                </div><!-- /.table-toolbarnya-->                
                <form id="myform" action="" target="_self" method="post">
                    <input type="hidden" id="txtsupplier" name="supplier">
                    <input type="hidden" id="txttanggal" name="tgl_isi">
                    <input type="hidden" name="umpan" value="umpan"/>
                    <input type="hidden" name="draw" value="1"/>
                </form>
                <table id="table_cust" width="100%" class="table table-bordered table-striped">
                    <thead class="label-primary">
                        <tr>
                            <th style="width:3%;vertical-align: middle;padding-right:10px;" rowspan="2">No</th>
                            <th style="width:30%;vertical-align: middle;" rowspan="2">Nama Menu</th>
                            <th style="width:5%;vertical-align: middle;" rowspan="2">Satuan</th>
                            <th style="width:10%;vertical-align: middle;" rowspan="2">harga Beli</th>
                            <th style="width:13%;vertical-align: middle;" colspan="2"><center>Stok</center></th>
                            <th style="width:13%;vertical-align: middle;" colspan="2"><center>Laku</center></th>
                            <th style="width:13%;vertical-align: middle;" colspan="2"><center>Retur</center></th>
                            <th style="width:13%;vertical-align: middle;" colspan="2"><center>Sisa</center></th>
                        </tr>
                        <tr>
                            <th style="width:3%;padding-right:10px;">Jumlah</th>
                            <th style="width:10%;padding-right:10px;">Rupiah</th>
                            <th style="width:3%;padding-right:10px;">Jumlah</th>
                            <th style="width:10%;padding-right:10px;">Rupiah</th>
                            <th style="width:3%;padding-right:10px;">Jumlah</th>
                            <th style="width:10%;padding-right:10px;">Rupiah</th>
                            <th style="width:3%;padding-right:10px;">Jumlah</th>
                            <th style="width:10%;padding-right:10px;">Rupiah</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>                    
                    <tfoot>
                        <tr>
                            <td colspan="4"></td>
                            <td style="text-align:right;padding-right:10px;"><span class="fot-style">Total</span></td>
                            <td><span id="jumstok" class="fot-style"></span></td>
                            <td style="text-align:right;padding-right:10px;"><span class="fot-style">Total</span></td>
                            <td><span id="jumlaku" class="fot-style"></span></td>
                            <td style="text-align:right;padding-right:10px;"><span class="fot-style">Total</span></td>
                            <td><span id="jumretur" class="fot-style"></span></td>
                            <td style="text-align:right;padding-right:10px;"><span class="fot-style">Total</span></td>
                            <td><span id="jumsisa" class="fot-style"></span></td>
                        </tr>             
                    </tfoot>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col -->
</div><!-- /.row -->
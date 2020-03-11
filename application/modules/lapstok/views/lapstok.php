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
                        "url": "<?php echo base_url('lapstok/data'); ?>",
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
                        $('#footer1').html(addPeriod(json.totalz));
                    },
                    "columnDefs": [
                            {"className": "dt-center", "targets": [0,1,6,7]}
                        ],
                    "columns": [
                            { "data": "urutan" },
                            { "data": "nama_stan" },
                            { "data": "kode_barang" },
                            { "data": "nama_barang" },
                            { "data": "satuan" },
                            { "data": "harga_beli" },
                            { "data": "harga_jual" },
                            { "data": "stok" },
                            { "data": "input" },
                    ]
                });	
                
                $('.selectpicker').selectpicker({
                    size: 10
                });

                $(document).on("click","#btncari",function(){
                        var table = $('#table_cust').DataTable(); 
                        table.ajax.reload( function ( json ) {
                            $('#footer1').html(addPeriod(json.totalz));
                        });
                });        
                    
                $(document).on("click","#btnexport",function(){
                        var cari = $("#txtcari").val();
                        var stan = $("#stan").val();
                        $("#carian").val(cari);
                        $("#stanan").val(stan);
                        $('#export_form').submit();
                });  
                
		$('#myform').on('submit', function(e) {
			e.preventDefault();
                        var post_data = $(this).serialize();
                        $('#btnsave').button('loading');
                        $.ajax({
				url : "<?php echo base_url('lapstok/save'); ?>",
				type: "POST",
				data : post_data,
				dataType: "json",
				success: function(data){
                                    $('#btnsave').button('reset');
                                    if(data.status == 'success'){
                                        $.notify('Successfull update data');
                                        var table = $('#table_cust').DataTable(); 
                                        table.ajax.reload( function ( json ) {
                                            $('#footer1').html(addPeriod(json.totalz));
                                        });
                                    }else{
                                        swal("warning","Can't simpan data!","warning");
                                    }
				}
			});
                });
                
                //if the letter is not digit then don't type anything
                $('#table_cust').on('keypress', '.number-only', function(e) {
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
                            $('#footer1').html(addPeriod(json.totalz));
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
    padding: 3px;
    vertical-align: middle;
    /* border: 1px solid #ccc; */
}
.table tfoot > tr td { 
    vertical-align: middle;   
    padding: 3px;
}
.table tr td {
    border: 1px solid #ccc;
}
.inp-gede{
    font-weight:bold;
    text-align:right;
}
</style>
<div class="row">
    <div class="col-md-12">
        <!-- Default box -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Laporan Stok</h3>
            </div>
            <div class="box-body"> 
                <form id="export_form" action="<?php echo base_url('lapstok/export'); ?>" method="post" target="_self">
                    <input type="hidden" id="carian" name="cari" value=""/>
                    <input type="hidden" id="stanan" name="stan" value=""/>
                    <input type="hidden" name="umpan" value="umpan"/>
                    <input type="hidden" name="draw" value="1"/>
                    <input type="hidden" name="start" value="0"/>
                    <input type="hidden" name="length" value=""/>
                </form>                    
                
                <div class="table-toolbarnya">
                    <form id="formFilter" class="form-horizontal">
                        <div class="col-sm-6" style="padding-left:0px">
                            <ul class="nav nav-pills">
                                <li><button type="button" class="btn btn-success" id="btnexport"><i class="fa fa-file-excel-o"></i> Export Excel</button></li>
                            </ul>                
                        </div>
                        <div class="col-sm-6" style="padding-right:0px">
                            <ul class="nav nav-pills pull-right">
                                <li>
                                    <div class="input-group">
                                        <div class="input-group-addon label-warning">Stan</div>
                                        <select class="form-control selectpicker" name="stan" id="stan" data-width="150px" required>
                                            <option value="">Pilih...</option>
                                            <?php
                                              $query = $this->db->query("SELECT * FROM tb_stan ");
                                              foreach ($query->result_array() as $data){
                                                  echo "<option value='".$data['no_stan']."'>".$data['nama_stan']."</option>";
                                              }
                                            ?>
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="txtcari" name="cari" placeholder="Pencarian"/>
                                        <span class="input-group-btn">
                                          <button type="button" class="btn btn-info" id="btncari"><i class="fa fa-search"></i> Cari</button>
                                        </span>
                                    </div><!-- /input-group -->
                                </li>
                            </ul> 
                        </div>
                    </form>
                </div><!-- /.table-toolbarnya-->

                <form id="myform">
                <table id="table_cust" width="100%" class="table table-bordered table-striped">
                    <thead class="label-primary">
                      <tr>
                            <th style="width:5%">No</th>
                            <th style="width:10%">Stan</th>
                            <th style="width:10%">Kode</th>
                            <th style="width:28%">Nama Barang</th>
                            <th style="width:10%">Satuan</th>
                            <th style="width:10%">Harga Beli</th>
                            <th style="width:10%">Harga Jual</th>
                            <th style="width:7%">Stok</th>
                            <th style="width:10%">Revisi Stok</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" style="text-align:right;padding-right:10px;"><span class="fot-style">Jumlah</span></td>
                            <td><span id="footer1" class="fot-style"></span></td>
                            <td style="padding-right:10px;">
                                <button type="submit" class="btn btn-primary pull-right" id="btnsave" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Loading..."><i class="fa fa-save"></i>&nbsp; Simpan</button>
                            </td>   
                        </tr>           
                    </tfoot>
                </table>
                </form>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col -->
</div><!-- /.row -->    
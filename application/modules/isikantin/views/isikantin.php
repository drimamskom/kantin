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
                        "url": "<?php echo base_url('isikantin/data'); ?>",
                        "data": function( d ) {
                                    var send = $('#formFilter').serializeArray();
                                    $.each(send, function(i, v) {
                                        d[v.name] = v.value;             
                                    });
                                },      
                        "dataType": "json",
                        "type": "POST"
                    },
                    "columnDefs": [
                            {"className": "dt-center", "targets": [0,5]}
                        ],
                    "columns": [
                            { "data": "urutan" },
                            { "data": "kode_barang" },
                            { "data": "nama_barang" },
                            { "data": "satuan" },
                            { "data": "harga" },
                            { "data": "input" },
                    ]
                }); 		
            
                $('.selectpicker').selectpicker({
                    size: 10
                });

                $(document).on("click","#btncari",function(){
                        var table = $('#table_cust').DataTable(); 
                        table.ajax.reload( null, false );
                        
                        var supplier = $('#supplier').val();
                        var tgl_isi  = $('#tgl_isi').val();                        
                        $('#txtsupplier').val(supplier);
                        $('#txttanggal').val(tgl_isi);
                });

                $(document).on("click","#btnpilih",function(){
                        var table = $('#table_cust').DataTable(); 
                        table.ajax.reload( null, false );
                        
                        var supplier = $('#supplier').val();
                        var tgl_isi  = $('#tgl_isi').val();                        
                        $('#txtsupplier').val(supplier);
                        $('#txttanggal').val(tgl_isi);
                });

		$('#myform').on('submit', function(e) {
			e.preventDefault();
                        var post_data = $(this).serialize();
                        $('#btnsave').button('loading');
                        $.ajax({
				url : "<?php echo base_url('isikantin/save'); ?>",
				type: "POST",
				data : post_data,
				dataType: "json",
				success: function(data){
                                    $('#btnsave').button('reset');
                                    if(data.status == 'success'){
                                        $.notify('Successfull update data');
                                        var table = $('#table_cust').DataTable(); 
                                        table.ajax.reload( null, false );
                        
                                        var supplier = $('#supplier').val();
                                        var tgl_isi  = $('#tgl_isi').val();                        
                                        $('#txtsupplier').val(supplier);
                                        $('#txttanggal').val(tgl_isi);
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
			}else{
                            $(this).parent().find("#kode[101210]").html('dddd');                            
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
                        table.ajax.reload( null, false );
                        
                        var supplier = $('#supplier').val();
                        var tgl_isi  = $('#tgl_isi').val();                        
                        $('#txtsupplier').val(supplier);
                        $('#txttanggal').val(tgl_isi);
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
                <h3 class="box-title">Pengisian Stok Kantin Per Hari</h3>
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
                                                  echo "<option value='".$data['kode_supplier']."'>".$data['kode_supplier']." - ".$data['nama_supplier']."</option>";
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
                
                <form id="myform">
                    <input type="hidden" id="txtsupplier" name="supplier">
                    <input type="hidden" id="txttanggal" name="tgl_isi">
                <table id="table_cust" width="100%" class="table table-bordered table-striped">
                    <thead class="label-primary">
                      <tr>
                            <th style="width:5%">No</th>
                            <th style="width:20%">Kode</th>
                            <th style="width:30%">Nama Menu</th>
                            <th style="width:10%">Satuan</th>
                            <th style="width:10%">Harga</th>
                            <th style="width:25%">Stok</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>                    
                    <tfoot>
                        <tr>
                            <td colspan="6" style="padding-right:10px;">
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
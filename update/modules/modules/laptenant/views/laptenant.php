<?php          
$sesi = $this->session->get_userdata();
$userr = $sesi['username'];
$fullname = $sesi['fullname'];
$akses = $sesi['akses'];
?>
<!-- Bootsrtap-select -->
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/datatables/dataTables.rowsGroup.js"></script>
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
                "autoWidth": true,
                "pageLength": 25,
                "processing": true,
                "serverSide": true,
	        "ajax": {
	            "url": "<?php echo base_url('laptenant/data'); ?>",
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
		        {"className": "dt-center", "targets": [0,7,10]}
		    ],
                "rowsGroup": [1,10],
	        "columns": [
		        { "data": "urutan" },
		        { "data": "link" },
		        { "data": "tgl" },
		        { "data": "kode_customer" },
		        { "data": "nama_customer" },
		        { "data": "nama_stan" },
		        { "data": "nama_barang" },
		        { "data": "harga" },
                { "data": "total" },
                { "data": "stat" },
		        { "data": "button" },
	        ]
	    });		
                
            $('.selectpicker').selectpicker({
                size: 10
            });

            $('#table_cust').on('click', '.link', function(e) {
                    var nota = $(this).html();
                    $('#nonota').val(nota);
                    $('#cetakform').submit();
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
                    var depot = $("#depot").val();
                    $("#carian").val(cari);
                    $("#tgl_mulaix").val(tgl_mulai);
                    $("#tgl_selesaix").val(tgl_selesai);
                    $("#depotx").val(depot);
                    $('#export_form').submit();
	    });  
            
            $(document).on( "click",".btnhapus", function() {
                    var id = $(this).attr("idnex");
                    var name = $(this).attr("namenex");

                    swal({   
                        title: "Hapus data?",   
                        text: "Anda yakin menghapus data : "+name+" ?",   
                        type: "warning",   
                        showCancelButton: true,   
                        confirmButtonColor: "#DD6B55",   
                        confirmButtonText: "Hapus",   
                        closeOnConfirm: true }, 
                        function(){   
                            var value = { id:id };
                            $.ajax({
                                url : "<?php echo base_url('laptenant/hapus'); ?>",
                                type: "POST",
                                data : value,
                                dataType: "json",
                                success: function(data){
                                    if(data.status == 'success'){
                                        $.notify('Successfull delete Data');
                                        var table = $('#table_cust').DataTable(); 
                                        // table.ajax.reload( function ( json ) {
                                        //     $('#all_subtotal').html("Rp, "+addPeriod(json.totalz));
                                            table.ajax.url('<?php echo base_url("laptenant/data"); ?>').load();
                                        // });
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
            //$('#view_subtotal').html(" "); 
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
                <h3 class="box-title">Laporan <?php echo $fullname ?></h3>
            </div>
            <div class="box-body"> 
                <form id="export_form" action="<?php echo base_url('laptenant/export'); ?>" method="post" target="_self">
                    <input type="hidden" id="carian" name="cari" value=""/>
                    <input type="hidden" id="tgl_mulaix" name="tgl_mulai" value=""/>
                    <input type="hidden" id="tgl_selesaix" name="tgl_selesai" value=""/>
                    <input type="hidden" id="depotx" name="depot" value=""/>
                    <input type="hidden" name="umpan" value="umpan"/>
                    <input type="hidden" name="draw" value="1"/>
                    <input type="hidden" name="start" value="0"/>
                    <input type="hidden" name="length" value=""/>
                </form>
                <p>
                <a href="<?php echo base_url('kantin'); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Tambah Transksi</a>
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
                            <!-- <li style="padding-left:10px;">
                                <div class="input-group">
                                    <div class="input-group-addon label-warning">Stan</div>
                                    <select class="form-control selectpicker" name="stan" id="stan" data-width="150px" title="Pilih...">
                                        <option value="">All</option>
                                        <?php
                                          $query = $this->db->query("SELECT * FROM tb_stan WHERE no_stan!='0' ");
                                          foreach ($query->result_array() as $data){
                                              echo "<option value='".$data['no_stan']."'>".$data['nama_stan']."</option>";
                                          }
                                        ?>
                                    </select>
                                </div>
                            </li> -->
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
                            <th style="width:10%">No. Pesanan</th>
                            <th style="width:5%">Tgl</th>
                            <th style="width:10%">Kode</th>
                            <th style="width:15%">Pelanggan</th>
                            <th style="width:5%">Stan</th>
                            <th style="width:15%">Menu</th>
                            <th style="width:5%">Jumlah</th>
                            <th style="width:10%">Total</th>
                            <th style="width:15%">Status</th>
                            <th style="width:5%">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="9"><b><span class="pull-right" style="margin-right:10px;">Totals</span></b></td>
                            <td><b><span id="all_subtotal"></span></b></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col -->
</div><!-- /.row -->    
        

<form id="cetakform" action="<?php echo base_url('laptenant/cetak'); ?>" method="post" target="_self">
    <input type="hidden" name="nonota" id="nonota" />
</form>
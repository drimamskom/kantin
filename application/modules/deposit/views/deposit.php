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
                        "url": "<?php echo base_url('deposit/data'); ?>",
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
                            {"className": "dt-center", "targets": [0,3]}
                        ],
                    "columns": [
                            { "data": "link" },
                            { "data": "nama" },
                            { "data": "deposit" },
                            { "data": "button" },
                    ]
                });	

                $(document).on("click","#btncari",function(){
                        var table = $('#table_cust').DataTable(); 
                        table.ajax.reload( null, false );
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
                                url : "<?php echo base_url('deposit/hapus'); ?>",
                                type: "POST",
                                data : value,
                                dataType: "json",
                                success: function(data){
                                    if(data.status == 'success'){
                                        $.notify('Successfull delete Data');
                                        var table = $('#table_cust').DataTable(); 
                                        table.ajax.reload( null, false );
                                    }else{
                                        swal("Error","Can't delete Data, error : "+name,"error");
                                    }
                                }
                            });
                        });
                });     

		$('#myform').on('submit', function(e) {
			e.preventDefault();
                        var post_data = $(this).serialize();
                        var crud=$("#crudmethod").val();
                        $('#btnsave').button('loading');
                        $.ajax({
				url : "<?php echo base_url('deposit/save'); ?>",
				type: "POST",
				data : post_data,
				dataType: "json",
				success: function(data){
		    		$('#btnsave').button('reset');
                                    if(crud == 'N'){
                                        if(data.status == 'duplicate'){                                            
                                            $("#sama").val("yes");
                                            swal({   
                                                title: "Tetap Lanjut??",   
                                                text: data.txt,   
                                                type: "warning",   
                                                showCancelButton: true,   
                                                confirmButtonColor: "#008D4C", 
                                                cancelButtonColor: "#DD6B55", 
                                                confirmButtonText: "Ya, Lanjutkan",
                                                cancelButtonText: "Tidak",
                                                closeOnConfirm: true,
                                                closeOnCancel: true 
                                            }, 
                                            function(isConfirm){
                                                if(isConfirm){
                                                    $('#myform').submit();
                                                }else{
                                                  window.location.href = "<?php echo base_url('deposit');?>";
                                                }
                                            });
                                        }else if(data.status == 'success'){
                                            $.notify('Successfull save data');
                                            var table = $('#table_cust').DataTable(); 
                                            table.ajax.reload( null, false );
                                            window.open('<?php echo base_url();?>deposit/cetak/'+data.nota, '_self');
                                            $("#modalcust").modal("hide");
                                            $("#myform").trigger('reset'); 
                                            $("#type").val("save");
                                            $("#crudmethod").val("N");
                                        }else{
                                            swal("warning",data.txt,"warning");
                                        }
                                    }else{
                                        swal("warning","Invalid Order!","warning");
                                    }
				}
			});
                });


                $('#table_cust').on('click', '.link', function(e) {
                        var nota = $(this).html();
                        window.open('<?php echo base_url();?>deposit/cetak/'+nota, '_self');
                });
            
                $('#txtidcard').keypress(function (e) {                  
                    var key = e.which;
                    if(key == 13){
                        var kode = $('#txtidcard').val();
                        $.ajax({
                            url : "<?php echo base_url('deposit/getcustomer'); ?>",
                            type: "POST",
                            data : { kode:kode },
                            dataType: "json",
                            success: function(data){
                                if(data.status == 'success'){
                                    $('#txtkode').val(data.kode);
                                    $('#txtnama').html(data.nama+" ( Rp, "+addPeriod(data.deposit)+" )");
                                    $('#txtdeposit').focus();
                                }else{
                                    $('#txtidcard').val('');
                                    swal(data.txt,"tekan tombol ESC untuk exit!!","warning");
                                }					
                            }
			});
                    }
		}); 

                $("#txtdeposit").keypress(function (e) {
                    var key = e.which;
                    if(key == 13){
                        $('#myform').submit();
                    }
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
                        table.ajax.reload( null, false );
                    }
                });
                
                $('#txtidcard').focus();
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
    /* border: 1px solid #ccc; */
}
.table tr td {
    border: 1px solid #ccc;
}
</style>
<?php          
$namabln = array("KS", "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
                 "Juli", "Agustus", "September", "Oktober", "November", "Desember");
$blnx = intval(date('m'));
$tgl_manusia = date('d')." ".$namabln[$blnx]." ".date('Y');
?>
<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Input Deposit</h3>
                <span class="pull-right"><b><?php echo $tgl_manusia; ?></b></span>
            </div>
            <div class="box-body">
                <form id="myform" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">IDCARD</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" id="txtidcard" name="idcard" placeholder="Tempelkan Kartu" required/>
                            <input type="hidden" id="crudmethod" name="crud" value="N"> 
                            <input type="hidden" id="txtid" name="idne" value="">
                            <input type="hidden" id="type" name="type" value="save">
                            <input type="hidden" id="sama" name="sama" value="no">
                        </div>
                    </div>
                    <div class="form-group"> 
                        <label class="col-sm-3 control-label">Nama</label>
                        <div class="col-sm-9">
                            <input type="hidden" id="txtkode" name="kode" value="">
                            <span id="txtnama" style="font-weight:bold;"></span>
                        </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Deposit</label>
                      <label class="col-sm-1 control-label">Rp.</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control number-only" id="txtdeposit" name="deposit" placeholder="Deposit" required/>
                      </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label"></label>
                      <div class="col-sm-9">
                        <button type="submit" class="btn btn-primary " id="btnsave" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Loading..."><i class="fa fa-save"></i>&nbsp; Simpan</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal"> Tutup</button>
                      </div>
                    </div>
              </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <!-- Default box -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Master Deposit</h3>
            </div>
            <div class="box-body">
                <div class="table-toolbarnya">
                    <form id="formFilter" class="form-horizontal">
                        <div class="col-sm-6" style="padding-left:0px">
                            <ul class="nav nav-pills">
                                <li>
                                    <div class="input-group">
                                        <div class="input-group-addon label-warning">Tanggal</div>
                                        <input type="text" class="form-control pull-right datepickerr" name="tgl_cari" id="tgl_cari" placeholder="Tgl Pencarian" value="<?php echo date('d/m/Y');?>" style="width:100px;">
                                    </div>
                                </li>
                            </ul>                
                        </div>
                        <div class="col-sm-6" style="padding-right:0px">
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
                            <th style="width:25%">Kode</th>
                            <th style="width:40%">Siswa</th>
                            <th style="width:20%">Saldo</th>
                            <th style="width:15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col -->
</div><!-- /.row -->

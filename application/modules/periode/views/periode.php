<!-- bootstrap-toggle -->
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/bootstrap-toggle/bootstrap-toggle.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/bootstrap-toggle/bootstrap-toggle.min.css" type="text/css"/>
<script type="text/javascript">  
	$(function(){ 
            $('.overlay').hide();
            
            $('#table_cust').DataTable({
                "paging": true,
	        "lengthChange": true,
	        "searching": true,
	        "ordering": true,
	        "info": false,
	        "autoWidth": false,
	        "pageLength": 10,
	        "processing": true,
	        "serverSide": true,
	        "ajax": {
	            "url": "<?php echo base_url('periode/data'); ?>",
                    "dataType": "json",
	            "type": "POST"
	        },
	        "columnDefs": [
		        {"className": "dt-center", "targets": [0,1,4,5]}
		    ],
	        "columns": [
		        { "data": "urutan" },
		        { "data": "nama_thnajaran" },
		        { "data": "tgl_mulai" },
		        { "data": "tgl_selesai" },
		        { "data": "cekbok" },
		        { "data": "button" },
	        ]
	    });	
            
            $('#table_cust').on( 'draw.dt', function () {
                $("[data-toggle='toggle']").bootstrapToggle('destroy')                 
                $("[data-toggle='toggle']").bootstrapToggle();
            });
        
            $(document).on("change",".toggle-aktif",function(){
                var id = this.name;
                var act = $(this).prop('checked');
                if(act){
                    $.ajax({
                        url : "<?php echo base_url('periode/setaktif'); ?>",
                        type: "POST",
                        data : { id:id, act:act },
                        dataType: "json",
                            success: function(data){
                                if(data.status == 'success'){
                                    $.notify('Successfull save data');
                                    var table = $('#table_cust').DataTable(); 
                                    table.ajax.reload( null, false );
                                }else{
                                    swal("warning","Data gagal di Update","warning");
                                }
                            }
                    });
                }else{
                    swal("warning","Tidak perlu di non-aktifkan, cukup set Aktif di periode yg lain!","warning");
                }
                //console.log(this.name+' Toggle: ' + $(this).prop('checked'));
            });
            
                $(document).on("click","#btnadd",function(){
                        $("#modalcust").modal("show");
                        $("#txtcode").focus();
                        $("#myform").trigger('reset'); 
                        $("#type").val("save");
                        $("#crudmethod").val("N");
                        $("#txtid").val("");
                });

		$(document).on("click",".btnedit",function(){
			var id=$(this).attr("idnex");
			$.ajax({
                            url : "<?php echo base_url('periode/cari'); ?>",
                            type: "POST",
                            data : { id:id },
                            dataType: "json",
				success: function(result){
                                    var data = result.data;
                                    $("#crudmethod").val("E");
                                    $("#txtid").val(data.id_periode);
                                    $("#txtcode").val(data.periode_text);
                                    $("#tgl_mulai").val(data.tgl_mulai);
                                    $("#tgl_selesai").val(data.tgl_selesai);
                                    $("#modalcust").modal('show');
                                    $("#txtcode").focus();
                            }
			});
		});

		$('#myform').on('submit', function(e) {
			e.preventDefault();
                        var post_data = $(this).serialize();
                        var crud=$("#crudmethod").val();
                        $('#btnsave').button('loading');
                        $.ajax({
                            url : "<?php echo base_url('periode/save'); ?>",
                            type: "POST",
                            data : post_data,
                            dataType: "json",
                            success: function(data){
                            $('#btnsave').button('reset');
                                if(crud == 'N'){
                                    if(data.status == 'success'){
                                        $.notify('Successfull save data');
                                        var table = $('#table_cust').DataTable(); 
                                        table.ajax.reload( null, false );
                                        $("#modalcust").modal("hide");
                                        $("#txtcode").focus();
                                        $("#myform").trigger('reset'); 
                                        $("#type").val("save");
                                        $("#crudmethod").val("N");
                                    }else{
                                        swal("warning",data.txt,"warning");
                                    }
                                }else if(crud == 'E'){
                                    if(data.status == 'success'){
                                        $.notify('Successfull update data');
                                        var table = $('#table_cust').DataTable(); 
                                        table.ajax.reload( null, false );
                                        $("#modalcust").modal("hide");
                                    }else{
                                        swal("warning","Can't update data!","warning");
                                    }
                                }else{
                                    swal("warning","Invalid Order!","warning");
                                }
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
                                url : "<?php echo base_url('periode/hapus'); ?>",
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

		//if the letter is not digit then don't type anything
		$(".number-only").keypress(function (e) {
                    if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                        return false;
                    }
		}); 

	}); 
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
                <h3 class="box-title">Master Tahun Pelajaran</h3>
            </div>
            <div class="box-body">
                <p>
                <button type="submit" class="btn btn-primary " id="btnadd" name="btnadd"><i class="fa fa-plus"></i> Tambah Tahun Pelajaran</button>
                </p>
                <table id="table_cust" width="100%" class="table table-bordered table-striped">
                    <thead class="label-primary">
                      <tr>
                            <th style="width:10%">No</th>
                            <th style="width:30%">Tahun Pelajaran</th>
                            <th style="width:15%">Tgl Mulai</th>
                            <th style="width:15%">Tgl Selesai</th>
                            <th style="width:15%">Aktif</th>
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
<div class="overlay">
  <i class="fa fa-refresh fa-spin"></i>
</div>

<div id="modalcust" class="modal">
	<div class="modal-dialog modal-md">
	  <div class="modal-content">
		<div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal">×</button>
		  <h4 class="modal-title">Form Master Tahun Pelajaran</h4>
		</div>
		<!--modal header-->
		<div class="modal-body">
		  <form id="myform" class="form-horizontal">
			<div class="form-group"> 
			  <label class="col-sm-3  control-label">Tahun Pelajaran</label>
			  <div class="col-sm-9">
                            <input type="text" class="form-control" id="txtcode" name="code" placeholder="Tahun Pelajaran" required/>
                            <input type="hidden" id="crudmethod" name="crud" value="N"> 
                            <input type="hidden" id="txtid" name="idne" value="">
                            <input type="hidden" id="type" name="type" value="save">
                          </div>
			</div>
			<div class="form-group"> 
			  <label class="col-sm-3  control-label">Tgl Mulai</label>
                          <div class="col-sm-9">
                            <div class="input-group">
                                <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right datepickerr" name="tgl_mulai" id="tgl_mulai" placeholder="Tanggal Mulai" value="<?php echo date('d/m/Y'); ?>">
                            </div>
                          </div>
			</div>
			<div class="form-group"> 
			  <label class="col-sm-3  control-label">Tgl Selesai</label>
                          <div class="col-sm-9">
                            <div class="input-group">
                                <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right datepickerr" name="tgl_selesai" id="tgl_selesai" placeholder="Tanggal Mulai" value="<?php echo date('d/m/Y'); ?>">
                            </div>
                          </div>
			</div>
			<div class="form-group"> 
			  <label class="col-sm-3  control-label"></label>
			  <div class="col-sm-9">
			    <button type="submit" class="btn btn-primary " id="btnsave" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Loading..."><i class="fa fa-save"></i>&nbsp; Simpan</button>
				<button type="button" class="btn btn-default" data-dismiss="modal"> Tutup</button>
			  </div>
			</div>
		  </form>
		  <!--modal footer-->
		</div>
		<!--modal-content-->
	  </div>
	  <!--modal-dialog modal-lg-->
	</div>
	<!--form-kantor-modal-->
  </div>
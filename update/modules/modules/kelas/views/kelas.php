<script type="text/javascript">  
	$(function(){ 
                $('#table_cust').DataTable( {
                    "paging": true,
                    "lengthChange": true,
                    "searching": true,
                    "ordering": true,
                    "info": false,
                    "autoWidth": false,
                    "pageLength": 25,
                    "processing": true,
                    "serverSide": true,
                    "ajax": {
                        "url": "<?php echo base_url('kelas/data'); ?>",
                        "dataType": "json",
                        "type": "POST"
                    },
                    "columnDefs": [
                            {"className": "dt-center", "targets": [0,2,3,4,5]}
                        ],
                    "columns": [
                            { "data": "urutan" },
                            { "data": "nama_kelas" },
                            { "data": "kelas" },
                            { "data": "jurusan" },
                            { "data": "kelompok" },
                            { "data": "button" },
                    ]
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
                            url : "<?php echo base_url('kelas/cari'); ?>",
                            type: "POST",
                            data : { id:id },
                            dataType: "json",
				success: function(result){
					var data = result.data;
					$("#crudmethod").val("E");
					$("#txtid").val(data.id_kelas);
					$("#txtcode").val(data.nama_kelas);
					$("#cbojurusan").val(data.jurusan);
					$("#cbokelas").val(data.kelas);
					$("#txtkelompok").val(data.kelompok);
					$("#modalcust").modal('show');
					$("#txtkelompok").focus();
				}
			});
		});

		$('#myform').on('submit', function(e) {
			e.preventDefault();
                        var post_data = $(this).serialize();
                        var crud=$("#crudmethod").val();
                        $('#btnsave').button('loading');
                        $.ajax({
                            url : "<?php echo base_url('kelas/save'); ?>",
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
                                url : "<?php echo base_url('kelas/hapus'); ?>",
                                type: "POST",
                                data : value,
                                dataType: "json",
                                success: function(data){
                                    if(data.status == 'success'){
                                        $.notify('Successfull delete Data');
                                        var table = $('#table_cust').DataTable(); 
                                        table.ajax.reload( null, false );
                                    }else{
                                        swal("Error",data.txt,"error");
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
                <h3 class="box-title">Master Kelas</h3>
            </div>
            <div class="box-body">
                <p>
                <button type="submit" class="btn btn-primary " id="btnadd" name="btnadd"><i class="fa fa-plus"></i> Tambah Kelas</button>
                </p>
                <table id="table_cust" width="100%" class="table table-bordered table-striped">
                    <thead class="label-primary">
                      <tr>
                            <th style="width:10%">No</th>
                            <th style="width:30%">Nama Kelas</th>
                            <th style="width:10%">Kelas</th>
                            <th style="width:25%">Jurusan</th>
                            <th style="width:10%">Kelompok</th>
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

<div id="modalcust" class="modal">
	<div class="modal-dialog modal-md">
	  <div class="modal-content">
		<div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal">×</button>
		  <h4 class="modal-title">Form Master Kelas</h4>
		</div>
		<!--modal header-->
		<div class="modal-body">
		  <form id="myform" class="form-horizontal">
			<div class="form-group"> 
			  <label class="col-sm-3  control-label">Nama Kelas</label>
			  <div class="col-sm-9">
                            <input type="text" class="form-control" id="txtcode" name="code" placeholder="Nama Kelas" required/>
                            <input type="hidden" id="crudmethod" name="crud" value="N"> 
                            <input type="hidden" id="txtid" name="idne" value="">
                            <input type="hidden" id="type" name="type" value="save">
                          </div>
			</div>
			<div class="form-group"> 
			  <label class="col-sm-3  control-label">Kelas</label>
			  <div class="col-sm-9">
                                <select class="form-control" id="cbokelas" name="kelas" required>
                                  <option value="X">X</option>
                                  <option value="XI">XI</option>
                                  <option value="XII">XII</option>
                               </select>
                            </div>
			</div>
			<div class="form-group"> 
			  <label class="col-sm-3  control-label">Jurusan</label>
			  <div class="col-sm-9">
                                <select class="form-control" id="cbojurusan" name="jurusan" required>
                                  <option value="IPA">IPA</option>
                                  <option value="IPS">IPS</option>
                               </select>
                            </div>
			</div>
			<div class="form-group"> 
			  <label class="col-sm-3  control-label">Kelompok</label>
			  <div class="col-sm-9">
                                <input type="text" class="form-control number-only" id="txtkelompok" name="kelompok" required/>
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
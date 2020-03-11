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
                        "url": "<?php echo base_url('stan/data'); ?>",
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
                            {"className": "dt-center", "targets": [0,2]}
                        ],
                    "columns": [
                            { "data": "no_stan" },
                            { "data": "nama_stan" },
                            { "data": "button" },
                    ]
                });

                $(document).on("click","#btncari",function(){
                        var table = $('#table_cust').DataTable(); 
                        table.ajax.reload( null, false );
                });

                $(document).on("click","#btnadd",function(){
                        $("#myform").trigger('reset'); 
                        $("#type").val("save");
                        $("#crudmethod").val("N");
                        $("#txtid").val("");
                        $("#modalcust").modal("show");
                        $("#txtstan").focus();
                });	

		$(document).on("click",".btnedit",function(){
                    var id=$(this).attr("idnex");
                    $.ajax({
                        url : "<?php echo base_url('stan/cari'); ?>",
                        type: "POST",
                        data : { id:id },
                        dataType: "json",
                            success: function(result){
                                    var data = result.data;
                                    $("#crudmethod").val("E");
                                    $("#txtid").val(data.no_stan);
                                    $("#txtstan").val(data.nama_stan);
                                    $("#modalcust").modal('show');
                                    $("#txtstan").focus();
                            }
			});
		});

		$('#myform').on('submit', function(e) {
                    e.preventDefault();
                    var post_data = $(this).serialize();
                    var crud=$("#crudmethod").val();
                    $('#btnsave').button('loading');
                    $.ajax({
                        url : "<?php echo base_url('stan/save'); ?>",
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
                                url : "<?php echo base_url('stan/hapus'); ?>",
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
                <h3 class="box-title">Master Stan</h3>
            </div>
            <div class="box-body">
                <div class="table-toolbarnya">
                    <form id="formFilter" class="form-horizontal">
                        <div class="col-sm-9" style="padding-left:0px">
                            <ul class="nav nav-pills">
                                <li>
                                    <button type="button" class="btn btn-success" id="btnadd"><i class="fa fa-plus"></i> Tambah Stan Baru</button>
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
                            <th style="width:5%">No Stan</th>
                            <th style="width:30%">Nama Stan</th>
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
                <h4 class="modal-title">Form Master Stan</h4>
            </div>
            <!--modal header-->
            <div class="modal-body">
                <form id="myform" class="form-horizontal">
                    <div class="form-group"> 
                        <label class="col-sm-3 control-label">Nama Stan</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="txtstan" name="stan" placeholder="Nama Stan" style="text-transform:uppercase;"required/>
                            <input type="hidden" id="crudmethod" name="crud" value="N"> 
                            <input type="hidden" id="txtid" name="idne" value="">
                            <input type="hidden" id="type" name="type" value="save">
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
            </div><!--modal footer-->
        </div><!--modal-content-->	  
    </div><!--modal-dialog modal-lg-->	
</div><!--form-kantor-modal-->
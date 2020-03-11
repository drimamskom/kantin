<!-- Select2 -->
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/select2/select2.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2.css" type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2-bootstrap.css" type="text/css"/>
<script type="text/javascript">   
	$(function(){ 
                $('.select2').select2({
                    placeholder: "Pilih...",
                    minimumResultsForSearch: Infinity,
                    theme: "bootstrap"
                });
                
		$('#table_cust').DataTable( {
                    "paging": true,
                    "lengthChange": true,
                    "searching": true,
                    "ordering": false,
                    "info": false,
                    "autoWidth": false,
                    "pageLength": 10,
                    "processing": true,
                    "serverSide": true,
                    "ajax": {
                        "url": "<?php echo base_url('user/data'); ?>",
                        "dataType": "json",
                        "type": "POST"
                    },
                    "columnDefs": [
                            {"className": "dt-center", "targets": [0,1,4,5]}
                        ],
                    "columns": [
                            { "data": "urutan" },
                            { "data": "nomor_induk" },
                            { "data": "username" },
                            { "data": "fullname" },
                            { "data": "akses" },
                            { "data": "button" },
                    ]
                });	

                $(document).on("click","#btnadd",function(){
                        $('.select2').select2({
                            placeholder: "Pilih...",
                            minimumResultsForSearch: Infinity,
                            theme: "bootstrap"
                        });
			$("#modalcust").modal("show");
			$("#myform").trigger('reset'); 
			$("#type").val("save");
			$("#crudmethod").val("N");
                        $("#cbonama").val(null).trigger("change");
                        $("#txtid").val("");
			$("#parentkary").hide();
			$("#parentnama").show();
                        $("#cboakses").val(null).trigger("change");
			$("#cboakses").focus();
                });
                

		$(document).on("click",".btnedit",function(){
			var id=$(this).attr("idnex");
			$.ajax({
                            url : "<?php echo base_url('user/cari'); ?>",
                            type: "POST",
                            data : { id:id },
                            dataType: "json",
                            success: function(result){                                
                                $('.select2').select2({
                                    placeholder: "Pilih...",
                                    minimumResultsForSearch: Infinity,
                                    theme: "bootstrap"
                                });
                                var data = result.data;
                                $("#parentkary").hide();
                                $("#parentnama").show();
                                $("#cboakses").val(data.akses).trigger("change");
                                
                                setTimeout(function () {
                                    $("#cbonama").val(data.nomor_induk).trigger("change");
                                    $("#crudmethod").val("E");
                                    $("#txtid").val(data.user_id);
                                    $("#txtcode").val(data.nomor_induk);
                                    $("#txtkaryawan").val(data.fullname);
                                    $("#txtusername").val(data.username);
                                }, 500);
                                    
                                // pass diKosongi & dikasih pemberitahuan kalao kosong
                                $("#txtpassword").val("");
                                $("#txtpassword").attr('placeholder','Isi Jika ingin Membuat password baru');

                                /* $("#txtpassword").val(data.password); */
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
                            url : "<?php echo base_url('user/save'); ?>",
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
                                url : "<?php echo base_url('user/hapus'); ?>",
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

                $('#cboakses').on("change", function (e) {
                    $('#cbonama').html('').select2({data: [] });
                    var kode = $(this).val();
                    if(kode=='kantin' || kode=='customer' || kode=='tenant'){
                        $("#parentkary").hide();
                        $("#parentnama").show();
                        $.ajax({
                            url : "<?php echo base_url('user/info'); ?>",
                            type: "POST",
                            data : { kode:kode },
                            dataType: "json",
                            success: function(result){    
                                $('#cbonama').select2({
                                    data: result.data,
                                    placeholder:"Pilih...",
                                    theme: "bootstrap"
                                });
                                $("#cbonama").val(null).trigger("change");
                            }
                        });                    
                    }else{
			$("#parentkary").show();
			$("#parentnama").hide();
                        $("#txtkaryawan").val("");  
                        $("#txtcode").val("0");                     
                    }
                });
            
                $('#cbonama').on("change", function (e) {
                    var kode = $(this).val();
                    var nama = $('#cbonama option:selected').text();
                    $("#txtcode").val(kode);   
                    $("#txtkaryawan").val(nama);
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
                <h3 class="box-title">Master User</h3>
            </div>
            <div class="box-body">
                <p>
                <button type="submit" class="btn btn-primary " id="btnadd" name="btnadd"><i class="fa fa-plus"></i> Tambah User</button>
                </p>
                <table id="table_cust" width="100%" class="table table-bordered table-striped">
                    <thead class="label-primary">
                      <tr>
                            <th style="width:5%">No</th>
                            <th style="width:10%">Nomor Induk</th>
                            <th style="width:15%">Username</th>
                            <th style="width:25%">Nama Lengkap</th>
                            <th style="width:10%">Akses</th>
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
              <h4 class="modal-title">Form Master User</h4>
            </div>
            <!--modal header-->
            <div class="modal-body">
              <form id="myform" class="form-horizontal">
                    <div class="form-group">
                      <label class="col-sm-3  control-label">Akses</label>
                      <div class="col-sm-9">
                            <select class="form-control select2" id="cboakses" name="akses" style="width:100%" required>
                                <option value=""></option>
                                <?php
                                $query = $this->db->query("SELECT * FROM tb_akses where aktif='1'");
                                foreach ($query->result_array() as $data){
                                    echo "<option value='".$data['akses']."'>".$data['nama_akses']."</option>";
                                }
                                ?>
                            </select>				  
                      </div>
                    </div>
                    <div class="form-group"> 
                        <label class="col-sm-3  control-label">Nama</label>
                        <div class="col-sm-9">
                            <div id="parentnama"><select class="form-control select2" id="cbonama" name="nama" style="width:100%"></select></div>
                            <div id="parentkary"><input type="text" class="form-control" id="txtkaryawan" name="karyawan" placeholder="Nama" required/></div>
                        </div>
                    </div>
                    <div class="form-group"> 
                        <label class="col-sm-3  control-label">Nomor Induk</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="txtcode" name="code" placeholder="Nomor Induk" />
                              <input type="hidden" id="crudmethod" name="crud" value="N"> 
                              <input type="hidden" id="txtid" name="idne" value="">
                              <input type="hidden" id="type" name="type" value="save">
                        </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3  control-label">Username</label>
                      <div class="col-sm-9">
                              <input type="text" class="form-control" id="txtusername" name="username" placeholder="Username" required/>
                            </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3  control-label">Password</label>
                      <div class="col-sm-9">
                              <input type="password" class="form-control" id="txtpassword" name="password" placeholder="Password">
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
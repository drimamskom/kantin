<!-- Bootsrtap-select -->
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
                    "autoWidth": false,
                    "pageLength": 50,
                    "processing": true,
                    "serverSide": true,
                    "ajax": {
                        "url": "<?php echo base_url('pelanggan/data'); ?>",
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
                            {"className": "dt-center", "targets": [0,1,4]}
                        ],
                    "columns": [
                            { "data": "urutan" },
                            { "data": "kode" },
                            { "data": "nama" },
                            { "data": "alamat" },
                            { "data": "jenis_kelamin" },
                            { "data": "agama" },
                            { "data": "nama_grup" },
                            { "data": "button" },
                    ]
                });	
                
                $('.selectpicker').selectpicker({
                    size: 10
                });

                $(document).on("click","#btncari",function(){
                        var table = $('#table_cust').DataTable(); 
                        table.ajax.reload( null, false );
                });

                $(document).on("click","#btnadd",function(){
                        $("#modalcust").modal("show");
                        $("#txtcode").attr('readonly', false);
                        $("#txtidcard").attr('readonly', false);
                        $("#txtcode").focus();
                        $("#myform").trigger('reset'); 
                        $("#type").val("save");
                        $("#crudmethod").val("N");
                        $("#txtid").val("");
                        $(".no-edit").show();
                });	

		$(document).on("click",".btnedit",function(){
                    $(".no-edit").hide();
                    var id=$(this).attr("idnex");
                    $.ajax({
                        url : "<?php echo base_url('pelanggan/cari'); ?>",
                        type: "POST",
                        data : { id:id },
                        dataType: "json",
                            success: function(result){
                                    var data = result.data;
                                    $("#crudmethod").val("E");
                                    $("#txtid").val(data.kode);
                                    $("#txtcode").val(data.kode);
                                    $("#txtidcard").val(data.kode2);
                                    $("#txtpelanggan").val(data.nama);
                                    $("#cbojenis_kelamin").val(data.jenis_kelamin);
                                    $("#txtalamat").val(data.alamat);
                                    $("#cboagama").val(data.agama);
                                    $("#cbogrup").val(data.grup);
                                    $('.selectpicker').selectpicker('render');              
                                    $("#modalcust").modal('show');
                                    $("#txtpelanggan").focus();
                                    $("#txtcode").attr('readonly', true);
                                    $("#txtidcard").attr('readonly', true);
                            }
			});
		});

		$('#myform').on('submit', function(e) {
			e.preventDefault();
                        var post_data = $(this).serialize();
                        var crud=$("#crudmethod").val();
                        $('#btnsave').button('loading');
                        $.ajax({
				url : "<?php echo base_url('pelanggan/save'); ?>",
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
                                url : "<?php echo base_url('pelanggan/hapus'); ?>",
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
                <h3 class="box-title">Master Pelanggan</h3>
            </div>
            <div class="box-body">
                <div class="table-toolbarnya">
                    <form id="formFilter" class="form-horizontal">
                        <div class="col-sm-9" style="padding-left:0px">
                            <ul class="nav nav-pills">
                                <li>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                          <span class="caret"></span>
                                          <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <button type="button" class="btn btn-success">Aksi</button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li><a href="javascript:void(0);" id="btnadd">Tambah Pelanggan Baru</a></li>
                                            <li class="divider"></li>
                                            <li><a href="<?php echo base_url('pelanggan/upload') ?>" id="btnupload">Upload Pelanggan Baru</a></li>                                           
                                        </ul>
                                    </div>
                                </li>
                                <li style="margin-left:25px;">
                                    <div class="input-group">
                                        <div class="input-group-addon label-warning">Grup</div>
                                        <select class="form-control selectpicker" name="grup" data-width="110px" title="Pilih...">
                                            <option value="">All</option>
                                            <?php
                                              $query = $this->db->query("SELECT * FROM tb_grup");
                                              foreach ($query->result_array() as $data){
                                                  echo "<option value='".$data['kode']."'>".$data['grup']."</option>";
                                              }
                                            ?>
                                        </select>
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
                            <th style="width:15%">Kode</th>
                            <th style="width:26%">Nama</th>
                            <th style="width:20%">Alamat</th>
                            <th style="width:10%">Jenis Kelamin</th>
                            <th style="width:7%">Agama</th>
                            <th style="width:7%">Grup</th>
                            <th style="width:10%">Action</th>
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
              <h4 class="modal-title">Form Master Pelanggan</h4>
            </div>
            <!--modal header-->
            <div class="modal-body">
              <form id="myform" class="form-horizontal">
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Kode</label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" id="txtcode" name="code" placeholder="Nomor Induk Siswa" required/>
                        <input type="hidden" id="crudmethod" name="crud" value="N"> 
                        <input type="hidden" id="txtid" name="idne" value="">
                        <input type="hidden" id="type" name="type" value="save">
                      </div>
                    </div>                        
                    <div class="form-group no-edit"> 
                        <label class="col-sm-3  control-label">IDCARD</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" id="txtidcard" name="idcard" placeholder="Tempelkan Kartu">
                        </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Nama Pelanggan</label>
                      <div class="col-sm-9">
                              <input type="text" class="form-control" id="txtpelanggan" name="pelanggan" placeholder="Nama Pelanggan" required/>
                            </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Alamat</label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" id="txtalamat" name="alamat" placeholder="Alamat" required/>
                      </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Jenis Kelamin</label>
                      <div class="col-sm-9">
                          <select class="form-control selectpicker" id="cbojenis_kelamin" name="jenis_kelamin" required>
                              <option value="L">Laki-Laki</option>
                              <option value="P">Perempuan</option>
                           </select>				  
                      </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Agama</label>
                      <div class="col-sm-9">
                            <select class="form-control selectpicker" id="cboagama" data-height="250" name="agama" required>
                                <?php
                                  $query = $this->db->query("SELECT * FROM tb_agama ");
                                  foreach ($query->result_array() as $data){
                                      echo "<option value='".$data['agama']."'>".$data['agama']."</option>";
                                  }
                                ?>
                            </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-3 control-label">Grup</label>
                      <div class="col-sm-9">
                              <select class="form-control selectpicker" id="cbogrup" name="grup" required>
                                  <?php
                                    $query = $this->db->query("SELECT * FROM tb_grup");
                                    foreach ($query->result_array() as $data){
                                        echo "<option value='".$data['kode']."'>".$data['grup']."</option>";
                                    }
                                  ?>
                              </select>				  
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
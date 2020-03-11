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
                    "pageLength": 25,
                    "processing": true,
                    "serverSide": true,
                    "ajax": {
                        "url": "<?php echo base_url('menudepot/data'); ?>",
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
                            {"className": "dt-center", "targets": [0,1,6]}
                        ],
                    "columns": [
                            { "data": "urutan" },
                            { "data": "kode_menu" },
                            { "data": "nama_menu" },
                            { "data": "nama_kategori" },
                            { "data": "nama_depot" },
                            { "data": "harga" },
                            { "data": "button" },
                    ]
                });		
            
                $('.selectpicker').selectpicker({
                    size: 4
                });

                $(document).on("click","#btncari",function(){
                        var table = $('#table_cust').DataTable(); 
                        table.ajax.reload( null, false );
                });

                $(document).on("click","#btnadd",function(){
                        $("#text_transfer").val("");
                        $("#modalcust").modal("show");
                        $("#txtmenu").focus();
                        $("#myform").trigger('reset'); 
                        $("#type").val("save");
                        $("#crudmethod").val("N");
                        $("#txtid").val("");
                });

		$(document).on("click",".btnedit",function(){
                    var id=$(this).attr("idnex");
                    $.ajax({
                        url : "<?php echo base_url('menudepot/cari'); ?>",
                        type: "POST",
                        data : { id:id },
                        dataType: "json",
                        success: function(result){
                                var data = result.data;
                                $("#text_transfer").val("");
                                $("#crudmethod").val("E");
                                $("#txtid").val(data.kode_menu);
                                $("#txtmenu").val(data.nama_menu);
                                $("#txtkategori").selectpicker('val',data.kategori);
                                $("#txtdepot").selectpicker('val',data.kode_depot);
                                $("#txtharga").val(data.harga);
                                $("#modalcust").modal('show');
                                $("#txtmenu").focus();
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
                                url : "<?php echo base_url('menudepot/hapus'); ?>",
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
				url : "<?php echo base_url('menudepot/save'); ?>",
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
<div class="row">
    <div class="col-md-12">
        <!-- Default box -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Master Menu</h3>
            </div>
            <div class="box-body">
                <div class="table-toolbarnya">
                    <form id="formFilter" class="form-horizontal">
                        <div class="col-sm-9" style="padding-left:0px">
                            <ul class="nav nav-pills">
                                <li>
                                    <button type="button" class="btn btn-success" id="btnadd"><i class="fa fa-plus"></i>&nbsp;Tambah Menu</button>
                                </li>
                                <li style="padding-left:10px;">
                                    <div class="input-group">
                                        <div class="input-group-addon label-warning">Kategori</div>
                                        <select class="form-control selectpicker" name="kategori" id="kategori" data-width="200px" >
                                            <option value="">All</option>
                                            <?php
                                              $query = $this->db->query("SELECT * FROM tb_depot_kategori ");
                                              foreach ($query->result_array() as $data){
                                                  echo "<option value='".$data['kode_kategori']."'>".$data['nama_kategori']."</option>";
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
                            <th style="width:20%">Nama Menu</th>
                            <th style="width:15%">Kategori</th>
                            <th style="width:15%">Depot</th>
                            <th style="width:15%">Harga</th>
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
              <h4 class="modal-title">Form Master Menu <span id="text_transfer"></span></h4>
            </div>
            <div class="modal-body">
              <form id="myform" class="form-horizontal">
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Nama Menu</label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" id="txtmenu" name="menu" placeholder="Nama Menu" required/>
                        <input type="hidden" id="crudmethod" name="crud" value="N"> 
                        <input type="hidden" id="txtid" name="idne" value="">
                        <input type="hidden" id="type" name="type" value="save">
                      </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Kategori</label>
                      <div class="col-sm-9">
                            <select class="form-control selectpicker" name="kategori" id="txtkategori" data-width="200px" title="Pilih...">
                                <?php
                                  $query1 = $this->db->query("SELECT * FROM tb_depot_kategori ");
                                  foreach ($query1->result_array() as $data1){
                                      echo "<option value='".$data1['kode_kategori']."'>".$data1['nama_kategori']."</option>";
                                  }
                                ?>
                            </select>
                      </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Depot</label>
                      <div class="col-sm-9">
                            <select class="form-control selectpicker" name="depot" id="txtdepot" data-width="200px" title="Pilih...">
                                <?php
                                  $query2 = $this->db->query("SELECT * FROM tb_depot ");
                                  foreach ($query2->result_array() as $data2){
                                      echo "<option value='".$data2['kode_depot']."'>".$data2['nama_depot']."</option>";
                                  }
                                ?>
                            </select>
                      </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Harga</label>
                      <label class="col-sm-1 control-label">Rp.</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control number-only" id="txtharga" name="harga" placeholder="Harga" required/>
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
</div>  
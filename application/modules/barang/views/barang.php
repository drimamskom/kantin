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
                        "url": "<?php echo base_url('barang/data'); ?>",
                        "data": function( d ) {
                                    var name = "stan";
                                    var arr  = [];
                                    var send = $('#formFilter').serializeArray();
                                    $.each(send, function(i, v) {
                                        if( v.name.indexOf('[]') !== -1 ){
                                            arr.push(v.value);
                                        }else{
                                            d[v.name] = v.value;
                                        }                                    
                                    });
                                    d[name] = arr;
                                },      
                        "dataType": "json",
                        "type": "POST"
                    },
                    "columnDefs": [
                            {"className": "dt-center", "targets": [0,1,8]}
                        ],
                    "columns": [
                            { "data": "urutan" },
                            { "data": "kode_barang" },
                            { "data": "nama_barang" },
                            { "data": "harga_beli" },
                            { "data": "harga_jual" },
                            { "data": "nama_supplier" },
                            { "data": "nama_tempat" },
                            { "data": "nama_stan" },
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
                        $("#text_transfer").val("");
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
                        url : "<?php echo base_url('barang/cari'); ?>",
                        type: "POST",
                        data : { id:id },
                        dataType: "json",
                        success: function(result){
                                var data = result.data; 
                                $("#text_transfer").val("");
                                $("#crudmethod").val("E");
                                $("#txtid").val(data.id);
                                $("#txtcode").val(data.kode_barang);
                                $("#txtbarang").val(data.nama_barang);
                                $("#txtjenis").val(data.jenis);
                                $("#txtsatuan").val(data.satuan);
                                $("#txttempat").val(data.tempat);
                                $("#txtstan").val(data.no_stan);
                                $("#txtsupplier").val(data.supplier);
                                $("#txtstok_minimal").val(data.min_stok);
                                $("#txtharga_beli").val(data.harga_beli);
                                $("#txtharga_jual").val(data.harga_jual);
                                $('.selectpicker').selectpicker('render');
                                $("#modalcust").modal('show');
                                $("#txtcode").focus();
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
                                url : "<?php echo base_url('barang/hapus'); ?>",
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
				url : "<?php echo base_url('barang/save'); ?>",
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
                <h3 class="box-title">Master Barang</h3>
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
                                            <li><a href="javascript:void(0);" id="btnadd">Tambah Barang Baru</a></li>
                                            <li class="divider"></li>
                                            <li><a href="<?php echo base_url('barang/upload') ?>" id="btnupload">Upload Barang Baru</a></li>
                                        </ul>
                                    </div>
                                </li>
                                <li style="padding-left:10px;">
                                    <div class="input-group">
                                        <div class="input-group-addon label-warning">Tempat</div>
                                        <select class="form-control selectpicker" name="tempat" id="tempat" data-width="130px" >
                                            <option value="">All</option>
                                            <?php
                                                $query = $this->db->query("SELECT * FROM tb_tempat ");
                                                foreach ($query->result_array() as $data){
                                                    echo "<option value='".$data['kode']."'>".$data['tempat']."</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <div class="input-group">
                                        <div class="input-group-addon label-warning">Stan</div>
                                        <select class="form-control selectpicker" name="stan[]" id="stan" data-actions-box="true" data-width="180px" multiple>
                                            <option value="">All</option>
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
                                        <div class="input-group-addon label-warning">Supplier</div>
                                        <select class="form-control selectpicker" name="supplier" id="supplier" data-width="150px" data-live-search="true">
                                            <option value="">Pilih...</option>
                                            <?php
                                                $query = $this->db->query("SELECT * FROM tb_supplier ");
                                                foreach ($query->result_array() as $data){
                                                    echo "<option value='".$data['kode_supplier']."'>".$data['nama_supplier']."</option>";
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
                            <th style="width:10%">Kode</th>
                            <th style="width:23%">Nama Barang</th>
                            <th style="width:10%">Harga Beli</th>
                            <th style="width:10%">Harga Jual</th>
                            <th style="width:10%">Supplier</th>
                            <th style="width:10%">Tempat</th>
                            <th style="width:10%">Stan</th>
                            <th style="width:12%">Action</th>
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
              <h4 class="modal-title">Form Master Barang <span id="text_transfer"></span></h4>
            </div>
            <div class="modal-body">
              <form id="myform" class="form-horizontal">
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Kode Barang</label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control no-space-allowed" id="txtcode" name="kode_barang" placeholder="Kode Barang / Barcode" style="text-transform:uppercase;" required/>
                        <small style="color:#8c8c8c;font-size:11px;">**Huruf BESAR dan tanpa spasi. ganti spasi dg karakter ( - _ / + @ $ * ... dst)</small>
                      </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Nama Barang</label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" id="txtbarang" name="barang" placeholder="Nama Barang" required/>
                        <input type="hidden" id="crudmethod" name="crud" value="N"> 
                        <input type="hidden" id="txtid" name="idne" value="">
                        <input type="hidden" id="type" name="type" value="save">
                      </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Supplier</label>
                      <div class="col-sm-9">
                          <select class="form-control selectpicker" name="supplier" id="txtsupplier" data-width="200px" required>
                                <option value="">Pilih</option>
                                <?php
                                    $query = $this->db->query("SELECT * FROM tb_supplier ");
                                    foreach ($query->result_array() as $data){
                                        echo "<option value='".$data['kode_supplier']."'>".$data['nama_supplier']."</option>";
                                    }
                                ?>
                            </select>
                      </div>
                    </div><div class="form-group"> 
                      <label class="col-sm-3 control-label">Jenis Barang</label>
                      <div class="col-sm-9">
                          <select class="form-control selectpicker" name="jenis" id="txtjenis" data-width="150px" required>
                                <option value="">Pilih</option>
                                <?php
                                    $query = $this->db->query("SELECT * FROM tb_jenis ");
                                    foreach ($query->result_array() as $data){
                                        echo "<option value='".$data['kode']."'>".$data['jenis']."</option>";
                                    }
                                ?>
                            </select>
                      </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Satuan</label>
                      <div class="col-sm-9">
                          <select class="form-control selectpicker" name="satuan" id="txtsatuan" data-width="150px" data-live-search="true" required>
                                <option value="">Pilih</option>
                                <?php
                                    $query = $this->db->query("SELECT * FROM tb_satuan ");
                                    foreach ($query->result_array() as $data){
                                        echo "<option value='".$data['display']."'>".$data['display']."</option>";
                                    }
                                ?>
                            </select>
                      </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Tempat</label>
                      <div class="col-sm-9">
                          <select class="form-control selectpicker" name="tempat" id="txttempat" data-width="150px" required>
                                <option value="">Pilih</option>
                                <?php
                                    $query = $this->db->query("SELECT * FROM tb_tempat ");
                                    foreach ($query->result_array() as $data){
                                        echo "<option value='".$data['kode']."'>".$data['tempat']."</option>";
                                    }
                                ?>
                            </select>
                      </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Stan</label>
                      <div class="col-sm-9">
                          <select class="form-control selectpicker" name="stan" id="txtstan" data-width="200px" required>
                                <option value="">Pilih</option>
                                <?php
                                    $query = $this->db->query("SELECT * FROM tb_stan ");
                                    foreach ($query->result_array() as $data){
                                        echo "<option value='".$data['no_stan']."'>".$data['nama_stan']."</option>";
                                    }
                                ?>
                            </select>
                      </div>
                    </div>                    
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Stok Minimal</label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control number-only" id="txtstok_minimal" name="stok_minimal" placeholder="Stok Minimal" required/>
                      </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Harga Beli</label>
                      <label class="col-sm-1 control-label">Rp.</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control number-only" id="txtharga_beli" name="harga_beli" placeholder="Harga Beli" required/>
                      </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-3 control-label">Harga Jual</label>
                      <label class="col-sm-1 control-label">Rp.</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control number-only" id="txtharga_jual" name="harga_jual" placeholder="Harga Jual" required/>
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
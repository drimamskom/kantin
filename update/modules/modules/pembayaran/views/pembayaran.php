<!-- Include the plugin's CSS and JS: -->
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/bootstrap-multiselect/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/bootstrap-multiselect/bootstrap-multiselect.css" type="text/css"/>
<script type="text/javascript"> 
	$(function(){ 
            $('#table_cust').DataTable( {
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
                    "url": "<?php echo base_url('pembayaran/data'); ?>",
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
		        {"className": "dt-center", "targets": [0,1,2,4,5,6,9,10]}
		    ],
	        "columns": [
                        { "data": "button" },
		        { "data": "urutan" },
		        { "data": "kode_pembayaran" },
		        { "data": "nama_pembayaran" },
		        { "data": "rupiah" },
		        { "data": "text" },
		        { "data": "reguler" },
		        { "data": "periode_text" },
		        { "data": "jenis_pembayaran" },
		        { "data": "tgl_mulai" },
		        { "data": "tgl_selesai" },
	        ]
	    });		

            $('#cbokelas').multiselect({
                    includeSelectAllOption: true
            });

            $(document).on("click","#btncari",function(){
                    var table = $('#table_cust').DataTable(); 
                    table.ajax.reload( null, false );
            });

            $(document).on("click","#btndelete",function(){
                // Iterate over all checkboxes in the table
                var tot=0;
                var sel=[];
                var table = $('#table_cust').DataTable(); 
                table.$('input[type="checkbox"]').each(function(){
                    // If checkbox is checked
                    if(this.checked){
                        // Create a hidden element 
                        sel.push(this.name);
                        tot=tot+1;
                    }
                });

                if(tot>0){
                    swal({   
                      title: "Delete Data?",   
                      text: "Are you Sure Delete Data : "+tot+" Item?",   
                      type: "warning",   
                      showCancelButton: true,   
                      confirmButtonColor: "#DD6B55",   
                      confirmButtonText: "Delete",   
                      closeOnConfirm: true }, 
                      function(){   
                        var value = { id:sel.join("-") };
                        $.ajax({
                                url : "<?php echo base_url('pembayaran/hapus'); ?>",
                                type: "POST",
                                data : value,
                                dataType: "json",
                                success: function(data){
                                    if(data.status == 'success'){
                                        $.notify('Successfull delete Data');
                                        var table = $('#table_cust').DataTable(); 
                                        table.ajax.reload( null, false );
                                    }else{
                                        //swal("warning",data.txt,"warning");
                                        swal("Error"," "+data.txt+" ","error");
                                    }
                                }
                        });
                    });
                }else{
                    swal("warning","Centang Salah satu!","warning");
                }
            });
            
	    $(document).on("click","#btnadd",function(){
			$("#modalcust").modal("show");
			$("#txtpembayaran").focus();
			$("#myform").trigger('reset'); 
			$("#type").val("save");
			$("#crudmethod").val("N");
                        $("#txtid").val("");
                        $('#cbokelas').multiselect('refresh');
	    });

		$(document).on("click",".btnedit",function(){
                    var id=$(this).attr("idnex");
                    $.ajax({
                        url : "<?php echo base_url('pembayaran/cari'); ?>",
                        type: "POST",
                        data : { id:id },
                        dataType: "json",
				success: function(result){
					var data = result.data;
					$("#crudmethod").val("E");
                                        $('#cbokelas').multiselect('refresh');
					$("#txtid").val(data.kode_pembayaran);
					$("#txtpembayaran").val(data.nama_pembayaran);
					$("#txtrupiah").val(data.rupiah);
					$("#cbokelas").val(data.id_kelas);
					$("#cboreguler").val(data.reguler);
					$("#cboperiode_text").val(data.id_periode);
					$("#cbojenis_pembayaran").val(data.id_jenis_pembayaran);
					$("#tgl_mulai").val(data.tgl_mulai);
					$("#tgl_selesai").val(data.tgl_selesai);
                                        $('#cbokelas').multiselect('select', data.id_kelas);
                                        //$('#cbokelas').opt('multiselect', false);
					$("#modalcust").modal('show');
					$("#txtke").focus();
				}
			});
		});

		$('#myform').on('submit', function(e) {
			e.preventDefault();
                        var post_data = $(this).serialize();
                        var crud=$("#crudmethod").val();
                        $('#btnsave').button('loading');
                        $.ajax({
				url : "<?php echo base_url('pembayaran/save'); ?>",
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
							$("#txtpembayaran").focus();
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

                // Handle click on "Select all" control
                $('#select-all').on('click', function(){
                   // Get all rows with search applied
                   var table = $('#table_cust').DataTable(); 
                   var rows = table.rows({ 'search': 'applied' }).nodes();
                   // Check/uncheck checkboxes for all rows in the table
                   $('input[type="checkbox"]', rows).prop('checked', this.checked);
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

<div class="box-header">
    <h3 class="box-title">Master Pembayaran</h3>
</div>
<div class="box-body">
    <div class="table-toolbarnya">
        <form id="formFilter" class="form-horizontal">
            <div class="col-sm-9" style="padding-left:0px">
                <ul class="nav nav-pills">
                    <li>
                        <button type="button" class="btn btn-primary" id="btnadd" name="btnadd"><i class="fa fa-plus"></i> Tambah Pembayaran</button>
                    </li>
                    <li>
                        <button type="button" class="btn btn-danger" id="btndelete" name="btndelete"><i class="fa fa-remove"></i> Delete Selected</button>
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
		<th style="width:3%"><center><input type="checkbox" name="select_all" value="1" id="select-all"></center></th>
		<th style="width:5%">No</th>
		<th style="width:10%">Kode</th>
		<th style="width:14%">Nama Pembayaran</th>
		<th style="width:10%">Rupiah</th>
		<th style="width:7%">Kelas</th>
		<th style="width:7%">Reguler</th>
		<th style="width:10%">TaPel</th>
		<th style="width:10%">Jns Pemby</th>
		<th style="width:7%">Tgl Mulai</th>
		<th style="width:10%">Tgl Selesai</th>
	  </tr>
	</thead>
	<tbody>
	</tbody>
    </table>
</div><!-- /.box-body -->

<div id="modalcust" class="modal">
	<div class="modal-dialog">
	  <div class="modal-content">
		<div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal">×</button>
		  <h4 class="modal-title">Form Master Pembayaran</h4>
		</div>
		<!--modal header-->
		<div class="modal-body">
		  <form id="myform" class="form-horizontal">
			<div class="form-group"> 
			  <label class="col-sm-3  control-label">Pembayaran</label>
			  <div class="col-sm-9">
                            <input type="text" class="form-control" id="txtpembayaran" name="pembayaran" placeholder="Nama Pembayaran" required/>
                            <input type="hidden" id="crudmethod" name="crud" value="N"> 
                            <input type="hidden" id="txtid" name="idne" value="">
                            <input type="hidden" id="type" name="type" value="save">
                          </div>
			</div>
			<div class="form-group"> 
			  <label class="col-sm-3 control-label">Tgl Mulai</label>
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
			  <label class="col-sm-3 control-label">Biaya</label>
			  <label class="col-sm-1 control-label">Rp</label>
			  <div class="col-sm-8"> 
                                <input type="text" class="form-control number-only" id="txtrupiah" name="rupiah" required/>
                            </div>
			</div>
			<div class="form-group"> 
			  <label class="col-sm-3  control-label">Kelas</label>
			  <div class="col-sm-9">
                                <select class="form-control" id="cbokelas" name="kelas[]" multiple="multiple" required>
                                    <?php
                                      $query = $this->db->query("SELECT * FROM m_kelas where aktif='1' ");
                                      foreach ($query->result_array() as $data){
                                          echo "<option value='".$data['id_kelas']."'>".$data['text']."</option>";
                                      }
                                    ?>
                                </select>
                          </div>
			</div>
			<div class="form-group">
			  <label class="col-sm-3  control-label">Thn Ajaran</label>
			  <div class="col-sm-9">
				  <select class="form-control" id="cboperiode_text" name="periode_text" required>
                                      <option value=""></option>
                                      <?php
                                        $query = $this->db->query("SELECT * FROM m_periode");
                                        foreach ($query->result_array() as $data){
                                            echo "<option value='".$data['id_periode']."'>".$data['periode_text']."</option>";
                                        }
                                      ?>
				  </select>				  
			  </div>
			</div>
			<div class="form-group"> 
			  <label class="col-sm-3  control-label">Reguler</label>
			  <div class="col-sm-9">
                              <select class="form-control" id="cboreguler" name="reguler" required>
                                  <option value=""></option>
                                    <?php
                                        $query = $this->db->query("SELECT * FROM m_reguler where aktif='1'");
                                        foreach ($query->result_array() as $data){
                                            echo "<option value='".$data['kode']."'>".$data['kode']."</option>";
                                        }
                                    ?>
                               </select>				  
			  </div>
			</div>
			<div class="form-group">
			  <label class="col-sm-3  control-label">Jenis Pembayaran</label>
			  <div class="col-sm-9">
				  <select class="form-control" id="cbojenis_pembayaran" name="jenis_pembayaran" required>
                                      <option value=""></option>
                                      <?php
                                        $query = $this->db->query("SELECT * FROM jenis_pembayaran");
                                        foreach ($query->result_array() as $data){
                                            echo "<option value='".$data['id_jenis_pembayaran']."'>".$data['jenis_pembayaran']."</option>";
                                        }
                                      ?>
				  </select>				  
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
<!-- Select2 -->
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/select2/select2.full.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2.css" type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2-bootstrap.css" type="text/css"/>
<script type="text/javascript">  
	$(function(){ 
                $('#table_cust').DataTable({
                    "paging": false,
                    "lengthChange": false,
                    "searching": false,
                    "ordering": false,
                    "info": false,
                    "autoWidth": false,
                    "pageLength": 25,
                    "processing": true,
                    "serverSide": true,
                    "ajax": {
                        "url": "<?php echo base_url('siswa/datanaik'); ?>",
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
                            {"className": "dt-center", "targets": [0,1,2]}
                        ],
                    "columns": [
                            { "data": "cekbok" },
                            { "data": "urutan" },
                            { "data": "nis" },
                            { "data": "nama" },
                    ]
                });
                
                $('#table_cust2').DataTable({
                    "paging": false,
                    "lengthChange": false,
                    "searching": false,
                    "ordering": false,
                    "info": false,
                    "autoWidth": false,
                    "pageLength": 25,
                    "processing": true,
                    "serverSide": true,
                    "ajax": {
                        "url": "<?php echo base_url('siswa/datanaik'); ?>",
                        "data": function( d ) {
                                var send = $('#formFilter2').serializeArray();
                                $.each(send, function(i, v) {
                                    d[v.name] = v.value;
                                });
                              },      
                        "dataType": "json",
                        "type": "POST"
                    },
                    "columnDefs": [
                            {"className": "dt-center", "targets": [0,1,2]}
                        ],
                    "columns": [
                            { "data": "cekbok" },
                            { "data": "urutan" },
                            { "data": "nis" },
                            { "data": "nama" },
                    ]
                });
                
                //Initialize Table kiri
                $("#cbothn_ajaran").select2({
                        placeholder: "Pilih Tahun Pelajaran",
                        theme: "bootstrap",
                        minimumResultsForSearch: Infinity,
                        templateResult: formatState,
                        templateSelection: template
                });                
                $("#cbokelas").select2({
                        placeholder: "Pilih Salah Kelas",
                        theme: "bootstrap",
                        minimumResultsForSearch: Infinity
                });
                $("#cbokelas").on("select2:select", function (e){ 
                    var table = $('#table_cust').DataTable(); 
                        table.ajax.reload( null, false );
                });
                $("#cbothn_ajaran").on("select2:select", function (e){ 
                    var ini = e.params.data.id; 
                    var itu = $("#cbothn_ajaran2").val();
                    if(itu.length!=0){
                        if(ini!=itu){
                            swal("warning","Tahun Pelajaran Harus sama!","warning");
                            $(this).val(null).trigger("change");
                        }
                    }
                });
                
                //Initialize Table Kanan
                $("#cbothn_ajaran2").select2({
                        placeholder: "Pilih Tahun Pelajaran",
                        theme: "bootstrap",
                        minimumResultsForSearch: Infinity,
                        templateResult: formatState,
                        templateSelection: template
                }); 
                $("#cbothn_ajaran2").on("select2:select", function (e){ 
                    var table = $('#table_cust2').DataTable(); 
                        table.ajax.reload( null, false );
                });
                $("#cbothn_ajaran2").on("select2:select", function (e){ 
                    var ini = e.params.data.id; 
                    var itu = $("#cbothn_ajaran").val();
                    if(itu.length!=0){
                        if(ini!=itu){
                            swal("warning","Tahun Pelajaran Harus sama!","warning");
                            $(this).val(null).trigger("change");
                        }
                    }
                });

                $(document).on("click","#btnnaikkan",function(){
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
                        var noww = getActualFullDate();
                        var tapel = $("#cbothn_ajaran").val();
                        var kelas = $("#cbokelas").val();
                        var tapel2 = $("#cbothn_ajaran2").val();
                        if(tapel2.length==0){
                            swal("warning","Tahun Pelajaran Kelulusan Harus dipilih dahulu!","warning");
                        }else{
                            swal({   
                              title: "Lulus?",   
                              text: "Apa anda yakin, Lulus : "+tot+" Siswa?",   
                              type: "success",   
                              showCancelButton: true,   
                              confirmButtonColor: "#DD6B55",   
                              confirmButtonText: "Lulus",   
                              closeOnConfirm: true }, 
                              function(){   
                                var value = { id:sel.join("-"), tipe:'lulus', noww:noww, tapel:tapel, kelas:kelas, tapel2:tapel2, kelas2:'0' };
                                $.ajax({
                                        url : "<?php echo base_url('siswa/naikturun_kelas'); ?>",
                                        type: "POST",
                                        data : value,
                                        dataType: "json",
                                        success: function(data){
                                            if(data.status == 'success'){
                                                $.notify('Successfull, '+data.txt);
                                                var table = $('#table_cust').DataTable(); 
                                                table.ajax.reload( null, false );
                                                var table2 = $('#table_cust2').DataTable(); 
                                                table2.ajax.reload( null, false );
                                            }else{
                                                swal("Error"," "+data.txt+" ","error");
                                            }
                                        }
                                });
                            });
                        }
                    }else{
                        swal("warning","Centang Siswa dahulu!","warning");
                    }
                });

                $(document).on("click","#btnulang",function(){
                    // Iterate over all checkboxes in the table
                    var tot=0;
                    var sel=[];
                    var table = $('#table_cust2').DataTable(); 
                    table.$('input[type="checkbox"]').each(function(){
                        // If checkbox is checked
                        if(this.checked){
                            // Create a hidden element 
                            sel.push(this.name);
                            tot=tot+1;
                        }
                    });
                    
                    if(tot>0){
                        var noww = getActualFullDate();
                        var tapel = $("#cbothn_ajaran").val();
                        var kelas = $("#cbokelas").val();
                        var kelas_text = $("#cbokelas").select2('data')[0].text;
                        var tapel2 = $("#cbothn_ajaran2").val();
                        if(tapel.length==0){
                            swal("warning","Tahun Pelajaran Tujuan Harus dipilih dahulu!","warning");
                        }else if(kelas.length==0){
                            swal("warning","Kelas Tujuan Harus dipilih dahulu!","warning");
                        }else{
                            swal({   
                              title: "Ulang Kelas?",   
                              text: "Apa anda yakin, Ulang Kelas "+kelas_text+" : "+tot+" Siswa?",   
                              type: "success",   
                              showCancelButton: true,   
                              confirmButtonColor: "#DD6B55",   
                              confirmButtonText: "Ulang Kelas",   
                              closeOnConfirm: true }, 
                              function(){   
                                var value = { id:sel.join("-"), noww:noww, tipe:'ulang', tapel:tapel, kelas:kelas, tapel2:tapel2, kelas2:'0' };
                                $.ajax({
                                        url : "<?php echo base_url('siswa/naikturun_kelas'); ?>",
                                        type: "POST",
                                        data : value,
                                        dataType: "json",
                                        success: function(data){
                                            if(data.status == 'success'){
                                                $.notify('Successfull, '+data.txt);
                                                var table = $('#table_cust').DataTable(); 
                                                table.ajax.reload( null, false );
                                                var table2 = $('#table_cust2').DataTable(); 
                                                table2.ajax.reload( null, false );
                                            }else{
                                                swal("Error"," "+data.txt+" ","error");
                                            }
                                        }
                                });
                            });
                        }
                    }else{
                        swal("warning","Centang Siswa dahulu!","warning");
                    }
                });

                //if the letter is not digit then don't type anything
                $(".number-only").keypress(function (e) {
                    if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                            return false;
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
                
                // Handle click on "Select all" control
                $('#select-all2').on('click', function(){
                   // Get all rows with search applied
                   var table = $('#table_cust2').DataTable(); 
                   var rows = table.rows({ 'search': 'applied' }).nodes();
                   // Check/uncheck checkboxes for all rows in the table
                   $('input[type="checkbox"]', rows).prop('checked', this.checked);
                });
   
	}); 
        
        function template(data, container) {
            var pecah = data.text.split('*');
            var text = pecah[0];
            var aktif = pecah[1];
            return text;
        }
        function formatState(state) {
            var pecah = state.text.split('*');
            var text = pecah[0];
            var aktif = pecah[1];
            if (!state.id) { return text; }
            if(aktif==='1'){
                var $state = $( '<span>' + text + ' <span class="pull-right badge bg-green">Aktif</span> </span>' );
            }else{
                var $state = $( '<span>' + text + ' </span>' );
            }
            return $state;
        }
        function addZero(i) {
            if (i < 10) {
                i = "0" + i;
            }
            return i;
        }
        function getActualFullDate() {
            var d = new Date();
            var day = addZero(d.getDate());
            var month = addZero(d.getMonth()+1);
            var year = addZero(d.getFullYear());
            var h = addZero(d.getHours());
            var m = addZero(d.getMinutes());
            var s = addZero(d.getSeconds());
            return year + "-" + month + "-" + day + " " + h + ":" + m + ":" + s;
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
    padding: 1px;
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
                <h3 class="box-title">Kelulusan Kelas</h3>
            </div>
            <div class="box-body">
                <table width="100%">
                    <td width="43%" valign="top">            
                        <form id="formFilter" class="form-horizontal">
                            <div class="col-sm-12" style="padding-left:0px"> <b>Siswa Dari Kelas :</b>
                                <ul class="nav">
                                    <li>
                                        <div class="input-group">
                                            <div class="input-group-addon label-warning">Tapel</div>
                                            <select class="form-control" id="cbothn_ajaran" name="thn_ajaran" required>
                                                <option value=""></option>
                                                <?php
                                                  $query = $this->db->query("SELECT * FROM tb_thnajaran");
                                                  foreach ($query->result_array() as $data){
                                                      echo "<option value='".$data['id_thnajaran']."'>".$data['nama_thnajaran']."*".$data['aktif']."</option>";
                                                  }
                                                ?>
                                            </select>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="input-group">
                                            <div class="input-group-addon label-warning">Kelas</div>
                                            <select class="form-control" id="cbokelas" name="kelas" required>
                                                <option value=""></option>
                                                <?php
                                                  $query = $this->db->query("SELECT * FROM tb_kelas where aktif='1'");
                                                  foreach ($query->result_array() as $data){
                                                      echo "<option value='".$data['id_kelas']."'>".$data['nama_kelas']."</option>";
                                                  }
                                                ?>
                                            </select>
                                        </div>
                                    </li>
                                </ul>                
                            </div>
                        </form>            

                        <table id="table_cust" width="100%" class="table table-striped table-bordered nowrap ">
                            <thead class="label-primary">
                              <tr>
                                    <th style="width:5%"><center><input type="checkbox" name="select_all" value="1" id="select-all"></center></th>
                                    <th style="width:5%">No</th>
                                    <th style="width:20%">NIS</th>
                                    <th style="width:70%">Nama</th>
                              </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </td>
                    <td width="14%" align="center" valign="top" style="padding-top:97px;">            
                        <button type="button" class="btn btn-success" id="btnnaikkan" name="btnnaikkan">Lulus Kelas <i class="fa fa-angle-double-right"></i></button>
                        <button type="button" class="btn btn-danger" id="btnulang" name="btnulang" style="margin-top:10px;"><i class="fa fa-angle-double-left"></i> Ulang Kelas</button>
                    </td>
                    <td width="43%" valign="top">          
                        <form id="formFilter2" class="form-horizontal">
                            <div class="col-sm-12" style="padding-left:0px"> <b>Lulus :</b>
                                <ul class="nav">
                                    <li>
                                        <div class="input-group">
                                            <div class="input-group-addon label-warning">Tapel</div>
                                            <select class="form-control" id="cbothn_ajaran2" name="thn_ajaran" required>
                                                <option value=""></option>
                                                <?php
                                                  $query = $this->db->query("SELECT * FROM tb_thnajaran");
                                                  foreach ($query->result_array() as $data){
                                                      echo "<option value='".$data['id_thnajaran']."'>".$data['nama_thnajaran']."*".$data['aktif']."</option>";
                                                  }
                                                ?>
                                            </select>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="input-group">
                                            <div class="input-group-addon label-warning">Lulus</div>
                                            <input type="hidden" name="kelas" value="lulus">
                                            <input type="text" class="form-control" id="txtlulus" name="lulus" value="LULUS" readonly="readonly"/>
                                        </div>
                                    </li>
                                </ul>                
                            </div>
                        </form>            

                        <table id="table_cust2" width="100%" class="table table-striped table-bordered nowrap ">
                            <thead class="label-primary">
                              <tr>
                                    <th style="width:5%"><center><input type="checkbox" name="select_all2" value="1" id="select-all2"></center></th>
                                    <th style="width:5%">No</th>
                                    <th style="width:20%">NIS</th>
                                    <th style="width:70%">Nama</th>
                              </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>            
                    </td>
                </table>      
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col -->
</div><!-- /.row -->
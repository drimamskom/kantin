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
                        placeholder: "Pilih Kelas",
                        theme: "bootstrap",
                        minimumResultsForSearch: Infinity
                });               
                $("#cboreguler").select2({
                        placeholder: "Pilih Golongan",
                        theme: "bootstrap",
                        minimumResultsForSearch: Infinity
                }); 
                $("#cboreguler").on("select2:select", function (e){ 
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
                $("#cbokelas2").select2({
                        placeholder: "Pilih Kelas",
                        theme: "bootstrap",
                        minimumResultsForSearch: Infinity
                });              
                $("#cboreguler2").select2({
                        placeholder: "Pilih Golongan",
                        theme: "bootstrap",
                        minimumResultsForSearch: Infinity
                }); 
                $("#cboreguler2").on("select2:select", function (e){ 
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

                $(document).on("click","#btnpindah",function(){
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
                        var reguler = $("#cboreguler").val();
                        var tapel2 = $("#cbothn_ajaran2").val();
                        var kelas2 = $("#cbokelas2").val();
                        var reguler2 = $("#cboreguler2").val();
                        var kelas2_text = $("#cbokelas2").select2('data')[0].text;
                        if(tapel2.length==0){
                            swal("warning","Tahun Pelajaran Tujuan Harus dipilih dahulu!","warning");
                        }else if(kelas2.length==0){
                            swal("warning","Kelas Tujuan Harus dipilih dahulu!","warning");
                        }else if(reguler2.length==0){
                            swal("warning","Golongan Tujuan Harus dipilih dahulu!","warning");
                        }else{
                            swal({   
                              title: "Pindah Golongan?",   
                              text: "Apa anda yakin, Pindah ke Golongan "+reguler2+" : "+tot+" Siswa?",   
                              type: "success",   
                              showCancelButton: true,   
                              confirmButtonColor: "#DD6B55",   
                              confirmButtonText: "Pindah Golongan",   
                              closeOnConfirm: true }, 
                              function(){   
                                var value = { id:sel.join("-"), tipe:'pindah_gol', noww:noww, tapel:tapel, kelas:kelas, reguler:reguler, tapel2:tapel2, kelas2:kelas2, reguler2:reguler2 };
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
                                                //swal("warning",data.txt,"warning");
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

                $(document).on("click","#btnpindah2",function(){
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
                        var reguler = $("#cboreguler").val();
                        var kelas_text = $("#cbokelas").select2('data')[0].text;
                        var tapel2 = $("#cbothn_ajaran2").val();
                        var kelas2 = $("#cbokelas2").val();
                        var reguler2 = $("#cboreguler2").val();
                        if(tapel.length==0){
                            swal("warning","Tahun Pelajaran Tujuan Harus dipilih dahulu!","warning");
                        }else if(kelas.length==0){
                            swal("warning","Kelas Tujuan Harus dipilih dahulu!","warning");
                        }else if(reguler.length==0){
                            swal("warning","Golongan Tujuan Harus dipilih dahulu!","warning");
                        }else{
                            swal({   
                              title: "Pindah Golongan?",   
                              text: "Apa anda yakin, Pindah Ke Golongan "+reguler+" : "+tot+" Siswa?",   
                              type: "success",   
                              showCancelButton: true,   
                              confirmButtonColor: "#DD6B55",   
                              confirmButtonText: "Pindah Golongan",   
                              closeOnConfirm: true }, 
                              function(){   
                                var value = { id:sel.join("-"), tipe:'pindah_gol2', noww:noww, tapel:tapel, kelas:kelas, reguler:reguler, tapel2:tapel2, kelas2:kelas2, reguler2:reguler2 };
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
                <h3 class="box-title">Pindah Golongan</h3>
            </div>
            <div class="box-body">
                <table width="100%">
                    <td width="42%" valign="top">            
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
                                    <li>
                                        <div class="input-group">
                                            <div class="input-group-addon label-warning">Golongan</div>
                                            <select class="form-control" id="cboreguler" name="reguler" required>
                                                <option value=""></option>
                                                <?php
                                                    $query = $this->db->query("SELECT * FROM tb_golongan where aktif='1'");
                                                    foreach ($query->result_array() as $data){
                                                        echo "<option value='".$data['golongan']."'>".$data['nama_golongan']."</option>";
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
                    <td width="16%" align="center" valign="top" style="padding-top:120px;">            
                        <button type="button" class="btn btn-success" id="btnpindah" name="btnpindah">Pindah Golongan <i class="fa fa-angle-double-right"></i></button>
                        <button type="button" class="btn btn-danger" id="btnpindah2" name="btnpindah2" style="margin-top:10px;"><i class="fa fa-angle-double-left"></i> Pindah Golongan</button>
                    </td>
                    <td width="42%" valign="top">          
                        <form id="formFilter2" class="form-horizontal">
                            <div class="col-sm-12" style="padding-left:0px"> <b>Pindah Ke Kelas :</b>
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
                                            <div class="input-group-addon label-warning">Kelas</div>
                                            <select class="form-control" id="cbokelas2" name="kelas" required>
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
                                    <li>
                                        <div class="input-group">
                                            <div class="input-group-addon label-warning">Reguler</div>
                                            <select class="form-control" id="cboreguler2" name="reguler" required>
                                                <option value=""></option>
                                                <?php
                                                    $query = $this->db->query("SELECT * FROM tb_golongan where aktif='1'");
                                                    foreach ($query->result_array() as $data){
                                                        echo "<option value='".$data['golongan']."'>".$data['nama_golongan']."</option>";
                                                    }
                                                ?>
                                           </select>
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
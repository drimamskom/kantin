<?php
$kolom = array();
array_push($kolom, array("data" => "urutan"));
array_push($kolom, array("data" => "nama"));
$query = $this->db->query("SELECT akses FROM tb_akses WHERE aktif='1' ");
foreach ($query->result_array() as $data){
    $datax = array("data" => $data['akses']);
    array_push($kolom, $datax);
}
?>
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
                        "url": "<?php echo base_url('aksesrole/data'); ?>",      
                        "dataType": "json",
                        "type": "POST"
                    },
                    "columnDefs": [
                            {"className": "dt-center", "targets": [0,2]}
                        ],
                    "columns": <?php echo json_encode($kolom);?>
                });

                $(document).on("click","#btncari",function(){
                        var table = $('#table_cust').DataTable(); 
                        table.ajax.reload( null, false );
                });
                                
                $('.head-row').click(function(){
                    var idname = this.id;
                    var pecah = idname.split('_');
                    var akses = pecah[1];
                    var cek = $("#"+idname).prop("checked");
                    if(cek){
                        $("."+akses).prop("checked", true);
                    }else{
                        $("."+akses).prop("checked", false);
                    }
                });

		$('#myform').on('submit', function(e) {
                    e.preventDefault();
                    var post_data = $(this).serialize();
                    $('#btnsave').button('loading');
                    $.ajax({
                        url : "<?php echo base_url('aksesrole/save'); ?>",
                        type: "POST",
                        data : post_data,
                        dataType: "json",
                        success: function(data){
                            $('#btnsave').button('reset');
                            if(data.status == 'success'){
                                $.notify('Successfull save data');
                                var table = $('#table_cust').DataTable(); 
                                table.ajax.reload( null, false );
                            }else{
                                swal("warning",data.txt,"warning");
                            }
                        }
                    });
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
.table tfoot > tr td {
    padding: 3px;
    /* border: 1px solid #ccc; */
}
.table tr td {
    border: 1px solid #ccc;
}
.fot-style{
    font-weight:bold;
}
</style>
<div class="row">
    <div class="col-md-12">
        <!-- Default box -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Akses Menu Role</h3>
            </div>
            <div class="box-body">                
                <form id="myform">
                <table id="table_cust" width="100%" class="table table-bordered table-striped">
                    <thead class="label-primary">
                        <tr>
                            <?php
                            foreach ($kolom as $key => $akses) {
                                $act = $akses["data"];
                                if($key==0){
                                   echo '<th style="width:5%">No</th>'; 
                                }else if($key==1){
                                   echo '<th style="width:25%">Nama Menu</th>'; 
                                }else{
                                  echo '<th style="width:10%" class="dt-center">';
                                  echo '<input type="checkbox" id="head_'.$act.'" class="head-row">&nbsp;';
                                  echo ucfirst($act).'</th>';
                                }
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">
                                <button type="submit" class="btn btn-primary pull-right" id="btnsimpan" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Loading..."><i class="fa fa-save"></i>&nbsp; Simpan</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                </form>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col -->
</div><!-- /.row -->
<!-- Select2 -->
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/select2/select2.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2.css" type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2-bootstrap.css" type="text/css"/>
<script type="text/javascript">  
    $(function(){ 
            $('#myform').on('submit', function(e) {
                    e.preventDefault();
		    var post_data = $(this).serialize();
		    $('#btnsave').button('loading');
		    var ps1 = $('#txtnewpin').val();
		    var ps2 = $('#txtrepin').val();
		    if(ps1 == ps2){
		    	$.ajax({
                            url : "<?php echo base_url('user/act_change_pin'); ?>",
                            type: "POST",
                            data : post_data,
                            dataType: "json",
                            success: function(data){
                            $('#btnsave').button('reset');
                                if(data.status == 'success'){
                                    $.notify('Successfull save data');
                                    $("#myform").trigger('reset'); 
                                    $("#type").val("change");
                                }else{
                                    swal("warning",data.txt,"warning");
                                }
                            }
                        });
		    }else{
	    		$('#btnsave').button('reset');
		    	swal("warning","Retype Pasword Salah","warning");
		    }
            });
            
            $('#txtidcard').focus();
    }); 
</script> 
<?php
$date = date('Y-m-d');
$sesi = $this->session->get_userdata();
$userr = $sesi['username'];
?>

<div class="row">
    <div class="col-md-12">
        <!-- Default box -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Ganti PIN</h3>
            </div>
            <div class="box-body">
                <form id="myform" class="form-horizontal">
                    <div class="form-group"> 
                      <label class="col-sm-2 control-label">Tempelkan Kartu</label>
                      <div class="col-sm-9">
                          <input type="password" class="form-control" id="txtidcard" name="idcard" required/>
                          <input type="hidden" name="type" id="type" value="change"> 
                      </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-2 control-label">PIN Baru</label>
                      <div class="col-sm-9">
                          <input type="password" class="form-control" id="txtnewpin" name="newpin" placeholder="PIN Baru" required/>
                            </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-2 control-label">Ketik Ulang PIN</label>
                      <div class="col-sm-9">
                              <input type="password" class="form-control" id="txtrepin" name="repin" placeholder="Ketik Ulang PIN" required/>
                            </div>
                    </div>
                    <div class="form-group"> 
                      <label class="col-sm-2 control-label"></label>
                      <div class="col-sm-9">
                        <button type="submit" class="btn btn-primary " id="btnsave" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Loading..."><i class="fa fa-save"></i>&nbsp; Simpan</button>
                            <button type="reset" class="btn btn-default"> Reset</button>
                      </div>
                    </div>
                </form>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col -->
</div><!-- /.row -->
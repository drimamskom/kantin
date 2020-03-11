<script type="text/javascript">  
    $(function(){         
        // We can attach the `fileselect` event to all file inputs on the page
        $(document).on('change', ':file', function() {
          var input = $(this),
              numFiles = input.get(0).files ? input.get(0).files.length : 1,
              label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
          input.trigger('fileselect', [numFiles, label]);
        });

        // We can watch for our custom `fileselect` event like this
        $(document).ready( function() {
            $(':file').on('fileselect', function(event, numFiles, label) {

                var input = $(this).parents('.input-group').find(':text'),
                    log = numFiles > 1 ? numFiles + ' files selected' : label;

                if( input.length ) {
                    input.val(log);
                } else {
                    if( log ) alert(log);
                }

            });
        });
    }); 
</script> 
<style>
.btn-file {
    position: relative;
    overflow: hidden;
}
.btn-file input[type=file] {
    position: absolute;
    top: 0;
    right: 0;
    min-width: 100%;
    min-height: 100%;
    font-size: 100px;
    text-align: right;
    filter: alpha(opacity=0);
    opacity: 0;
    outline: none;
    background: white;
    cursor: inherit;
    display: block;
}
</style>
<div class="row">
    <div class="col-md-12">
        <!-- Default box -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Upload Depot Baru</h3>
            </div>
            <div class="box-body">
                <?php echo @$pesan; ?>
                <?php echo form_open_multipart('depot/do_upload', array('class' => 'upload-form col-md-6')); ?>
                    <div class="form-group"> 
                        <label class="control-label">Pilih File Excel</label>
                        <div class="input-group">
                            <label class="input-group-btn">
                                <span class="btn btn-primary">
                                    Browse &hellip; <input type="file" id="upfiles" name="upfiles" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" style="display: none;" >
                                </span>
                            </label>
                            <input type="text" class="form-control" readonly>
                        </div>
                        <span id="infox2" class="help-block">
                            Dokument Upload harus file excel, dan berformat <b>.xls/.xlsx</b> <br>
                            Klik <a href="<?php echo base_url('assets/template/template_upload_depot_baru.xls');?>"><b><u>Disini</u></b></a> Untuk Download Template Uploadnya. 
                        </span>
                    </div>
                    <div class="form-group"> 
                        <a href="<?php echo base_url('depot') ?>" class="btn btn-warning"><i class="fa fa-angle-left"></i>&nbsp; Kembali</a>
                        <div class="pull-right">
                            <button type="submit" class="btn btn-primary" id="btnupload" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Loading..."><i class="fa fa-upload"></i>&nbsp; Upload</button>
                        </div>
                    </div>
                <?php echo form_close(); ?>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col -->
</div><!-- /.row -->
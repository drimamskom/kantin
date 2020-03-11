<!-- Select2 -->
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/select2/select2.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2.css" type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2-bootstrap.css" type="text/css"/>
<script type="text/javascript">  
    $(function(){ 
        //Initialize Select2 Elements
        $(".select2").select2({
                placeholder: "Pilih Tahun Pelajaran",
                theme: "bootstrap",
                minimumResultsForSearch: Infinity
        });
        
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
                <h3 class="box-title">Upload Siswa Baru</h3>
            </div>
            <div class="box-body">
                <?php echo @$pesan; ?>
                <?php echo form_open_multipart('siswa/do_upload', array('class' => 'upload-form col-md-6')); ?>
                    <div class="form-group">
                        <label class="control-label">Tahun Ajaran</label>
                        <select class="form-control select2" id="cbothn_ajaran" name="thn_ajaran" required>
                            <option value=""></option>
                            <?php
                              $query = $this->db->query("SELECT * FROM tb_thnajaran");
                              foreach ($query->result_array() as $data){
                                  if($data['aktif']=='1'){ 
                                      $aktif = '(<span class="pull-right badge bg-green">Aktif</span>)';
                                  }else{
                                      $aktif = '';
                                  }
                                  echo "<option value='".$data['id_thnajaran']."'>".$data['nama_thnajaran']." ".$aktif."</option>";
                              }
                            ?>
                        </select>
                    </div>
                    <div class="form-group"> 
                        <label class="control-label">Data Siswa Baru</label>
                        <div class="input-group">
                            <label class="input-group-btn">
                                <span class="btn btn-primary">
                                    Browse &hellip; <input type="file" id="upfiles" name="upfiles" style="display: none;" >
                                </span>
                            </label>
                            <input type="text" class="form-control" readonly>
                        </div>
                        <span id="infox2" class="help-block">
                            Dokument Upload harus file excel, dan berformat <b>.xls/.xlsx</b>
                            <a href="<?php echo base_url('assets/template/template_upload_siswa_baru.xls');?>"><u>Template Excel</u></a>
                        </span>
                    </div>
                    <div class="form-group"> 
                        <a href="<?php echo base_url('siswa') ?>" class="btn btn-warning"><i class="fa fa-angle-left"></i>&nbsp; Kembali</a>
                        <div class="pull-right">
                            <button type="submit" class="btn btn-primary" id="btnupload" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Loading..."><i class="fa fa-upload"></i>&nbsp; Upload</button>
                        </div>
                    </div>
                <?php echo form_close(); ?>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col -->
</div><!-- /.row -->
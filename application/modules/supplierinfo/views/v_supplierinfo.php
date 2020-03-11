<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SMANEMART</title>
  <link rel="icon" href="<?php echo base_url();?>assets/img/smanema.png">
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.5 -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/bootstrap/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/bootstrap/css/ionicons.min.css">
  <!-- Theme -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/dist/css/AdminLTE.min.css">
  <!-- Skin -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/dist/css/skins/skin-blue.min.css">
  <!-- SweetAlert -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/sweetalert/sweetalert.css">
  <!-- datatables -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/datatables/dataTables.bootstrap.css">
  <!-- datepicker --> 
  <link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/datepicker/datepicker3.css" type="text/css" />
  <!-- Bootstrap-validator --> 
  <link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/bootstrap-validator/bootstrapValidator.css" type="text/css" />

    <!-- jQuery 2.1.4 -->
    <script src="<?php echo base_url();?>assets/plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="<?php echo base_url();?>assets/bootstrap/js/bootstrap.min.js"></script>
    <!-- AdminLTE App -->
    <script src="<?php echo base_url();?>assets/dist/js/app.min.js"></script>
    <!-- SlimScroll -->
    <script src="<?php echo base_url();?>assets/plugins/slimScroll/jquery.slimscroll.min.js"></script>
    <!-- FastClick -->
    <script src="<?php echo base_url();?>assets/plugins/fastclick/fastclick.min.js"></script>
    <!-- SweetAlert -->
    <script src="<?php echo base_url();?>assets/plugins/sweetalert/sweetalert.min.js"></script>
    <!-- Bootstrap-notify -->
    <script src="<?php echo base_url();?>assets/plugins/bootstrap-notify/bootstrap-notify.min.js"></script>
    <!-- responsive datatables -->
    <script src="<?php echo base_url();?>assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo base_url();?>assets/plugins/datatables/dataTables.bootstrap.min.js"></script>
    <script src="<?php echo base_url();?>assets/plugins/datatables/extensions/Responsive/js/dataTables.responsive.min.js"></script>
    <!-- datepicker --> 
    <script src="<?php echo base_url();?>assets/plugins/datepicker/bootstrap-datepicker.js" type="text/javascript"></script>
    <!-- Bootstrap-validator --> 
    <script src="<?php echo base_url();?>assets/plugins/bootstrap-validator/bootstrapValidator.js" type="text/javascript"></script>
    <!-- Select2 -->
    <script type="text/javascript" src="<?php echo base_url();?>assets/plugins/select2/select2.js"></script>
    <link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2.css" type="text/css"/>
    <link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2-bootstrap.css" type="text/css"/>
    <script type="text/javascript"> 
    $(function () { 
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
                "url": "<?php echo base_url('supplierinfo/data'); ?>",
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
                    {"className": "dt-center", "targets": [0,4]}
                ],
            "columns": [
                    { "data": "urutan" },
                    { "data": "nama_barang" },
                    { "data": "satuan" },
                    { "data": "harga" },
                    { "data": "input" },
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
                "url": "<?php echo base_url('supplierinfo/data2'); ?>",
                "data": function( d ) {
                            var send = $('#formFilter2').serializeArray();
                            $.each(send, function(i, v) {
                                d[v.name] = v.value;             
                            });
                        },      
                "dataType": "json",
                "type": "POST"
            },
            "initComplete": function( settings, json ) {
                addFooter(json);
            },
            "columnDefs": [
                    {"className": "dt-center", "targets": [0,4,6,8,10]}
            ],
            "columns": [
                    { "data": "urutan" },
                    { "data": "nama_barang" },
                    { "data": "satuan" },
                    { "data": "harga" },
                    { "data": "stok" },
                    { "data": "jumstok" },
                    { "data": "laku" },
                    { "data": "jumlaku" },
                    { "data": "retur" },
                    { "data": "jumretur" },
                    { "data": "sisa" },
                    { "data": "jumsisa" },
            ]
        });
               
        //aksi submit pembayaran
        $('#formStok').on('submit', function(e) {
            e.preventDefault();
            $("#overlay-saving").show();
            var post_data = $(this).serialize();
            $.ajax({
                url : "<?php echo base_url('supplierinfo/save'); ?>",
                type: "POST",
                data : post_data,
                dataType: "json",
                success: function(data){
                    $("#overlay-saving").hide();
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
                    
        //if the letter is not digit then don't type anything
        $('#table_cust').on('keypress', '.number-only', function(e) {
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                        return false;
                }else{
                    $(this).parent().find("#kode[101210]").html('dddd');                            
                }
        });
                
        //Fungsi enter langsung mencari
        $("#txtcari1").keypress(function (e) {
            var key = e.which;
            if(key == 13){
                var table = $('#table_cust').DataTable(); 
                table.ajax.reload( null, false );
            }
        });
        $("#txtcari2").keypress(function (e) {
            var key = e.which;
            if(key == 13){
                var table2 = $('#table_cust2').DataTable(); 
                table2.ajax.reload( function ( json ) {
                    addFooter(json);
                });
            }
        });
        
        $(document).on("click","#btnexport",function(){
            var cari = $("#txtcari").val();
            var tgl_cari = $("#tgl_cari").val();
            $("#carian").val(cari);
            $("#tgl_carix").val(tgl_cari);
            $('#export_form').submit();
        });
        
        //Datepicker
        $('.datepickerr').datepicker({
            autoclose: true,
            todayHighlight: true,
            format : 'dd/mm/yyyy',
            beforeShow: function (input, inst){ 
                if($(input).attr('readonly') !== undefined ) {
                    if(inst.o_dpDiv === undefined) 
                        inst.o_dpDiv = inst.dpDiv;
                        inst.dpDiv = $('<div style="display: none;"></div>');
                }else{
                    if(inst.o_dpDiv !== undefined) {
                        inst.dpDiv = inst.o_dpDiv;
                    }
                }
            }
        });         
        //Set selection select2
        $('.select2').select2({
            minimumResultsForSearch: Infinity,
            theme: "bootstrap"
        }); 
        //Notify Animation Default
        $.notifyDefaults({
          type: 'success',
          delay: 500
        });   
        
        $("#overlay-saving").hide();
    });     
    function addFooter(json){
        var datajson = json.totalz;
        $('#jumstok').html("Rp. "+addPeriod(datajson.jumstok));
        $('#jumlaku').html("Rp. "+addPeriod(datajson.jumlaku));
        $('#jumretur').html("Rp. "+addPeriod(datajson.jumretur));
        $('#jumsisa').html("Rp. "+addPeriod(datajson.jumsisa));
    }
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
        th.dt-center, td.dt-center { text-align: center; padding-right:10px; }       
        .inp-gede{
            height:50px;
            font-weight:bold;
            font-size:34px;
            text-align:right;
        }
        .fot-style{
            font-weight:bold;
        }
        /* Modal Center */
        .modal {
            text-align: center;
            padding: 0!important;
        }
        .modal:before {
            content: '';
            display: inline-block;
            height: 100%;
            vertical-align: middle;
            margin-right: -4px;
        }
        .modal-dialog {
            display: inline-block;
            text-align: left;
            vertical-align: middle;
        }
    </style>
    </head>
    <body class="hold-transition skin-blue layout-top-nav">
        <!-- Site wrapper -->
        <div class="wrapper">
          <header class="main-header">
            <nav class="navbar navbar-static-top">
              <div class="container">
                <div class="navbar-header">
                  <a href="<?php echo base_url('supplierinfo');?>" class="navbar-brand"><b>SMANE</b>MART</a>
                  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                    <i class="fa fa-bars"></i>
                  </button>
                </div>
                <div class="navbar-custom-menu">
                  <ul class="nav navbar-nav">
                      <li class="dropdown messages-menu">
                          <a href="<?php echo base_url('login/logout');?>" class="btn btn-warning">
                              <i class="fa fa-sign-out"></i> Logout
                          </a>
                      </li>
                  </ul>
                </div>
              </div><!-- /.container-fluid -->
            </nav>
          </header>        

          <!-- =============================================== -->

          <!-- Content Wrapper. Contains page content -->
          <div class="content-wrapper">
            <!-- Main content -->
            <section class="content">
              <div class="row">      
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header">
                            <div class="nav-tabs-custom" style="margin-bottom:5px;">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#tab_1" data-toggle="tab">Input Stok Harian (Mandiri)</a></li>
                                    <li><a href="#tab_2" data-toggle="tab">Laporan Stok Harian (Mandiri)</a></li>
                                </ul>
                            </div>
                        <div class="box-body"> 
                            <div class="tab-content">
                                <!-- TAB 1 -->
                                <div class="tab-pane active" id="tab_1">
                                    <?php
                                        $namabln = array("KS", "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
                                                         "Juli", "Agustus", "September", "Oktober", "November", "Desember");
                                        $sesi = $this->session->get_userdata();
                                        $userr = $sesi['username'];
                                        $nomor_induk = $sesi['nomor_induk'];
                                        $nama_lengkap = $sesi['fullname'];
                                        $akses = $sesi['akses'];
                                        $blnx = intval(date('m'));
                                        $tgl_manusia = date('d')." ".$namabln[$blnx]." ".date('Y');
                                    ?>
                                    <form id="formFilter" class="form-horizontal">
                                        <div class="col-sm-9" style="padding-left:0px">
                                            <ul class="nav nav-pills">
                                                <li>
                                                    Tgl : <b> <?php echo $tgl_manusia;?> </b>                                     
                                                </li>
                                                <li style="padding-left:10px;">
                                                    Supplier : <b> <?php echo $nama_lengkap;?> </b>
                                                </li>
                                            </ul>                
                                        </div>
                                        <div class="col-sm-3" style="padding-right:0px">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="txtcari1" name="cari" placeholder="Pencarian"/>
                                                <span class="input-group-btn">
                                                  <button type="button" class="btn btn-info" id="btncari"><i class="fa fa-search"></i> Cari</button>
                                                </span>
                                            </div><!-- /input-group -->
                                        </div>
                                    </form>
                                    <form id="formStok">
                                    <table id="table_cust" width="100%" class="table table-bordered table-striped">
                                        <thead>
                                          <tr>
                                                <th style="width:5%">NO</th>
                                                <th style="width:40%">NAMA MENU</th>
                                                <th style="width:20%">SATUAN</th>
                                                <th style="width:20%">HARGA</th>
                                                <th style="width:15%">STOK</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="5">
                                                    <button type="submit" class="btn btn-primary btn-lg pull-right" id="btnsave"><i class="fa fa-save"></i> SIMPAN</button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    </form>
                                </div><!-- /.tab_1 -->
                                <!-- TAB 2 -->
                                <div class="tab-pane" id="tab_2">
                                    <div class="table-toolbarnya" style="margin-bottom:10px;">
                                        <form id="export_form" action="<?php echo base_url('supplierinfo/export'); ?>" method="post" target="_self">
                                            <input type="hidden" id="carian" name="cari" value=""/>
                                            <input type="hidden" id="tgl_carix" name="tgl_cari" value=""/>
                                            <input type="hidden" name="umpan" value="umpan"/>
                                            <input type="hidden" name="draw" value="1"/>
                                        </form>  
                                        
                                        <form id="formFilter2" class="form-horizontal">
                                            <div class="col-sm-6" style="padding-left:0px">
                                                <ul class="nav nav-pills">
                                                    <li><button type="button" class="btn btn-success" id="btnexport"><i class="fa fa-file-excel-o"></i> Export Excel</button></li>
                                                    <li style="padding-left:15px">
                                                        <div class="input-group">
                                                            <div class="input-group-addon label-warning">Supplier</div>
                                                            <input type="text" class="form-control" style="width:200px;" value="<?php echo $nama_lengkap;?>" readonly/>
                                                        </div>
                                                    </li>
                                                </ul>                
                                            </div>
                                            <div class="col-sm-6" style="padding-right:0px">
                                                <ul class="nav nav-pills pull-right">
                                                    <li>
                                                        <div class="input-group">
                                                            <div class="input-group-addon label-warning">Tanggal</div>
                                                            <input type="text" class="form-control pull-right datepickerr" name="tgl_cari" id="tgl_cari" style="width:100px;" value="<?php echo date('d/m/Y');?>">
                                                        </div>
                                                    </li>
                                                    <li style="padding-left:5px">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="txtcari2" name="cari" placeholder="Pencarian"/>
                                                            <span class="input-group-btn">
                                                                <button type="button" class="btn btn-info" id="btncari"><i class="fa fa-search"></i> Cari</button>
                                                            </span>
                                                        </div>
                                                    </li>
                                                </ul> 
                                            </div>
                                        </form>
                                    </div>
                                    <table id="table_cust2" width="100%" class="table table-bordered table-striped">
                                        <thead class="label-primary">
                                          <tr>
                                                <th style="width:3%;vertical-align: middle;padding-right:10px;" rowspan="2">No</th>
                                                <th style="width:30%;vertical-align: middle;" rowspan="2">Nama Menu</th>
                                                <th style="width:5%;vertical-align: middle;" rowspan="2">Satuan</th>
                                                <th style="width:10%;vertical-align: middle;" rowspan="2">harga</th>
                                                <th style="width:13%;vertical-align: middle;" colspan="2"><center>Stok</center></th>
                                                <th style="width:13%;vertical-align: middle;" colspan="2"><center>Laku</center></th>
                                                <th style="width:13%;vertical-align: middle;" colspan="2"><center>Retur</center></th>
                                                <th style="width:13%;vertical-align: middle;" colspan="2"><center>Sisa</center></th>
                                          </tr>
                                          <tr>
                                                <th style="width:3%;padding-right:10px;">Jumlah</th>
                                                <th style="width:10%;padding-right:10px;">Rupiah</th>
                                                <th style="width:3%;padding-right:10px;">Jumlah</th>
                                                <th style="width:10%;padding-right:10px;">Rupiah</th>
                                                <th style="width:3%;padding-right:10px;">Jumlah</th>
                                                <th style="width:10%;padding-right:10px;">Rupiah</th>
                                                <th style="width:3%;padding-right:10px;">Jumlah</th>
                                                <th style="width:10%;padding-right:10px;">Rupiah</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td style="text-align:right;padding-right:10px;"><span class="fot-style">Total</span></td>
                                                <td><span id="jumstok" class="fot-style"></span></td>
                                                <td style="text-align:right;padding-right:10px;"><span class="fot-style">Total</span></td>
                                                <td><span id="jumlaku" class="fot-style"></span></td>
                                                <td style="text-align:right;padding-right:10px;"><span class="fot-style">Total</span></td>
                                                <td><span id="jumretur" class="fot-style"></span></td>
                                                <td style="text-align:right;padding-right:10px;"><span class="fot-style">Total</span></td>
                                                <td><span id="jumsisa" class="fot-style"></span></td>
                                            </tr>             
                                        </tfoot>
                                    </table>
                                </div><!-- /.tab_2 -->
                            </div><!-- /.tab_content -->
                        </div><!-- /.box-body -->  
                        <div id="overlay-saving" class="overlay">
                            <i class="fa fa-refresh fa-spin"></i>
                        </div>
                    </div><!-- /.box -->
                </div><!-- /.col -->
            </div><!-- /.row -->
                        
            </section><!-- /.content -->
            <!-- ========================================================================================================== -->

          </div><!-- /.content-wrapper -->
          
        <!-- Main Footer -->
        <footer class="main-footer">
          <!-- To the right -->
          <div class="pull-right hidden-xs">
          <!-- Text right in here -->
          </div>
          <!-- Default to the left -->
          <strong>Copyright &copy; 2016. </strong> All rights reserved.
        </footer>
         
    </div><!-- ./wrapper -->

  </body>
  </html>
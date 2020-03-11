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
    <script>
      $(function () {
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
        //Notify Animation Default
        $.notifyDefaults({
          type: 'success',
          delay: 500
        });
        //Menu Active Handler
        //var url = window.location.pathname;  
        //var activePage = url.substring(url.lastIndexOf('/')+1);
        var activePage = "<?php echo $this->uri->segment(1); ?>";
        $('.sidebar-menu li a').each(function(){  
            var currentPage = this.href.substring(this.href.lastIndexOf('/')+1);
            if (activePage === currentPage) {
                $(this).parent().addClass("active");
                $(this).parent().parent().addClass("menu-open");
                $(this).parent().parent().parent().addClass("active");
            } 
        });
      });
    </script> 
    <style type="text/css">
      .table{
        font-size:13px;
      }
      table.dataTable thead > tr > th{
        padding-right: 10px;
      }
    </style> 
    </head>
    <body class="hold-transition skin-blue sidebar-mini">
        <!-- Site wrapper -->
        <div class="wrapper">

          <header class="main-header">
            <!-- Logo -->
            <a href="#" class="logo">
              <!-- mini logo for sidebar mini 50x50 pixels -->
              <span class="logo-mini"><b>MART</b></span>
              <!-- logo for regular state and mobile devices -->
              <span class="logo-lg"><b>SMANE</b>MART</span>
            </a>
            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top" role="navigation">
              <!-- Sidebar toggle button-->
              <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </a>
              <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li class="dropdown messages-menu">
                        <a href="javascript:location.reload();" class="btn btn-warning">
                            <i class="fa fa-refresh"></i> Refresh
                        </a>
                    </li>
                </ul>
              </div>
            </nav>
          </header>

          <!-- =============================================== -->
          <?php          
            $sesi = $this->session->get_userdata();
            $userr = $sesi['username'];
            $nomor_induk = $sesi['nomor_induk'];
            $fullname = $sesi['fullname'];
            $akses = $sesi['akses'];
          ?>
          
          <!-- Left side column. contains the sidebar -->
          <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
              <!-- Sidebar user panel -->
              <div class="user-panel">
                <div class="pull-left image">
                  <img src="<?php echo base_url();?>assets/dist/img/avatar5.png" class="img-circle" alt="User Image">
                </div>
                <div class="pull-left info">
                  <p><?php echo $fullname; ?></p>
                  <a href="#"><i class="fa fa-circle text-success"></i> <?php echo $akses; ?></a>
                </div>
              </div>
              <!-- sidebar menu: : style can be found in sidebar.less -->
              <ul class="sidebar-menu">
                    <li class="header">MAIN NAVIGATION</li>
                    <?php
                        // data main menu
                        $main_menu = $this->db->query("SELECT * FROM tb_menu WHERE parent='0' AND aktif='1' AND main='1'
                                                        AND id IN (SELECT menu FROM tb_user_role WHERE akses = '$akses' )
                                                        ORDER BY urut");
                        foreach ($main_menu->result() as $main) {
                            // Query untuk mencari data sub menu
                            $sub_menu = $this->db->query("SELECT * FROM tb_menu WHERE parent='".$main->id."' AND aktif='1' AND main='1' 
                                                        AND id IN (SELECT menu FROM tb_user_role WHERE akses = '$akses' )
                                                        ORDER BY urut");
                            // periksa apakah ada sub menu
                            if ($sub_menu->num_rows() > 0) {
                                // main menu dengan sub menu
                                echo "<li class='treeview'>" . anchor($main->link, '<i class="' . $main->icon . '"></i> <span>' . $main->judul_menu .
                                        '</span> <i class="fa fa-angle-left pull-right"></i>');
                                // sub menu nya disini
                                echo "<ul class='treeview-menu'>";
                                foreach ($sub_menu->result() as $sub) {
                                    echo "<li>" . anchor($sub->link, '<i class="' . $sub->icon . '"></i> <span>' . $sub->judul_menu) . "</span> </li>";
                                }
                                echo"</ul></li>";
                            } else {
                                // main menu tanpa sub menu
                                echo "<li>" . anchor($main->link, '<i class="' . $main->icon . '"></i> <span>' . $main->judul_menu) . "</span> </li>";
                            }
                        }
                    ?>                  
              </ul>
            </section>
            <!-- /.sidebar -->
          </aside>

          <!-- =============================================== -->

          <!-- Content Wrapper. Contains page content -->
          <div class="content-wrapper">
            <!-- Content Header (Page header) 
            <section class="content-header">
            </section>
			
            <!-- ========================================================================================================== -->
            <!-- Main content -->
            <section class="content">
				
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
    <!-- Bootstrap-validator --> 
    <script src="<?php echo base_url();?>assets/plugins/bootstrap-validator/bootstrapValidator.js" type="text/javascript"></script>
    <!-- Select2 -->
    <script type="text/javascript" src="<?php echo base_url();?>assets/plugins/select2/select2.js"></script>
    <link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2.css" type="text/css"/>
    <link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2-bootstrap.css" type="text/css"/>
    <script type="text/javascript"> 
    $(function () { 
        var n = 0;
        var kodex = [];
        var data = [];
        var option = [];
        for (var i = 1; i < 21; i++) {
            option += '<option value="'+i+'">'+i+'</option>';
        }
        
        $("#idcard").val('');
        $("#idcard").focus();
        refreshMenuList('1');
        refreshMenuList('2');
        var lock = "<?php echo $info; ?>";
        if(lock=="gagal"){
            $("#overlay-login").show();
            $("#overlay-menu").show();
        }else{
            $("#overlay-login").hide();
            $("#overlay-menu").hide();
        }   
        //Set selection select2
        $('.select2').select2({
            minimumResultsForSearch: Infinity,
            theme: "bootstrap"
        }); 
        //List Pesanan
        $(document).on("click","#btncari1",function(){
            refreshMenuList('1');
        });  
        $(document).on("click",".btnpesan",function(){
            var kode = $(this).attr("kodeex");
            var menu = $(this).attr("menuex");
            var harga= $(this).attr("hargaex");
            var jum  = $(this).attr("jumex");
            if(jum!='0'){
                if($.inArray(kode, kodex)!='-1') {
                    // console.log(kode + ' is in the array!');
                    var val = parseInt(data[kode].val)+1;
                    var fieldNum = data[kode].field; 
                    $("#inpjumlah_"+fieldNum).select2().val(val).trigger("change");
                    data[kode] = {field:fieldNum, val:val}; 
                    settotal();
                    //Set selection select2
                    $('.select2').select2({
                        minimumResultsForSearch: Infinity,
                        theme: "bootstrap"
                    });
                }else{
                    // console.log(kode + ' is NOT in the array...');
                    n = n+1;  
                    var tr = '<tr id="field_'+n+'" class="temp-row">'+
                                '<td align="center"><button id="remove_'+n+'" val="'+kode+'" class="btn btn-danger btn-xs delete-row"><i class="glyphicon glyphicon-remove"></i></button></td>'+
                                '<td id="'+kode+'" class="tanda"><input type="hidden" name="kode[]" value="'+kode+'"/> <span id="txtobat_'+n+'">'+menu+'</span></td>'+
                                '<td><select id="inpjumlah_'+n+'" name="jumlah[]" class="form-control select2 jumlahx">'+option+'</select></td>'+
                                '<td><input type="hidden" id="inpharga_'+n+'" name="harga[]" value="'+harga+'"/> <span id="txtharga_'+n+'">Rp, '+addPeriod(harga)+'</span></td>'+
                                '<td><input type="hidden" id="inptotal_'+n+'" class="totals" name="total[]" value="'+harga+'"/> <span id="txttotal_'+n+'">Rp, '+addPeriod(harga)+'</span></td>'+
                            '</tr>';
                    $('#list_infois > tbody').append(tr);
                    settotal();	
                    kodex = [];
                    $('#list_infois > tbody tr').each(function(){
                        var tr_id  = $(this).attr('id');
                        var pecah  = tr_id.split('_');
                        var field  = pecah[1];
                        var kode = $(this).find('td.tanda').attr('id');
                        kodex.push(kode);
                        data[kode] = {field:field, val:1};                
                    });
                    //Set selection select2
                    $('.select2').select2({
                        minimumResultsForSearch: Infinity,
                        theme: "bootstrap"
                    });
                }
                $(this).attr("jumex",parseInt(jum)-1);
                $(this).find(".jumnya").html(parseInt(jum)-1);
            }else{
                swal("Stok Sudah Habis","tekan Esc untuk exit!","warning");
            }
        });
        $(document).on("change",".jumlahx",function(){
            var idname = this.id;
            var pecah = idname.split('_');
            var fieldNum = pecah[1];                                    
            var valbaru = $(this).val();                      
            var harga = $("#inpharga_"+fieldNum).val();  
            var total = parseFloat(harga)*parseFloat(valbaru);
            $("#inptotal_"+fieldNum).val(total);
            $("#txttotal_"+fieldNum).html('Rp, '+addPeriod(total));
            settotal();	
        }); 
        //saat ganti jumlah  
        $(document).on("click",".delete-row",function(){
            var idname = this.id;
            var pecah = idname.split('_');
            var fieldNum = pecah[1];
            var fieldID = "#field_" + fieldNum;
            $(fieldID).remove();
            /* Menghapus daata kode di array KODEX */
            var kode = $(this).attr('val');
            var index = kodex.indexOf(kode);
            kodex.splice(index, 1);
            settotal();	
            // console.log(JSON.stringify(kodex));
        });
        // AKsi Pembayaran                  
        $('#actbayar').click(function(){ 
            var deposit = $('#deposit').val();
            var total = $('#subtotal1').val();
            if(parseFloat(total)>parseFloat(deposit)){
                swal("Error","Deposit Tidak mencukupi, Deposit: "+addPeriod(deposit)+" - Tagihan: "+addPeriod(total),"error");
            }else{
                swal({   
                    title: "Bayar Pesanan?",   
                    text: "Apakah Mau Membayar : Rp, "+addPeriod(total)+" ?",   
                    type: "warning",   
                    showCancelButton: true,   
                    confirmButtonColor: "#DD6B55",   
                    confirmButtonText: "Bayar",   
                    closeOnConfirm: true }, 
                    function(){   
                        $('#myform').submit();
                    }); 
            }
        });
        //aksi submit pembayaran
        $('#myform').on('submit', function(e) {
            e.preventDefault();
            var post_data = $(this).serialize();
            $.ajax({
                url : "<?php echo base_url('kantin/save'); ?>",
                type: "POST",
                data : post_data,
                dataType: "json",
                success: function(data){
                    if(data.status == 'success'){
                        $.notify('Successfull save data');
                        refreshMenuList('1');
                        refreshMenuList('2');
                        $("#myform").trigger('reset'); 
                        $('.temp-row').remove();
                        settotal();
                        $('#nonota').val(data.nota);
                        //$('#cetakform').submit();
                        /*
                        window.open('<?php echo base_url();?>kantin/cetak/'+data.nota, '_blank');
                        swal({   
                            title: "Pesan Lagi?",   
                            text: "Apakah Mau Pesan Lagi?",   
                            type: "warning",   
                            showCancelButton: true,   
                            confirmButtonColor: "#DD6B55", 
                            confirmButtonText: "Tidak",
                            cancelButtonText: "Ya, Lagi",
                            closeOnConfirm: true,
                            closeOnCancel: true 
                        }, 
                        function(isConfirm){
                            if(isConfirm){
                              window.location.href = "<?php // echo base_url('login/logout');?>";
                            }else{
                              window.location.href = "<?php // echo base_url('kantin/kantin');?>";
                            }
                        });
                        */
                    }else{
                        swal("warning",data.txt,"warning");
                    }					
                }
            });
        });
        
        $(document).on("change","#txtstan1",function(){
            refreshMenuList('1');
        });
        //Fungsi enter langsung mencari
        $("#txtcari1").keypress(function (e) {
            var key = e.which;
            if(key == 13){
                refreshMenuList('1');
            }
        });
        $("#idcard").keypress(function (e) {
            var key = e.which;
            if(key == 13){
                e.preventDefault();
                $("#modalpin").modal("show");
            }
        });  
        //Notify Animation Default
        $.notifyDefaults({
          type: 'success',
          delay: 500
        });   
          
        var counter = 15;
        var idleSecons = 15;
        var idleTimer;
        var downTimer;
        
        $(document.body).bind("mousemove keydown click",resetTimer);
        $(document.body).bind("mousemove keydown click",countdownTimer);
        resetTimer();
        countdownTimer();
        
        function countdownTimer(){
            counter = 15;
            $("#countdownz").html(counter);
            clearTimeout(downTimer);
            downTimer = setInterval(function(){
                            counter--;
                            if(counter>=0){
                                $("#countdownz").html(counter);
                            }
                            if(counter===0){
                                counter+=15;
                                $("#countdownz").html(counter);
                            }
                        }, 1000);
        }
        function resetTimer(){
            clearTimeout(idleTimer);
            idleTimer = setTimeout(whenUserIdle,idleSecons*1000);   
        }
        function whenUserIdle(){
            window.location.href = "<?php echo base_url('login/logout');?>";
        }
    }); 
    function kurangtext(){
        var str = $("#pinx").val();
        var newStr = str.substring(0, str.length-1);
        $("#pinx").val(newStr);
        
    }
    function cleartext(){
        $("#pinx").val('');
    }
    function addtext(text){
        var oldtext = $("#pinx").val();
        if(oldtext.length<6){
            var newtext = oldtext+text;
            $("#pinx").val(newtext);
        }else{
            $("#pinx").val(oldtext);
        }
    }
    function refresh(){
        location.reload();
    } 
    function smartsubmit(){
        var pin = $("#pinx").val();
        $("#pin").val(pin);
        $("#modalpin").modal("hide");
        $("#loginform").submit();
    }  
    function smartshow(){
        $("#loginform").trigger('reset'); 
        $("#pin").val('');
        $("#idcard").val('');
        $("#idcard").focus();      
    }
    function refreshMenuList(ke){
        var stan = $("#txtstan"+ke).val();
        var cari  = $("#txtcari"+ke).val();
        $("#list"+ke).html("");
        $.ajax({
            url: "<?php echo base_url('kantin/stan_menu'); ?>",
            type: "POST",
            data : { stan:stan, cari:cari },
            dataType: "json",
            success: function(data){
                for (var i = 0; i < data.length; i++) {
                    var n = i+1;
                    var li = '<div class="square btn btn-primary ripple btnpesan" kodeex="'+ data[i].kode_barang +'" menuex="'+ data[i].nama_barang +'" hargaex="'+ data[i].harga_jual +'" jumex="'+ data[i].jum +'" >'+
                                '<div class="btn-group" style="width:100%;margin-bottom:5px;">'+
                                    '<button type="button" class="btn btn-warning" style="width:70%;padding:1px;">'+ data[i].nama_stan +'</button>'+
                                    '<button type="button" class="btn btn-danger jumnya" style="width:30%;padding:1px;">'+ data[i].jum +'</button>'+
                                '</div>'+
                                '<div class="sub" style="padding-right:10px;"><b>'+ data[i].nama_barang +'</b><br>'+ 
                                'Rp, '+ addPeriod(data[i].harga_jual) +'</div>'+                             
                             '</div>';
                    $('#list'+ke).append(li);
                }
            }
        });
    }
    function settotal(){
        var t=0;  
        $('.totals').each(function(i,e){  
            var val = $(this).val();
            var tot = val.replace(/\./g, "");
            var amt = tot-0;  
            t+=amt;  
        });  
        var hasil = addPeriod(t);
        $('#subtotal1').val(t); 
        $('#subtotal').html("Rp, "+hasil);
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
        th.dt-center, td.dt-center { text-align: center; }
        .table {
            font-size:13px;
            border: 1px solid #ccc;
        }
        .table thead > th{
            border: 1px solid #fff;
        }
        .table tbody > tr td {
            border: 1px solid #ccc; 
        }
        .table tfoot > tr td {
            border: 1px solid #ccc;
        }
        .table tr td {
            border: 1px solid #ccc;
        }
        #list_infois tbody > tr td {
            border: 1px solid #ccc; 
            padding: 3px 5px;
            vertical-align: middle;
        }
        #list_infois tfoot > tr td {
            border: 1px solid #ccc;
            padding: 3px 5px;
            vertical-align: middle; 
        }

        /* Ripple magic */
        .ripple {
            position: relative;
            overflow: hidden;
        }
        .ripple:after {
            content: "";
            display: block;
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            background-image: radial-gradient(circle, #000 10%, transparent 10.01%);
            background-repeat: no-repeat;
            background-position: 50%;
            transform: scale(10, 10);
            opacity: 0;
            transition: transform .5s, opacity 1s;
        }
        .ripple:active:after {
            transform: scale(0, 0);
            opacity: .2;
            transition: 0s;
        }
        /* Kolom */
        div.square {
            width: 135px;
            height: 100px;
            float: left;
            margin: 2px;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            white-space: normal;
        }
        div.group {
            float: left;   
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
        /* Modal PIN */
        .keys:not(:last-of-type) {
            margin-bottom: 4px;
        }
        .calculator button {
            width: 60px;
            height: 60px;
        }
        .calculator { 
            border: 1px solid #D6D6D6;
            padding: 5px 8px;
            width: 100%;
            display: inline-block;
            align-content: center;
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
                  <a href="<?php echo base_url('kantin');?>" class="navbar-brand"><b>SMANE</b>MART</a>
                  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                    <i class="fa fa-bars"></i>
                  </button>
                </div>
                <div class="navbar-custom-menu">
                  <ul class="nav navbar-nav">
                      <li class="dropdown messages-menu">
                          <a href="javascript:location.reload();" class="btn btn-warning">
                              <i class="fa fa-refresh"></i> Refresh
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
                <div class="col-md-4">     
                    <?php
                    if($info!="gagal"){
                        $sesi = $this->session->get_userdata();
                        $userr = $sesi['username'];
                        $nomor_induk = $sesi['nomor_induk'];
                        $nama_lengkap = $sesi['fullname'];
                        $akses = $sesi['akses'];
                        $que = $this->db->query("SELECT SUM(jumlah) deposit, kode FROM tb_deposit_moves 
                                                 WHERE kode='$nomor_induk' GROUP BY kode");
                        $jjj = $que->num_rows();
                        if($jjj>0){
                            $roe = $que->first_row();
                            $deposit = $roe->deposit;
                        }else{
                            $deposit = 0;
                        }
                    ?>
                    <!-- TABLE: LATEST ORDERS -->
                    <div class="box box-primary">
                      <div class="box-header with-border label-primary">
                        <table width="100%">
                            <tr>
                                <td width="49%" style="font-size:20px;"><b><?php echo $nama_lengkap; ?></b></td>
                                <td width="2%"></td>
                                <td width="49%"><b class="pull-right">Deposit :<br><span class="pull-right" style="font-size:27px;">Rp, <?php echo number_format($deposit, 0, ".", "."); ?></span></b></td>
                            </tr>
                        </table>
                        <input type="hidden" id="deposit" name="deposit" value="<?php echo $deposit; ?>" />
                      </div><!-- /.box-header -->
                      <div class="box-body">
                        <b>Pesanan Anda :</b>
                        <div class="table-responsive" style="width:100%;height:400px">
                            <form id="myform">
                            <table id="list_infois" width="100%" class="table no-margin">
                                <thead class="label-primary">
                                    <tr>
                                    <th width="5%">No</th>
                                    <th width="27%">Menu</th>
                                    <th width="20%">Jml</th>
                                    <th width="23%">Harga</th>
                                    <th width="25%">Total</th>
                                  </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot valign="bottom">
                                    <tr>
                                        <td colspan="3"></td>
                                        <td><b>Subtotal</b> <input type="hidden" id="subtotal1" name="subtotal" /></td>
                                        <td id="subtotal">0</td>
                                    </tr>
                                </tfoot>
                            </table>
                            </form>
                        </div><!-- /.table-responsive -->
                      </div><!-- /.box-body -->
                      <div class="box-footer">
                          <!-- <form id="cetakform" action="<?php echo base_url('kantin/cetak'); ?>" method="post" target="_self">
                              <input type="hidden" name="nonota" id="nonota" />
                          </form> -->
                          
                        <button type="button" id="actbayar" class="btn btn-primary btn-lg pull-right" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Loading..."><i class="fa fa-money"></i>&nbsp;Bayar</button>
                        <a href="<?php echo base_url('login/logout');?>" class="btn btn-danger btn-lg pull-right" style="margin-right:15px;">Logout</a>
                        
                      </div><!-- /.box-footer -->
                    </div><!-- /.box -->
                    <?php
                    }else{
                    ?>
                    <div class="box box-primary">
                        <div class="box-body">
                            <center>                            
                            <div style="height:150px;">
                                <form id="loginform" action="<?php echo base_url('login/card_login'); ?>" method="post">
                                <table width="40%">
                                    <tr>
                                        <td style="padding:70px 20px;;">
                                            <input type="hidden" name="pin" id="pin" style="width:50px;"/>
                                            <input type="password" id="idcard" name="idcard" style="width:50px;"/>
                                        </td>
                                    </tr>
                                </table>   
                                </form>
                            </div>
                            </center>
                        </div>
                        <div id="overlay-login" class="overlay">
                            <div style="margin:20px;">
                            <center>
                                <p><b>Tempelkan Kartu ke scanner...</b></p>
                                <img width="130" height="100" src="<?php echo base_url();?>assets/img/scan.png">
                            </center>
                            </div>
                        </div>
                    </div>
                    <?php
                    }
                    ?>   
                </div><!-- /.col -->                
                
                <div class="col-md-8">
                    <!-- PRODUCT LIST -->
                    <div class="box box-primary">
                      <div class="box-header with-border">
                        <h3 class="box-title">Daftar Pesanan</h3>
                        <span class="pull-right" id="countdownz">15</span>
                      </div><!-- /.box-header -->
                      <div class="box-body">  
                            <table width="100%" height="450px">
                              <tr valign="top">
                                  <td>                              
                                    <ul class="nav nav-pills" style="padding:0px 1px;margin:0px 15px 3px 0px;">
                                        <li>
                                            <select class="form-control select2" id="txtstan1" name="stan" style="width:150px;">
                                                <option value="">All</option>
                                                <?php
                                                  $query = $this->db->query("SELECT * FROM tb_stan WHERE no_stan!='0' ");
                                                  foreach ($query->result_array() as $data){
                                                      echo "<option value='".$data['no_stan']."'>".$data['nama_stan']."</option>";
                                                  }
                                                ?>
                                          </select>
                                        </li>
                                        <li class="pull-right">
                                          <div class="input-group">
                                              <input type="text" class="form-control" id="txtcari1" name="cari" placeholder="Cari.."/>
                                              <span class="input-group-btn">
                                                <button type="button" class="btn btn-info" id="btncari1"><i class="fa fa-search"></i> Cari</button>
                                              </span>
                                          </div>
                                        </li>
                                    </ul>
                                    <div class="group">
                                        <div id="list1" class="line">                                
                                        </div>                                   
                                     </div>
                                  </td>
                              </tr>
                          </table>
                      </div><!-- /.box-body -->                      
                        <!-- Main overlay -->
                        <div id="overlay-menu" class="overlay">
                            <i class="fa fa-lock" style="margin-left:-100px;"> Login Terlebih Dahulu</i>
                        </div>
                    </div><!-- /.box -->
                </div><!-- /.col -->
            </div><!-- /.row -->	
            
            <div id="modalcust" class="modal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body">
                          <form id="myform" class="form-horizontal">
                                <div class="form-group"> 
                                  <label class="col-sm-3 control-label" style="font-weight:bold;font-size:20px;">Total Bayar</label>
                                  <div class="col-sm-9">
                                        <div class="input-group">
                                            <span class="input-group-addon btn" style="font-weight:bold;font-size:20px;">Rp, </span>
                                            <input type="text" id="biayax" class="form-control input-lg number-only" readonly="readonly" style="height:50px;font-weight:bold;font-size:34px;text-align:right;" value="0"/>
                                        </div>
                                  </div>
                                </div>
                                <div class="form-group"> 
                                  <label class="col-sm-3 control-label" style="font-weight:bold;font-size:20px;">Pembayaran</label>
                                  <div class="col-sm-9">
                                        <div class="input-group">
                                            <span class="input-group-addon btn" style="font-weight:bold;font-size:20px;">Rp, </span>
                                            <input type="text" id="bayarx" class="form-control input-lg number-only" style="height:50px;font-weight:bold;font-size:34px;text-align:right;" value="0"/>
                                        </div>
                                  </div>
                                </div>
                                <div class="form-group"> 
                                  <label class="col-sm-3 control-label" style="font-weight:bold;font-size:20px;">Kembalian</label>
                                  <div class="col-sm-9">
                                        <div class="input-group">
                                            <span class="input-group-addon btn" style="font-weight:bold;font-size:20px;">Rp, </span>
                                            <input type="text" id="kembalix" class="form-control input-lg number-only" readonly="readonly" style="height:50px;font-weight:bold;font-size:34px;text-align:right;" value="0"/>
                                        </div>
                                  </div>
                                </div>
                                <div class="form-group"> 
                                  <label class="col-sm-3 control-label"></label>
                                  <div class="col-sm-9">
                                        <button type="button" id="btnsave" class="btn btn-primary" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Loading..."><i class="fa fa-money"></i>&nbsp;Bayar (F9)</button> 
                                        <button type="button" id="btnprint" class="btn btn-warning" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Loading..."><i class="fa fa-print"></i>&nbsp;Print (F8)</button> 
                                        <button type="button" class="btn btn-default" data-dismiss="modal"> Tutup</button>
                                  </div>
                                </div>
                          </form>
                        </div>
                    </div>
                </div>
            </div> 
            
            <div id="modalpin" class="modal" data-keyboard="false" data-backdrop="static">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-body">
                            <label style="font-weight:bold;font-size:20px;">Masukkan PIN</label>              
                            <input type="password" id="pinx" class="form-control input-lg number-only" style="height:50px;font-weight:bold;font-size:34px;text-align:right;" value="" maxlength="6" disabled/>
                            <div class="col-md-12 calculator">
                                <div class="keys">
                                    <button type="button" class="btn btn-info btn-lg" onclick="addtext('1')"><b>1</b></button>
                                    <button type="button" class="btn btn-info btn-lg" onclick="addtext('2')"><b>2</b></button>
                                    <button type="button" class="btn btn-info btn-lg" onclick="addtext('3')"><b>3</b></button>
                                    <button type="button" class="btn btn-info btn-lg" onclick="addtext('4')"><b>4</b></button>
                                </div>
                                <div class="keys">
                                    <button type="button" class="btn btn-info btn-lg" onclick="addtext('5')"><b>5</b></button>
                                    <button type="button" class="btn btn-info btn-lg" onclick="addtext('6')"><b>6</b></button>
                                    <button type="button" class="btn btn-info btn-lg" onclick="addtext('7')"><b>7</b></button>
                                    <button type="button" class="btn btn-info btn-lg" onclick="addtext('8')"><b>8</b></button>
                                </div>
                                <div class="keys">
                                    <button type="button" class="btn btn-info btn-lg" onclick="addtext('9')"><b>9</b></button>
                                    <button type="button" class="btn btn-info btn-lg" onclick="addtext('0')"><b>0</b></button>
                                    <button type="button" class="btn btn-info btn-lg" onclick="kurangtext()"><<</button>
                                    <button type="button" class="btn btn-info btn-lg" onclick="cleartext()"><b>C</b></button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="btnlogin" class="btn btn-primary btn-lg pull-right" onclick="smartsubmit()">&nbsp;&nbsp;&nbsp; Benar &nbsp;&nbsp;&nbsp;</button> 
                            <button type="button" class="btn btn-danger btn-lg pull-left" data-dismiss="modal" onclick="refresh()">&nbsp;&nbsp;&nbsp; Salah &nbsp;&nbsp;&nbsp;</button>
                        </div>
                    </div>
                </div>
            </div>  
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
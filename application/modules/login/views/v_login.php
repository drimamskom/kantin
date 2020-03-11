<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SMANEMART Login</title>
  <link rel="icon" href="<?php echo base_url();?>assets/img/smanema.png">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="<?php echo base_url();?>assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo base_url();?>assets/dist/css/AdminLTE.min.css">
  
  <script src="<?php echo base_url();?>assets/plugins/jQuery/jQuery-2.1.4.min.js"></script>
  <script src="<?php echo base_url();?>assets/bootstrap/js/bootstrap.min.js"></script>
  <script src="<?php echo base_url();?>assets/dist/js/app.min.js"></script> 
  <script type="text/javascript"> 
    $(function(){                    
        $("#idcard").keypress(function (e) {
            var key = e.which;
            if(key == 13){
                e.preventDefault();
                //$("#modalpin").modal("show");
                $("#loginform").submit();
            }
        }); 
        //if the letter is not digit then don't type anything
        $(".number-only").keypress(function (e) {
            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                return false;
            }
        });
        
        $("#akun_login").hide();
        $("#smartcard_login").show();
        $("#idcard").val('');
        $("#idcard").focus();
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
        $("#akun_login").hide();
        $("#smartcard_login").show();  
        $("#loginform").trigger('reset'); 
        $("#idcard").val('');
        $("#idcard").focus();      
    }
    function akunshow(){       
        $("#akun_login").show();
        $("#smartcard_login").hide();  
        $("#akunform").trigger('reset'); 
        $("#username").val('');
        $("#username").focus();       
    }
  </script>
  <style>
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
<body class="hold-transition login-page">
    <div class="login-box" style="margin-top:15px;margin-bottom:10px;">
        <div class="login-logo" style="margin-bottom:0px;">
            <a href="<?php echo base_url();?>">
                <img class="img-circle" src="<?php echo base_url();?>assets/dist/img/smanema.png" alt="User Avatar" width="200"/>
                <b>SMANE</b>MART
            </a>
        </div><!-- /.login-logo -->
        <div class="login-box-body" style="padding:0px;">
            <div class="col-md-6" style="padding:0px;">
                <button class="btn btn-primary btn-block btn-flat" onclick="smartshow()">SmartCard Login</button>
            </div>
            <div class="col-md-6" style="padding:0px;">
                <button class="btn btn-success btn-block btn-flat" onclick="akunshow()">Akun Login</button>
            </div>
            <div class="col-md-12" style="padding:0px;">
                <div id="smartcard_login" class="box" style="border-top:0px;">
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
                
                <div id="akun_login" class="box" style="border-top:0px;">
                    <div class="box-body">
                        <p class="login-box-msg">Login Terlebih dahulu</p>
                        <form id="akunform" action="<?php echo base_url('login/aksi_login'); ?>" method="post">
                            <div class="form-group has-feedback">
                                <input type="text" class="form-control" name="username" id="username" placeholder="Username">
                                <span class="glyphicon glyphicon-user form-control-feedback"></span>
                            </div>
                            <div class="form-group has-feedback">
                                <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                            </div>
                            <div class="row">
                                <div class="col-sm-8"></div><!-- /.col -->
                                <div class="col-sm-4">
                                    <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
                                </div><!-- /.col -->
                            </div>
                        </form>
                    </div>
                </div>
                <!-- info gagal -->
                <?php
                if(isset($info)){
                    echo '<div class="alert alert-danger"><strong>Gagal!</strong> </br> '.$text.'</div>';
                }
                ?>
            </div>
        </div>
        <div class="login-box-msg" style="padding:0px;">
            <center><p>Copyright &copy; 2017 All rights reserved</p></center>
        </div>
    </div><!-- /.login-box --> 
           
    <div id="modalpin" class="modal" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body">
                    <label style="font-weight:bold;font-size:20px;">Masukkan PIN</label>              
                    <input type="password" id="pinx" class="form-control input-lg number-only" style="height:50px;font-weight:bold;font-size:34px;text-align:right;" value="" maxlength="6" disabled/>
                    <div class="col-md-12 calculator">
                        <div class="keys">
                            <button type="button" class="btn btn-info btn-lg ripple" onclick="addtext('1')"><b>1</b></button>
                            <button type="button" class="btn btn-info btn-lg ripple" onclick="addtext('2')"><b>2</b></button>
                            <button type="button" class="btn btn-info btn-lg ripple" onclick="addtext('3')"><b>3</b></button>
                            <button type="button" class="btn btn-info btn-lg ripple" onclick="addtext('4')"><b>4</b></button>
                        </div>
                        <div class="keys">
                            <button type="button" class="btn btn-info btn-lg ripple" onclick="addtext('5')"><b>5</b></button>
                            <button type="button" class="btn btn-info btn-lg ripple" onclick="addtext('6')"><b>6</b></button>
                            <button type="button" class="btn btn-info btn-lg ripple" onclick="addtext('7')"><b>7</b></button>
                            <button type="button" class="btn btn-info btn-lg ripple" onclick="addtext('8')"><b>8</b></button>
                        </div>
                        <div class="keys">
                            <button type="button" class="btn btn-info btn-lg ripple" onclick="addtext('9')"><b>9</b></button>
                            <button type="button" class="btn btn-info btn-lg ripple" onclick="addtext('0')"><b>0</b></button>
                            <button type="button" class="btn btn-info btn-lg ripple" onclick="kurangtext()"><<</button>
                            <button type="button" class="btn btn-info btn-lg ripple" onclick="cleartext()"><b>C</b></button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="btnlogin" class="btn btn-primary btn-lg ripple pull-right" onclick="smartsubmit()">&nbsp;&nbsp;&nbsp; Benar &nbsp;&nbsp;&nbsp;</button> 
                    <button type="button" class="btn btn-danger btn-lg ripple pull-left" data-dismiss="modal" onclick="refresh()">&nbsp;&nbsp;&nbsp; Salah &nbsp;&nbsp;&nbsp;</button>
                </div>
            </div>
        </div>
    </div>  
    
</body>
</html>

<?php          
$namabln = array("KS", "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
                 "Juli", "Agustus", "September", "Oktober", "November", "Desember");
$sesi = $this->session->get_userdata();
$userr = $sesi['username'];
$fullname = $sesi['fullname'];
$akses = $sesi['akses'];
$blnx = intval(date('m'));
$tgl_manusia = date('d')." ".$namabln[$blnx]." ".date('Y');
?>
<!-- typeahead -->
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/typeahead/bootstrap3-typeahead.js"></script>
<!-- Select2 -->
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/select2/select2.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2.css" type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2-bootstrap.css" type="text/css"/>
<script type="text/javascript">  
	$(function(){  
                $("#overlay-kartu").hide();
                //Initialize Select2 Elements        
                $(".select2").select2({
                    minimumResultsForSearch: Infinity,
                    theme: "bootstrap"
                });
                //isi pas modal
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
                        "url": "<?php echo base_url('kasir/caribarang'); ?>",
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
                            {"className": "dt-center", "targets": [0,5,6]}
                        ],
                    "columns": [
                            { "data": "urutan" },
                            { "data": "kode_barang" },
                            { "data": "nama_barang" },
                            { "data": "satuan" },
                            { "data": "harga_jual" },
                            { "data": "stok" },
                            { "data": "button" },
                    ]
                });
                
		$('#myform').on('submit', function(e) {
                    e.preventDefault();
                    var depositx  = $('#depositx').val(); 
                    var subtotal2 = $('#subtotal2').val(); 
                    var tagihanx  = subtotal2.replace(/\./g, "");
                    var kembali   = parseInt(depositx)-parseInt(tagihanx);
                    if(kembali>=0){
                        var data1 = $(this).serialize();
                        var data2 = "&biaya="+tagihanx+"&bayar="+tagihanx+"&kembali="+kembali;
                        var post_data = data1.concat(data2);
                        var crud=$("#crudmethod").val();
                        $('#btnsave').button('loading');                       
                        $.ajax({
                            url : "<?php echo base_url('kasir/save'); ?>",
                            type: "POST",
                            data : post_data,
                            dataType: "json",
                            success: function(data){
		    		$('#btnsave').button('reset');  
                                if(data.status == 'success'){
                                    $.notify('Successfull save data');
                                    $("#modalcust").modal("hide");
                                    $("#myform").trigger('reset'); 
                                    $('.temp-row').remove();
                                    settotal();		 
                                    resetform();    
                                    window.open('<?php echo base_url();?>kasir/cetak/'+data.nota, '_self');
                                    $('#customerx').val(''); 
                                    $('#depositx').val(''); 
                                    $('#nama_pelanggan').html(''); 
                                    $('#deposit_pelanggan').html(''); 
                                    $("#overlay-kartu").show();
                                    $('#pelanggan').val(''); 
                                    $('#pelanggan').focus(); 
                                }else{
                                    swal("warning",data.txt,"warning");
                                }					
                            }
			});
                    }else{
                        swal("warning","Uang Deposit Tidak Mencukupi! Rp, "+addPeriod(depositx),"warning");
                    }
                });

                $(document).bind('keypress', function(e) {
                    var kode = $('#pelanggan').val();
                    if(e.keyCode==120){ //f9
                        if(kode.length==0){
                            $("#overlay-kartu").show();
                            $('#pelanggan').focus();
                        }else{                     
                            $('#myform').submit();
                        }
                        
                    }else if(e.keyCode==112){ //f1
                        $("#jumlahx").val('');
                        $('#jumlahx').focus(); 
                    }
                });
                
		$('#btncaribarang').click(function(){ 
                    $("#modalcust").modal("show");
                    $("#txtcari").val('');
                    $('#txtcari').focus(); 	
		});
                
                $('#btnsave').click(function(){   
                    var kode = $('#pelanggan').val();
                    if(kode.length==0){
                        $("#overlay-kartu").show();
                        $('#pelanggan').focus();
                    }else{                      
                        $('#myform').submit();
                    }
                }); 
                
                $('#cbobarang').keyup(function (e) {                    
                    var key = e.which;
                    if(key == 13){
                        addnewrow();
                    }
		});
                 
		$('#jumlahx').keyup(function (e) {                  
                    var key = e.which;
                    if(key == 13){
                        $('#cbobarang').focus();
                    }
		}); 
                 
		$('#pelanggan').keyup(function (e) {                  
                    var key = e.which;
                    if(key == 13){
                        $("#overlay-kartu").hide();
                        var kode = $('#pelanggan').val();
                        $.ajax({
                            url : "<?php echo base_url('kasir/getcustomer'); ?>",
                            type: "POST",
                            data : { kode:kode },
                            dataType: "json",
                            success: function(data){
                                if(data.status == 'success'){
                                    $('#customerx').val(data.kode);
                                    $('#depositx').val(data.deposit);
                                    $('#nama_pelanggan').html(data.nama);
                                    $('#deposit_pelanggan').html("( Rp, "+addPeriod(data.deposit)+" )");
                                    $('#cbobarang').focus();
                                }else{
                                    $('#pelanggan').val('');
                                    swal(data.txt,"tekan tombol ESC untuk exit!!","warning");
                                }					
                            }
			});
                    }
		});   
                
                $(document).on("click",".btnpilih",function(){
                    var id = $(this).attr("idnex");
                    $('#cbobarang').val(id);
                    addnewrow();                    
                    $("#modalcust").modal("hide");
		});
                
		$('.delete-row').click(function(){  
			var idname = this.id;
			var pecah = idname.split('_');
			var fieldNum = pecah[1];
			var fieldID = "#field_" + fieldNum;
			$(fieldID).remove();
			settotal();		
		});
                
		//if the letter is not digit then don't type anything
		$(".number-only").keypress(function (e) {
                    if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                        return false;
                    }
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
                
                $("#tempat").on("change", function (e) {
                    var table = $('#table_cust').DataTable(); 
                    table.ajax.reload( null, false );
                });
                
            $("#overlay-kartu").show();
            $('#pelanggan').val(''); 
            $('#pelanggan').focus(); 
	}); 

	function addnewrow(){  
            var barcode = $('#cbobarang').val();
            var jumlah  = $('#jumlahx').val();
			if(jumlah.length==0){
				swal("Jumlah Qty Belum Diisi","tekan tombol ESC untuk exit!!","warning");
				settotal();		 
				resetform();
				$('#cbobarang').focus();
			}else{
            $.ajax({
                url : "<?php echo base_url('kasir/getdata'); ?>",
                type: "POST",
                data : { barcode:barcode },
                dataType: "json",
                success: function(result){
                    var data = result.data;
                    if(data.status == 'success'){
                        var barang  = data.kode_barang;
                        var namabarang = data.nama_barang;
                        var harga   = data.harga;
                        var satuan  = data.satuan;
                        var tot     = harga*jumlah;
                        var total   = addPeriod(tot);
                        var n = ($('#list_infois > tbody tr').length-0)+1;  
                        var tr = '<tr id="field_'+n+'" class="temp-row">'+
                                    '<td align="center"><button id="remove_'+n+'" class="btn btn-danger btn-xs delete-row"><i class="glyphicon glyphicon-remove"></i></button></td>'+
                                    '<td><input type="hidden" name="kodebarang[]" value="'+barang+'"/> <span id="txtobat_'+n+'">'+namabarang+'</span></td>'+
                                    '<td><input type="hidden" name="satuan[]" value="'+satuan+'"/> <span id="txtsatuan_'+n+'">'+satuan+'</span></td>'+
                                    '<td><input type="hidden" name="jumlah[]" value="'+jumlah+'"/> <span id="txtjumlah_'+n+'">'+jumlah+'</span></td>'+
                                    '<td><input type="hidden" name="harga[]" value="'+harga+'"/> <span id="txtharga_'+n+'">Rp. '+harga+'</span></td>'+                                
                                    '<td><input type="hidden" class="totals" name="total[]" value="'+total+'"/> <span id="txttotal_'+n+'">Rp. '+total+'</span></td>'+
                                '</tr>';
                        $('#list_infois > tbody').append(tr);
                        settotal();		 
                        resetform();
                        $('.delete-row').click(function(){  
                                var idname = this.id;
                                var pecah = idname.split('_');
                                var fieldNum = pecah[1];
                                var fieldID = "#field_" + fieldNum;
                                $(fieldID).remove();
                                settotal();		
                        }); 
                        $('#cbobarang').focus();
                        if(data.stok==0){
                            swal("Stok\n"+namabarang+"\nTercatat Sudah Habis","Jika masih ada barang, harap di masukkan ke sistem!!","warning");
                        }
                        
                    }else{
                        swal(data.txt,"tekan tombol ESC untuk exit!!","warning");
                    }
                }
            });
			}
	} 
        
        function resetform(){   
            $('#cbobarang').val(''); 
            $('#jumlahx').val('1'); 
            $('#cbobarang').focus();
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
            $('#subtotal2').val(hasil); 
            $('#subtotal').html("Rp. "+hasil);
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
    border: 1px solid #ccc;
}
.table thead > th{
    border: 1px solid #fff;
}
#table_cust tbody > tr td {
    padding: 3px;
    /* border: 1px solid #ccc; */
}
.table tr td {
    border: 1px solid #ccc;
}
.no-bottom{
    margin-bottom: 0px;
}
</style>
<div class="row">
    <div class="col-md-12">
        <!-- Default box -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Transaksi Kasir</h3>
                <span class="pull-right"><b><?php echo $tgl_manusia." | ".date('H:i:s'); ?></b></span> 
            </div>
            <div class="box-body"> 
                    <div class="row">
                        <div class="col-sm-6">
                            <table width="100%" style="margin-left:10px;">
                                <tr>
                                    <td width="17%">Kasir</td>
                                    <td width="3%">:</td>
                                    <td width="80%"><b><?php echo $fullname; ?></b></td>
                                </tr>
                                <tr>
                                    <td>ID Card</td>
                                    <td>:</td>
                                    <td>
                                        <input type="password" class="form-control" id="pelanggan" name="pelanggan" style="width:50%" required/>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Pelanggan</td>
                                    <td>:</td>
                                    <td>
                                        <input type="hidden" id="depositx" value=""/>
                                        <b><span id="nama_pelanggan"></span></b>
                                        <b><span id="deposit_pelanggan"></span></b>
                                    </td>
                                </tr>
                            </table>
                        </div><!-- /.col -->

                        <div class="col-sm-6">
                            <div class="form-group"> 
                                <label>Total Bayar :</label>
                                <div class="input-group">
                                    <span class="input-group-addon btn" style="font-weight:bold;font-size:20px;">Rp. </span>
                                    <input type="text" id="subtotal2" class="form-control input-lg number-only" readonly="readonly" style="height:50px;font-weight:bold;font-size:34px;text-align:right;" value="0"/>
                                </div>
                            </div>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                    
                    <div class="row">
                        <div class="col-sm-12">
                            <table width="100%" style="margin-left:10px;">
                                <tr>
                                    <td width="8%">Nama Barang</td>
                                    <td width="1%">:</td>
                                    <td width="8%" style="padding-left:5px;">
                                        <div class="input-group">
                                            <input type="text" class="form-control number-only" id="jumlahx" value="1" required/>
                                            <span class="input-group-addon btn">X</span>
                                        </div>
                                    </td>
                                    <td width="38%" style="padding-left:5px;">
                                        <div class="input-group">
                                            <span class="input-group-btn">
                                              <button type="button" class="btn btn-info" id="btncaribarang"><i class="fa fa-search"></i></button>
                                            </span>
                                            <input type="password" id="cbobarang" class="form-control" style="width:100%"/>
                                        </div>
                                    </td>
                                    <td width="40%" style="padding-left:5px;">
                                        <a href="javascript:void(0);" onclick="addnewrow()" class="btn btn-success btn-sm pull-left"><i class="glyphicon glyphicon-plus"></i>  Tambah</a>
                                    </td>
                                </tr>
                            </table>
                            <br>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                    
                    <!-- Table row -->
                    <div class="row">
                        <div class="col-xs-12 table-responsive">                            
                        <form id="myform">
                            <input type="hidden" id="customerx" name="customer" value=""/>
                            <table id="list_infois" width="100%" class="table table-striped">
                                <thead class="label-primary">
                                <tr>
                                    <th width="5%"><center>#</center></th>
                                    <th width="40%">Nama Barang</th>
                                    <th width="10%">Satuan</th>
                                    <th width="10%">Jumlah</th>
                                    <th width="15%">Harga</th>
                                    <th width="20%">Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="5" align="justify">
                                        <b><span class="pull-right">Subtotal</span></b>
                                    </td>
                                    <td><b> <span id="subtotal">Rp. 0</span> <input type="hidden" name="subtotal" id="subtotal1"> </b></td>
                                </tr>
                                </tfoot>
                            </table>
                        </form><!-- /.form --> 
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                    <!-- this row will not appear when submit -->
                    <div class="row no-print">
                        <div class="col-xs-12">
                            <div class="pull-right">
                                <button type="button" id="btnsave" class="btn btn-primary" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Loading..."><i class="fa fa-money"></i>&nbsp;Bayar (F9)</button> 
                            </div>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
            </div><!-- /.box-body -->            
            <div id="overlay-kartu" class="overlay">
                <div style="margin:20px;">
                <center>
                    <p><b>Tempelkan Kartu Pelanggan ke scanner...</b></p>
                    <img width="130" height="100" src="<?php echo base_url();?>assets/img/scan.png">
                </center>
                </div>
            </div>
        </div><!-- /.box -->
    </div><!-- /.col -->
</div><!-- /.row -->  

<div id="modalcust" class="modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">Pencarian Barang...</h4>
            </div>
            <div class="modal-body">
                <form id="formFilter" class="form-horizontal">
                    <div class="col-sm-6" style="padding-left:0px;">
                        <ul class="nav nav-pills">
                            <li>
                                <!--                                
                                <div class="input-group">
                                    <div class="input-group-addon label-warning">Tempat</div>
                                    <select class="form-control select2" name="tempat" id="tempat" data-width="130px" >
                                        <option value="">All</option>
                                        <?php
                                            $query = $this->db->query("SELECT * FROM tb_tempat ORDER BY tempat DESC ");
                                            foreach ($query->result_array() as $data){
                                                echo "<option value='".$data['kode']."'>".$data['tempat']."</option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                                -->
                            </li>
                        </ul>                
                    </div>
                    <div class="col-sm-6" style="padding-right:0px;padding-bottom:5px;">
                        <div class="input-group">
                            <input type="text" class="form-control" id="txtcari" name="cari" placeholder="Pencarian"/>
                            <span class="input-group-btn">
                              <button type="button" class="btn btn-info" id="btncari"><i class="fa fa-search"></i> Cari</button>
                            </span>
                        </div>
                    </div>
                </form>
                <table id="table_cust" width="100%" class="table table-bordered table-striped">
                    <thead class="label-primary">
                        <tr>
                            <th style="width:5%">No</th>
                            <th style="width:20%">Barcode</th>
                            <th style="width:40%">Nama Barang</th>
                            <th style="width:10%">Satuan</th>
                            <th style="width:10%">Harga</th>
                            <th style="width:7%">Stok</th>
                            <th style="width:8%">Pilih</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
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
                        "url": "<?php echo base_url('userhist/data'); ?>",
                        "data": function( d ) {
                                    var send = $('#formFilter').serializeArray();
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
                            {"className": "dt-center", "targets": [0]}
                        ],
                    "columns": [
                            { "data": "urutan" },
                            { "data": "tanggal" },
                            { "data": "transaksi" },
                            { "data": "nama_transaksi" },
                            { "data": "jumlah" },
                    ]
                });	
            
                $('#txtidcard').keypress(function (e) {                  
                    var key = e.which;
                    if(key == 13){
                        var kode = $('#txtidcard').val();
                        $.ajax({
                            url : "<?php echo base_url('userhist/getcustomer'); ?>",
                            type: "POST",
                            data : { kode:kode },
                            dataType: "json",
                            success: function(data){
                                if(data.status == 'success'){
                                    $('#txtnama').html(data.nama);
                                    $('#txtdeposit').html(" Rp, "+addPeriod(data.userhist));
                                    $('#txtcari').val(data.kode);                                    
                                    var table = $('#table_cust').DataTable(); 
                                    table.ajax.reload( function ( json ) {
                                        addFooter(json);
                                    });
                                    $('#txtidcard').val('');
                                    $('#txtidcard').focus();
                                }else{
                                    swal(data.txt,"tekan tombol ESC untuk exit!!","warning");
                                    $('#txtidcard').val('');
                                    $('#txtidcard').focus();
                                }					
                            }
			});
                    }
		}); 
            
		//if the letter is not digit then don't type anything
		$(".number-only").keypress(function (e) {
			if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
				return false;
			}
		});    
                
                $('#txtidcard').focus();
	}); 
                   
        function addFooter(json){
            $('#saldo').html("Rp. "+addPeriod(json.totalz));
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
.table tbody > tr td {    
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
<?php          
$namabln = array("KS", "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
                 "Juli", "Agustus", "September", "Oktober", "November", "Desember");
$blnx = intval(date('m'));
$tgl_manusia = date('d')." ".$namabln[$blnx]." ".date('Y');
?>
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">History Transaksi Pelanggan</h3>
                <span class="pull-right"><b><?php echo $tgl_manusia; ?></b></span>
            </div>
            <div class="box-body">
                <form id="formFilter">
                    <input type="hidden" class="form-control" id="txtcari" name="cari" />
                </form>
                <table width="50%">
                    <tr style="padding:10px;">
                        <td width="5%"></td>
                        <td width="20%"><span style="font-weight:bold;">IDCARD</span></td>
                        <td width="5%">:</td>
                        <td width="70%">
                            <input type="password" class="form-control" id="txtidcard" name="idcard" placeholder="Tempelkan Kartu" required/>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><span style="font-weight:bold;">Nama</span></td>
                        <td>:</td>
                        <td>
                            <span id="txtnama" style="font-weight:bold;"></span>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><span style="font-weight:bold;">Deposit</span></td>
                        <td>:</td>
                        <td>
                            <span id="txtdeposit" style="font-weight:bold;"></span>
                        </td>
                    </tr>
                </table> 
                <table id="table_cust" width="100%" class="table table-bordered table-striped">
                    <thead class="label-primary">
                        <tr>
                            <th style="width:10%">No</th>
                            <th style="width:20%">Tanggal</th>
                            <th style="width:25%">Kode</th>
                            <th style="width:25%">Transaksi</th>
                            <th style="width:20%">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>               
                    <tfoot>
                        <tr>
                            <td colspan="3"></td>
                            <td style="text-align:right;padding-right:10px;"><span class="fot-style">Saldo Akhir</span></td>
                            <td><span id="saldo" class="fot-style"></span></td>
                        </tr>             
                    </tfoot>
                </table>
            </div>
        </div>
    </div>    
</div><!-- /.row -->

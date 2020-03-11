<?php          
$sesi = $this->session->get_userdata();
$userr = $sesi['username'];
$fullname = $sesi['fullname'];
$akses = $sesi['akses'];
?>
<!-- Select2 -->
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/select2/select2.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2.css" type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/select2/select2-bootstrap.css" type="text/css"/>
<script type="text/javascript">  
	$(function(){ 
            
            $('.select2').select2({
                minimumResultsForSearch: Infinity,
                theme: "bootstrap"
            });   
            
            $('#cbosupplier').select2({
                placeholder: "Pilih Supplier",
                theme: "bootstrap"
            }); 
            
            $('#myform').on('submit', function(e) {
                    e.preventDefault();
                    var post_data = $(this).serialize();
                    $('#btnsave').button('loading');
                    $.ajax({
                        url : "<?php echo base_url('pembelian/save'); ?>",
                        type: "POST",
                        data : post_data,
                        dataType: "json",
                        success: function(data){
                            $('#btnsave').button('reset');
                            if(data.status == 'success'){
                                $.notify('Successfull save data');
                                $("#modalcust").modal("hide");
                                $("#myform").trigger('reset'); 
                                resetinp_Form();
                                window.open('<?php echo base_url();?>pembelian/cetak/'+data.nota, '_self');
                            }else{
                                swal("warning",data.txt,"warning");
                            }
                        }
                    });
            });

            // Isupplierisalisai semua aksi di setiap penambahan row
            $('#add-row').click(function(){  
                addnewrow();  
                setselect2();
                actionpasadd();
            });
            
            //Action Pas-Add (Default-Row)            
            setselect2();
            actionpasadd();
	}); 
        
	function addnewrow(){  
            var n = ($('#list_infois > tbody tr').length-0)+1;  
            var tr = '<tr id="field_'+n+'" class="temp_form">'+
                        '<td><button id="remove_'+n+'" class="btn btn-danger btn-xs delete-row"><i class="glyphicon glyphicon-remove"></i></button></td>'+
                        '<td><div class="form-group no-bottom"> <select id="cbobarang_'+n+'" class="cbobarang" name="cbobarang[]" style="width:100%" required></select> </div></td>'+
                        '<td><div class="form-group no-bottom"> <input id="harga_'+n+'" type="text" class="form-control number-only harga" name="harga[]" required/> </div></td>'+
                        '<td><div class="form-group no-bottom"> <input type="text" class="form-control datepicker2 expired" name="expired[]" value="" /></div></td>'+
                        '<td><div class="form-group no-bottom"> <input id="qty_'+n+'" type="text" class="form-control number-only qty" name="qty[]" required/> </div></td>'+
                        '<td><div class="form-group no-bottom"> <input id="satuan_'+n+'" type="text" class="form-control satuan" name="satuan[]" required/> </div></td>'+
                        '<td><div class="form-group no-bottom"> <input type="text" class="form-control number-only total" name="total[]" readonly/> </div></td>'+
                        '<td><div class="form-group no-bottom"> <input type="text" class="form-control number-only2 diskon" name="diskon[]" /> </div></td>'+
                        '<td><div class="form-group no-bottom"> <input type="text" class="form-control number-only subtotal" name="subtotal[]" readonly/> </div></td>'+
                    '</tr>';
            $('#list_infois > tbody').append(tr);   
	} 
        
        function resetinp_Form(){       
            $("#cbosupplier").val(null).trigger("change");
            $(".cbobarang").val(null).trigger("change");
            $('.temp_form').remove();
	}
        
        function setselect2(){
            //INITIALIZE SELECT2 OBAT
            $(".cbobarang").select2({
                ajax: {
                    url: "<?php echo base_url('pembelian/getbarang'); ?>",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: function (data, params) {
                        // parse the results into the format expected by Select2
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data, except to indicate that infinite
                        // scrolling can be used
                        params.page = params.page || 1;

                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 30) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                width: 'element',
                dropdownAutoWidth : true,
                formatResult: function (option) {
                    return "<div>" + option.text + "</div>";
                },
                formatSelection: function (option) {
                    return option.text;
                },
                theme: "bootstrap",
                escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
                minimumInputLength: 1,
                //templateResult: formatRepo, // omitted for brevity, see the source of this page
                //templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
            });
            
            $('.cbobarang').on("change", function (e) { 
                    var idname = this.id;
                    var pecah = idname.split('_');
                    var fieldNum = pecah[1];                                
                    var kode = $(this).val();
                    var text = $(this).text();
                    $.ajax({
                        url : "<?php echo base_url('pembelian/info'); ?>",
                        type: "POST",
                        data : { kode:kode },
                        dataType: "json",
                        success: function(result){
                            var data = result.data;
                            $('#satuan_'+fieldNum).val(data.satuan);
                            $('#harga_'+fieldNum).val(data.harga_beli);
                            $('#qty_'+fieldNum).focus();
                        }
                    });
            });
        }
        
        function actionpasadd(){  
            // INITIALIZE DATEPICKER
            $('.datepicker2').datepicker({
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
            //delete-row
            $('.delete-row').click(function(){  
                var idname = this.id;
                var pecah = idname.split('_');
                var fieldNum = pecah[1];
                var fieldID = "#field_" + fieldNum;
                $(fieldID).remove();
                total();		
            });
                
            //if the letter is not digit then don't type anything
            $(".number-only").keypress(function (e) {
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                        return false;
                }
            }); 

            //if the letter is not digit then don't type anything
            $(".number-only2").keypress(function (e) {
                if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
                    return false;
                }
            });

            $('.qty').keyup(function (e) {
                var tr = $(this).parent().parent().parent();
                var harga = tr.find('.harga').val(); 
                var qty = tr.find('.qty').val(); 
                var jml = harga*qty;
                tr.find('.total').val(jml);
                tr.find('.subtotal').val(jml);
                total();
            }); 

            $('.diskon').keyup(function (e) {
                var tr = $(this).parent().parent().parent();
                var total = tr.find('.total').val(); 
                var diskon = tr.find('.diskon').val(); 
                var persen = (total/100)*diskon;
                var jml = total-persen;
                tr.find('.subtotal').val(jml);
                total();
            });
        }
        
        function total(){
            var t=0;  
            $('.subtotal').each(function(i,e){  
                var amt = $(this).val()-0;  
                t+=amt;  
            });  
            var hasil = addPeriod(t);
            $('#subtotal1').val(t); 
            $('#txtsubtotal1').html("Rp. "+addPeriod(t));
            $('#subtotal2').val(t); 
            $('#bayar').val(t); 
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
    /* border: 1px solid #ccc; */
}
.table tr td {
    border: 1px solid #ccc;
}
.no-bottom {
    margin-bottom: 0px;
}
</style>

<div class="row">
    <div class="col-md-12">
        <!-- Default box -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Transaksi Pembelian</h3>
            </div>
            <div class="box-body">
                <form id="myform">
                    <div class="form-horizontal">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group"> 
                                    <label class="col-sm-3 control-label">Nama Supplier</label>
                                    <div class="col-sm-9">
                                        <select class="form-control" id="cbosupplier" name="supplier" style="width:100%" required>
                                            <option value=""></option>
                                            <?php
                                              $query = $this->db->query("SELECT * FROM tb_supplier WHERE tempat='2' AND aktif='1' ");
                                              foreach ($query->result_array() as $data){
                                                  echo "<option value='".$data['kode_supplier']."'>".$data['kode_supplier']." - ".$data['nama_supplier']."</option>";
                                              }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group"> 
                                    <label class="col-sm-3 control-label">No. Faktur</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="faktur" id="faktur" placeholder="No. Faktur" value="" required>
                                        <small style="color:#8c8c8c;font-size:11px;">**jika kosong, bisa diisi dg : kode_supplier + thn + bln + tgl + urutan (tanpa spasi)</small>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Pembayaran</label>
                                    <div class="col-sm-9">
                                        <select class="form-control select2" id="cbopembayaran" name="pembayaran" style="width:50%" required>
                                            <option value="CASH">CASH</option>
                                            <option value="TEMPO">TEMPO</option>
                                            <option value="KONSINYASI">KONSINYASI</option>
                                        </select>
                                    </div>
                                </div>
                            </div><!-- /.col -->
                            
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Tanggal</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                        <input type="text" class="form-control pull-right datepickerr" name="tanggal" id="tanggal" placeholder="tanggal" value="<?php echo date('d/m/Y'); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Jatuh Tempo</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                        <input type="text" class="form-control pull-right datepickerr" name="tgl_jatuh_tempo" id="tgl_jatuh_tempo" placeholder="Tgl Jatuh Tempo" value="<?php echo date('d/m/Y'); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group"> 
                                    <label class="col-sm-3 control-label">Tempo</label>
                                    <div class="col-sm-9">
                                        <div class="input-group" style="width:150px;">
                                            <input type="text" class="form-control number-only" name="tempo" id="tempo" placeholder="0" value="0" required>
                                            <div class="input-group-addon">Hari</div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div><!-- /.form-horizontal -->
                    
                    <!-- Table row -->
                    <div class="row">
                        <div class="col-xs-12 table-responsive">
                            <table id="list_infois" width="100%" class="table table-striped">
                                <thead class="label-primary">
                                <tr>
                                    <th width="3%">#</th>
                                    <th width="30%">Nama Barang</th>
                                    <th width="10%">Harga Beli</th>
                                    <th width="11%">Expired</th>
                                    <th width="8%">Qty</th>
                                    <th width="10%">Satuan</th>
                                    <th width="10%">Total</th>
                                    <th width="8%">Diskon(%)</th>
                                    <th width="10%">Subtotal</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr id="field_1">
                                    <td><button id="remove_1" class="btn btn-danger btn-xs delete-row"><i class="glyphicon glyphicon-remove"></i></button></td>
                                    <td><div class="form-group no-bottom"> <select id="cbobarang_1" class="cbobarang" name="cbobarang[]" style="width:100%" required></select> </div></td>
                                    <td><div class="form-group no-bottom"> <input id="harga_1" type="text" class="form-control number-only harga" name="harga[]" required/> </div></td>
                                    <td><div class="form-group no-bottom"> <input type="text" class="form-control datepicker2 expired" name="expired[]" value="" /></div></td>
                                    <td><div class="form-group no-bottom"> <input id="qty_1" type="text" class="form-control number-only qty" name="qty[]" required/> </div></td>
                                    <td><div class="form-group no-bottom"> <input id="satuan_1" type="text" class="form-control satuan" name="satuan[]" required/> </div></td>
                                    <td><div class="form-group no-bottom"> <input type="text" class="form-control number-only total" name="total[]" readonly/> </div></td>
                                    <td><div class="form-group no-bottom"> <input type="text" class="form-control number-only2 diskon" name="diskon[]" /> </div></td>
                                    <td><div class="form-group no-bottom"> <input type="text" class="form-control number-only subtotal" name="subtotal[]" readonly/> </div></td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="8" align="justify">
                                        <button id="add-row" class="btn btn-success btn-sm pull-left"><i class="glyphicon glyphicon-plus"></i>  Tambah</button>
                                        <b> <span class="pull-right">Grand Subtotal</span> </b></td>
                                    <td><b> <span id="txtsubtotal1"></span> <input type="hidden" name="subtotal1" id="subtotal1"> </b></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                    
                    <div class="row">
                        <div class="col-xs-12">
                            <table width="100%">
                                <tr>
                                    <td width="87%" style="padding-right:10px;"><b> <span class="pull-right">Discount</span> </b></td>
                                    <td width="13%"><div class="form-group no-bottom"> <input type="text" class="form-control number-only2" name="diskon1" id="diskon1"/> </div></td>
                                </tr>
                                <tr>
                                    <td style="padding-right:10px;"><b> <span class="pull-right">Subtotals</span> </b></td>
                                    <td><div class="form-group no-bottom"> <input type="text" class="form-control number-only2" name="subtotal2" id="subtotal2" readonly/> </div></td>
                                </tr>
                                <tr>
                                    <td style="padding-right:10px;"><b> <span class="pull-right">Cash Disc</span> </b></td>
                                    <td><div class="form-group no-bottom"> <input type="text" class="form-control number-only2" name="diskon_cash" id="diskon_cash"/> </div></td>
                                </tr>
                                <tr>
                                    <td style="padding-right:10px;"><b> <span class="pull-right">Netto PPN 1</span> </b></td>
                                    <td><div class="form-group no-bottom"> <input type="text" class="form-control number-only2" name="ppn" id="ppn"/> </div></td>
                                </tr>
                                <tr>
                                    <td style="padding-right:10px;"><b> <span class="pull-right">Harus Bayar</span> </b></td>
                                    <td><div class="form-group no-bottom"> <input type="text" class="form-control number-only2" name="bayar" id="bayar" readonly/> </div></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <br>
                    <!-- this row will not appear when submit -->
                    <div class="row no-print">
                        <div class="col-xs-12">
                            <div class="pull-right">
                                <button type="submit" class="btn btn-primary" id="btnsave" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Loading..."><i class="fa fa-save"></i>&nbsp; Simpan</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal"> Tutup</button>
                            </div>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </form><!-- /.form --> 
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col -->
</div><!-- /.row -->
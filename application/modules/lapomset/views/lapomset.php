<!-- Bootsrtap-select -->
<link rel="stylesheet" href="<?php echo base_url();?>assets/plugins/bootstrap-select/bootstrap-select.css" type="text/css"/>
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/bootstrap-select/bootstrap-select.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/plugins/bootstrap-select/i18n/defaults-en_US.js"></script>
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
                        "url": "<?php echo base_url('lapomset/data'); ?>",
                        "data": function( d ) {
                                    var head = ["jenis", "kategori", "satuan" ];
                                    $.each( head, function( index, value ){
                                        d[value] = [];
                                    });
                                    var send = $('#formFilter').serializeArray();
                                    $.each(send, function(i, v) {
                                        if( v.name.indexOf('[]') !== -1 ){
                                            var nama = v.name.replace('[]', '');
                                            if($.inArray(nama, head) > -1){
                                                d[nama].push(v.value);
                                            }                                            
                                        }else{
                                            d[v.name] = v.value;
                                        }                                    
                                    });                                    
                                },      
                        "dataType": "json",
                        "type": "POST"
                    },
                    "initComplete": function( settings, json ) {
                        var datajson = json.totalz;
                        $('#footer1').html(datajson.stok_awal);
                        $('#footer2').html(datajson.qty2);
                        $('#footer3').html("Rp. "+addPeriod(datajson.total2));
                        $('#footer4').html(datajson.qty1);
                        $('#footer5').html("Rp. "+addPeriod(datajson.total1));
                        $('#footer6').html("Rp. "+addPeriod(datajson.omset));
                    },
                    "columnDefs": [
                            {"className": "dt-center", "targets": [0,1,2,4,5,6,7,8,10]}
                        ],
                    "columns": [
                            { "data": "urutan" },
                            { "data": "kode_obat" },
                            { "data": "barcode" },
                            { "data": "nama_obat" },
                            { "data": "jenis_obat" },
                            { "data": "id_kategori" },
                            { "data": "id_satuan" },
                            { "data": "stok_awal" },
                            { "data": "qty2" },
                            { "data": "total2" },
                            { "data": "qty1" },
                            { "data": "total1" },
                    ]
                });	
                
                $('.selectpicker').selectpicker({
                    size: 4
                });

                $(document).on("click","#btncari",function(){
                        var table = $('#table_cust').DataTable(); 
                        table.ajax.reload( function ( json ) {
                            var datajson = json.totalz;
                            $('#footer1').html(datajson.stok_awal);
                            $('#footer2').html(datajson.qty2);
                            $('#footer3').html("Rp. "+addPeriod(datajson.total2));
                            $('#footer4').html(datajson.qty1);
                            $('#footer5').html("Rp. "+addPeriod(datajson.total1));
                            $('#footer6').html("Rp. "+addPeriod(datajson.omset));
                        });
                });        
                    
                $(document).on("click","#btnexport",function(){
                        var cari = $("#txtcari").val();
                        var jenis = $("#jenis").val();
                        var kategori = $("#kategori").val();
                        var satuan = $("#satuan").val();
                        
                        if(jenis!=null){
                            var count1 = jenis.length;
                            for (var i = 0; i < count1; i++) {
                                $('#jenisx').append('<input type="hidden" name="jenis[]" value="'+jenis[i]+'" />');
                            }
                        }
                        if(kategori!=null){
                            var count2 = kategori.length;
                            for (var i = 0; i < count2; i++) {
                                $('#kategorix').append('<input type="hidden" name="kategori[]" value="'+kategori[i]+'" />');
                            }
                        }
                        if(satuan!=null){
                            var count3 = satuan.length;
                            for (var i = 0; i < count3; i++) {
                                $('#satuanx').append('<input type="hidden" name="satuan[]" value="'+satuan[i]+'" />');
                            }
                        }
                        $("#carian").val(cari);
                        $('#export_form').submit();
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
                        table.ajax.reload( function ( json ) {
                            var datajson = json.totalz;
                            $('#footer1').html(datajson.stok_awal);
                            $('#footer2').html(datajson.qty2);
                            $('#footer3').html("Rp. "+addPeriod(datajson.total2));
                            $('#footer4').html(datajson.qty1);
                            $('#footer5').html("Rp. "+addPeriod(datajson.total1));
                            $('#footer6').html("Rp. "+addPeriod(datajson.omset));
                        });
                    }
                });
	});
        
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
    padding: 1px;
    /* border: 1px solid #ccc; */
}
.table tfoot > tr td {
    padding: 5px 1px;
    /* border: 1px solid #ccc; */
}
.table tr td {
    border: 1px solid #ccc;
}
.fot-style{
    font-weight:bold;
}
</style>
<div class="row">
    <div class="col-md-12">
        <!-- Default box -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Laporan Omset</h3>
            </div>
            <div class="box-body"> 
                <form id="export_form" action="<?php echo base_url('lapomset/export'); ?>" method="post" target="_self">
                    <input type="hidden" id="carian" name="cari" value=""/>
                    <div id="jenisx"></div>
                    <div id="kategorix"></div>
                    <div id="satuanx"></div>
                    <input type="hidden" name="umpan" value="umpan"/>
                    <input type="hidden" name="draw" value="1"/>
                    <input type="hidden" name="start" value="0"/>
                    <input type="hidden" name="length" value=""/>
                </form>    
                <p>
                    <button type="button" class="btn btn-success" id="btnexport"><i class="fa fa-file-excel-o"></i> Export Excel</button>
                </p>
                <div class="table-toolbarnya">
                    <form id="formFilter" class="form-horizontal">
                        <div class="col-sm-9" style="padding-left:0px">
                            <ul class="nav nav-pills">
                                <li>
                                    <div class="input-group">
                                        <div class="input-group-addon label-warning">Jenis</div>
                                        <select class="form-control selectpicker" id="jenis" name="jenis[]" data-size="10" data-actions-box="true" data-width="180px" multiple>
                                            <option value="">All</option>
                                            <?php
                                                $query = $this->db->query("SELECT * FROM m_jenis where aktif='1'");
                                                foreach ($query->result_array() as $data){
                                                    echo "<option value='".$data['kode']."'>".$data['nama']."</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <div class="input-group">
                                        <div class="input-group-addon label-warning">Kategori</div>
                                        <select class="form-control selectpicker" id="kategori" name="kategori[]" data-size="10" data-actions-box="true" data-width="180px" multiple>
                                            <?php
                                                $query = $this->db->query("SELECT * FROM m_kategori where aktif='1'");
                                                foreach ($query->result_array() as $data){
                                                    echo "<option value='".$data['kode']."'>".$data['nama']."</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <div class="input-group">
                                        <div class="input-group-addon label-warning">Satuan</div>
                                        <select class="form-control selectpicker" id="satuan" name="satuan[]" data-size="10" data-actions-box="true" data-width="180px" multiple>
                                            <?php
                                                $query = $this->db->query("SELECT * FROM m_satuan where aktif='1'");
                                                foreach ($query->result_array() as $data){
                                                    echo "<option value='".$data['kode']."'>".$data['nama']."</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </li>
                                
                            </ul>                
                        </div>
                        <div class="col-sm-3" style="padding-right:0px">
                            <div class="input-group">
                                <input type="text" class="form-control" id="txtcari" name="cari" placeholder="Pencarian"/>
                                <span class="input-group-btn">
                                  <button type="button" class="btn btn-info" id="btncari"><i class="fa fa-search"></i> Cari</button>
                                </span>
                            </div><!-- /input-group -->
                        </div>
                    </form>
                </div><!-- /.table-toolbarnya-->

                <table id="table_cust" width="100%" class="table table-bordered table-striped">
                    <thead class="label-primary">
                      <tr>
                            <th style="width:5%">No</th>
                            <th style="width:9%">Kode</th>
                            <th style="width:10%">barcode</th>
                            <th style="width:15%">Nama Obat</th>
                            <th style="width:7%">Jenis</th>
                            <th style="width:7%">Kategori</th>
                            <th style="width:9%">Satuan</th>
                            <th style="width:6%">Stok Awal</th>
                            <th style="width:6%">Stok Beli</th>
                            <th style="width:10%">Total Beli</th>
                            <th style="width:6%">Stok Jual</th>
                            <th style="width:10%">Total Jual</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" style="text-align: right;"><span class="fot-style">Totals</span></td>
                            <td><span id="footer1" class="fot-style"></span></td>
                            <td><span id="footer2" class="fot-style"></span></td>
                            <td><span id="footer3" class="fot-style"></span></td>
                            <td><span id="footer4" class="fot-style"></span></td>
                            <td><span id="footer5" class="fot-style"></span></td>
                        </tr> 
                        <tr>
                            <td colspan="11" style="text-align:right;padding-right:5px;"><span class="fot-style">Total Keuntungan</span></td>
                            <td><span id="footer6" class="fot-style"></span></td>
                        </tr>            
                    </tfoot>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col -->
</div><!-- /.row -->    
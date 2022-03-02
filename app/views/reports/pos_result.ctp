<?php
include("includes/function.php");
$rnd = rand();
$oTable = "oTable" . $rnd;
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
$tblName   = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var <?php echo $oTable; ?>;
    $(document).ready(function(){
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        <?php echo $oTable; ?> = $("#<?php echo $tblName; ?>").dataTable({
            "aLengthMenu": [[50, 100, 500, 1000, 5000, 10000, 1000000*1000000], [50, 100, 500, 1000, 5000, 10000, "All"]],
            "iDisplayLength": 1000000*1000000,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/posAjax/<?php echo str_replace("/", "|||", implode(',', $_POST)); ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:nth-child(5)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(6)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(7)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(8)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(9)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(10)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(11)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(12)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnPrintInvoicePos").click(function(event){
                    event.preventDefault();
                    url = "<?php echo $this->base . '/point_of_sales'; ?>/printReceipt/"+$(this).attr("rel");
                    $.ajax({
                        type: "POST",
                        url: url,
                        beforeSend: function(){
                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        },
                        success: function(printResult){
                            w=window.open();
                            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                            w.document.write(printResult);
                            w.document.close();
                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                        }
                    });
                });
                var total = 0;
                var bankCharge = 0;
                var balance = 0;
                var totalDollar = 0;
                var totalRiel = 0;
                var totalChange = 0;
                var totalChangeRiel = 0;
                var currencyVal3 = "";
                var currencyVal4 = "";
                var currencyVal5 = "";
                var currencyVal6 = "";
                var currencyVal7 = "";
                $("#<?php echo $tblName; ?> tr:gt(0)").each(function(){
                    var strVal3 = $(this).find("td:eq(4)").text().split(" ");
                    var strVal4 = $(this).find("td:eq(5)").text().split(" ");
                    var strVal5 = $(this).find("td:eq(6)").text().split(" ");
                    var strVal6 = $(this).find("td:eq(7)").text().split(" ");
                    var strVal7 = $(this).find("td:eq(8)").text().split(" ");
                    var strVal8 = $(this).find("td:eq(9)").text().split(" ");
                    var strVal9 = $(this).find("td:eq(10)").text().split(" ");
                    
//                    currencyVal3 = strVal3[1];
//                    currencyVal4 = strVal4[1];
//                    currencyVal5 = strVal5[1];
//                    currencyVal6 = strVal6[1]; 
//                    currencyVal7 = strVal7[1]; 
                    
                    total += Number(strVal3[0].replace(/,/g, ""));
                    bankCharge += Number(strVal4[0].replace(/,/g, ""));
                    balance += Number(strVal5[0].replace(/,/g, ""));
                    totalDollar += Number(strVal6[0].replace(/,/g, ""));
                    totalRiel += Number(strVal7[0].replace(/,/g, ""));
                    totalChange += Number(strVal8[0].replace(/,/g, ""));
                    totalChangeRiel += Number(strVal9[0].replace(/,/g, ""));
                });
                
                $('#<?php echo $tblName; ?> > tbody:last').append('<tr><td class="first" style="font-weight: bold;" colspan="4"><?php echo TABLE_TOTAL; ?>:</td><td class="" style="text-align: right;font-weight: bold;">' + (total.toFixed(2) + " " + currencyVal3) + '</td><td class="" style="text-align: right;font-weight: bold;">' + (bankCharge.toFixed(2) + " " + currencyVal3) + '</td><td class="" style="text-align: right;font-weight: bold;">' + (balance.toFixed(2) + " " + currencyVal3) + '</td><td class="" style="text-align: right;font-weight: bold;">' + (totalDollar.toFixed(2) + " " + currencyVal4) + '</td><td class="" style="text-align: right;font-weight: bold;">' + (totalRiel.toFixed(2) + " " + currencyVal5) + '</td><td class="" style="text-align: right;font-weight: bold;">' + (totalChange.toFixed(2) + " " + currencyVal6) + '</td><td class="" style="text-align: right;font-weight: bold;">' + (totalChangeRiel.toFixed(2) + " " + currencyVal7) + '</td><td colspan="2"></td></tr>');
                $('.formatCurrency').formatCurrency({colorize:true});
                $('.formatCurrencyRiel').formatCurrency({colorize:true, symbol:'៛'});
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0 ]
            }],
            "aaSorting": [[ 1, "desc" ]]
        });
        $("#<?php echo $btnPrint; ?>").click(function(){
            $(".dataTables_length").hide();
            $(".dataTables_filter").hide();
            $(".dataTables_paginate").hide();
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
            $(".dataTables_length").show();
            $(".dataTables_filter").show();
            $(".dataTables_paginate").show();
        });
        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/invoice_pos<?php echo $user['User']['id']; ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_REPORT_POS . '</b><br /><br />';
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    ?>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table_report">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_INVOICE_DATE; ?></th>
                    <th><?php echo TABLE_INVOICE_CODE; ?></th>
                    <th style="width: 140px !important;"><?php echo 'Payment Method'; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_BANK_CHARGE; ?></th>
                    <th style="width: 120px !important;"><?php echo GENERAL_BALANCE; ?></th>
                    <th style="width: 120px !important;"><?php echo GENERAL_PAID; ?></th>
                    <th style="width: 120px !important;"><?php echo GENERAL_PAID; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_CHANGE; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_CHANGE; ?></th>
                    <th style="width: 180px !important;"><?php echo TABLE_CREATED_BY; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="11" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div style="clear: both;"></div>
<br />
<div class="buttons">
    <button type="button" id="<?php echo $btnPrint; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
        <?php echo ACTION_PRINT; ?>
    </button>
    <button type="button" id="<?php echo $btnExport; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/csv.png" alt=""/>
        <?php echo ACTION_EXPORT_TO_EXCEL; ?>
    </button>
</div>
<div style="clear: both;"></div>
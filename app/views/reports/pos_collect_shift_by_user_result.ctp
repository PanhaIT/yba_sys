<?php
include('includes/function.php');
$rnd = rand();
$oTable = "oTable" . $rnd;
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
$tblName = "tbl" . rand(); ?>
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
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/posCollectShiftByUserAjax/<?php echo str_replace("/", "|||", implode(',', $_POST)); ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:nth-child(2)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(3)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(5)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(6)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(7)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(8)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(9)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnPrintCustomerInvoice").click(function(event){
                    event.preventDefault();
                    var url = "<?php echo $this->base . '/sales_orders'; ?>/printInvoice/"+$(this).attr("rel");
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
                var totalCaseRigister      = 0;
                var totalCaseAdjust        = 0;
                var totalCaseAture         = 0;
                var totalCaseSales         = 0;
                var totalCaseSpread        = 0;
                var labelCurrency          = "";
                var labelCurrencyOther     = "";
                $("#<?php echo $tblName; ?> tr:gt(0)").each(function(){
                    totalCaseRigister         += replaceNum($(this).find(".btnShiftCollect").attr("totalRegis"));
                    totalCaseAdjust           += replaceNum($(this).find(".btnShiftCollect").attr("totalAdj"));
                    totalCaseAture            += replaceNum($(this).find(".btnShiftCollect").attr("totalActure"));
                    totalCaseSales            += replaceNum($(this).find(".btnShiftCollect").attr("totalSales"));
                    totalCaseSpread           += replaceNum($(this).find(".btnShiftCollect").attr("totalSpread"));
                    labelCurrency              = $(this).find(".btnShiftCollect").attr("symbolmain")!=""?$(this).find(".btnShiftCollect").attr("symbolmain"):"";
                    labelCurrencyOther         = $(this).find(".btnShiftCollect").attr("symbol")!=""?$(this).find(".btnShiftCollect").attr("symbol"):"";
                });
                $('#<?php echo $tblName; ?> > tbody:last').append('<tr><td class="first" style="font-weight: bold;" colspan="4"><?php echo TABLE_TOTAL; ?>:</td><td class="formatCurrency" style="text-align: right;font-weight: bold;">' + (totalCaseAture) + '</td><td class="formatCurrency" style="text-align: right;font-weight: bold;">' + (totalCaseRigister) + '</td><td class="formatCurrency" style="text-align: right;font-weight: bold;">' + (totalCaseAdjust) + '</td><td class="formatCurrency" style="text-align: right;font-weight: bold;">' + (totalCaseSales) + '</td><td class="formatCurrency" style="text-align: right;font-weight: bold;">' + (totalCaseSpread) + '</td></tr>');
                if(labelCurrency == undefined){
                    labelCurrency = "";    
                }
                if(labelCurrencyOther == undefined){
                    labelCurrencyOther = "";    
                }
                $('.formatCurrency').formatCurrency({colorize:true, symbol: labelCurrency+" "});
                $('.formatCurrencyOther').formatCurrency({colorize:true, symbol: labelCurrencyOther, roundToDecimalPlace: 0});
                
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
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_REPORT_COLLECT_SHIFT_BY_USER . '</b><br /><br />';
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
                    <th style="width: 100px !important;"><?php echo TABLE_CODE; ?></th>
                    <th style="width: 150px !important;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 150px !important;"><?php echo TABLE_APPROVE_BY; ?></th>
                    <th><?php echo TABLE_TOTAL." ".TABLE_STATUS_COLLECT; ?></th>
                    <th><?php echo TABLE_TOTAL." ".TABLE_SHORT_CASE_IN_REGISTER; ?></th>
                    <th><?php echo TABLE_TOTAL." ".TABLE_TOTAL_ADJUST_END_REGISTER; ?></th>
                    <th><?php echo TABLE_TOTAL." ".TABLE_TOTAL_SALES_END_REGISTER; ?></th>
                    <th><?php echo TABLE_TOTAL." ".TABLE_TOTAL_SPREAD_REGISTER; ?></th>
                    <th style="width: 150px !important;"><?php echo TABLE_CREATED_BY; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="14" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
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
</div>
<div style="clear: both;"></div>
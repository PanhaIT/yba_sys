<?php
include("includes/function.php");
$rnd = rand();
$oTable = "oTable" . $rnd;
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;

?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var <?php echo $oTable; ?>;
    $(document).ready(function(){
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        <?php echo $oTable; ?> = $("#<?php echo $tblName; ?>").dataTable({
            "aLengthMenu": [[50, 100, 500, 1000, 5000, 10000, 1000000*1000000], [50, 100, 500, 1000, 5000, 10000, "All"]],
            "iDisplayLength": 50,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/generalLedgerAjax/<?php echo str_replace("/", "|||", implode(',', $_POST)); ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:nth-child(10)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(11)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td").css("vertical-align", "top");
                // btn link to general ledger
                $(".link2glJournal").each(function(){
                    var general_ledger_id=$(this).val();
                    $(this).closest("tr").css("cursor", "pointer");
                    $(this).closest("tr").click(function(){
                        $('#tabs ul li a').not("[href=#]").each(function(index) {
                            if($(this).text().indexOf(jQuery.trim("<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>"))!=-1){
                                $("#tabs").tabs("select", $(this).attr("href"));
                                var selIndex = $("#tabs").tabs("option", "selected");
                                $("#tabs").tabs("remove", selIndex);
                            }
                        });
                        $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexById/" + general_ledger_id, "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
                    });
                });
                var totalDebit = 0;
                var totalCrebit = 0;
                $("#<?php echo $tblName; ?> tr:gt(0)").each(function(){
                    totalDebit += Number($(this).find("td:eq(10)").text().replace(/,/g, ""));
                    totalCrebit += Number($(this).find("td:eq(11)").text().replace(/,/g, ""));
                });
                $('#<?php echo $tblName; ?> > tbody:last').append('<tr><td class="first" colspan="8"></td><td style="font-weight: bold;" colspan="2">TOTAL</td><td class="formatCurrency"  style="text-align: right;border-top: 1px solid #000;border-bottom: 3px double #000;">' + (totalDebit) + '</td><td class="formatCurrency"  style="text-align: right;border-top: 1px solid #000;border-bottom: 3px double #000;">' + (totalCrebit) + '</td></tr>');
                $('.formatCurrency').formatCurrency({colorize:true});

//                $("#<?php echo $printArea; ?> .dataTables_length").hide();
//                $("#<?php echo $printArea; ?> .dataTables_info").hide();
//                $("#<?php echo $printArea; ?> .dataTables_paginate").hide();

                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1,-2,-3,-4 ]
            }],
            "aaSorting": [[ 1, "asc" ]]
        });
        $("#<?php echo $btnPrint; ?>").click(function(){
            $(".dataTables_length").hide();
            $(".dataTables_filter").hide();
            $(".dataTables_paginate").hide();
            $("#<?php echo $tblName; ?> tr:not(:last) th:nth-child(3)").hide();
            $("#<?php echo $tblName; ?> tr:not(:last) td:nth-child(3)").hide();
            $("#<?php echo $tblName; ?> tr:not(:last) th:nth-child(5)").hide();
            $("#<?php echo $tblName; ?> tr:not(:last) td:nth-child(5)").hide();
            $("#<?php echo $tblName; ?> tr:not(:last) th:nth-child(6)").hide();
            $("#<?php echo $tblName; ?> tr:not(:last) td:nth-child(6)").hide();
            $("#<?php echo $tblName; ?> tr:last td:first-child").attr("colspan","5");
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
            $("#<?php echo $tblName; ?> tr:not(:last) th:nth-child(3)").show();
            $("#<?php echo $tblName; ?> tr:not(:last) td:nth-child(3)").show();
            $("#<?php echo $tblName; ?> tr:not(:last) th:nth-child(5)").show();
            $("#<?php echo $tblName; ?> tr:not(:last) td:nth-child(5)").show();
            $("#<?php echo $tblName; ?> tr:not(:last) th:nth-child(6)").show();
            $("#<?php echo $tblName; ?> tr:not(:last) td:nth-child(6)").show();
            $("#<?php echo $tblName; ?> tr:last td:first-child").attr("colspan","8");
        });
        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/journal_<?php echo $user['User']['id']; ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <style type="text/css">
        #<?php echo $tblName; ?> th{
            vertical-align: top;
            padding: 10px;
        }
        #<?php echo $tblName; ?> td{
            vertical-align: top;
            padding: 10px;
        }
    </style>
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_JOURNAL_ENTRY . '</b><br /><br />';
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
                    <th style="width: 60px !important;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 80px !important;">Creator</th>
                    <th style="width: 120px !important;"><?php echo TABLE_REFERENCE; ?></th>
                    <th style="width: 40px !important;"><?php echo TABLE_ADJUST; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_TYPE; ?></th>
                    <th style="width: 100px !important;">Account Code</th>
                    <th><?php echo TABLE_ACCOUNT; ?></th>
                    <th><?php echo GENERAL_DESCRIPTION; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_CLASS; ?></th>
                    <th style="width: 80px !important;"><?php echo GENERAL_DEBIT; ?></th>
                    <th style="width: 80px !important;"><?php echo GENERAL_CREDIT; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="10" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td><br />&nbsp;</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php echo $this->element('report_footer'); ?>
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
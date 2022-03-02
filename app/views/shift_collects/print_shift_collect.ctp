<?php
include("includes/function.php");
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<style type="text/css" media="screen">
    * {
        font-family: "Leelawadee UI", "Kh-Battambang", serif;
    }
    .tableAdjInfor, .tableAdjInfor td, .tableAdjInfor th {
        border: 1px solid black;
    }
    .titleBlock{
        text-align: center; 
        font-weight: bold; 
        width: 348px;
        display: table;
        height: 25px;
    }
    .titleBlock span{
        vertical-align: middle;
        display: table-cell;
    }   
    div#header_waiting { display: none;}
    div.wrap-print-slip { width:350px;}
    div#print-footer {display: none;} 
    b{font-size:12px;}
</style> 
<style type="text/css" media="print">
    * {
        font-family: "Leelawadee UI", "Kh-Battambang", serif;
    }
    .tableAdjInfor, .tableAdjInfor td, .tableAdjInfor th {
        border: 1px solid black;
    }
    .titleBlock{
        text-align: center; 
        font-weight: bold; 
        width: 100%;
        display: table;
        height: 25px;
    }
    .titleBlock span{
        vertical-align: middle;
        display: table-cell;
    }    
    div#header_waiting { width:100%; text-align: center; margin: 0px auto; display: block; padding-bottom: 20px; padding-top: 0px; page-break-after: always}
    div.wrap-print-slip { width:100%;}
    #btnDisappearPrint { display: none;}
    div#print-footer {display: block; margin-top: 10px; width:100%}
    .wrap-print-slip table tr td{ padding-top:2px;padding-bottom: 2px;}
    td{
        font-size: 11px !important;
    }
</style>
<div class="wrap-print-slip" style="font-size: 14px; padding: 0px;">
    <?php
        $time = explode(" ", $shifts['ShiftCollect']['date']);
        $msg  = "";
        $exchangeId = '';
        $currencyId = '';
        $currencyRate = '';
        $currencySymbol = '';
        if($branch['Branch']['pos_currency_id'] != ''){
            $sqlCurrencyOther = mysql_query("SELECT branch_currencies.currency_center_id, IFNULL(branch_currencies.rate_to_sell,0), IFNULL(branch_currencies.exchange_rate_id,0), currency_centers.symbol FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.id = ".$branch['Branch']['pos_currency_id']);
            if(mysql_num_rows($sqlCurrencyOther)){
                $rowCurrencyOther = mysql_fetch_array($sqlCurrencyOther);
                $currencyId     = $rowCurrencyOther[0];
                $currencyRate   = $rowCurrencyOther[1];
                $exchangeId     = $rowCurrencyOther[2];
                $currencySymbol = $rowCurrencyOther[3];
            }
        }
        
        echo $this->element('/print/header_small_change_shift', array('msg' => $msg, 'address' => $branch['Branch']['address'], 'telephone' => $branch['Branch']['telephone'], 'logo' => $company['Company']['photo'], 'title' => $branch['Branch']['name']));
    ?>
    <div style="clear:both;"></div>       
    <div>
        <div style="margin-top:2px; ">
            <div class="titleBlock" style="border: 1px solid #000; border-bottom: none;">
                <span><?php echo MENU_SHIFT_COLLECT; ?></span>                
            </div>
            <table width="100%" cellpadding="2" cellspacing="0" style="width: 100%; border: 1px solid #000; padding: 10px; border-bottom: none;">
                <tr>
                    <td style="width: 20%;"><?php echo MENU_SHIFT_COLLECT_CODE; ?>: </td>
                    <td style="width: 30%;">: 
                        <?php
                        echo $shifts['ShiftCollect']['code'];
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo TABLE_SHIFT_USER_SALES; ?></td>
                    <td>: 
                        <?php 
                            $querySales = mysql_query("SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id = '".$shifts['ShiftCollect']['user_id']."'");
                            $dataSales  = mysql_fetch_array($querySales);
                            echo $dataSales[0];
                        ?>
                    </td>                    
                </tr>  
                <tr>
                    <td><?php echo TABLE_APPROVE_BY; ?></td>
                    <td>: 
                        <?php 
                            $queryEmp = mysql_query("SELECT name FROM employees WHERE id = '".$shifts['ShiftCollect']['employee_id']."'");
                            $dataEmp  = mysql_fetch_array($queryEmp);
                            echo $dataEmp[0];                            
                        ?>
                    </td>                    
                </tr> 
                <tr>
                    <td><?php echo TABLE_DATE; ?> </td>
                    <td>: 
                        <?php
                        echo date('d/m/Y H:i:s', strtotime($shifts['ShiftCollect']['date']));
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo GENERAL_EXCHANGE_RATE; ?></td>      
                    <td colspan="2">
                        : 1.00 (<?php echo $branch['CurrencyCenter']['symbol']; ?>) = <?php echo number_format($currencyRate, 9); ?> (<?php echo $currencySymbol; ?>)
                    </td>
                </tr>
            </table> 
            <div class="titleBlock" style="border: 1px solid #000;"><?php echo TABLE_CASE_SHIFT; ?></div>
            <table width="100%" cellpadding="2" cellspacing="2" style="width: 100%; border: 1px solid #000; padding: 10px; border-top: none;">   
                <tr>
                    <td></td>
                    <td style="width: 25%; text-align: right; font-weight: bold;">
                        <?php echo TABLE_AMT_SHIFT." ".$branch['CurrencyCenter']['symbol']; ?>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo TABLE_AMT_SHIFT." ".$currencySymbol; ?>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo POS_TOTAL_PRICES." ".$branch['CurrencyCenter']['symbol']; ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo TABLE_TOTAL_ACTURE_REGISTER; ?></td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($shifts['ShiftCollect']['total_cash_collect'], 2);?>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($shifts['ShiftCollect']['total_cash_collect_other'], 0);?>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($shifts['ShiftCollect']['total_cash_collect'] + ($shifts['ShiftCollect']['total_cash_collect_other'] / $currencyRate), 2);?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo TABLE_SHORT_CASE_IN_REGISTER; ?></td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($shifts['ShiftCollect']['total_register'], 2);?>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($shifts['ShiftCollect']['total_register_other'], 0);?>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($shifts['ShiftCollect']['total_register'] + ($shifts['ShiftCollect']['total_register_other'] / $currencyRate), 2);?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo TABLE_TOTAL_ADJUST_END_REGISTER; ?></td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($shifts['ShiftCollect']['total_adj'], 2);?>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($shifts['ShiftCollect']['total_adj_other'], 0);?>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($shifts['ShiftCollect']['total_adj'] + ($shifts['ShiftCollect']['total_adj_other'] / $currencyRate), 2);?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><?php echo TABLE_TOTAL_SPREAD_REGISTER; ?></td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($shifts['ShiftCollect']['total_spread'], 2);?>
                    </td>
                    
                </tr>
            </table> 
            <table width="100%" cellpadding="2" cellspacing="2" style="width: 100%; border: 1px solid #000; padding: 10px; border-top: none;">   
                <tr>
                    <td style="width: 40%; padding-top: 50px;"><hr/></td>
                    <td style="width: 20%; padding-top: 50px;">&nbsp;</td>
                    <td style="width: 40%; padding-top: 50px;"><hr/></td>
                </tr>
                <tr>
                    <td style="text-align: center;"><?php echo $dataEmp[0];?></td>
                    <td>&nbsp;</td>
                    <td style="text-align: center;"><?php echo $dataSales[0];?></td>
                </tr>
            </table>
        </div>
        <div style="clear:both;"></div>
        <br/>
        <div>
            <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' class='noprint'>
        </div>
        <div style="clear:both"></div>
        <br/><br/>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $(window).keypress(function(e) {
            if ((e.which && e.which == 13) || e.keyCode == 13) {
                var ws = window;
                try {
                    jsPrintSetup.setOption('scaling', 100);
                    jsPrintSetup.clearSilentPrint();
                    jsPrintSetup.setOption('printBGImages', 1);
                    jsPrintSetup.setOption('printBGColors', 1);
                    jsPrintSetup.setSilentPrint(1);
                    // Udaya-Receipt = name of printer
                    // we add douplicate \\ for it working, if user use share printer
                    jsPrintSetup.setPrinter('Udaya-Receipt');
                    jsPrintSetup.printWindow(ws);
                    jsPrintSetup.printWindow(ws);
                    ws.close();
                } catch (e) {
                    ws.print();
                    ws.close();
                }
            }
        });
        $("#btnDisappearPrint").click(function() {
            var ws = window;
            try {
                jsPrintSetup.setOption('scaling', 100);
                jsPrintSetup.clearSilentPrint();
                jsPrintSetup.setOption('printBGImages', 1);
                jsPrintSetup.setOption('printBGColors', 1);
                jsPrintSetup.setSilentPrint(1);
                // Udaya-Receipt = name of printer
                // we add douplicate \\ for it working, if user use share printer
                jsPrintSetup.setPrinter('Udaya-Receipt');
                jsPrintSetup.printWindow(ws);
                jsPrintSetup.printWindow(ws);
                ws.close();
            } catch (e) {
                ws.print();
                ws.close();
            }
        });
    });
</script>
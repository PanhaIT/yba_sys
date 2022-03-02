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
        $time = explode(" ", $shifts['Shift']['date_end']);
        $msg  = "";
        $exchangeId = '';
        $currencyId = '';
        $currencyRate = 0;
        $currencySymbol = '';
        if($branch['Branch']['pos_currency_id'] != ''){
            $sqlCurrencyOther = mysql_query("SELECT branch_currencies.currency_center_id, IFNULL(branch_currencies.rate_to_sell,0), IFNULL(branch_currencies.exchange_rate_id,0), currency_centers.symbol FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.id = ".$branch['Branch']['pos_currency_id']);
            if(@mysql_num_rows($sqlCurrencyOther)){
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
                <span><?php echo MENU_TITLE_SHIFT; ?></span>                
            </div>
            <table width="100%" cellpadding="2" cellspacing="0" style="width: 100%; border: 1px solid #000; padding: 10px; border-bottom: none;">
                <tr>
                    <td style="width: 20%;"><?php echo TABLE_CHANGE_SHIFT_CODE; ?>: </td>
                    <td style="width: 30%;">: 
                        <?php
                        echo $shifts['Shift']['shift_code'];
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo TABLE_SHIFT_USER_SALES; ?></td>
                    <td>: <?php echo $user['User']['username']; ?></td>                    
                </tr>                
                <tr>
                    <td><?php echo TABLE_DATE_TIME_START; ?> </td>
                    <td>: 
                        <?php
                        echo date('d/m/Y H:i:s', strtotime($shifts['Shift']['date_start']));
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo TABLE_DATE_TIME_END; ?></td>
                    <td>: 
                        <?php
                        echo date('d/m/Y H:i:s', strtotime($shifts['Shift']['date_end']));
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
                    <td style="width: 33.33%; text-align: right; font-weight: bold;">
                        <?php echo $branch['CurrencyCenter']['symbol']; ?>
                    </td>
                    <td style="width: 33.33%; text-align: right; font-weight: bold;">
                        <?php echo $currencySymbol; ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo TABLE_SHORT_CASE_IN_REGISTER; ?></td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($shifts['Shift']['total_register'], 2);?>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($shifts['Shift']['total_register_other'], 0);?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo TABLE_TOTAL_ADJUST_END_REGISTER; ?></td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($totalAdj, 2);?>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($totalAdjOther, 0);?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo TABLE_TOTAL_ACTURE_REGISTER; ?></td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($shifts['Shift']['total_acture'], 2);?>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($shifts['Shift']['total_acture_other'], 0);?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="width: 12%;text-align: center;"></td>
                </tr>
            </table> 
            <div class="titleBlock" style="border: 1px solid #000; border-top: none; border-bottom: none;"><?php echo TABLE_TOTAL_ADJUST_INFO; ?></div>
            <table class="tableAdjInfor" width="100%" cellpadding="2" cellspacing="2" style="border-collapse: collapse; width: 100%; padding: 10px; border-top: none;">
                <tr>
                    <td class="first" style="text-align: center; width: 10%;"><?php echo TABLE_DATE; ?></td>
                    <td style="text-align: center; width: 22%;"><?php echo TABLE_AMT_SHIFT; ?> (<?php echo $branch['CurrencyCenter']['symbol']; ?>)</td>
                    <td style="text-align: center; width: 22%;"><?php echo TABLE_AMT_SHIFT; ?> (<?php echo $currencySymbol; ?>)</td>
                    <td style="text-align: center;"><?php echo GENERAL_DESCRIPTION; ?></td>
                </tr>
                <?php  
                    $queryGetAdj = mysql_query("SELECT created, total_adj, total_adj_other, description FROM shift_adjusts WHERE shift_id = '".$shifts['Shift']['id']."'");
                    if(mysql_num_rows($queryGetAdj)){
                        while($dataGetAdj = mysql_fetch_array($queryGetAdj)){
                ?>
                <tr>
                    <td style="text-align: center;"><?php echo date("H:i:s", strtotime($dataGetAdj[0])); ?></td>
                    <td style="text-align: right;"><?php echo number_format($dataGetAdj[1], 2); ?></td>
                    <td style="text-align: right;"><?php echo number_format($dataGetAdj[2], 2); ?></td>
                    <td><?php echo $dataGetAdj[3]; ?></td>
                </tr>
                <?php
                        }
                    }else{
                ?>
                <tr>
                    <td colspan="7" class="dataTables_empty first"><?php echo TABLE_NO_RECORD; ?></td>
                </tr>
                <?php
                    }
                ?>
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
        $("#btnDisappearPrint").click(function() {
            var ws = window;
            ws.print();
            ws.close();
        });
    });
</script>
<?php 
include("includes/function.php");
$allowLots    = false;
$allowExpired = false;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    }
}
$status = array('0'=>'Void','1'=>'Issued','2'=>'Partial','3'=>'Fulfilled');
$colSpan = 8;
?>
<style type="text/css" media="screen">
    div.print-footer {display: none;}
</style> 
<style type="text/css" media="print">
    div.print_doc { width:100%;}
    #btnDisappearPrint { display: none;}
    div.print-footer {display: block; width:100%;} 
</style>
<div class="print_doc">
    <table style="width: 100%;">
        <tr>
            <td style="vertical-align: top; text-align: center; width: 75%; font-size: 20px; font-weight: bold;">
                អេស លីកហ្គ័រ
            </td>
            <td style="text-align: left; width: 10%;"></td>
            <td style="white-space: nowrap;"></td>
        </tr>
        <tr>
            <td style="vertical-align: top; text-align: center; width: 75%; font-size: 18px; font-weight: bold;">
                S Liquor
            </td>
            <td style="text-align: left; width: 10%;"></td>
            <td style="white-space: nowrap;"></td>
        </tr>
        <tr>
            <td style="vertical-align: top; text-align: center; width: 75%; font-size: 15px; font-weight: bold;">
                ប័ណ្ណបញ្ជូល ទំនិញ (ស្តុក)
            </td>
            <td style="text-align: left; width: 10%;">GTN No:</td>
            <td style="white-space: nowrap;">
                <?php echo $this->data['TransferOrder']['to_code']; ?>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; text-align: center; width: 75%; font-size: 15px; font-weight: bold;">
                GOODS TRANSFER NOTE
            </td>
            <td style="text-align: left; width: 10%;">Date:</td>
            <td style="white-space: nowrap;">
                <?php echo dateShort($this->data['TransferOrder']['order_date'], "d/M/Y"); ?>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; text-align: center; width: 75%;"></td>
            <td style="text-align: left; width: 10%;">Created By:</td>
            <td style="white-space: nowrap;">
                <?php echo $this->data['User']['first_name']." ".$this->data['User']['last_name']; ?>
            </td>
        </tr>
    </table>
    <div style="height: 10px"></div>
    <table cellpadding="5" width="100%">
        <tr>
            <td style="font-size: 11px;"><?php echo TABLE_BRANCH; ?>: <?php echo $this->data['Branch']['name']; ?></td>
            <td style="vertical-align: top; font-size: 11px; width: 30%;">
                <?php echo TABLE_FROM_WAREHOUSE; ?>: <?php echo $fromLocationGroups['LocationGroup']['name']; ?>
            </td>
        </tr>
        <tr>
            <td style="font-size: 10px;"></td>
            <td style="vertical-align: top; font-size: 11px;">
                <?php echo TABLE_TO_WAREHOUSE; ?>: <?php echo $toLocationGroups['LocationGroup']['name']; ?>
            </td>
        </tr>
    </table>
    <br />
    <div>
        <div>
            <table id="tblTO" class="table_print">
                <tr>
                    <th class="first" style="width:4%; font-size: 10px;"><?php echo 'ល.រ'; ?></th>
                    <th style="width:8%; font-size: 10px;"><?php echo 'លេខកូដផលិតផល'; ?></th>
                    <th style="width:26%; font-size: 10px;"><?php echo 'ឈ្មោះផលិតផល'; ?></th>
                    <th style="width:10%; font-size: 10px; <?php if($allowLots == false){  ?>display: none;<?php } else { $colSpan++; } ?>"><?php echo TABLE_LOTS_NO; ?></th>
                    <th style="width:11%; font-size: 10px; <?php if($allowExpired == false){ ?>display: none;<?php } else { $colSpan++; } ?>"><?php echo TABLE_EXPIRED_DATE; ?></th>
                    <th style="width:11%; font-size: 10px;"><?php echo 'ពីទីតាំង'; ?></th>
                    <th style="width:11%; font-size: 10px;"><?php echo 'ទៅទីតាំង'; ?></th>
                    <th style="width:7%; font-size: 10px;"><?php echo 'បរិមាណ '; ?></th>
                    <th style="width:12%; font-size: 10px;"><?php echo 'ឯកត្តាគិត'; ?></th>
                    <th style="width:12%; font-size: 10px;"><?php echo 'ថ្លៃជាមធ្យម'; ?></th>
                    <th style="width:12%; font-size: 10px;"><?php echo 'សរុប'; ?></th>
                </tr>
                <tr>
                    <th class="first" style="width:4%; font-size: 10px;"><?php echo 'No.'; ?></th>
                    <th style="width:8%; font-size: 10px;"><?php echo 'PRODUCT CODE'; ?></th>
                    <th style="width:26%; font-size: 10px;"><?php echo 'NAME OF PRODUCT'; ?></th>
                    <th style="width:10%; font-size: 10px; <?php if($allowLots == false){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
                    <th style="width:11%; font-size: 10px; <?php if($allowExpired == false){ ?>display: none;<?php } ?>"><?php echo TABLE_EXPIRED_DATE; ?></th>
                    <th style="width:11%; font-size: 10px;"><?php echo 'FROM LOCATION'; ?></th>
                    <th style="width:11%; font-size: 10px;"><?php echo 'TO LOCATION'; ?></th>
                    <th style="width:7%; font-size: 10px;"><?php echo 'QUANTITY'; ?></th>
                    <th style="width:12%; font-size: 10px;"><?php echo 'UoM'; ?></th>
                    <th style="width:12%; font-size: 10px;"><?php echo 'AVG Cost'; ?></th>
                    <th style="width:12%; font-size: 10px;"><?php echo 'Total'; ?></th>
                </tr>
            <?php
            $totalCost = 0;
            if(!empty($transferOrderDetails)){
                $index = 0;
                foreach($transferOrderDetails AS $transferOrderDetail){
            ?>
                <tr class="recordTODetail">
                    <td class="first" style="width:4%; font-size: 10px;"><?php echo ++$index; ?></td>
                    <td style="width:8%; font-size: 10px;">
                        <?php echo $transferOrderDetail['Product']['barcode']; ?>
                    </td>
                    <td style="width:18%; font-size: 10px;">
                        <?php echo $transferOrderDetail['Product']['name']; ?>
                    </td>
                    <td style="width:10%; font-size: 10px; <?php if($allowLots == false){ ?>display: none;<?php } ?>">
                        <?php echo $transferOrderDetail['TransferOrderDetail']['lots_number']; ?>
                    </td>
                    <td style="width:11%; font-size: 10px; <?php if($allowExpired == false){ ?>display: none;<?php } ?>">
                        <?php 
                        if($transferOrderDetail['TransferOrderDetail']['expired_date'] != "0000-00-00" && $transferOrderDetail['TransferOrderDetail']['expired_date'] != ""){
                            $expDateLbl = dateShort($transferOrderDetail['TransferOrderDetail']['expired_date'], 'd/M/Y');
                        }else{
                            $expDateLbl = "";
                        }
                        echo $expDateLbl; 
                        ?>
                    </td>
                    <td style="width:11%; font-size: 10px;">
                        <?php
                        $sqlLocationFrom = mysql_query("SELECT name FROM locations WHERE id = {$transferOrderDetail['TransferOrderDetail']['location_from_id']} ORDER BY name");
                        $rowLocationFrom = mysql_fetch_array($sqlLocationFrom);
                        echo $rowLocationFrom[0];
                        ?>
                    </td>
                    <td style="width:11%; font-size: 10px;">
                        <?php
                        $sqlLocationTo = mysql_query("SELECT name FROM locations WHERE id = {$transferOrderDetail['TransferOrderDetail']['location_to_id']} ORDER BY name");
                        $rowLocationTo = mysql_fetch_array($sqlLocationTo);
                        echo $rowLocationTo[0];
                        ?>
                    </td>
                    <td style="width:7%; font-size: 10px;">
                        <?php echo number_format($transferOrderDetail['TransferOrderDetail']['qty'], 0); ?>
                    </td>
                    <td style="width:12%; font-size: 10px;">
                        <?php
                        $uomId = $transferOrderDetail['TransferOrderDetail']['qty_uom_id'];
                        $query = mysql_query("SELECT abbr FROM uoms WHERE id=".$uomId." ORDER BY name ASC");
                        $row   = mysql_fetch_array($query);
                        echo $row[0];
                        ?>
                    </td>
                    <td style="text-align: right;">
                        <?php
                            $avgCost = 0;
                            if($this->data['TransferOrder']['total_cost'] > 0){
                                echo number_format($transferOrderDetail['TransferOrderDetail']['unit_cost'], 2)." $";
                            }else{
                                $conversion = $transferOrderDetail['Product']['small_val_uom'] / $transferOrderDetail['TransferOrderDetail']['conversion'];
                                $sqlAvg = mysql_query("SELECT avg_cost FROM inventory_valuations WHERE pid = ".$transferOrderDetail['Product']['id']." AND avg_cost IS NOT NULL ORDER BY id DESC LIMIT 1");
                                if(@mysql_num_rows($sqlAvg)){
                                    $rowAvg = mysql_fetch_array($sqlAvg);
                                    $avgCost = $rowAvg[0] / $conversion;
                                    echo number_format($avgCost, 2)." $";
                                }
                            }
                        ?>
                    </td>
                    <td style="text-align: right;">
                        <?php
                            if($transferOrderDetail['TransferOrderDetail']['total_cost'] > 0){
                                $totalCost += $transferOrderDetail['TransferOrderDetail']['total_cost'];
                                echo number_format($transferOrderDetail['TransferOrderDetail']['total_cost'], 2)." $";
                            }else{
                                $totalCost += $avgCost * $transferOrderDetail['TransferOrderDetail']['qty'];
                                echo number_format($avgCost * $transferOrderDetail['TransferOrderDetail']['qty'], 2)." $";
                            }
                        ?>
                    </td>
                </tr>
            <?php
                }
            }
            ?>
                <tr>
                    <td colspan="<?php echo $colSpan; ?>" style="text-align: right;">សរុបចុងក្រោយ / Total</td>
                    <td style="text-align: right;">
                        <?php echo number_format($totalCost, 2)." $"; ?>
                    </td>
                </tr>
            </table>
            <table cellpadding="5" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="font-size: 11px;">*** បរិយាយ (Memo) : <?php echo $this->data['TransferOrder']['note']; ?></td>
                </tr>
            </table>
            <table style="width: 100%;">
                <tr>
                    <td style="text-align: center; font-size: 10px; width: 20%;">អ្នករៀបចំ</td>
                    <td style="text-align: center; font-size: 10px; width: 20%;">អ្នកប្រគល់ទំនិញ</td>
                    <td style="text-align: center; font-size: 10px; width: 20%;">អ្នកទទូលទំនិញ</td>
                    <td style="text-align: center; font-size: 10px; width: 20%;">ប្រធានឃ្លាំង</td>
                    <td style="text-align: center; font-size: 10px; width: 20%;">គណនេយ្យករ</td>
                </tr>
                <tr>
                    <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                    <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                    <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                    <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                    <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                </tr>
            </table>
        </div>
        <br />
        <div style="clear:both;"></div>
        <div style="float:left;width: 450px">
            <div>
                <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' onClick='window.print();window.close();' class='noprint'>
            </div>
        </div>
        <div style="clear:both"></div>
    </div>
</div>
<div style="clear:both"></div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).dblclick(function(){
            window.close();
        });
    });
</script>
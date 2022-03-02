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
    <?php
    $queryCycleProduct = mysql_query("  SELECT *,
                                        (SELECT name FROM branches WHERE id = inventory_physicals.branch_id) AS branch_name,
                                        (SELECT name FROM location_groups WHERE id=inventory_physicals.location_group_id) AS location_group_name
                                        FROM inventory_physicals WHERE id=" . $id);
    $dataCycleProduct = mysql_fetch_array($queryCycleProduct);
    ?>
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
                ប័ណ្ណលាយបញ្ចូល ទំនិញ (ស្តុក)
            </td>
            <td style="text-align: left; width: 10%; font-size: 11px;">GMN No:</td>
            <td style="white-space: nowrap; font-size: 11px;">
                <?php echo $dataCycleProduct['code']; ?>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; text-align: center; width: 75%; font-size: 15px; font-weight: bold;">
                GOODS MIXED NOTE (Stock)
            </td>
            <td style="text-align: left; width: 10%; font-size: 11px;">Date:</td>
            <td style="white-space: nowrap; font-size: 11px;">
                <?php echo dateShort($dataCycleProduct['date'], "d/M/Y"); ?>
            </td>
        </tr>
        <tr>
            <td style="font-size: 11px;">
                <?php echo TABLE_BRANCH; ?> : <?php echo $dataCycleProduct['branch_name']; ?>
            </td>
            <td style="text-align: left; width: 10%; font-size: 11px;">Created By:</td>
            <td style="white-space: nowrap;">
                <?php
                $sqlUser = mysql_query("SELECT * FROM users WHERE id = ".$dataCycleProduct['created_by']);
                $rowUser = mysql_fetch_array($sqlUser);
                echo $rowUser['first_name']." ".$rowUser['last_name'];
                ?>
            </td>
        </tr>
        <tr>
            <td style="font-size: 11px;">
                <?php echo TABLE_LOCATION_GROUP ?> : <?php echo $dataCycleProduct['location_group_name']; ?>
            </td>
            <td style="text-align: left; width: 10%;"></td>
            <td style="white-space: nowrap;"></td>
        </tr>
    </table>
    <div style="height: 15px"></div>
    <div>
        <div>
            <table class="table_print">
                <tr>
                    <th class="first" style="font-size: 10px;"><?php echo 'ល.រ'; ?></th>
                    <th style="width: 80px !important; font-size: 10px;"><?php echo 'លេខកូដផលិតផល'; ?></th>
                    <th style="font-size: 10px;"><?php echo 'NAME OF PRODUCT'; ?></th>
                    <th style="width: 120px !important; font-size: 10px;"><?php echo 'បរិមាណ '; ?></th>
                    <th style="width: 80px !important; font-size: 10px;"><?php echo 'ឯកត្តាគិត'; ?></th>
                    <th style="width: 100px !important; font-size: 10px; <?php if($allowLots == false){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
                    <th style="width: 100px !important; font-size: 10px; <?php if($allowExpired == false){ ?>display: none;<?php } ?>"><?php echo TABLE_EXPIRED_DATE; ?></th>
                    <th style="width: 100px !important; font-size: 10px;"><?php echo 'ទីតាំង'; ?></th>
                </tr>
                <tr>
                    <th class="first" style="font-size: 10px;"><?php echo 'No.'; ?></th>
                    <th style="width: 80px !important; font-size: 10px;"><?php echo 'PRODUCT CODE'; ?></th>
                    <th style="font-size: 10px;"><?php echo 'NAME OF PRODUCT'; ?></th>
                    <th style="width: 120px !important; font-size: 10px;"><?php echo 'QUANTITY'; ?></th>
                    <th style="width: 80px !important; font-size: 10px;"><?php echo 'UoM'; ?></th>
                    <th style="width: 100px !important; font-size: 10px; <?php if($allowLots == false){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
                    <th style="width: 100px !important; font-size: 10px; <?php if($allowExpired == false){ ?>display: none;<?php } ?>"><?php echo TABLE_EXPIRED_DATE; ?></th>
                    <th style="width: 100px !important; font-size: 10px;"><?php echo 'LOCATION'; ?></th>
                </tr>
                <?php
                $index=1;
                $queryDetail=mysql_query("  SELECT
                                                product_id,
                                                (SELECT code FROM products WHERE id=product_id) AS code,
                                                (SELECT barcode FROM products WHERE id=product_id) AS barcode,
                                                (SELECT name FROM products WHERE id=product_id) AS name,
                                                (SELECT abbr FROM uoms WHERE id=(SELECT price_uom_id FROM products WHERE id=product_id)) AS uom,
                                                location_id,
                                                lots_number,
                                                expired_date,
                                                qty_diff
                                            FROM inventory_physical_details WHERE inventory_physical_id=".$dataCycleProduct['id']);
                while($dataDetail=mysql_fetch_array($queryDetail)){
                ?>
                <tr>
                    <td class="first" style="text-align: right; font-size: 10px;"><?php echo $index++; ?></td>
                    <td style="font-size: 10px;"><?php echo $dataDetail['barcode']; ?></td>
                    <td style="font-size: 10px;"><?php echo $dataDetail['name']; ?></td>
                    <?php
                        $value = $dataDetail['qty_diff'];
                        $product = mysql_query("SELECT price_uom_id, (SELECT abbr FROM uoms WHERE id = products.price_uom_id) AS uom_name FROM products WHERE id=" . $dataDetail['product_id']);
                        $row = mysql_fetch_array($product);
                        $mainUom  = $row['uom_name'];
                        $smallUom = 1;
                        $smallUomLabel = "";
                        $sqlSmUom = mysql_query("SELECT value, (SELECT abbr FROM uoms WHERE id = uom_conversions.to_uom_id) as abbr FROM uom_conversions WHERE from_uom_id = " . $row['price_uom_id'] . " AND is_small_uom = 1 AND is_active = 1");
                        while (@$d = mysql_fetch_array($sqlSmUom)) {
                            $smallUom = $d['value'];
                            $smallUomLabel = $d['abbr'];
                        }
                     ?>
                    <td style="text-align: center; font-size: 10px;">
                        <?php
                            echo $value; 
                        ?>
                    </td>
                    <td style="text-align: center; font-size: 10px;"><?php echo $smallUomLabel!=''?$smallUomLabel:$mainUom; ?></td>
                    <td style="text-align: center; font-size: 10px; <?php if($allowLots == false){ ?>display: none;<?php } ?>"><?php echo $dataDetail['lots_number']; ?></td>
                    <td style="text-align: center; font-size: 10px; <?php if($allowExpired == false){ ?>display: none;<?php } ?>">
                        <?php 
                        if($dataDetail['expired_date'] != '' && $dataDetail['expired_date'] != '0000-00-00'){
                            echo dateShort($dataDetail['expired_date']);
                        }
                        ?>
                    </td>
                    <td style="text-align: center; font-size: 10px;">
                        <?php
                            $sqlLocation = mysql_query("SELECT name FROM locations WHERE id = ".$dataDetail['location_id']);
                            $rowLocation = mysql_fetch_array($sqlLocation);
                            echo $rowLocation[0];
                        ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
        <br />
        <table style="width: 100%">
            <tr>
                <td style="font-size: 10px; font-weight: bold; height: 50px; vertical-align: top; width: 120px; text-decoration: underline">*** បរិយាយ (Memo) :</td>
                <td style="width: 55%; vertical-align: top; font-size: 10px;">
                    <?php echo $dataCycleProduct['note']; ?>
                </td>
                <td></td>
            </tr>
        </table>
        <br />
        <table style="width: 100%;">
            <tr>
                <td style="text-align: left; font-size: 10px; width: 33%;">Prepared By:</td>
                <td style="text-align: left; font-size: 10px; width: 34%;">Verify By:</td>
                <td style="text-align: left; font-size: 10px; width: 33%;">Approved By:</td>
            </tr>
            <tr>
                <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
            </tr>
            <tr>
                <td style="text-align: center; font-size: 10px;">Stock Controller</td>
                <td style="text-align: center; font-size: 10px;">Warehouse Manager</td>
                <td style="text-align: center; font-size: 10px;">Accountant</td>
            </tr>
        </table>
        <div style="clear:both"></div>
        <div style="float:left;width: 450px">
            <div>
                <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' onClick='window.print();window.close();' class='noprint'>
            </div>
        </div>
        <div style="clear:both"></div>
    </div>
</div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).dblclick(function(){
            window.close();
        });
    });
</script>

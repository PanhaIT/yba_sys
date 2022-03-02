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
    $sqlCom = mysql_query("SELECT photo FROM companies WHERE id = ".$this->data['TransferOrder']['company_id']);
    $rowCom = mysql_fetch_array($sqlCom);
    $sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option FROM setting_options");
    $rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
    include("includes/function.php");
    $msg = 'Transfer Receive';
    echo $this->element('/print/header', array('msg' => $msg, 'barcode' => $this->data['TransferReceiveResult']['code'], 'logo' => $rowCom[0]));
    ?>
    <div style="height: 10px"></div>
    <table width="100%">
        <tr>
            <td style="width: 10%; text-transform: uppercase; font-size: 10px;"><?php echo TABLE_TRANSFER_RECEIVE_NO; ?></td>
            <td style="width: 15%; font-size: 10px;"><?php echo $this->data['TransferReceiveResult']['code']; ?></td>
            <td style="width: 10%; font-size: 10px; text-transform: uppercase;"><?php echo TABLE_TRANSFER_RECEIVE_DATE; ?></td>
            <td style="width: 15%; font-size: 10px;"><?php echo dateShort($this->data['TransferReceiveResult']['date'], 'd/M/Y'); ?></td>
            <td style="width: 10%; font-size: 10px; text-transform: uppercase;"><?php echo TABLE_TO_NUMBER; ?></td>
            <td style="width: 15%; font-size: 10px;" colspan="3">
                <?php echo $this->data['TransferOrder']['to_code']; ?>
            </td>
        </tr>
    </table>
    <br />
    <div>
        <?php
        if(!empty($transferReceiveDetails)){
        ?>
        <div>
            <table class="table_print" style="border: none;">
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th style="text-transform: uppercase; width: 15%; font-size: 10px;"><?php echo TABLE_SKU; ?></th>
                    <th style="text-transform: uppercase; width: 30%; font-size: 10px;"><?php echo TABLE_PRODUCT_NAME; ?></th>
                    <th style="text-transform: uppercase; width: 10%; font-size: 10px; <?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
                    <th style="text-transform: uppercase; width: 12%; font-size: 10px;"><?php echo TABLE_EXP_DATE_SHORT ?></th>
                    <th style="text-transform: uppercase; width: 10%; font-size: 10px;"><?php echo TABLE_QTY_TRANSFER; ?></th>
                    <th style="text-transform: uppercase; width: 10%; font-size: 10px;"><?php echo TABLE_RECEIVED; ?></th>
                    <th style="text-transform: uppercase; width: 10%; font-size: 10px;"><?php echo TABLE_LOCATION_FROM; ?></th>
                    <th style="text-transform: uppercase; width: 10%; font-size: 10px;"><?php echo TABLE_LOCATION_TO; ?></th>
                </tr>
                <?php
                $index = 0;
                foreach($transferReceiveDetails AS $transferReceiveDetail) {
                ?>
                    <tr>
                        <td class="first" style="text-align: left; font-size: 10px;"><?php echo++$index; ?></td>
                        <td style="font-size: 10px;"><?php echo $transferReceiveDetail['Product']['code']; ?></td>
                        <td><?php echo $transferReceiveDetail['Product']['name']; ?></td>
                        <td style="font-size: 10px; <?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>"><?php echo $transferReceiveDetail['TransferReceive']['lots_number']; ?></td>
                        <td style="font-size: 10px;"><?php echo $transferReceiveDetail['TransferReceive']['expired_date']!='0000-00-00'?dateShort($transferReceiveDetail['TransferReceive']['expired_date'], 'd/M/Y'):''; ?></td>
                        <td style="font-size: 10px;"><?php echo number_format($transferReceiveDetail['TransferOrderDetail']['qty'], 0); ?></td>
                        <td style="font-size: 10px;"><?php echo number_format($transferReceiveDetail['TransferReceive']['qty'], 0); ?></td>
                        <td style="font-size: 10px;">
                            <?php
                            $locationFrom = '';
                            $locationTo   = '';
                            $sqlLoc = mysql_query("SELECT id, name FROM locations WHERE id IN (".$transferReceiveDetail['TransferOrderDetail']['location_from_id'].",".$transferReceiveDetail['TransferOrderDetail']['location_to_id'].")");
                            while($rowLoc = mysql_fetch_array($sqlLoc)){
                                if($rowLoc['id'] == $transferReceiveDetail['TransferOrderDetail']['location_from_id']){
                                    $locationFrom = $rowLoc['name'];
                                }else if($rowLoc['id'] == $transferReceiveDetail['TransferOrderDetail']['location_to_id']){
                                    $locationTo   = $rowLoc['name'];
                                }
                            }
                            echo $locationFrom;
                            ?>
                        </td>
                        <td style="font-size: 10px;"><?php echo $locationTo;  ?></td>
                    </tr>
                <?php
                }
                ?>
            </table>
        </div>
        <?php
        }
        ?>
        <br />
        <div style="float:left;width: 450px">
            <div>
                <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' onClick='window.print();window.close();' class='noprint'>
            </div>
        </div>
        <div style="clear:both"></div>
    </div>
    <div style="width: 100%; position: fixed; bottom: 40px;" class="print-footer">
        <table style="width: 100%;">
                <tr>
                    <td style="font-size: 10px; width: 50%; height: 80px; vertical-align: top; text-transform: uppercase;"><?php echo TABLE_PREPARE_BY;?></td>
                    <td style="font-size: 10px; width: 50%; height: 80px; vertical-align: top; text-transform: uppercase;"><?php echo TABLE_CHECKED_RECEIVE_BY;?></td>
                </tr>
        </table>
    </div>
</div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).dblclick(function(){
            window.close();
        });
        $("#btnDisappearPrint").click(function(){
            $("#footerPrint").show();
            window.print();
            window.close();
        });
    });
</script>
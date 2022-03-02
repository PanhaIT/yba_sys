<style type="text/css" media="screen">
    .titleHeader{
        vertical-align: top; 
        padding-bottom: 0px !important; 
        padding-top: 0px !important;
        padding-right: 2px !important;
        font-size: 11px;
    }
    .titleContent{
        font-weight: bold;
        text-align: right;
    }
    .titleHeaderTable{
        padding-bottom: 0px !important; 
        padding-top: 0px !important;
        text-transform: uppercase; 
        font-size: 11px;
        background-color: #dedede !important;
        color: #000;
    }
    .titleHeaderHeight{
        height: 20px !important;
    }
    .contentHeight{
        height: 14px !important;
    }
</style>
<style type="text/css" media="print">
    #footerTablePrint { width: 100%; position: fixed; bottom: 0px; }
    .titleHeader{
        vertical-align: top; 
        padding-bottom: 0px !important; 
        padding-top: 0px !important;
        padding-right: 2px !important;
        font-size: 11px;
    }
    .titleContent{
        font-weight: bold;
        text-align: right;
    }
    .titleHeaderTable{
        padding-bottom: 0px !important; 
        padding-top: 0px !important;
        text-transform: uppercase; 
        font-size: 11px;
        background-color: #dedede !important;
        color: #000;
    }
    .titleHeaderHeight{
        height: 20px !important;
    }
    .contentHeight{
        height: 14px !important;
    }
    div.print_doc { width:100%;}
    #btnDisappearPrint { display: none;}
    div.print-footer {display: block; width: 100%; position: fixed; bottom: 2px; font-size: 11px; text-align: center;}
</style>
<div class="print_doc">
    <?php
    include("includes/function.php");
    $sqlSym = mysql_query("SELECT symbol FROM currency_centers WHERE id = ".$this->data['Company']['currency_center_id']);
    $rowSym = mysql_fetch_array($sqlSym);
    $msg = 'OFFICIAL RECEIPT';
    ?>
    <table cellpadding="0" cellspacing="0" style="width: 100%;">
        <thead>
            <tr>
                <td style="padding: 0px;">   
                    <table cellpadding="5" cellspacing="0" style="width: 100%;">
                        <tr>
                            <td style="width: 50%;" rowspan="2">
                                <img alt="" src="<?php echo $this->webroot; ?>public/company_photo/<?php echo $this->data['Company']['photo']; ?>" style="max-width: 280px;"/>
                            </td>
                            <td style="width: 50%; font-size: 20px; font-weight: bold;">អេស លីកហ្គ័រ</td>
                        </tr>
                        <tr>
                            <td style="font-size: 20px; font-weight: bold;">S Liquor</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="text-align: center; font-size: 18px; font-weight: bold;">
                    ប័ណ្ណទទួលប្រាក់​ (OFFICIAL RECEIPT )
                </td>
            </tr>
            <tr>
                <td>
                    <table cellpadding="5" cellspacing="0" style="width: 100%; margin-top: 10px;">
                        <tr>
                            <td style="width: 60%;">
                                <table width="100%" cellpadding="5" cellspacing="0">
                                    <tr>
                                        <td colspan="2" style="font-size: 14px; font-weight: bold;">អតិថិជន  Customer</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 100px; font-size: 12px;">ឈ្មោះក្រុមហ៊ុន :</td>
                                        <td style="font-size: 12px;"><?php echo $this->data['Customer']['name_kh']; ?></td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 12px;">Company Name :</td>
                                        <td style="font-size: 12px;"><?php echo $this->data['Customer']['name']; ?></td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <table style="width: 80%; float: right;">
                                    <tr>
                                        <td>លេខប័ណ្ណទទួលប្រាក់​ ​​(OR):</td>
                                        <td><?php echo $this->data['ReceivePayment']['reference']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>កាលបរិច្ចេទ Date:</td>
                                        <td><?php echo dateShort($this->data['ReceivePayment']['date'])." ".dateShort($this->data['ReceivePayment']['created'], "H:i:s"); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Created By :</td>
                                        <td><?php echo $this->data['User']['username']; ?></td>
                                    </tr>
                                </table>
                                <div style="clear:both"></div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <table style="width: 100%;">
                                    <tr>
                                        <td>From :</td>
                                        <td><input type="checkbox" /> សាច់ប្រាក់ Cash</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td><input type="checkbox" /> មូលប្បទានប័ត្រ​ Cheque  ​(Cheque Nº: …………………………………)</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td><input type="checkbox" /> ផ្ទេប្រាក់ Transfer  ​(Bank Account: …………………………………………Bank Name : …………………………………………)</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>Memo : <?php echo $this->data['ReceivePayment']['note'];?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <table class="table_print" style="border: none; margin: 0px; padding: 0px; width: 100%; margin-top: 0px;">
                        <tr>
                            <th style="width: 5%;" class="titleHeaderTable titleHeaderHeight">ល.រ</th>
                            <th style="width: 20%;" class="titleHeaderTable">លេខវិ​ក័​យ​ប័ត្រ​ </th>
                            <th class="titleHeaderTable">បរិយាយ</th>
                            <th style="width: 10%;" class="titleHeaderTable">ថ្លៃទំនិញ</th>
                        </tr>
                        <tr>
                            <th style="width: 5%;" class="titleHeaderTable titleHeaderHeight">No.</th>
                            <th style="width: 20%;" class="titleHeaderTable">Invoice Nº</th>
                            <th class="titleHeaderTable">Memo</th>
                            <th style="width: 10%;" class="titleHeaderTable">AMOUNT</th>
                        </tr>
                        <?php
                        $index = 0;
                        $total = 0;
                        foreach ($paymentDetails as $paymentDetail) {
                        ?>
                        <tr>
                            <td class="first" style="text-align: center; font-size: 11px; height: 20px; padding-bottom: 0px; padding-top: 0px;"><?php echo ++$index; ?></td>
                            <td style="font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><?php echo $paymentDetail['SalesOrder']['so_code']; ?></td>
                            <td style="font-size: 11px; padding-bottom: 0px; padding-top: 0px; font-weight: bold;">
                                <?php echo $paymentDetail['SalesOrder']['memo']; ?>
                            </td>
                            <td style="font-size: 11px; padding-bottom: 0px; padding-top: 0px; text-align: right;"><span style="float: left; width: 12px; font-size: 11px;"><?php echo $rowSym[0]; ?></span><?php echo number_format($paymentDetail['ReceivePaymentDetail']['paid'], 2); ?></td>
                        </tr>
                        <?php
                            $total += $paymentDetail['ReceivePaymentDetail']['paid'];
                        }
                        ?>
                        <tr>
                            <td style="text-align: right; font-size: 11px; font-weight: bold; padding-bottom: 0px; padding-top: 0px; height: 20px;" colspan="3">
                                <?php echo 'សរុបតម្លៃរួម/Grand Total (USD) :'; ?>
                            </td>
                            <td style="text-align: right; font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><span style="float: left; width: 12px; font-size: 11px;"><?php echo $rowSym[0]; ?></span><?php echo number_format($total, 2); ?></td>
                        </tr>
                    </table>
                    <div style="clear:both"></div>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td style="height: 165px;">
                    <table cellpadding="5" cellspacing="0" style="width: 100%;">
                        <tr>
                            <td style="height: 80px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                            <td style="height: 80px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                            <td style="height: 80px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                        </tr>
                        <tr>
                            <td style="width: 33%; text-align: center; font-size: 12px;">អ្នកប្រគល់ PAYER</td>
                            <td style="width: 34%; text-align: center; font-size: 12px;">អ្នកប្រមូល COLLECTOR</td>
                            <td style="width: 33%; text-align: center; font-size: 12px;">អ្នកទទួល RECEIVED</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tfoot>
    </table>
    <br />
    <div style="float:left;width: 450px">
        <div>
            <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' class='noprint' />
        </div>
    </div>
    <div style="clear:both"></div>
</div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).dblclick(function(){
            window.close();
        });
        $("#btnDisappearPrint").click(function(){
            $("#footerTablePrint").show();
            $("#footerTablePrint").css("width", "100%");
            window.print();
            window.close();
        });
    });
</script>
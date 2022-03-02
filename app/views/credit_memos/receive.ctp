<?php
include("includes/function.php");
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
// Prevent Button Submit
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#CreditMemoReceiveForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#CreditMemoReceiveForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {                
                $(".txtSaveRCreditMemo").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $(".expired_date").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result == 'error'){
                    errorPickCM();
                }else{                    
                    $(".btnBackCreditMemo").click();
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            }
        });
        $(".btnBackCreditMemo").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
            oCache.iCacheLower = -1;
            oTableCreditMemo.fnDraw(false);
        });
    });
    function errorPickCM(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_DATA_INVALID; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(".btnBackCreditMemo").click();
                    $(this).dialog("close");
                }
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackCreditMemo">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('CreditMemo', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<?php
    echo $this->Form->hidden('memo_id',array('name'=>'data[memo_id]', 'value' => $credit_memo['CreditMemo']['id']));
?>
<fieldset>
    <legend><?php __(MENU_CREDIT_MEMO_MANAGEMENT_INFO); ?></legend>
    <table width="100%" cellpadding="10">
        <tr>
            <td width="15%"><?php echo TABLE_CREDIT_MEMO_NUMBER; ?> :</td>
            <td width="25%">
                <div class="inputContainer" style="width:100%">
                    <?php echo $credit_memo['CreditMemo']['cm_code']; ?>
                </div>
            </td>
            <td><?php echo TABLE_CREDIT_MEMO_DATE; ?> :</td>
            <td>
                <div class="inputContainer" style="width:100%">
                    <?php echo dateShort($credit_memo['CreditMemo']['order_date']); ?>
                    <?php echo $this->Form->hidden('',array('name'=>'data[receive_date]','value'=>$credit_memo['CreditMemo']['order_date'], 'id'=>'date_receive_cm')); ?>
                </div>
            </td>
            <td><?php echo TABLE_LOCATION; ?> :</td>
            <td colspan="3">
                <div class="inputContainer" style="width:100%">
                    <?php echo $credit_memo['Location']['name']; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td width="15%"><?php echo TABLE_INVOICE_DATE; ?> :</td>
            <td width="25%">
                <div class="inputContainer" style="width:100%">
                    <?php echo ($credit_memo['CreditMemo']['invoice_date'] != '' && $credit_memo['CreditMemo']['invoice_date'] != '0000-00-00')?date('d/m/Y', strtotime($credit_memo['CreditMemo']['invoice_date'])):''; ?>
                </div>
            </td>
            <td><?php echo TABLE_INVOICE_CODE; ?> :</td>
            <td><?php echo $credit_memo['CreditMemo']['invoice_code']; ?></td>
        </tr>
    </table>
</fieldset>
<?php
    if (!empty($creditMemoDetails)) {
?>
<div>
<fieldset>
    <legend><?php echo TABLE_PRODUCT; ?></legend>
    <table class="table" >
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_BARCODE; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_SKU; ?></th>
            <th><?php echo TABLE_NAME; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_QTY; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_F_O_C; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_UOM; ?></th>
            <th style="width: 120px !important; <?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_EXPIRED_DATE; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_NOTE; ?></th>
        </tr>
<?php
    $index = 0;
    $totalPrice = 0;
    foreach ($creditMemoDetails as $creditMemoDetail) {
?>
        <tr><td class="first"><?php echo++$index; ?></td>
            <td><?php echo $creditMemoDetail['Product']['barcode']; ?></td>
            <td><?php echo $creditMemoDetail['Product']['code']; ?></td>
            <td><?php echo $creditMemoDetail['Product']['name']; ?></td>
            <td><?php echo $creditMemoDetail['CreditMemoDetail']['qty']; ?></td>
            <td><?php echo $creditMemoDetail['CreditMemoDetail']['qty_free']; ?></td>
            <td><?php echo $creditMemoDetail['Uom']['abbr']; ?></td>
            <td style="<?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>"><?php echo $creditMemoDetail['CreditMemoDetail']['lots_number']; ?></td>
            <td><?php if($creditMemoDetail['CreditMemoDetail']['expired_date']!=''&&$creditMemoDetail['CreditMemoDetail']['expired_date']!='0000-00-00'){ echo dateShort($creditMemoDetail['CreditMemoDetail']['expired_date']); } ?></td>
            <td><?php echo $creditMemoDetail['CreditMemoDetail']['note']; ?></td>
        </tr>
<?php
    }
?>
    </table>
</fieldset>
</div>
<?php
    }
?>
<br/>
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSaveRCreditMemo"><?php echo ACTION_RECEIVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>


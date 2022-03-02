<?php
// Authentication
$this->element('check_access');
// Product
// exit;
// $viewAdjIssued  = checkAccess($user['User']['id'], 'inventory_adjustments', 'viewAdjustmentIssued');
// $viewTotalSales = checkAccess($user['User']['id'], 'dashboards', 'viewTotalSales');
// $viewExpense    = checkAccess($user['User']['id'], 'dashboards', 'viewExpenseGraph');
// $viewSalesTop10 = checkAccess($user['User']['id'], 'dashboards', 'viewSalesTop10Graph');
// $viewProfitLoss = checkAccess($user['User']['id'], 'dashboards', 'viewProfitLoss');
// $viewReceivable = checkAccess($user['User']['id'], 'dashboards', 'viewReceivable');
// $viewPayable    = checkAccess($user['User']['id'], 'dashboards', 'viewPayable');
// $viewReorderLevel = checkAccess($user['User']['id'], 'dashboards', 'viewReorderLevel');
// $viewCustomerPaymentAlert = checkAccess($user['User']['id'], 'dashboards', 'viewCustomerPaymentAlert');
// $listDashboard    = array();

?>
<script type="text/javascript">
    $(document).ready(function(){
        // Action Refresh
        // $(".refreshDashboard").bind('mouseover', function(){
        //     $(this).attr('src', '<?php //echo $this->webroot; ?>img/button/refresh-active.png');
        // });

        // $(".refreshDashboard").bind('mouseout', function(){
        //     $(this).attr('src', '<?php //echo $this->webroot; ?>img/button/refresh-inactive.png');
        // });
        
        // Action Minimize
        // $(".minimizeDashboard").bind('mouseover', function(){
        //     $(this).attr('src', '<?php //echo $this->webroot; ?>img/button/minimize-active.png');
        // });

        // $(".minimizeDashboard").bind('mouseout', function(){
        //     $(this).attr('src', '<?php //echo $this->webroot; ?>img/button/minimize-inactive.png');
        // });
        
        // Action Customize Dashboard
        // $("#customizeDashboard").click(function(event){
        //     event.preventDefault();
        //     var contents = createCusDashboard();
        //     $("#dialog").html(contents);
        //     $("#dialog").dialog({
        //         title: 'Customize Dashboard',
        //         resizable: false,
        //         modal: true,
        //         width: 850,
        //         height: 500,
        //         position: 'center',
        //         open: function(event, ui){
        //             $(".ui-dialog-buttonpane").show();
        //             // Set Checkbox Style
        //             $('.dashboardOption').bootstrapToggle('destroy');
        //             $('.dashboardOption').bootstrapToggle({on:"Show", off:"Hide"}).change(function(){
        //                 var dashName = $(this).closest("tr").find(".customizeDashName").text();
        //                 var objDash;
        //                 var dis;
        //                 var url  = '';
        //                 var auto = 1;
        //                 var time = 5;
        //                 var cntView = '';
        //                 var comapnyView = '';
        //                 var filterView = '';
        //                 var groupView = '';
        //                 var chartView = '';
        //                 if($(this).prop('checked')){
        //                     dis  = 1;
        //                 } else {
        //                     dis  = 2;
        //                 }
        //                 $(".boxDashboard").each(function() {
        //                     var name    = $(this).find(".dashboardName").text();
        //                     if(name == dashName){
        //                         objDash = $(this);
        //                         if(dis == 1){
        //                             objDash.attr('display', dis);
        //                            $(this).show();
        //                         } else {
        //                             objDash.attr('display', dis);
        //                             $(this).hide();
        //                         }
        //                         url  = $(this).attr("role");
        //                         auto = $(this).find(".defaultCheckSetting").val();
        //                         time = $(this).find(".defaultTimeSetting").val();
        //                         cntView = $(this).children("div:first").first().attr("id");
        //                         if(objDash.find(".branchView").val() != undefined){
        //                             comapnyView = objDash.find(".branchView").val()+"/";
        //                         }
        //                         if(objDash.find(".filterView").val() != undefined){
        //                             filterView = objDash.find(".filterView").val()+"/";
        //                         }
        //                         if(objDash.find(".groupView").val() != undefined){
        //                             groupView = objDash.find(".groupView").val()+"/";
        //                         }
        //                         if(objDash.find(".chartView").val() != undefined){
        //                             chartView = objDash.find(".chartView").val();
        //                         }
        //                     }
        //                 });
        //                 $.ajax({
        //                     dataType: 'json',
        //                     type:   'GET',
        //                     url:    "<?php //echo $this->base; ?>/<?php //echo $this->params['controller']; ?>/userDashboard/"+url+"/"+auto+"/"+time+"/"+dis,
        //                     success: function(msg){
        //                         if(msg.result == 0){
        //                             if(dis == 1){
        //                                 objDash.hide();
        //                                 objDash.attr('display', 2);
        //                             } else {
        //                                 objDash.show();
        //                                 objDash.attr('display', 1);
        //                             }
        //                         } else {
        //                             if(dis == 1){
        //                                 $("#"+cntView).html('Loading....');
        //                                 $("#"+cntView).load("<?php //echo $this->base; ?>/"+url+"/"+comapnyView+filterView+groupView+chartView);
        //                             }
        //                         }
        //                     }
        //                 });
        //             });
        //         },
        //         buttons: {
        //             '<?php //echo ACTION_CLOSE; ?>': function() {
        //                 $(this).dialog("close");
        //             }
        //         }
        //     });
        // });
        <?php
        // if($viewAdjIssued){
        //     // Module Id 
        //     $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Physical Count (Issue)' OR name = 'Inventory Adjustment (Issue)' LIMIT 1");
        //     $rowMod = mysql_fetch_array($sqlMod);
            
        //     $listDashboard[$rowMod['id']] = MENU_INVENTORY_ADJUSTMENT;
        //     $viewAdjIssuedAuto = 1;
        //     $viewAdjIssuedTime = 5;
        //     $viewAdjIssuedDisp = '';
        //     $displayAdjIssued  = 1;
        //     $sqlDash = mysql_query("SELECT * FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        //     if(mysql_num_rows($sqlDash)){
        //         $rowDash = mysql_fetch_array($sqlDash);
        //         $viewAdjIssuedAuto = $rowDash['auto_refresh'];
        //         $viewAdjIssuedTime = $rowDash['time_refresh'];
        //         if($rowDash['display'] == 2){
        //             $viewAdjIssuedDisp = 'display: none';
        //         }
        //         $displayAdjIssued = $rowDash['display'];
        //     }
        //     if($displayAdjIssued == 1){
        ?>
        //$("#adjIssuedView").load("<?php //echo $this->base; ?>/inventory_adjustments/viewAdjustmentIssued/"+$("#filterBranchAdjustmentIssued").val());
        <?php        
            //}
        ?>
        //$('#settingAdjIssued').makeMenu({url : "<?php //echo $this->base; ?>/<?php //echo $this->params['controller']; ?>/userDashboard/inventory_adjustments/viewAdjustmentIssued"});
        <?php
        // }
        // if($viewTotalSales){
        //     // Module Id 
        //     $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Total Sales By Graph' LIMIT 1");
        //     $rowMod = mysql_fetch_array($sqlMod);

        //     $listDashboard[$rowMod['id']] = TABLE_TOTAL_SALES;
        //     $viewTotalSalesAuto = 1;
        //     $viewTotalSalesTime = 30;
        //     $viewTotalSalesDisp = '';
        //     $displayTotalSales  = 1;
        //     $sqlDash = mysql_query("SELECT * FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        //     if(mysql_num_rows($sqlDash)){
        //         $rowDash = mysql_fetch_array($sqlDash);
        //         $viewTotalSalesAuto = $rowDash['auto_refresh'];
        //         $viewTotalSalesTime = $rowDash['time_refresh'];
        //         if($rowDash['display'] == 2){
        //             $viewTotalSalesDisp = 'display: none';
        //         }
        //         $displayTotalSales = $rowDash['display'];
        //     }
        //     if($displayTotalSales == 1){
        ?>
        //$("#TotalSalesView").load("<?php //echo $this->base; ?>/dashboards/viewTotalSales/"+$("#filterBranchTotalSales").val()+"/"+$("#filterTotalSales").val()+"/"+$("#groupTotalSales").val()+"/"+$("#chartTotalSales").val());
        <?php    
           // }
        ?>
        //$('#settingTotalSales').makeMenu({url : "<?php //echo $this->base; ?>/<?php //echo $this->params['controller']; ?>/userDashboard/dashboards/viewTotalSales"});
        <?php    
        // }
        // if($viewExpense){
        //     // Module Id 
        //     $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Expense (Graph)' LIMIT 1");
        //     $rowMod = mysql_fetch_array($sqlMod);
            
        //     $listDashboard[$rowMod[0]] = MENU_EXPENSE;
        //     $viewExpenseGraphAuto  = 1;
        //     $viewExpenseGraphTime  = 5;
        //     $viewExpenseGraphDisp  = '';
        //     $displayExpenseGraph   = 1;
        //     $sqlDash = mysql_query("SELECT * FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        //     if(mysql_num_rows($sqlDash)){
        //         $rowDash = mysql_fetch_array($sqlDash);
        //         $viewExpenseGraphAuto = $rowDash['auto_refresh'];
        //         $viewExpenseGraphTime = $rowDash['time_refresh'];
        //         if($rowDash['display'] == 2){
        //             $viewExpenseGraphDisp = 'display: none';
        //         }
        //         $displayExpenseGraph = $rowDash['display'];
        //     }
        //     if($displayExpenseGraph == 1){
        ?>
        //$("#expenseGraphView").load("<?php //echo $this->base; ?>/dashboards/viewExpenseGraph/"+$("#filterBranchExpenseGraph").val()+"/"+$("#filterExpenseGraph").val());
        <?php        
           // }
        ?>
        //$('#settingExpenseGraph').makeMenu({url : "<?php //echo $this->base; ?>/<?php //echo $this->params['controller']; ?>/userDashboard/dashboards/viewExpenseGraph"});
        <?php
        // }
        // if($viewSalesTop10){
        //     // Module Id 
        //     $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Sales Top 10 Items (Graph)' LIMIT 1");
        //     $rowMod = mysql_fetch_array($sqlMod);
            
        //     $listDashboard[$rowMod[0]] = MENU_EXPENSE;
        //     $viewSalesTop10Auto  = 1;
        //     $viewSalesTop10Time  = 5;
        //     $viewSalesTop10Disp  = '';
        //     $displaySalesTop10   = 1;
        //     $sqlDash = mysql_query("SELECT * FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        //     if(mysql_num_rows($sqlDash)){
        //         $rowDash = mysql_fetch_array($sqlDash);
        //         $viewSalesTop10Auto = $rowDash['auto_refresh'];
        //         $viewSalesTop10Time = $rowDash['time_refresh'];
        //         if($rowDash['display'] == 2){
        //             $viewSalesTop10Disp = 'display: none';
        //         }
        //         $displaySalesTop10 = $rowDash['display'];
        //     }
        //     if($displaySalesTop10 == 1){
        ?>
        //$("#salesTop10View").load("<?php //echo $this->base; ?>/dashboards/viewSalesTop10Graph/"+$("#filterBranchSalesTop10").val()+"/"+$("#filterSalesTop10").val());
        <?php        
            //}
        ?>
        //$('#settingSalesTop10').makeMenu({url : "<?php //echo $this->base; ?>/<?php //echo $this->params['controller']; ?>/userDashboard/dashboards/viewSalesTop10Graph"});
        <?php
        // }
        // if($viewProfitLoss){
        //     // Module Id 
        //     $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Profit & Loss (Graph)' LIMIT 1");
        //     $rowMod = mysql_fetch_array($sqlMod);
            
        //     $listDashboard[$rowMod['id']] = MENU_PROFIT_AND_LOSS;
        //     $viewProfitLossAuto = 1;
        //     $viewProfitLossTime = 5;
        //     $viewProfitLossDisp = '';
        //     $displayProfitLoss  = 1;
        //     $sqlDash = mysql_query("SELECT * FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        //     if(mysql_num_rows($sqlDash)){
        //         $rowDash = mysql_fetch_array($sqlDash);
        //         $viewProfitLossAuto = $rowDash['auto_refresh'];
        //         $viewProfitLossTime = $rowDash['time_refresh'];
        //         if($rowDash['display'] == 2){
        //             $viewProfitLossDisp = 'display: none';
        //         }
        //         $displayProfitLoss = $rowDash['display'];
        //     }
        //     if($displayProfitLoss == 1){
        ?>
        //$("#profitLossView").load("<?php //echo $this->base; ?>/dashboards/viewProfitLoss/"+$("#filterProfitLoss").val());
        <?php        
            //}
        ?>
        //$('#settingProfitLoss').makeMenu({url : "<?php //echo $this->base; ?>/<?php //echo $this->params['controller']; ?>/userDashboard/dashboards/viewProfitLoss"});
        <?php
        // }
        // if($viewReceivable){
        //     // Module Id 
        //     $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Total Receivables' LIMIT 1");
        //     $rowMod = mysql_fetch_array($sqlMod);
            
        //     $listDashboard[$rowMod['id']] = TABLE_TOTAL_RECEIVABLES;
        //     $viewReceivableAuto = 1;
        //     $viewReceivableTime = 5;
        //     $viewReceivableDisp = '';
        //     $displayReceivable  = 1;
        //     $sqlDash = mysql_query("SELECT * FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        //     if(mysql_num_rows($sqlDash)){
        //         $rowDash = mysql_fetch_array($sqlDash);
        //         $viewReceivableAuto = $rowDash['auto_refresh'];
        //         $viewReceivableTime = $rowDash['time_refresh'];
        //         if($rowDash['display'] == 2){
        //             $viewReceivableDisp = 'display: none';
        //         }
        //         $displayReceivable = $rowDash['display'];
        //     }
        //     if($displayReceivable == 1){
        ?>
        //$("#receivableView").load("<?php //echo $this->base; ?>/dashboards/viewReceivable/"+$("#filterBranchReceivable").val());
        <?php        
            //}
        ?>
        //$('#settingReceivable').makeMenu({url : "<?php //echo $this->base; ?>/<?php //echo $this->params['controller']; ?>/userDashboard/dashboards/viewReceivable"});
        <?php
        // }
        // if($viewPayable){
        //     // Module Id 
        //     $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Total Payables' LIMIT 1");
        //     $rowMod = mysql_fetch_array($sqlMod);
            
        //     $listDashboard[$rowMod['id']] = TABLE_TOTAL_PAYABLES;
        //     $viewPayableAuto = 1;
        //     $viewPayableTime = 5;
        //     $viewPayableDisp = '';
        //     $displayPayable  = 1;
        //     $sqlDash = mysql_query("SELECT * FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        //     if(mysql_num_rows($sqlDash)){
        //         $rowDash = mysql_fetch_array($sqlDash);
        //         $viewPayableAuto = $rowDash['auto_refresh'];
        //         $viewPayableTime = $rowDash['time_refresh'];
        //         if($rowDash['display'] == 2){
        //             $viewPayableDisp = 'display: none';
        //         }
        //         $displayPayable = $rowDash['display'];
        //     }
        //     if($displayPayable == 1){
        ?>
        //$("#payableView").load("<?php //echo $this->base; ?>/dashboards/viewPayable/"+$("#filterBranchPayable").val());
        <?php        
            //}
        ?>
        //$('#settingPayable').makeMenu({url : "<?php //echo $this->base; ?>/<?php //echo $this->params['controller']; ?>/userDashboard/dashboards/viewPayable"});
        <?php
        // }
        // if($viewReorderLevel){
        //     // Module Id 
        //     $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Product Reorder Level' LIMIT 1");
        //     $rowMod = mysql_fetch_array($sqlMod);
            
        //     $listDashboard[$rowMod['id']] = TABLE_PRODUCT_REORDER_LEVEL;
        //     $viewReorderLevelAuto = 1;
        //     $viewReorderLevelTime = 5;
        //     $viewReorderLevelDisp = '';
        //     $displayReorderLevel  = 1;
        //     $sqlDash = mysql_query("SELECT * FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        //     if(mysql_num_rows($sqlDash)){
        //         $rowDash = mysql_fetch_array($sqlDash);
        //         $viewReorderLevelAuto = $rowDash['auto_refresh'];
        //         $viewReorderLevelTime = $rowDash['time_refresh'];
        //         if($rowDash['display'] == 2){
        //             $viewReorderLevelDisp = 'display: none';
        //         }
        //         $displayReorderLevel = $rowDash['display'];
        //     }
        //     if($displayReorderLevel == 1){
        ?>
        //$("#reorderLevelView").load("<?php //echo $this->base; ?>/dashboards/viewReorderLevel/");
        <?php        
            //}
        ?>
        //$('#settingReorderLevel').makeMenu({url : "<?php //echo $this->base; ?>/<?php //echo $this->params['controller']; ?>/userDashboard/dashboards/viewReorderLevel"});
        <?php
        //}
        // if($viewCustomerPaymentAlert){
        //     // Module Id 
        //     $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Customer Payment Alert' LIMIT 1");
        //     $rowMod = mysql_fetch_array($sqlMod);
            
        //     $listDashboard[$rowMod['id']] = TABLE_CUSTOMER_PAYMENT_TOMORROW;
        //     $viewCustomerPaymentAlertAuto = 1;
        //     $viewCustomerPaymentAlertTime = 5;
        //     $viewCustomerPaymentAlertDisp = '';
        //     $displayCustomerPaymentAlert  = 1;
        //     $sqlDash = mysql_query("SELECT * FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        //     if(mysql_num_rows($sqlDash)){
        //         $rowDash = mysql_fetch_array($sqlDash);
        //         $viewCustomerPaymentAlertAuto = $rowDash['auto_refresh'];
        //         $viewCustomerPaymentAlertTime = $rowDash['time_refresh'];
        //         if($rowDash['display'] == 2){
        //             $viewCustomerPaymentAlertDisp = 'display: none';
        //         }
        //         $displayCustomerPaymentAlert = $rowDash['display'];
        //     }
        //     if($displayCustomerPaymentAlert == 1){
        ?>
        //$("#customerPaymentAlertView").load("<?php //echo $this->base; ?>/dashboards/viewCustomerPaymentAlert/"+$("#filterCustomerPaymentAlert").val());
        <?php        
            //}
        ?>
        //$('#settingCustomerPaymentAlert').makeMenu({url : "<?php //echo $this->base; ?>/<?php //echo $this->params['controller']; ?>/userDashboard/dashboards/viewCustomerPaymentAlert"});
        <?php
        //}
        ?>
    });
    
    function createCusDashboard(){
        // var i   = 0;
        // var div = '';
        // $(".boxDashboard").each(function() {
        //     if(i == 3){
        //         i  = 0;
        //         div += '<div style="clear: both;"></div>';
        //     }
        //     var display = $(this).attr("display");
        //     var name    = $(this).find(".dashboardName").text();
        //     var checked = '';
        //     if(display == 1){
        //         checked = 'checked="checked"';
        //     }
        //     div += '<div style="float: left; margin-right: 5px; heidht: 40px; width: 250px; border: 1px solid #1761c7;">';
        //     div += '<table cellpadding="5" cellspacing="0" style="width: 100%;">';
        //     div += '<tr>';
        //     div += '<td style="width: 70%; vertical-align: top;" class="customizeDashName">'+name+'</td>';
        //     div += '<td style="vertical-align: top;"><input type="checkbox" '+checked+' class="dashboardOption" data-size="small" data-toggle="toggle" /></td>';
        //     div += '</tr>';
        //     div += '</table>';
        //     div += '</div>';
        //     i++;
        // });
        // if(i > 5){
        //      div += '<div style="clear: both;"></div>';
        // }
        // return div;
    }
</script>
<?php
// if(!empty($listDashboard)){
?>
<!-- <div class="buttons">
    <a href="#" class="positive" id="customizeDashboard">
        <img src="<?php //echo $this->webroot; ?>img/button/setting-active.png" />
        Customize Dashboard
    </a>
</div> -->
<div style="clear: both;"></div>
<br/>
<?php
// }
// if($viewProfitLoss){
?>
<!-- <div class="boxDashboard" role="dashboards/viewProfitLoss" display="<?php //echo $displayProfitLoss; ?>" confirm="<?php //echo MESSAGE_CONFIRM_HIDE; ?>" dialog="<?php //echo DIALOG_CONFIRMATION; ?>" cancel="<?php //echo ACTION_CANCEL; ?>" hide="<?php //echo TABLE_HIDE; ?>" style="<?php //echo $viewProfitLossDisp; ?>">
    <h1 class="title"><span class="dashboardName"><?php //echo MENU_PROFIT_AND_LOSS; ?></span>
        <img onmouseover="Tip('Setting')" src="<?php //echo $this->webroot; ?>img/button/setting-inactive.png" id="settingProfitLoss" style="width: 20px; float: right; cursor: pointer;" />
        <img onmouseover="Tip('Loading...')" src="<?php //echo $this->webroot; ?>img/button/refresh-animation.gif" id="loadingProfitLoss" style="width: 20px; float: right; display: none; margin-right: 10px;" /> 
        <img onmouseover="Tip('Refresh')" src="<?php //echo $this->webroot; ?>img/button/refresh-inactive.png" id="refreshProfitLoss" class="refreshDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" /> 
        <img onmouseover="Tip('Hide')" src="<?php //echo $this->webroot; ?>img/button/minimize-inactive.png" id="minimizeProfitLoss" class="minimizeDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" />
        <div style="width: 155px; float: right; margin-right: 5px;">
            <?php
            //if(COUNT($branches) > 1){
            ?>
            <select id="filterProfitLoss" class="branchView select-style" style="width: 150px; border: none;">
                <option value="all"><?php //echo TABLE_ALL; ?></option>
                <?php 
                //foreach($branches AS $branch){
                ?>
                <option value="<?php //echo $branch['Branch']['id']; ?>"><?php //echo $branch['Branch']['name']; ?></option>
                <?php
                //}
                ?>
            </select>
            <?php
            //} else {
            ?>
            <input type="hidden" value="<?php //echo @$branches[0]['Branch']['id']; ?>" id="filterProfitLoss" class="branchView" />
            <?php
            //}
            ?>
        </div>
        <div style="clear: both;"></div>
        <div id="settingProfitLossMenu" class="settingMenu">
            <input type="hidden" class="defaultCheckSetting" value="<?php //echo $viewProfitLossAuto; ?>" />
            <input type="hidden" class="defaultTimeSetting" value="<?php //echo $viewProfitLossTime; ?>" />
            <input type="hidden" class="selectSetting" value="0" />
            <div class="divMenu">
                <h1>Setting <span style="float: right; margin-right: 3px; color: #337ab7; display: none;" class="saveCompleted">Save Completed</span><span style="float: right; margin-right: 3px; color: red; display: none;" class="saveFailed">Save Failed</span></h1>
                <table cellpadding="5" cellspacing="0">
                    <tr>
                        <td style="width: 40%;">Auto Refresh</td>
                        <td style="text-align: left;">
                            <input type="checkbox" class="settingCheck" data-size="small" data-toggle="toggle" />
                        </td>
                    </tr>
                    <tr>
                        <td>Every</td>
                        <td>
                            <input type="text" style="width: 45px;" value="0" class="settingProfitLossTimeRefresh" /> Second(s)
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button type="button" style="float: right; margin-right: 10px;" class="settingProfitLossSave">
                                <span class="settingProfitLossTxtSave"><?php //echo ACTION_SAVE; ?></span>
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </h1>
    <div style="width: 100%; font-size: 12px; height: 350px; text-align: center;" id="profitLossView">
        <img src="<?php //echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />
    </div>
</div> -->
<?php 
// }
// if($viewTotalSales){
?>
<!-- <div class="boxDashboard" role="dashboards/viewTotalSales" display="<?php //echo $displayTotalSales; ?>" confirm="<?php //echo MESSAGE_CONFIRM_HIDE; ?>" dialog="<?php //echo DIALOG_CONFIRMATION; ?>" cancel="<?php //echo ACTION_CANCEL; ?>" hide="<?php //echo TABLE_HIDE; ?>" style="<?php //echo $viewTotalSalesDisp; ?>">
    <h1 class="title"><span class="dashboardName"><?php //echo TABLE_TOTAL_SALES; ?></span>
        <img onmouseover="Tip('Setting')" src="<?php //echo $this->webroot; ?>img/button/setting-inactive.png" id="settingTotalSales" style="width: 20px; float: right; cursor: pointer;" />
        <img onmouseover="Tip('Loading...')" src="<?php //echo $this->webroot; ?>img/button/refresh-animation.gif" id="loadingTotalSales" style="width: 20px; float: right; display: none; margin-right: 10px;" /> 
        <img onmouseover="Tip('Refresh')" src="<?php //echo $this->webroot; ?>img/button/refresh-inactive.png" id="refreshTotalSales" class="refreshDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" /> 
        <img onmouseover="Tip('Hide')" src="<?php //echo $this->webroot; ?>img/button/minimize-inactive.png" id="minimizeTotalSales" class="minimizeDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" />
        <div style="width: 405px; float: right; text-align: right; margin-right: 10px;">
            <?php
            //if(COUNT($branches) > 1){
            ?>
            <select id="filterBranchTotalSales" class="branchView select-style" style="width: 130px; font-size: 10px; border: none;">
                <option value="all"><?php //echo TABLE_ALL; ?></option>
                <?php 
                //foreach($branches AS $branch){
                ?>
                <option value="<?php //echo $branch['Branch']['id']; ?>"><?php //echo $branch['Branch']['name']; ?></option>
                <?php
                //}
                ?>
            </select>
            <?php
            //} else {
            ?>
            <input type="hidden" value="<?php //echo @$branches[0]['Branch']['id']; ?>" id="filterBranchTotalSales" class="branchView" />
            <?php
            //}
            ?>
            <select id="filterTotalSales" class="filterView select-style" style="width: 100px; font-size: 10px; border: none;">
                <option value="ThisWeek">This Week</option>
                <option value="ThisWeekToDate">This Week-to-date</option>
                <option value="ThisMonth">This Month</option>
                <option value="LastWeek">Last Week</option>
                <option value="LastWeekToDate">Last Week-to-date</option>
                <option value="LastMonth">Last Month</option>
            </select>
            <select id="groupTotalSales" class="groupView select-style" style="width: 100px; font-size: 10px; border: none;">
                <option value="1">Group By Day</option>
                <option value="2" selected="selected">Group By Month</option>
                <option value="3">Group By Quarter</option>
                <option value="4">Group By Year</option>
            </select>
            <input type="hidden" value="column" id="chartTotalSales" />
           <select id="chartTotalSales" class="chartView" style="width: 100px; border: none;">
                <option value="line">Line Chart</option>
                <option value="column" selected="selected">Bar Chart</option>
                <option value="area">Area Chart</option>
            </select>-->
        <!-- </div>
        <div style="clear: both;"></div>
        <div id="settingTotalSalesMenu" class="settingMenu">
            <input type="hidden" class="defaultCheckSetting" value="<?php //echo $viewTotalSalesAuto; ?>" />
            <input type="hidden" class="defaultTimeSetting" value="<?php //echo $viewTotalSalesTime; ?>" />
            <input type="hidden" class="selectSetting" value="0" />
            <div class="divMenu">
                <h1>Setting <span style="float: right; margin-right: 3px; color: #337ab7; display: none;" class="saveCompleted">Save Completed</span><span style="float: right; margin-right: 3px; color: red; display: none;" class="saveFailed">Save Failed</span></h1>
                <table cellpadding="5" cellspacing="0">
                    <tr>
                        <td style="width: 40%;">Auto Refresh</td>
                        <td style="text-align: left;">
                            <input type="checkbox" class="settingCheck" data-size="small" data-toggle="toggle" />
                        </td>
                    </tr>
                    <tr>
                        <td>Every</td>
                        <td>
                            <input type="text" style="width: 45px;" value="0" class="settingTotalSalesTimeRefresh" /> Second(s)
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button type="button" style="float: right; margin-right: 10px;" class="settingTotalSalesSave">
                                <span class="settingTotalSalesTxtSave"><?php //echo ACTION_SAVE; ?></span>
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </h1>
    <div style="width: 100%; font-size: 12px; height: 350px; text-align: center;" id="TotalSalesView">
        <img src="<?php //echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />
    </div> -->
</div>
<?php   
// }
// if($viewExpense){
?>
<!-- <div class="boxDashboard" role="dashboards/viewExpenseGraph" display="<?php //echo $displayExpenseGraph; ?>" confirm="<?php //echo MESSAGE_CONFIRM_HIDE; ?>" dialog="<?php //echo DIALOG_CONFIRMATION; ?>" cancel="<?php //echo ACTION_CANCEL; ?>" hide="<?php //echo TABLE_HIDE; ?>" style="<?php //echo $viewExpenseGraphDisp; ?>">
    <h1 class="title"><span class="dashboardName"><?php //echo MENU_EXPENSE; ?></span>
        <img onmouseover="Tip('Setting')" src="<?php //echo $this->webroot; ?>img/button/setting-inactive.png" id="settingExpenseGraph" style="width: 20px; float: right; cursor: pointer;" />
        <img onmouseover="Tip('Loading...')" src="<?php //echo $this->webroot; ?>img/button/refresh-animation.gif" id="loadingExpenseGraph" style="width: 20px; float: right; display: none; margin-right: 10px;" /> 
        <img onmouseover="Tip('Refresh')" src="<?php //echo $this->webroot; ?>img/button/refresh-inactive.png" id="refreshExpenseGraph" class="refreshDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" /> 
        <img onmouseover="Tip('Hide')" src="<?php //echo $this->webroot; ?>img/button/minimize-inactive.png" id="minimizeExpenseGraph" class="minimizeDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" />
        <div style="width: 260px; float: right; text-align: right; margin-right: 10px;">
            <?php
            //if(COUNT($branches) > 1){
            ?>
            <select id="filterBranchExpenseGraph" class="branchView select-style" style="width: 150px; border: none;">
                <option value="all"><?php //echo TABLE_ALL; ?></option>
                <?php 
                //foreach($branches AS $branch){
                ?>
                <option value="<?php //echo $branch['Branch']['id']; ?>"><?php //echo $branch['Branch']['name']; ?></option>
                <?php
                //}
                ?>
            </select>
            <?php
            //} else {
            ?>
            <input type="hidden" value="<?php //echo @$branches[0]['Branch']['id']; ?>" id="filterBranchExpenseGraph" class="branchView" />
            <?php
            //}
            ?>
            <select id="filterExpenseGraph" class="filterView select-style" style="width: 100px; border: none;">
                <option value="ThisMonth">This Month</option>
                <option value="ThisQuarter">This Quarter</option>
                <option value="ThisYear">This Year</option>
                <option value="LastMonth">Last Month</option>
                <option value="LastQuarter">Last Quarter</option>
                <option value="LastYear">Last Year</option>
            </select>
        </div>
        <div style="clear: both;"></div>
        <div id="settingExpenseGraphMenu" class="settingMenu">
            <input type="hidden" class="defaultCheckSetting" value="<?php //echo $viewExpenseGraphAuto; ?>" />
            <input type="hidden" class="defaultTimeSetting" value="<?php //echo $viewExpenseGraphTime; ?>" />
            <input type="hidden" class="selectSetting" value="0" />
            <div class="divMenu">
                <h1>Setting <span style="float: right; margin-right: 3px; color: #337ab7; display: none;" class="saveCompleted">Save Completed</span><span style="float: right; margin-right: 3px; color: red; display: none;" class="saveFailed">Save Failed</span></h1>
                <table cellpadding="5" cellspacing="0">
                    <tr>
                        <td style="width: 40%;">Auto Refresh</td>
                        <td style="text-align: left;">
                            <input type="checkbox" class="settingCheck" data-size="small" data-toggle="toggle" />
                        </td>
                    </tr>
                    <tr>
                        <td>Every</td>
                        <td>
                            <input type="text" style="width: 45px;" value="0" class="settingExpenseGraphTimeRefresh" /> Second(s)
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button type="button" style="float: right; margin-right: 10px;" class="settingExpenseGraphSave">
                                <span class="settingExpenseGraphTxtSave"><?php //echo ACTION_SAVE; ?></span>
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </h1>
    <div style="width: 100%; font-size: 12px; height: 360px; text-align: center;" id="expenseGraphView">
        <img src="<?php //echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />
    </div>
</div> -->
<?php 
// }
// if($viewSalesTop10){
?>
<!-- <div class="boxDashboard" role="dashboards/viewSalesTop10" display="<?php //echo $displaySalesTop10; ?>" confirm="<?php //echo MESSAGE_CONFIRM_HIDE; ?>" dialog="<?php //echo DIALOG_CONFIRMATION; ?>" cancel="<?php //echo ACTION_CANCEL; ?>" hide="<?php //echo TABLE_HIDE; ?>" style="<?php //echo $viewSalesTop10Disp; ?>">
    <h1 class="title"><span class="dashboardName">Sales Top 10 Items</span>
        <img onmouseover="Tip('Setting')" src="<?php //echo $this->webroot; ?>img/button/setting-inactive.png" id="settingSalesTop10" style="width: 20px; float: right; cursor: pointer;" />
        <img onmouseover="Tip('Loading...')" src="<?php //echo $this->webroot; ?>img/button/refresh-animation.gif" id="loadingSalesTop10" style="width: 20px; float: right; display: none; margin-right: 10px;" /> 
        <img onmouseover="Tip('Refresh')" src="<?php //echo $this->webroot; ?>img/button/refresh-inactive.png" id="refreshSalesTop10" class="refreshDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" /> 
        <img onmouseover="Tip('Hide')" src="<?php //echo $this->webroot; ?>img/button/minimize-inactive.png" id="minimizeSalesTop10" class="minimizeDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" />
        <div style="width: 270px; float: right; text-align: right; margin-right: 10px;">
            <?php
            //if(COUNT($branches) > 1){
            ?>
            <select id="filterBranchSalesTop10" class="branchView select-style" style="width: 150px; border: none;">
                <option value="all"><?php //echo TABLE_ALL; ?></option>
                <?php 
                //foreach($branches AS $branch){
                ?>
                <option value="<?php //echo $branch['Branch']['id']; ?>"><?php //echo $branch['Branch']['name']; ?></option>
                <?php
                //}
                ?>
            </select>
            <?php
            //} else {
            ?>
            <input type="hidden" value="<?php //echo @$branches[0]['Branch']['id']; ?>" id="filterBranchSalesTop10" class="branchView" />
            <?php
            //}
            ?>
            <select id="filterSalesTop10" class="filterView select-style" style="width: 100px; border: none;">
                <option value="ThisMonth">This Month</option>
                <option value="ThisQuarter">This Quarter</option>
                <option value="ThisYear">This Year</option>
                <option value="LastMonth">Last Month</option>
                <option value="LastQuarter">Last Quarter</option>
                <option value="LastYear">Last Year</option>
            </select>
        </div>
        <div style="clear: both;"></div>
        <div id="settingSalesTop10Menu" class="settingMenu">
            <input type="hidden" class="defaultCheckSetting" value="<?php //echo $viewSalesTop10Auto; ?>" />
            <input type="hidden" class="defaultTimeSetting" value="<?php //echo $viewSalesTop10Time; ?>" />
            <input type="hidden" class="selectSetting" value="0" />
            <div class="divMenu">
                <h1>Setting <span style="float: right; margin-right: 3px; color: #337ab7; display: none;" class="saveCompleted">Save Completed</span><span style="float: right; margin-right: 3px; color: red; display: none;" class="saveFailed">Save Failed</span></h1>
                <table cellpadding="5" cellspacing="0">
                    <tr>
                        <td style="width: 40%;">Auto Refresh</td>
                        <td style="text-align: left;">
                            <input type="checkbox" class="settingCheck" data-size="small" data-toggle="toggle" />
                        </td>
                    </tr>
                    <tr>
                        <td>Every</td>
                        <td>
                            <input type="text" style="width: 45px;" value="0" class="settingSalesTop10TimeRefresh" /> Second(s)
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button type="button" style="float: right; margin-right: 10px;" class="settingSalesTop10Save">
                                <span class="settingSalesTop10TxtSave"><?php //echo ACTION_SAVE; ?></span>
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </h1>
    <div style="width: 100%; font-size: 12px; height: 360px; text-align: center;" id="salesTop10View">
        <img src="<?php //echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />
    </div>
</div> -->
<?php 
// }
// if($viewReceivable){
?>
<!-- <div class="boxDashboard" role="dashboards/viewReceivable" display="<?php //echo $displayReceivable; ?>" confirm="<?php //echo MESSAGE_CONFIRM_HIDE; ?>" dialog="<?php //echo DIALOG_CONFIRMATION; ?>" cancel="<?php //echo ACTION_CANCEL; ?>" hide="<?php //echo TABLE_HIDE; ?>" style="<?php //echo $viewReceivableDisp; ?>">
    <h1 class="title"><span class="dashboardName"><?php //echo TABLE_TOTAL_RECEIVABLES; ?></span>
        <img onmouseover="Tip('Setting')" src="<?php //echo $this->webroot; ?>img/button/setting-inactive.png" id="settingReceivable" style="width: 20px; float: right; cursor: pointer;" />
        <img onmouseover="Tip('Loading...')" src="<?php //echo $this->webroot; ?>img/button/refresh-animation.gif" id="loadingReceivable" style="width: 20px; float: right; display: none; margin-right: 10px;" /> 
        <img onmouseover="Tip('Refresh')" src="<?php //echo $this->webroot; ?>img/button/refresh-inactive.png" id="refreshReceivable" class="refreshDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" /> 
        <img onmouseover="Tip('Hide')" src="<?php //echo $this->webroot; ?>img/button/minimize-inactive.png" id="minimizeReceivable" class="minimizeDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" />
        <div style="width: 160px; float: right; margin-right: 10px;">
            <?php
            //if(COUNT($branches) > 1){
            ?>
            <select id="filterBranchReceivable" class="branchView select-style" style="width: 150px; border: none;">
                <option value="all"><?php //echo TABLE_ALL; ?></option>
                <?php 
                //foreach($branches AS $branch){
                ?>
                <option value="<?php //echo $branch['Branch']['id']; ?>"><?php //echo $branch['Branch']['name']; ?></option>
                <?php
                //}
                ?>
            </select>
            <?php
            //} else {
            ?>
            <input type="hidden" value="<?php //echo @$branches[0]['Branch']['id']; ?>" id="filterBranchReceivable" class="branchView" />
            <?php
            //}
            ?>
        </div>
        <div style="clear: both;"></div>
        <div id="settingReceivableMenu" class="settingMenu">
            <input type="hidden" class="defaultCheckSetting" value="<?php //echo $viewReceivableAuto; ?>" />
            <input type="hidden" class="defaultTimeSetting" value="<?php //echo $viewReceivableTime; ?>" />
            <input type="hidden" class="selectSetting" value="0" />
            <div class="divMenu">
                <h1>Setting <span style="float: right; margin-right: 3px; color: #337ab7; display: none;" class="saveCompleted">Save Completed</span><span style="float: right; margin-right: 3px; color: red; display: none;" class="saveFailed">Save Failed</span></h1>
                <table cellpadding="5" cellspacing="0">
                    <tr>
                        <td style="width: 40%;">Auto Refresh</td>
                        <td style="text-align: left;">
                            <input type="checkbox" class="settingCheck" data-size="small" data-toggle="toggle" />
                        </td>
                    </tr>
                    <tr>
                        <td>Every</td>
                        <td>
                            <input type="text" style="width: 45px;" value="0" class="settingReceivableTimeRefresh" /> Second(s)
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button type="button" style="float: right; margin-right: 10px;" class="settingReceivableSave">
                                <span class="settingReceivableTxtSave"><?php //echo ACTION_SAVE; ?></span>
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </h1>
    <div style="width: 100%; font-size: 12px; height: 150px; text-align: center; text-align: center;" id="receivableView">
        <img src="<?php //echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-0px" />
    </div>
</div> -->
<?php 
// }
// if($viewPayable){
?>
<!-- <div class="boxDashboard" role="dashboards/viewPayable" display="<?php //echo $displayPayable; ?>" confirm="<?php //echo MESSAGE_CONFIRM_HIDE; ?>" dialog="<?php //echo DIALOG_CONFIRMATION; ?>" cancel="<?php //echo ACTION_CANCEL; ?>" hide="<?php //echo TABLE_HIDE; ?>" style="<?php //echo $viewPayableDisp; ?>">
    <h1 class="title"><span class="dashboardName"><?php //echo TABLE_TOTAL_PAYABLES; ?></span>
        <img onmouseover="Tip('Setting')" src="<?php //echo $this->webroot; ?>img/button/setting-inactive.png" id="settingPayable" style="width: 20px; float: right; cursor: pointer;" />
        <img onmouseover="Tip('Loading...')" src="<?php //echo $this->webroot; ?>img/button/refresh-animation.gif" id="loadingPayable" style="width: 20px; float: right; display: none; margin-right: 10px;" /> 
        <img onmouseover="Tip('Refresh')" src="<?php //echo $this->webroot; ?>img/button/refresh-inactive.png" id="refreshPayable" class="refreshDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" /> 
        <img onmouseover="Tip('Hide')" src="<?php //echo $this->webroot; ?>img/button/minimize-inactive.png" id="minimizePayable" class="minimizeDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" />
        <div style="width: 160px; float: right; margin-right: 10px;">
            <?php
            //if(COUNT($branches) > 1){
            ?>
            <select id="filterBranchPayable" class="branchView select-style" style="width: 150px; border: none;">
                <option value="all"><?php //echo TABLE_ALL; ?></option>
                <?php 
                //foreach($branches AS $branch){
                ?>
                <option value="<?php //echo $branch['Branch']['id']; ?>"><?php //echo $branch['Branch']['name']; ?></option>
                <?php
                //}
                ?>
            </select>
            <?php
            //} else {
            ?>
            <input type="hidden" value="<?php //echo @$branches[0]['Branch']['id']; ?>" id="filterBranchPayable" class="branchView" />
            <?php
           // }
            ?>
        </div>
        <div style="clear: both;"></div>
        <div id="settingPayableMenu" class="settingMenu">
            <input type="hidden" class="defaultCheckSetting" value="<?php //echo $viewPayableAuto; ?>" />
            <input type="hidden" class="defaultTimeSetting" value="<?php //echo $viewPayableTime; ?>" />
            <input type="hidden" class="selectSetting" value="0" />
            <div class="divMenu">
                <h1>Setting <span style="float: right; margin-right: 3px; color: #337ab7; display: none;" class="saveCompleted">Save Completed</span><span style="float: right; margin-right: 3px; color: red; display: none;" class="saveFailed">Save Failed</span></h1>
                <table cellpadding="5" cellspacing="0">
                    <tr>
                        <td style="width: 40%;">Auto Refresh</td>
                        <td style="text-align: left;">
                            <input type="checkbox" class="settingCheck" data-size="small" data-toggle="toggle" />
                        </td>
                    </tr>
                    <tr>
                        <td>Every</td>
                        <td>
                            <input type="text" style="width: 45px;" value="0" class="settingPayableTimeRefresh" /> Second(s)
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button type="button" style="float: right; margin-right: 10px;" class="settingPayableSave">
                                <span class="settingPayableTxtSave"><?php //echo ACTION_SAVE; ?></span>
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </h1>
    <div style="width: 100%; font-size: 12px; height: 150px; text-align: center;" id="payableView">
        <img src="<?php //echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-0px" />
    </div>
</div> -->
<?php 
// }
// if($viewAdjIssued){
?>
<!-- <div class="boxDashboard" role="inventory_adjustments/viewAdjustmentIssued" display="<?php //echo $displayAdjIssued; ?>" confirm="<?php //echo MESSAGE_CONFIRM_HIDE; ?>" dialog="<?php //echo DIALOG_CONFIRMATION; ?>" cancel="<?php //echo ACTION_CANCEL; ?>" hide="<?php //echo TABLE_HIDE; ?>" style="<?php //echo $viewAdjIssuedDisp; ?>">
    <h1 class="title"><span class="dashboardName"><?php //echo MENU_INVENTORY_ADJUSTMENT; ?></span>
        <img onmouseover="Tip('Setting')" src="<?php //echo $this->webroot; ?>img/button/setting-inactive.png" id="settingAdjIssued" style="width: 20px; float: right; cursor: pointer;" />
        <img onmouseover="Tip('Loading...')" src="<?php //echo $this->webroot; ?>img/button/refresh-animation.gif" id="loadingAdjIssued" style="width: 20px; float: right; display: none; margin-right: 10px;" /> 
        <img onmouseover="Tip('Refresh')" src="<?php //echo $this->webroot; ?>img/button/refresh-inactive.png" id="refreshAdjIssued" class="refreshDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" /> 
        <img onmouseover="Tip('Hide')" src="<?php //echo $this->webroot; ?>img/button/minimize-inactive.png" id="minimizeAdjIssued" class="minimizeDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" />
        <div style="width: 160px; float: right; margin-right: 10px;">
            <?php
            ///if(COUNT($branches) > 1){
            ?>
            <select id="filterBranchAdjustmentIssued" class="branchView select-style" style="width: 150px; border: none;">
                <option value="all"><?php //echo TABLE_ALL; ?></option>
                <?php 
                //foreach($branches AS $branch){
                ?>
                <option value="<?php //echo $branch['Branch']['id']; ?>"><?php //echo $branch['Branch']['name']; ?></option>
                <?php
                //}
                ?>
            </select>
            <?php
            //} else {
            ?>
            <input type="hidden" value="<?php //echo @$branches[0]['Branch']['id']; ?>" id="filterBranchAdjustmentIssued" class="branchView" />
            <?php
            //}
            ?>
        </div>
        <div style="clear: both;"></div>
        <div id="settingAdjIssuedMenu" class="settingMenu">
            <input type="hidden" class="defaultCheckSetting" value="<?php //echo $viewAdjIssuedAuto; ?>" />
            <input type="hidden" class="defaultTimeSetting" value="<?php //echo $viewAdjIssuedTime; ?>" />
            <input type="hidden" class="selectSetting" value="0" />
            <div class="divMenu">
                <h1>Setting <span style="float: right; margin-right: 3px; color: #337ab7; display: none;" class="saveCompleted">Save Completed</span><span style="float: right; margin-right: 3px; color: red; display: none;" class="saveFailed">Save Failed</span></h1>
                <table cellpadding="5" cellspacing="0">
                    <tr>
                        <td style="width: 40%;">Auto Refresh</td>
                        <td style="text-align: left;">
                            <input type="checkbox" class="settingCheck" data-size="small" data-toggle="toggle" />
                        </td>
                    </tr>
                    <tr>
                        <td>Every</td>
                        <td>
                            <input type="text" style="width: 45px;" value="0" class="settingAdjIssuedTimeRefresh" /> Second(s)
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button type="button" style="float: right; margin-right: 10px;" class="settingAdjIssuedSave">
                                <span class="settingAdjIssuedTxtSave"><?php //echo ACTION_SAVE; ?></span>
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </h1>
    <div style="width: 100%; font-size: 12px; min-height: 360px; text-align: center;" id="adjIssuedView">
        <img src="<?php //echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />
    </div>
</div> -->
<?php 
// }
// if($viewReorderLevel){
?>
<!-- <div class="boxDashboard" role="inventory_adjustments/viewReorderLevel" display="<?php //echo $displayReorderLevel; ?>" confirm="<?php //echo MESSAGE_CONFIRM_HIDE; ?>" dialog="<?php //echo DIALOG_CONFIRMATION; ?>" cancel="<?php //echo ACTION_CANCEL; ?>" hide="<?php //echo TABLE_HIDE; ?>" style="<?php //echo $viewReorderLevelDisp; ?>">
    <h1 class="title"><span class="dashboardName"><?php //echo TABLE_PRODUCT_REORDER_LEVEL; ?></span>
        <img onmouseover="Tip('Setting')" src="<?php //echo $this->webroot; ?>img/button/setting-inactive.png" id="settingReorderLevel" style="width: 20px; float: right; cursor: pointer;" />
        <img onmouseover="Tip('Loading...')" src="<?php //echo $this->webroot; ?>img/button/refresh-animation.gif" id="loadingReorderLevel" style="width: 20px; float: right; display: none; margin-right: 10px;" /> 
        <img onmouseover="Tip('Refresh')" src="<?php //echo $this->webroot; ?>img/button/refresh-inactive.png" id="refreshReorderLevel" class="refreshDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" /> 
        <img onmouseover="Tip('Hide')" src="<?php //echo $this->webroot; ?>img/button/minimize-inactive.png" id="minimizeReorderLevel" class="minimizeDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" />
        <div style="width: 160px; float: right; margin-right: 10px;">
            <?php
            //if(COUNT($branches) > 1){
            ?>
            <select id="filterWarehouseReorderLevel" class="branchView select-style" style="width: 150px; border: none;">
                <option value="all"><?php //echo TABLE_ALL; ?></option>
                <?php 
                //foreach($branches AS $branch){
                ?>
                <option value="<?php //echo $branch['Branch']['id']; ?>"><?php //echo $branch['Branch']['name']; ?></option>
                <?php
                //}
                ?>
            </select>
            <?php
            //} else {
            ?>
            <input type="hidden" value="<?php //echo @$branches[0]['Branch']['id']; ?>" id="filterWarehouseReorderLevel" class="branchView" />
            <?php
            //}
            ?>
        </div>
        <div style="clear: both;"></div>
        <div id="settingReorderLevelMenu" class="settingMenu">
            <input type="hidden" class="defaultCheckSetting" value="<?php //echo $viewReorderLevelAuto; ?>" />
            <input type="hidden" class="defaultTimeSetting" value="<?php //echo $viewReorderLevelTime; ?>" />
            <input type="hidden" class="selectSetting" value="0" />
            <div class="divMenu">
                <h1>Setting <span style="float: right; margin-right: 3px; color: #337ab7; display: none;" class="saveCompleted">Save Completed</span><span style="float: right; margin-right: 3px; color: red; display: none;" class="saveFailed">Save Failed</span></h1>
                <table cellpadding="5" cellspacing="0">
                    <tr>
                        <td style="width: 40%;">Auto Refresh</td>
                        <td style="text-align: left;">
                            <input type="checkbox" class="settingCheck" data-size="small" data-toggle="toggle" />
                        </td>
                    </tr>
                    <tr>
                        <td>Every</td>
                        <td>
                            <input type="text" style="width: 45px;" value="0" class="settingReorderLevelTimeRefresh" /> Second(s)
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button type="button" style="float: right; margin-right: 10px;" class="settingReorderLevelSave">
                                <span class="settingReorderLevelTxtSave"><?php //echo ACTION_SAVE; ?></span>
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </h1>
    <div style="width: 100%; font-size: 12px; min-height: 360px; text-align: center;" id="reorderLevelView">
        <img src="<?php //echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />
    </div>
</div> -->
<?php 
// }
// if($viewCustomerPaymentAlert){
?>
<!-- <div class="boxDashboard" role="dashboards/viewCustomerPaymentAlert" display="<?php //echo $displayCustomerPaymentAlert; ?>" confirm="<?php //echo MESSAGE_CONFIRM_HIDE; ?>" dialog="<?php //echo DIALOG_CONFIRMATION; ?>" cancel="<?php //echo ACTION_CANCEL; ?>" hide="<?php //echo TABLE_HIDE; ?>" style="<?php //echo $viewCustomerPaymentAlertDisp; ?>">
    <h1 class="title"><span class="dashboardName"><?php //echo TABLE_CUSTOMER_PAYMENT_TOMORROW; ?></span>
        <img onmouseover="Tip('Setting')" src="<?php //echo $this->webroot; ?>img/button/setting-inactive.png" id="settingCustomerPaymentAlert" style="width: 20px; float: right; cursor: pointer;" />
        <img onmouseover="Tip('Loading...')" src="<?php //echo $this->webroot; ?>img/button/refresh-animation.gif" id="loadingCustomerPaymentAlert" style="width: 20px; float: right; display: none; margin-right: 10px;" /> 
        <img onmouseover="Tip('Refresh')" src="<?php //echo $this->webroot; ?>img/button/refresh-inactive.png" id="refreshCustomerPaymentAlert" class="refreshDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" /> 
        <img onmouseover="Tip('Hide')" src="<?php //echo $this->webroot; ?>img/button/minimize-inactive.png" id="minimizeCustomerPaymentAlert" class="minimizeDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" />
        <div style="width: 155px; float: right; margin-right: 5px;">
            <?php
            //if(COUNT($branches) > 1){
            ?>
            <select id="filterCustomerPaymentAlert" class="branchView select-style" style="width: 150px; border: none;">
                <option value="all"><?php //echo TABLE_ALL; ?></option>
                <?php 
                //foreach($branches AS $branch){
                ?>
                <option value="<?php //echo $branch['Branch']['id']; ?>"><?php //echo $branch['Branch']['name']; ?></option>
                <?php
                //}
                ?>
            </select>
            <?php
            //} else {
            ?>
            <input type="hidden" value="<?php //echo @$branches[0]['Branch']['id']; ?>" id="filterCustomerPaymentAlert" class="branchView" />
            <?php
            //}
            ?>
        </div>
        <div style="clear: both;"></div>
        <div id="settingCustomerPaymentAlertMenu" class="settingMenu">
            <input type="hidden" class="defaultCheckSetting" value="<?php //echo $viewCustomerPaymentAlertAuto; ?>" />
            <input type="hidden" class="defaultTimeSetting" value="<?php //echo $viewCustomerPaymentAlertTime; ?>" />
            <input type="hidden" class="selectSetting" value="0" />
            <div class="divMenu">
                <h1>Setting <span style="float: right; margin-right: 3px; color: #337ab7; display: none;" class="saveCompleted">Save Completed</span><span style="float: right; margin-right: 3px; color: red; display: none;" class="saveFailed">Save Failed</span></h1>
                <table cellpadding="5" cellspacing="0">
                    <tr>
                        <td style="width: 40%;">Auto Refresh</td>
                        <td style="text-align: left;">
                            <input type="checkbox" class="settingCheck" data-size="small" data-toggle="toggle" />
                        </td>
                    </tr>
                    <tr>
                        <td>Every</td>
                        <td>
                            <input type="text" style="width: 45px;" value="0" class="settingCustomerPaymentAlertTimeRefresh" /> Second(s)
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button type="button" style="float: right; margin-right: 10px;" class="settingCustomerPaymentAlertSave">
                                <span class="settingCustomerPaymentAlertTxtSave"><?php //echo ACTION_SAVE; ?></span>
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </h1>
    <div style="width: 100%; font-size: 12px; height: 350px; text-align: center;" id="customerPaymentAlertView">
        <img src="<?php //echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />
    </div>
</div> -->
<?php 
// }
?>
<div style="clear: both;"></div>




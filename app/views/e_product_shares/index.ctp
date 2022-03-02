<?php
$tblName = rand();
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableEProductShare;
    var data = "all";
    var tabProductId  = $(".ui-tabs-selected a").attr("href");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        var waitForFinalEventProView = (function () {
          var timers = {};
          return function (callback, ms, uniqueId) {
            if (!uniqueId) {
              uniqueId = "Don't call this twice without a uniqueId";
            }
            if (timers[uniqueId]) {
              clearTimeout (timers[uniqueId]);
            }
            timers[uniqueId] = setTimeout(callback, ms);
          };
        })();
        
        $(window).resize(function(){
            var tabSelected = $(".ui-tabs-selected a").attr("href");
            if(tabSelected == tabProductId){
                waitForFinalEventProView(function(){
                getScrollTable(); 
              }, 500, "Finish");
            }
        }); 
        oTableEProductShare = $("#<?php echo $tblName; ?>").dataTable({
            "sScrollY": calcDataTableHeight(),
            "aaSorting": [[0, 'DESC']],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/"+$("#showECommerceProductShop").val()+"/"+$("#showECommerceProductPgroup").val(),
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                var tableBody   = $(".dataTables_scrollBody .table").attr("style");
                $(".dataTables_scrollHeadInner .table").removeAttr('style');
                $(".dataTables_scrollBody .table").removeAttr('style');
                $(".dataTables_scrollHeadInner .table").attr("style", "width: 90%; padding:0px; margin-top:5px;");
                $(".dataTables_scrollBody .table").attr("style", tableBody+" padding:0px; margin:0px;");
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');                
                $("#<?php echo $tblName; ?> td:nth-child(9)").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> tr").click(function(){
                    changeBackgroupProduct();
                    $(this).closest("tr").css('background','#eeeca9');
                });
                $(".viewProductPrice").click(function(){
                    var id = $(this).attr('data');
                    $.ajax({
                        type:   "POST",
                        url:    "<?php echo $this->base.'/'.$this->params['controller']; ?>/productPrice/"+id,
                        beforeSend: function(){
                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        },
                        success: function(msg){
                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            $("#dialog").html(msg);
                            $("#dialog").dialog({
                                title: '<?php echo ACTION_SET_PRICE; ?>',
                                resizable: false,
                                modal: true,
                                width: '800',
                                height: '650',
                                position:'center',
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
                    });
                });
                return sPre;
            },
            "fnDrawCallback": function(oSettings, json) {
                $("#<?php echo $tblName; ?> .colspanParent").parent().attr("colspan", 11);
                $("#<?php echo $tblName; ?> .colspanParentHidden").parent().css("display", "none");
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ -1 ]
            }]
        });
        
        
        $("#showECommerceProductShop, #showECommerceProductPgroup, #changeCategoryProductView, #displayProduct").change(function(){
            var Tablesetting = oTableEProductShare.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#showECommerceProductShop").val()+"/"+$("#showECommerceProductPgroup").val();
            oCache.iCacheLower = -1;
            oTableEProductShare.fnDraw(false);
        });
    });
    
    function getScrollTable(){
        $('div.dataTables_scrollBody').css('height',calcDataTableHeight());
        oTableEProductShare.fnAdjustColumnSizing();
    }
    
    function changeBackgroupProduct(){
        $("#<?php echo $tblName; ?> tbody tr").each(function(){
                $(this).removeAttr('style');
        });
    }
    
    function calcDataTableHeight() {
        var tableHeight = $(window).height() - ($(".ui-layout-north").height() + $(".ui-layout-south").height() + $(".ui-tabs-nav").height() + $("#divHeader").height() + 37 + 22 + 56 + 110.3);
        return tableHeight;
    }
</script>
<div class="leftPanel">
    <div style="padding-top: 3px; padding-bottom: 3px; padding-left: 5px; padding-right: 5px; border: 1px dashed #bbbbbb; margin-bottom: 5px;" id="divHeader">
        <div style="float:left;">
            <label for="showECommerceProductShop"><?php echo TABLE_COMPANY ?></label>: 
            <select id="showECommerceProductShop" style="width: 200px; height: 30px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <?php
                $sqlShop = mysql_query("SELECT * FROM e_store_shares WHERE is_share = 1");
                while($rowShop = mysql_fetch_array($sqlShop)) {
                ?>
                <option value="<?php echo $rowShop['company_id']; ?>"><?php echo $rowShop['name']; ?></option>
                <?php
                }
                ?>
            </select>
            &nbsp;&nbsp;&nbsp;
            <label for="showECommerceProductPgroup"><?php echo MENU_PRODUCT_GROUP_MANAGEMENT; ?></label>:
            <select id="showECommerceProductPgroup" style="width: 200px; height: 30px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <?php
                $sqlPgroup = mysql_query("SELECT pgroups.id, pgroups.name FROM e_pgroup_shares INNER JOIN pgroups ON pgroups.id = e_pgroup_shares.pgroup_id WHERE 1 GROUP BY e_pgroup_shares.pgroup_id;");
                while($rowPgroup = mysql_fetch_array($sqlPgroup)) {
                ?>
                <option value="<?php echo $rowPgroup['id']; ?>"><?php echo $rowPgroup['name']; ?></option>
                <?php
                }
                ?>
            </select>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div id="dynamic" style="height: 100%">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0" style="width: 100%;">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_COMPANY; ?></th>
                    <th><?php echo TABLE_GROUP; ?></th>
                    <th><?php echo TABLE_BARCODE; ?></th>
                    <th><?php echo TABLE_SKU; ?></th>
                    <th><?php echo TABLE_NAME; ?></th>
                    <th><?php echo TABLE_UOM; ?></th>
                    <th><?php echo TABLE_PRICE; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="8" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="rightPanel"></div>
<table id="tblMutiPhoto" class="table">
    <tr>
        <th style="width:15%;" class="first">Vendor</th>
        <th style="width:12%;">Size</th>
        <th style="width:12%;">Color</th>
        <th style="width:35%;">Note</th>
        <th style="">Photo</th>
        <th style="width:100px;">Action</th>
    </tr>
    <?php
    $vendorName = '';
    $sizeName   = '';
    $colorName  = '';
    for($i=0;$i<sizeof($productVariantTmp);$i++){
        $sqlVendor = mysql_query("SELECT name FROM vendors WHERE id={$productVariantTmp[$i]["ProductVariantTmp"]["vendor_id"]} ORDER BY id ASC");
        if(mysql_num_rows($sqlVendor)){
            $rowVendor  = mysql_fetch_array($sqlVendor);
            $vendorName = $rowVendor[0];
        }
        $sqlColor = mysql_query("SELECT name FROM colors WHERE id={$productVariantTmp[$i]["ProductVariantTmp"]["color_id"]} ORDER BY id ASC");
        if(mysql_num_rows($sqlColor)){
            $rowColor  = mysql_fetch_array($sqlColor);
            $colorName = $rowColor[0];
        }
        $sqlSize = mysql_query("SELECT name FROM sizes WHERE id={$productVariantTmp[$i]["ProductVariantTmp"]["size_id"]} ORDER BY id ASC");
        if(mysql_num_rows($sqlSize)){
            $rowSize  = mysql_fetch_array($sqlSize);
            $sizeName = $rowSize[0];
        }
    ?>
    <tr class="tblMutiPhotoList">
        <td class="first">
            <?php echo $vendorName;?>
            <input name="data[Product][vendor_id][]" type="hidden" value="<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["vendor_id"];?>" id="variant_vendor_<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["id"];?>" class="variant_vendor"/>
        </td>
        <td>
            <?php echo $sizeName;?>
            <input name="data[Product][size_id][]" type="hidden" value="<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["size_id"];?>" id="variant_size_<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["id"];?>" class="variant_size"/>
        </td>
        <td>
            <?php echo $colorName;?>
            <input name="data[Product][color_id][]" type="hidden" value="<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["color_id"];?>" id="variant_color_<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["id"];?>" class="variant_color"/>
        </td>
        <td>
            <?php echo $this->Form->textarea('note', array('name'=>'data[Product][note][]','class'=>'variant_note','rel'=>$productVariantTmp[$i]['ProductVariantTmp']['id'],'id'=>'variant_note_'.$productVariantTmp[$i]['ProductVariantTmp']['id'],'style' => 'width:95%; height:35px;')); ?>
        </td>
        <td style="text-align:left; padding:0px 0px 0px 0px;">
            <table style="width:100%;border:none; margin:0px 0px 0px 0px; padding:0px 0px 0px 0px;text-align:left;">
                <tr>
                    <td style="border:none;text-align:left; width:85px; display:none; padding:0px 0px 0px 0px;" id="tblPhotoDisplay_<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["id"];?>">
                        <img id="photoDisplay_<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["id"];?>" class="photoDisplay" alt="" style="text-align:left; width:80px; height:80px;"/>
                    </td>
                    <td style="border:none;text-align:left; padding:0px 0px 0px 0px;">
                        <span id="labelPhoto_<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["id"];?>"></span>
                        <img id="btnRemoveMultiPhoto_<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["id"];?>" rel="<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["id"];?>" alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveMultiPhoto" align="absmiddle" style="cursor: pointer;text-align:left; display:none;" onmouseover="Tip('Remove')" />
                    
                        <input type="file" id="ProductPhoto_<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["id"];?>" class="ProductPhoto" rel="<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["id"];?>" name="photo_<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["id"];?>" style="text-align:left;"/>
                        <input name="data[Product][photo][]" type="hidden" id="ProductPhotoMultiData_<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["id"];?>" class="ProductPhotoMultiData" value=""/>
                    </td>
                </tr>
            </table>
        </td>
        <td style="text-align:center;">
            <img id="btnRemoveRowPhoto_<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["id"];?>" class="btnRemoveRowPhoto"  rel="<?php echo $productVariantTmp[$i]["ProductVariantTmp"]["id"];?>" alt="Remove" src="<?php echo $this->webroot . 'img/button/pos/remove-icon-png-25.png'; ?>" align="absmiddle" style="cursor: pointer; width:25px; height:25px;" onmouseover="Tip('Remove')" />
        </td>
    </tr>
        <?php
    }
    ?>
</table>

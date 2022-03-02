<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackSlideDisplay").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableSlideDisplay.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackSlideDisplay">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<table style="width: 100%">
    <tr>
        <td style="text-align: left;">
            <?php 
            if($this->data['SlideDisplay']['photo'] != ''){
                $photo = "public/slide_show/".$this->data['SlideDisplay']['photo'];
            }else{
                $photo = "img/button/no-images.png";
            }
            ?>
            <img id="photoDisplay" alt="" src="<?php echo $this->webroot; ?><?php echo $photo; ?>" style="width: 200px;" />
        </td>
    </tr>
</table>
<fieldset>
    <legend><?php __(MENU_SLIDE_DISPLAY_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_NAME); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['SlideDisplay']['name']; ?></td>
        </tr>
    </table>
</fieldset>
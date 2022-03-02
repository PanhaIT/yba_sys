<style type="text/css" media="print">
    div.print_doc { width:100%;}
    #btnDisappearPrint { display: none;}
</style>
<div class="print_doc" style="width: 595px; padding-top: 10px;">
    <div style="width: 100px; margin: 0px auto;">
        <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' class='noprint' style="font-size: 30px; height: 50px;">
    </div>
    <br />
    <table cellpadding="0" cellspacing="0" style="width: 100%; margin-top: 10px;">
        <tr>
            <td>
                <table cellpadding="0" cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style="text-align: center;">
                            <img class="barcode" alt="" src="<?php echo $this->webroot; ?>barcodegen.1d-php5.v2.2.0/generate_barcode.php?str=<?php echo $location['Location']['name']; ?>" style="border:0px; padding: 0px; margin: 0px; width: 300px; height: 80px;" />
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="text-align: center; font-size: 20px; font-weight: bold;">
                <?php echo $location['Location']['name']; ?>
            </td>
        </tr>
    </table>
</div>
<div style="clear:both"></div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#btnDisappearPrint").click(function(){
            $("#footerPrint").show();
            window.print();
            window.close();
        });
    });
</script>
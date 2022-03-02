<?php
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$numberShow = "numberShow" . $rnd;
$pager = "pager" . $rnd;

include('includes/function.php');
include('includes/report.php');

// paging feature
if (!isset($_POST['page']) || $_POST['page'] == '')
    $_POST['page'] = 1;
if (!isset($_POST['number_show']) || $_POST['number_show'] == '')
    $_POST['number_show'] = 10;
$start = ($_POST['number_show'] * $_POST['page']) - $_POST['number_show'];

/**
 * condition for date
 */
$condition = ' Vendor.is_active = 1 ';
if ($_POST['date_from'] != '') {
    $condition != '' ? $condition.=' AND ' : '';
    $condition.=' "' . dateConvert($_POST['date_from']) . '" <= DATE(Vendor.created)';
}
if ($_POST['date_to'] != '') {
    $condition != '' ? $condition.=' AND ' : '';
    $condition.=' "' . dateConvert($_POST['date_to']) . '" >= DATE(Vendor.created)';
}
if ($_POST['company_id'] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'Vendor.id IN (SELECT vendor_id FROM vendor_companies WHERE company_id ='.$_POST['company_id'].')';
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'Vendor.id IN (SELECT vendor_id FROM vendor_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].'))';
}
if ($_POST['province'] != '') {
    $condition != '' ? $condition.=' AND ' : '';
    $condition.=" Vendor.province_id = '{$_POST['province']}' ";
}
if ($_POST['district'] != '') {
    $condition != '' ? $condition.=' AND ' : '';
    $condition.=" Vendor.district_id = '{$_POST['district']}' ";
}
if ($_POST['commune'] != '') {
    $condition != '' ? $condition.=' AND ' : '';
    $condition.=" Vendor.commune_id = '{$_POST['commune']}' ";
}
if ($_POST['village'] != '') {
    $condition != '' ? $condition.=' AND ' : '';
    $condition.=" Vendor.village_id = '{$_POST['village']}' ";
}
if ($_POST['vendor_code'] != '') {
    $condition != '' ? $condition.=' AND ' : '';
    $condition.=" Vendor.vendor_code = '{$_POST['vendor_code']}' ";
}
if ($_POST['other'] != '') {
    $condition != '' ? $condition.=' AND ' : '';
    $condition.=" (Vendor.name LIKE '%{$_POST['other']}%'
                    OR Vendor.work_telephone = '{$_POST['other']}'
                    OR Vendor.other_number = '{$_POST['other']}'
                    OR Vendor.fax_number = '{$_POST['other']}'
                    OR Vendor.email_address = '{$_POST['other']}'
                    OR Vendor.address LIKE '%{$_POST['other']}%') ";
}
if ($_POST['created_by'] != '') {
    $condition != '' ? $condition.=' AND ' : '';
    $condition.=" Vendor.created_by = '{$_POST['created_by']}' ";
}
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $btnPrint; ?>").click(function(){
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
        });

        $("#<?php echo $numberShow; ?>").change(function() {
            var result=$(this).parent().parent().attr("id");
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/customerAddressListResult",
                data:   "number_show=" + $("#<?php echo $numberShow; ?>").val() +
                        "&date_from=<?php echo "{$_POST['date_from']}" ?>" +
                        "&date_to=<?php echo "{$_POST['date_to']}" ?>"+
                        "&vendor_code=<?php echo "{$_POST['vendor_code']}" ?>"+
                        "&other=<?php echo "{$_POST['other']}" ?>"+
                        "&province=<?php echo "{$_POST['province']}" ?>"+
                        "&district=<?php echo "{$_POST['district']}" ?>"+
                        "&commune=<?php echo "{$_POST['commune']}" ?>"+
                        "&village=<?php echo "{$_POST['village']}" ?>"+
                        "&created_by=<?php echo "{$_POST['created_by']}" ?>",
                beforeSend: function(){
                    $('#loading_paging').show();
                },
                success: function(msg){
                    $('#loading_paging').hide();
                    $("#"+result).html(msg);
                }
            });
        });
        $("a.<?php echo $pager; ?>").click(function(event) {
            var result=$(this).parent().parent().attr("id");
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/customerAddressListResult",
                data:   "number_show=" + $("#<?php echo $numberShow; ?>").val() +
                        "&page=" + $(this).attr('rel') +
                        "&date_from=<?php echo "{$_POST['date_from']}" ?>" +
                        "&date_to=<?php echo "{$_POST['date_to']}" ?>"+
                        "&vendor_code=<?php echo "{$_POST['vendor_code']}" ?>"+
                        "&other=<?php echo "{$_POST['other']}" ?>"+
                        "&province=<?php echo "{$_POST['province']}" ?>"+
                        "&district=<?php echo "{$_POST['district']}" ?>"+
                        "&commune=<?php echo "{$_POST['commune']}" ?>"+
                        "&village=<?php echo "{$_POST['village']}" ?>"+
                        "&created_by=<?php echo "{$_POST['created_by']}" ?>",
                beforeSend: function(){
                    $('#loading_paging').show();
                },
                success: function(msg){
                    $('#loading_paging').hide();
                    $("#"+result).html(msg);
                }
            });
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_REPORT . '<br />' . MENU_VENDOR_ADDRESS_LIST . '</b><br /><br />';
    if ($_POST['date_from'] != '') {
        $msg .= REPORT_FROM . ': ' . $_POST['date_from'];
    }
    if ($_POST['date_to'] != '') {
        $msg .= ' ' . REPORT_TO . ': ' . $_POST['date_to'];
    }
    if ($_POST['vendor_code'] != '') {
        $msg .= ' <br />' . TABLE_CODE . ': ' . $_POST['vendor_code'];
    }
    if ($_POST['other'] != '') {
        $msg .= ' ' . TABLE_OTHER . ': ' . $_POST['other'];
    }
    if ($_POST['province'] != '') {
        $msg .= ' <br />' . TABLE_PROVINCE . ': ' . getNameById($_POST['province'], 'provinces');
    }
    if ($_POST['district'] != '') {
        $msg .= ' ' . TABLE_DISTRICT . ': ' . getNameById($_POST['district'], 'districts');
    }
    if ($_POST['commune'] != '') {
        $msg .= ' ' . TABLE_COMMUNE . ': ' . getNameById($_POST['commune'], 'communes');
    }
    if ($_POST['village'] != '') {
        $msg .= ' ' . TABLE_VILLAGE . ': ' . getNameById($_POST['village'], 'villages');
    }
    if ($_POST['created_by'] != '') {
        $msg .= ' ' . TABLE_CREATED_BY . ': ' . getUserNameById($_POST['created_by']);
    }
    echo $this->element('/print/header-report', array('msg' => $msg));
    $sql = "    SELECT Vendor.id, 
                    Vendor.vendor_code,
                    Vendor.name,
                    Vendor.work_telephone,
                    Vendor.other_number,
                    Vendor.fax_number,
                    Vendor.email_address,
                    Vendor.address
                FROM vendors AS Vendor ";
    $sql_query = $sql . ($condition != '' ? ' WHERE ' . $condition : $condition) . " LIMIT " . $start . ", " . $_POST['number_show'];
    $query = mysql_query($sql_query);
    while ($data = mysql_fetch_array($query)) {
    ?>
    <div style="float: left; margin-right: 10px; height: 200px; width: 300px;">
        <table class="table" cellspacing="0" style="height: 200px;">
            <tr>
                <th class="first" style="height: 30%;">
                    <?php echo "{$data['vendor_code']}<br />{$data['name']}" ?>
                </th>
            </tr>
            <tr>
                <td class="first" style="vertical-align: top;">
                    <?php echo "{$data['address']}<br />Tel: {$data['work_telephone']}<br />Email: {$data['email_address']}" ?>
                </td>
            </tr>
        </table>
    </div>
    <?php
    }
    ?>
</div>
<div style="clear:both"></div>
<br />
<?php
// paging feature
$query_total = mysql_query($sql . ($condition != '' ? 'WHERE ' . $condition : '') . "");
$total = ceil(mysql_num_rows($query_total) / $_POST['number_show']); ?>
<div style="float:left">
    <?php echo PAGE_PAGE . ' ' . $_POST['page'] . ' ' . PAGE_OF . ' ' . $total . ', ' . PAGE_SHOWING . ' ' . mysql_num_rows($query) . ' ' . PAGE_RECORDS_OUT_OF . ' ' . mysql_num_rows($query_total); ?>
</div>
<?php
echo $this->element('paging', array("total" => $total, "number" => 4, "dots" => "···", "pager" => $pager));
?>
<div style="float:right" class="noprint">
    <?php echo PAGE_DISPLAY; ?> #
    <select id="number_show" style="font-size:14px">
        <option value="10000000000" <?php echo $_POST['number_show'] == 10000000000 ? 'selected="selected"' : ''; ?>><?php echo TABLE_ALL; ?></option>
        <option value="10" <?php echo $_POST['number_show'] == 10 ? 'selected="selected"' : ''; ?>>10</option>
        <option value="25" <?php echo $_POST['number_show'] == 25 ? 'selected="selected"' : ''; ?>>25</option>
        <option value="50" <?php echo $_POST['number_show'] == 50 ? 'selected="selected"' : ''; ?>>50</option>
        <option value="100" <?php echo $_POST['number_show'] == 100 ? 'selected="selected"' : ''; ?>>100</option>
    </select>
    <div style="float:right;padding:0 5px 0 5px"><img id="loading_paging" style="display:none;" alt="" src="<?php echo $this->webroot; ?>img/chrome.gif" /></div>
</div>
<div style="clear:both"></div>
<div class="buttons">
    <button type="button" id="<?php echo $btnPrint; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
        <?php echo ACTION_PRINT; ?>
    </button>
</div>
<div style="clear: both;"></div>
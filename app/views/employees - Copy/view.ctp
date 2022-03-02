<?php 
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackOther").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableEmployee.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();
            rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackOther">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_EMPLOYEE_INFO); ?></legend>
    <div style="width: 35%; vertical-align: top; float: left;">
        <table style="width: 100%;" cellpadding="5">
            <tr>
                <td style="width: 40%;"><?php echo TABLE_EMPLOYEE_NUMBER; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $employee['Employee']['employee_code']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_NAME; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $employee['Employee']['name']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_SEX; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $employee['Employee']['sex']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_DATE_OF_BIRTH; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php 
                        if($employee['Employee']['dob'] != "" && $employee['Employee']['dob'] != "0000-00-00"){
                            $dob = dateShort($employee['Employee']['dob']);
                        }else{
                            $dob = "";
                        }
                        echo $dob; 
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_TELEPHONE_PERSONAL; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $employee['Employee']['personal_number']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_TELEPHONE_OTHER; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $employee['Employee']['other_number']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_EMAIL; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $employee['Employee']['email']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <fieldset>
                        <legend><?php echo TABLE_ADDRESS; ?></legend>
                        <table cellpadding="3" cellspacing="0" style="width: 100%;">
                            <tr>
                                <td style="width: 18%;"><?php echo TABLE_NO; ?></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $employee['Employee']['house_no']; ?>
                                    </div>
                                </td>
                                <td style="width: 12%;"><?php echo TABLE_STREET; ?></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php 
                                        echo $employee['Street']['name']; 
                                        ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 18%;"><?php echo TABLE_PROVINCE; ?></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php 
                                        if($employee['Employee']['province_id'] > 0){
                                            $provinceId = $employee['Employee']['province_id'];
                                            $districtId = $employee['Employee']['district_id']>0?$employee['Employee']['district_id']:'0';
                                            $communeId  = $employee['Employee']['commune_id']>0?$employee['Employee']['commune_id']:'0';
                                            $villageId  = $employee['Employee']['village_id']>0?$employee['Employee']['village_id']:'0';
                                            $sqlAddress = mysql_query("SELECT p.name AS p_name, d.name AS d_name, c.name AS c_name, v.name AS v_name FROM provinces AS p LEFT JOIN districts AS d ON d.province_id = p.id AND d.id = {$districtId} LEFT JOIN communes AS c ON c.district_id = d.id AND c.id = {$communeId} LEFT JOIN villages AS v ON v.commune_id = c.id AND v.id = {$villageId} WHERE p.id = {$employee['Employee']['province_id']}");
                                            $rowAddress = mysql_fetch_array($sqlAddress);
                                        }else{
                                            $rowAddress['p_name'] = '';
                                            $rowAddress['d_name'] = '';
                                            $rowAddress['c_name'] = '';
                                            $rowAddress['v_name'] = '';
                                        }
                                        echo $rowAddress['p_name'];
                                        ?>
                                    </div>
                                </td>
                                <td style="width: 12%;"><?php echo TABLE_DISTRICT; ?></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $rowAddress['d_name']; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 18%;"><?php echo TABLE_COMMUNE; ?></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $rowAddress['c_name']; ?>
                                    </div>
                                </td>
                                <td style="width: 12%;"><?php echo TABLE_VILLAGE; ?></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $rowAddress['v_name']; ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                </td>
            </tr>
        </table>
    </div>
    <div style="width: 35%; vertical-align: top; float: left;">
        <table style="width: 100%;" cellpadding="5">
            <tr>
                <td style="width: 40%;"><?php echo USER_GROUP; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php  
                        $sqlGroup = mysql_query("SELECT GROUP_CONCAT(name) FROM egroups WHERE id IN (SELECT egroup_id FROM employee_egroups WHERE employee_id = ".$employee['Employee']['id'].")");
                        $rowGroup = mysql_fetch_array($sqlGroup);
                        echo $rowGroup[0];
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><?php echo TABLE_NAME_IN_KHMER; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $employee['Employee']['name_kh']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_START_WORKING_DATE; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php 
                        if($employee['Employee']['start_working_date'] != "" && $employee['Employee']['start_working_date'] != "0000-00-00"){
                            $startWorking = dateShort($employee['Employee']['start_working_date']);
                        }else{
                            $startWorking = "";
                        }
                        echo $startWorking; 
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_TERMINATION_DATE; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php 
                        if($employee['Employee']['termination_date'] != "" && $employee['Employee']['termination_date'] != "0000-00-00"){
                            $startTermination = dateShort($employee['Employee']['termination_date']);
                        }else{
                            $startTermination = "";
                        }
                        echo $startTermination; 
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_POSITION; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php 
                        if($employee['Employee']['position_id'] != ""){
                            $sqlPos = mysql_query("SELECT name FROM positions WHERE id = ".$employee['Employee']['position_id']);
                            $rowPos = mysql_fetch_array($sqlPos);
                            echo $rowPos[0];
                        }
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_SALARY; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $employee['Employee']['salary']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_WORK_FOR_VENDOR; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php  
                        if($employee['Employee']['work_for_vendor_id'] != ""){
                            $sqlVen = mysql_query("SELECT name FROM vendors WHERE id = ".$employee['Employee']['work_for_vendor_id']);
                            $rowVen = mysql_fetch_array($sqlVen);
                            echo $rowVen[0];
                        }
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><?php echo TABLE_NOTE; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $employee['Employee']['note']; ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div style="width: 29%; vertical-align: top; float: right;">
        <table style="width: 100%;" cellpadding="5">
            <tr>
                <td colspan="2" style="text-align: center;">
                    <?php 
                    if($employee['Employee']['photo'] != ''){
                        $photo = "public/employee_photo/".$employee['Employee']['photo'];
                    }else{
                        $photo = "img/button/no-images.png";
                    }
                    ?>
                    <img id="photoDisplayEmployee" alt="" src="<?php echo $this->webroot; ?><?php echo $photo; ?>" style=" max-width: 250px; max-height: 250px;" />
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</fieldset>
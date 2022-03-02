<?php

class GeneralSettingsController extends AppController {

    var $uses = array('User');
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
    }
    
    function save(){
        $this->layout = 'ajax';
        if (!empty($this->data)) {
            $user = $this->getCurrentUser();
            // Check Lots/Series
            if(!empty($this->data['uom_detail'])){
                if($this->data['uom_detail'] == 1){
                    $uomDetail = 0;
                } else {
                    $uomDetail = 1;
                }
            }else{
                $uomDetail = 1;
            }
            $sqlPOSShift = mysql_query("SELECT id FROM shifts WHERE status = 1 OR status = 2");
            if(mysql_num_rows($sqlPOSShift)){
                $this->data['shift_pos'] = 1;
            }
            mysql_query("UPDATE setting_options SET allow_delivery = ".$this->data['allow_delivery'].", uom_detail_option = ".$uomDetail.", shift =".$this->data['shift_pos'].", product_cost_decimal =".$this->data['product_decimal']);
            // Location Setting
            mysql_query("UPDATE location_settings SET location_status =".$this->data['location_pb']." WHERE id = 1");
            mysql_query("UPDATE location_settings SET location_status =".$this->data['location_br']." WHERE id = 2");
            mysql_query("UPDATE location_settings SET location_status =".$this->data['location_pos']." WHERE id = 3");
            mysql_query("UPDATE location_settings SET location_status =".$this->data['location_sale']." WHERE id = 4");
            mysql_query("UPDATE location_settings SET location_status =".$this->data['location_cm']." WHERE id = 5");
            
            // Get System Info
            $array = array();
            $array['titleKh'] = $this->data['system_name_kh'];
            $array['title'] = $this->data['system_name'];
            $array['start'] = $this->data['system_start'];
            
            // Upload Logo
            if ($_FILES['photo_big']['name'] != '') {
                $target_folder = 'img/';
                $ext = explode(".", $_FILES['photo_big']['name']);
                $target_name = 'logo.' . $ext[sizeof($ext) - 1];
                move_uploaded_file($_FILES['photo_big']['tmp_name'], $target_folder . $target_name);
            }
            if ($_FILES['photo_small']['name'] != '') {
                $target_folder = 'img/';
                $ext = explode(".", $_FILES['photo_small']['name']);
                $target_name = 'logo_s.' . $ext[sizeof($ext) - 1];
                move_uploaded_file($_FILES['photo_small']['tmp_name'], $target_folder . $target_name);
            }
            
            // Printer
            $silent = 0;
            if(!empty($this->data['pos_print_silent'])){
                $silent = 1;
            }
            $sqlCheck = mysql_query("SELECT id FROM printers WHERE module_name = 'POS'");
            if(mysql_num_rows($sqlCheck)){
                mysql_query("UPDATE printers SET printer_name = '".$this->data['pos_printer']."', silent = '".$silent."', modified = '".date("Y-m-d H:i:s")."', modified_by = ".$user['User']['id']." WHERE module_name = 'POS';");
            } else {
                mysql_query("INSERT INTO `printers` (`module_name`, `printer_name`, `silent`, `created`, `created_by`, `modified`) VALUES ('".$this->data['pos_printer']."', 'POS', ".$silent.", '".date("Y-m-d H:i:s")."', ".$user['User']['id'].", '".date("Y-m-d H:i:s")."');");
            }
            
            // Create System Info
            $json = json_encode($array);
            $filename = "config/system_config.fg";
            $file = fopen($filename, "w");
            fwrite($file, $json);
            fclose($file);
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
            exit;
        } else {
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }
    
    function saveRecalculate($date = null){
        $this->layout = 'ajax';
        if(!empty($date) && $date != '0000-00-00' && $date != '00/00/00'){
            if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $date)) {
                $date = $this->Helper->dateConvert($date);
            }
            mysql_query("UPDATE tracks SET val = '".$date."', is_recalculate = 1 WHERE id = 1;");
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
        } else {
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
        }
        exit;
    }
}

?>
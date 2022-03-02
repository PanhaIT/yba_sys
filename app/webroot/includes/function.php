<?php

if (!function_exists('date_parse_from_format')) {

    function date_parse_from_format($format, $date) {
        $i = 0;
        $pos = 0;
        $output = array();
        while ($i < strlen($format)) {
            $pat = substr($format, $i, 1);
            $i++;
            switch ($pat) {
                case 'd': //    Day of the month, 2 digits with leading zeros    01 to 31
                    $output['day'] = substr($date, $pos, 2);
                    $pos+=2;
                    break;
                case 'D': // A textual representation of a day: three letters    Mon through Sun
                    //TODO
                    break;
                case 'j': //    Day of the month without leading zeros    1 to 31
                    $output['day'] = substr($date, $pos, 2);
                    if (!is_numeric($output['day']) || ($output['day'] > 31)) {
                        $output['day'] = substr($date, $pos, 1);
                        $pos--;
                    }
                    $pos+=2;
                    break;
                case 'm': //    Numeric representation of a month: with leading zeros    01 through 12
                    $output['month'] = (int) substr($date, $pos, 2);
                    $pos+=2;
                    break;
                case 'n': //    Numeric representation of a month: without leading zeros    1 through 12
                    $output['month'] = substr($date, $pos, 2);
                    if (!is_numeric($output['month']) || ($output['month'] > 12)) {
                        $output['month'] = substr($date, $pos, 1);
                        $pos--;
                    }
                    $pos+=2;
                    break;
                case 'Y': //    A full numeric representation of a year: 4 digits    Examples: 1999 or 2003
                    $output['year'] = (int) substr($date, $pos, 4);
                    $pos+=4;
                    break;
                case 'y': //    A two digit representation of a year    Examples: 99 or 03
                    $output['year'] = (int) substr($date, $pos, 2);
                    $pos+=2;
                    break;
                case 'g': //    12-hour format of an hour without leading zeros    1 through 12
                    $output['hour'] = substr($date, $pos, 2);
                    if (!is_numeric($output['day']) || ($output['hour'] > 12)) {
                        $output['hour'] = substr($date, $pos, 1);
                        $pos--;
                    }
                    $pos+=2;
                    break;
                case 'G': //    24-hour format of an hour without leading zeros    0 through 23
                    $output['hour'] = substr($date, $pos, 2);
                    if (!is_numeric($output['day']) || ($output['hour'] > 23)) {
                        $output['hour'] = substr($date, $pos, 1);
                        $pos--;
                    }
                    $pos+=2;
                    break;
                case 'h': //    12-hour format of an hour with leading zeros    01 through 12
                    $output['hour'] = (int) substr($date, $pos, 2);
                    $pos+=2;
                    break;
                case 'H': //    24-hour format of an hour with leading zeros    00 through 23
                    $output['hour'] = (int) substr($date, $pos, 2);
                    $pos+=2;
                    break;
                case 'i': //    Minutes with leading zeros    00 to 59
                    $output['minute'] = (int) substr($date, $pos, 2);
                    $pos+=2;
                    break;
                case 's': //    Seconds: with leading zeros    00 through 59
                    $output['second'] = (int) substr($date, $pos, 2);
                    $pos+=2;
                    break;
                case 'l': // (lowercase 'L')    A full textual representation of the day of the week    Sunday through Saturday
                case 'N': //    ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0)    1 (for Monday) through 7 (for Sunday)
                case 'S': //    English ordinal suffix for the day of the month: 2 characters    st: nd: rd or th. Works well with j
                case 'w': //    Numeric representation of the day of the week    0 (for Sunday) through 6 (for Saturday)
                case 'z': //    The day of the year (starting from 0)    0 through 365
                case 'W': //    ISO-8601 week number of year: weeks starting on Monday (added in PHP 4.1.0)    Example: 42 (the 42nd week in the year)
                case 'F': //    A full textual representation of a month: such as January or March    January through December
                case 'u': //    Microseconds (added in PHP 5.2.2)    Example: 654321
                case 't': //    Number of days in the given month    28 through 31
                case 'L': //    Whether it's a leap year    1 if it is a leap year: 0 otherwise.
                case 'o': //    ISO-8601 year number. This has the same value as Y: except that if the ISO week number (W) belongs to the previous or next year: that year is used instead. (added in PHP 5.1.0)    Examples: 1999 or 2003
                case 'e': //    Timezone identifier (added in PHP 5.1.0)    Examples: UTC: GMT: Atlantic/Azores
                case 'I': // (capital i)    Whether or not the date is in daylight saving time    1 if Daylight Saving Time: 0 otherwise.
                case 'O': //    Difference to Greenwich time (GMT) in hours    Example: +0200
                case 'P': //    Difference to Greenwich time (GMT) with colon between hours and minutes (added in PHP 5.1.3)    Example: +02:00
                case 'T': //    Timezone abbreviation    Examples: EST: MDT ...
                case 'Z': //    Timezone offset in seconds. The offset for timezones west of UTC is always negative: and for those east of UTC is always positive.    -43200 through 50400
                case 'a': //    Lowercase Ante meridiem and Post meridiem    am or pm
                case 'A': //    Uppercase Ante meridiem and Post meridiem    AM or PM
                case 'B': //    Swatch Internet time    000 through 999
                case 'M': //    A short textual representation of a month: three letters    Jan through Dec
                default:
                    $pos++;
            }
        }
        return $output;
    }

}

function dateConvert($rawDate) {
    if (($rawDate == '00/00/0000 00:00:00') || ($rawDate == ''))
        return false;

    $table_date = split('/', $rawDate);
    $day = $table_date[sizeof($table_date) - 3];
    $month = $table_date[sizeof($table_date) - 2];

    $year = $table_date[sizeof($table_date) - 1];

    $str_date = $year . '-' . $month . '-' . $day;
    return ($str_date);
}

function dateShort($rawDate, $format='d/m/Y') {
    if (($rawDate == '0000-00-00 00:00:00') || ($rawDate == ''))
        return false;
    $year = substr($rawDate, 0, 4);
    $month = (int) substr($rawDate, 5, 2);
    $day = (int) substr($rawDate, 8, 2);
    $hour = (int) substr($rawDate, 11, 2);
    $minute = (int) substr($rawDate, 14, 2);
    $second = (int) substr($rawDate, 17, 2);

    if (@date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {
        return date($format, mktime($hour, $minute, $second, $month, $day, $year));
    } else {
        return ereg_replace('2037' . '$', $year, date($format, mktime($hour, $minute, $second, $month, $day, 2037)));
    }
}

function days_in_month($month, $year) {
    return date('t', mktime(0, 0, 0, $month + 1, 0, $year));
}

function curPageName() {
    return substr($_SERVER["SCRIPT_NAME"], strrpos($_SERVER["SCRIPT_NAME"], "/") + 1);
}

function curPageURL() {
    $pageURL = 'http';
    if (!empty($_SERVER['HTTPS'])) {
        if ($_SERVER['HTTPS'] == 'on') {
            $pageURL .= "s";
        }
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return str_replace(curPageName(), '', $pageURL);
}

function highlight($strtobold, $keywords) {
    $patterns = Array();
    $replaces = Array();
    if ($keywords != "") {
        $words = explode(" ", $keywords);
        foreach ($words as $word) {
            $patterns[] = "/" . $word . "/i";
            $replaces[] = "<span class='txt_highlight'>$0</span>";
        }
        return preg_replace(str_replace(array('(', ')'), array('\(', '\)'), $patterns), $replaces, $strtobold);
    } else {
        return $strtobold;
    }
}

function renameFile($old_name, $new_name) {
    $ext = split("\.", $old_name);
    return $new_name . '.' . $ext[sizeof($ext) - 1];
}

function getRandomString($l = 10) {
    $c = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxwz0123456789";
    for (; $l > 0; $l--)
        $s .= $c{rand(0, strlen($c))};
    return str_shuffle($s);
}

// Resize Image Step 1/2 : Build in function for BMP
function imagecreatefrombmp($filename) {
    //Ouverture du fichier en mode binaire
    if (!$f1 = fopen($filename, "rb"))
        return FALSE;

    //1 : Chargement des ent?tes FICHIER
    $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1, 14));
    if ($FILE['file_type'] != 19778)
        return FALSE;

    //2 : Chargement des ent?tes BMP
    $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' .
                    '/Vcompression/Vsize_bitmap/Vhoriz_resolution' .
                    '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1, 40));
    $BMP['colors'] = pow(2, $BMP['bits_per_pixel']);
    if ($BMP['size_bitmap'] == 0)
        $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
    $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel'] / 8;
    $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
    $BMP['decal'] = ($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
    $BMP['decal'] -= floor($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
    $BMP['decal'] = 4 - (4 * $BMP['decal']);
    if ($BMP['decal'] == 4)
        $BMP['decal'] = 0;

    //3 : Chargement des couleurs de la palette
    $PALETTE = array();
    if ($BMP['colors'] < 16777216) {
        $PALETTE = unpack('V' . $BMP['colors'], fread($f1, $BMP['colors'] * 4));
    }

    //4 : Cr?ation de l'image
    $IMG = fread($f1, $BMP['size_bitmap']);
    $VIDE = chr(0);

    $res = imagecreatetruecolor($BMP['width'], $BMP['height']);
    $P = 0;
    $Y = $BMP['height'] - 1;
    while ($Y >= 0) {
        $X = 0;
        while ($X < $BMP['width']) {
            if ($BMP['bits_per_pixel'] == 24)
                $COLOR = unpack("V", substr($IMG, $P, 3) . $VIDE);
            elseif ($BMP['bits_per_pixel'] == 16) {
                $COLOR = unpack("n", substr($IMG, $P, 2));
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            } elseif ($BMP['bits_per_pixel'] == 8) {
                $COLOR = unpack("n", $VIDE . substr($IMG, $P, 1));
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            } elseif ($BMP['bits_per_pixel'] == 4) {
                $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                if (($P * 2) % 2 == 0)
                    $COLOR[1] = ($COLOR[1] >> 4); else
                    $COLOR[1] = ($COLOR[1] & 0x0F);
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            }elseif ($BMP['bits_per_pixel'] == 1) {
                $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                if (($P * 8) % 8 == 0)
                    $COLOR[1] = $COLOR[1] >> 7;
                elseif (($P * 8) % 8 == 1)
                    $COLOR[1] = ($COLOR[1] & 0x40) >> 6;
                elseif (($P * 8) % 8 == 2)
                    $COLOR[1] = ($COLOR[1] & 0x20) >> 5;
                elseif (($P * 8) % 8 == 3)
                    $COLOR[1] = ($COLOR[1] & 0x10) >> 4;
                elseif (($P * 8) % 8 == 4)
                    $COLOR[1] = ($COLOR[1] & 0x8) >> 3;
                elseif (($P * 8) % 8 == 5)
                    $COLOR[1] = ($COLOR[1] & 0x4) >> 2;
                elseif (($P * 8) % 8 == 6)
                    $COLOR[1] = ($COLOR[1] & 0x2) >> 1;
                elseif (($P * 8) % 8 == 7)
                    $COLOR[1] = ($COLOR[1] & 0x1);
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            }else {
                return FALSE;
            }
            imagesetpixel($res, $X, $Y, $COLOR[1]);
            $X++;
            $P += $BMP['bytes_per_pixel'];
        }
        $Y--;
        $P+=$BMP['decal'];
    }

    //Fermeture du fichier
    fclose($f1);

    return $res;
}

// Resize Image Step 2/2
function Resize($Dir, $Image, $NewDir, $NewImage, $ResizedWidth, $ResizedHeight, $Quality, $RespectRatio=true) {
    list($ImageWidth, $ImageHeight, $TypeCode) = getimagesize($Dir . $Image);
    $ImageType = ($TypeCode == 1 ? "gif" : ($TypeCode == 2 ? "jpeg" : ($TypeCode == 3 ? "png" : ($TypeCode == 6 ? "bmp" : FALSE))));
    $CreateFunction = "imagecreatefrom" . $ImageType;
    $OutputFunction = "image" . $ImageType;
    if ($ImageType) {
        $ImageSource = $CreateFunction($Dir . $Image);
        if ($RespectRatio == true) {
            if ($ImageWidth > $ImageHeight) {
                if ($ResizedWidth > $ImageWidth)
                    $ResizedWidth = $ImageWidth;
                $ResizedHeight = $ResizedWidth * $ImageHeight / $ImageWidth;
            }else {
                if ($ResizedHeight > $ImageHeight)
                    $ResizedHeight = $ImageHeight;
                $ResizedWidth = $ImageWidth * $ResizedHeight / $ImageHeight;
            }
        }
        $ResizedImage = imagecreatetruecolor($ResizedWidth, $ResizedHeight);
        imagecopyresampled($ResizedImage, $ImageSource, 0, 0, 0, 0, $ResizedWidth, $ResizedHeight, $ImageWidth, $ImageHeight);
        imagejpeg($ResizedImage, $NewDir . $NewImage, $Quality);
        return true;
    }else {
        return false;
    }
}

function getDays($start, $end) {
    $start_ts = strtotime($start);
    $end_ts = strtotime($end);
    $diff = $end_ts - $start_ts;
    return round($diff / 86400);
}

function isAllowVoid($isPos, $status, $saleOrderDate, $allowVoid, $user, $closingDate, $definedDate, $fulfilled) {
    if($user != 3){
        if ($allowVoid) {  // admin allow void
            $dateNow = date('Y-m-d');
                if ($isPos == 1) {
                    if ($status != 0) {
                        if ((strtotime($dateNow) - strtotime($saleOrderDate)) / (60 * 60 * 24) < 45) {   // Void for POS within 30 days
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                } else {
                    if ($status == 1) {   // Void for sale order not yet delivery
                        return true;
                    }else{
                        return false;
                    }
                }
        } else {
            return false;
        }
    }else{
        return true;
    }
}

function in_object($val, $obj) {

    if ($val == "") {
        trigger_error("in_object expects parameter 1 must not empty", E_USER_WARNING);
        return false;
    }
    if (!is_object($obj)) {
        $obj = (object) $obj;
    }

    foreach ($obj as $key => $value) {
        if (!is_object($value) && !is_array($value)) {
            if ($value == $val) {
                return true;
            }
        } else {
            return in_object($val, $value);
        }
    }
    return false;
}

function thisMonth($datestr) {
    date_default_timezone_set(date_default_timezone_get());
    $dt = strtotime($datestr);
    $res['start'] = date('Y-m-d', strtotime('first day of this month', $dt));
    $res['end'] = date('Y-m-d', strtotime('last day of this month', $dt));
    return $res;
}

function thisWeek($datestr) {
    date_default_timezone_set(date_default_timezone_get());
    $dt = strtotime($datestr);
    $res['start'] = date('N', $dt) == 7 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('last sunday', $dt));
    $res['end'] = date('N', $dt) == 6 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('next saturday', $dt));
    return $res;
}

function lastWeek($datestr) {
    date_default_timezone_set(date_default_timezone_get());
    $dt = strtotime($datestr);
    $lastWeek = strtotime(date('Y-m-d', strtotime("-7 days", $dt)));
    $res['start'] = date('N', $lastWeek) == 7 ? date('Y-m-d', $lastWeek) : date('Y-m-d', strtotime('last sunday', $lastWeek));
    $res['end'] = date('N', $lastWeek) == 6 ? date('Y-m-d', $lastWeek) : date('Y-m-d', strtotime('next saturday', $lastWeek));
    return $res;
}

function getProductQty($qty){
    $return = false;
    $qtyPoint = explode(".",$qty);
    if(@$qtyPoint[1]){
        $length = strlen($qtyPoint[1]);
        if($length == 5 || $length == 6){
            $len = substr($qtyPoint[1], 0,3);
            if($len == 999){
               $return = true;
            }
        }else if($length == 1 || $length == 2 || $length == 3 || $length == 4){
            $len = substr($qtyPoint[1],0,1);;
            if($len == 9){
               $return = true; 
            }
        }else{
            $len = substr($qtyPoint[1],0,4);
            if($len == 9999){
               $return = true; 
            }
        }
        if($return == true){
            $value = number_format($qty,2);
        }else{
            $value = $qty;
        }
    }else{
        $value = $qty;
    }
    return $value;
}

function replaceThousand($value){
    $value = str_replace(",","",$value);
    return $value;
}

function convertDicemalUom($value){
    $number = explode(".",$value);
    if(@$number[1]){
        $num = ($value * 100000000000000);
        $num = explode(".", $num);
        if(@$num[1]){
            $value = $num[0] / 100000000000000;
        }
    }
    return $value;
}

function isAllowVoidAll($user, $closingDate, $orderDate, $defineDate) {
    $dateNow = date('Y-m-d');
    if (strtotime($dateNow) > strtotime($closingDate)) {
        return true;
    } else {
        return false;
    }
    
}
function showTotalQty($total_qty, $labelMainUom, $smallUom, $smallUomLabel) {
    $totalRemain = "";
    $totalMain = (int) ($total_qty / $smallUom);
    $checkRemain = (int) ($total_qty % $smallUom);
    if ($checkRemain != 0) {
        $totalRemain = ($total_qty - (int) ($totalMain * $smallUom)) . "" . $smallUomLabel;
    }
    return $totalMain . " " . $labelMainUom . " " . $totalRemain;
}

function roundPrice($total_price){
    $numberCon  = ($total_price * 10);
    $number     = explode(".", $numberCon);
    if(!empty($number[1])){
        if ($number[1] <= 5 && $number[1] > 0) {
            $total_price = ($number[0] . ".5");
        } elseif ($number[1] > 5 && $number[1] <= 9) {
            $total_price = ($number[0] + 1);
        } else {
            $total_price = $number[0];
        }
        $total_price = $total_price / 10;
    }else{
        $total_price = $total_price;
    }
    $total_price = str_replace(",", "", $total_price);
    return $total_price;
}

function getSysconfig(){
    $config = "";
    $fileConfig = "config/system_config.fg";
    if (file_exists($fileConfig)) {
        $handle   = fopen($fileConfig, "r");
        $contents = fread($handle, filesize($fileConfig));
        fclose($handle);
        $config   = json_decode($contents, true);
    }
    return $config;
}

function convertIntegerToWords($x){

    $nwords = array('zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 
                     'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 
                     'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 
                     'nineteen', 'twenty', 30 => 'thirty', 40 => 'forty', 
                     50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 
                     90 => 'ninety', '00' => ' US Dollar Only' );

     if(!is_numeric($x)) 
     { 
         $w = '#'; 
     }else if(fmod($x, 1) != 0) 
     { 
         $w = '#'; 
     }else{ 
         if($x < 0) 
         { 
             $w = 'minus '; 
             $x = -$x; 
         }else{ 
             $w = ''; 
         } 
         if($x < 21) 
         { 
             $w .= $nwords[$x]; 
         }else if($x < 100) 
         { 
             $w .= $nwords[10 * floor($x/10)]; 
             $r = fmod($x, 10); 
             if($r > 0) 
             { 
                 $w .= '-'. $nwords[$r]; 
             } 
         } else if($x < 1000) 
         { 
             $w .= $nwords[floor($x/100)] .' hundred'; 
             $r = fmod($x, 100); 
             if($r > 0) 
             { 
                 $w .= ' '. convertIntegerToWords($r); 
             } 
         } else if($x < 1000000) 
         { 
             $w .= convertIntegerToWords(floor($x/1000)) .' thousand'; 
             $r = fmod($x, 1000); 
             if($r > 0) 
             { 
                 $w .= ' '; 
                 if($r < 100) 
                 { 
                     $w .= ' '; 
                 } 
                 $w .= convertIntegerToWords($r); 
             } 
         } else { 
             $w .= convertIntegerToWords(floor($x/1000000)) .' million'; 
             $r = fmod($x, 1000000); 
             if($r > 0) 
             { 
                 $w .= ' '; 
                 if($r < 100) 
                 { 
                     $word .= ' '; 
                 } 
                 $w .= convertIntegerToWords($r); 
             } 
         } 
     } 
     return $w; 
}

function convertNumberToWords($number) {
    $number = replaceThousand($number);
    if(!is_numeric($number)) return false;
    $nums = explode('.', $number);
    $out  = convertIntegerToWords($nums[0]);
    $cent = (int) $nums[1];
    if(isset($cent)) {
        if(convertIntegerToWords($cent) != 'Only'){
            $out .= ' and ' . convertIntegerToWords($cent) .' cents US Dollar';
        }else{
            $out .= ' ' . convertIntegerToWords($cent);
        }
    }
    return $out;
}

function convertNumberToKhmerWords($number) {
    $number = replaceThousand($number);
    if(!is_numeric($number)) return false;
    $nums = explode('.', $number);
    $out  = convertIntegerToKhmerWords($nums[0]) . 'ដុល្លារ';
    $cent = (int) $nums[1];
    if(isset($cent)) {
        if(convertIntegerToKhmerWords($cent) != 'តែប៉ុណ្ណោះ'){
            $out .= ' និង' . convertIntegerToKhmerWords($cent) .'សេន';
        }else{
            $out .= ' ' . convertIntegerToKhmerWords($cent);
        }
    }
    return $out;
}

function convertMonthToKhmer($month){
    $monKhmer = '';
    switch ($month) {
        case 1: 
            $monKhmer = 'មករា';
            break;
        case 2: 
            $monKhmer = 'កុម្ភះ';
            break;
        case 3: 
            $monKhmer = 'មិនា';
            break;
        case 4: 
            $monKhmer = 'មេសា';
            break;
        case 5: 
            $monKhmer = 'ឪសភា';
            break;
        case 6: 
            $monKhmer = 'មិថុនា';
            break;
        case 7: 
            $monKhmer = 'កក្កដា';
            break;
        case 8: 
            $monKhmer = 'សីហា';
            break;
        case 9: 
            $monKhmer = 'កញ្ញា';
            break;
        case 10: 
            $monKhmer = 'តុលា';
            break;
        case 11: 
            $monKhmer = 'វិចិ្ឆកា';
            break;
        case 12: 
            $monKhmer = 'ធ្នូ';
            break;
    }
    return $monKhmer;
}

function getTermType($termTypeId){
    $sqlTermType = mysql_query("SELECT name FROM term_condition_types WHERE id = ".$termTypeId);
    $rowTermType = mysql_fetch_array($sqlTermType);
    return $rowTermType['name'];
}

function getTermOption($termTypeId, $termDefaultId){
    $result = '';
    $sqlTerm = mysql_query("SELECT id, name FROM term_conditions WHERE term_condition_type_id = ".$termTypeId." ORDER BY name ASC");
    while($rowTerm = mysql_fetch_array($sqlTerm)){
        $select = '';
        if($rowTerm['id'] == $termDefaultId){
            $select  = 'selected="selected"';
        }
        $result .= '<option value="'.$rowTerm['id'].'" '.$select.'>'.$rowTerm['name'].'</option>';
    }
    return $result;
}

function dateDiff($start, $end) {
  $start_ts = strtotime($start);
  $end_ts = strtotime($end);
  $diff = $end_ts - $start_ts;
  return round($diff / 86400);
}

function arraySortBy($field, &$array, $direction = 'asc') {
	usort($array, create_function('$a, $b', '
		$a = $a["' . $field . '"];
		$b = $b["' . $field . '"];

		if ($a == $b)
		{
			return 0;
		}

		return ($a ' . ($direction == 'desc' ? '>' : '<') .' $b) ? -1 : 1;
	'));

	return true;
}

function getIntroduction($controllers, $dashboard, $user, $variable, $functionName){
    $result = '';
    $introdution = array();
    $sqlFunc = mysql_query("SELECT id, module, description_en, description_kh, element, position FROM module_introduces WHERE id NOT IN (SELECT introduce_id FROM user_introduces WHERE user_id = ".$user.") AND controllers = '".$controllers."' AND pages = '".$dashboard."' ORDER BY step ASC;");
//    if(mysql_num_rows($sqlFunc)){
//        while($rowFunc = mysql_fetch_array($sqlFunc)){
//            $sqlCheckPermission = mysql_query("SELECT id FROM permissions WHERE group_id = (SELECT group_id FROM user_groups WHERE user_id = ".$user." LIMIT 1) AND module_id = (SELECT id FROM modules WHERE sys_code = '".$rowFunc['module']."' LIMIT 1) LIMIT 1");
//            if(mysql_num_rows($sqlCheckPermission)){
//                $introdution[] = '{element: "'.$rowFunc['element'].'", intro: "'.$rowFunc['description_en'].'", position: "'.$rowFunc['position'].'"}';
//                mysql_query("INSERT INTO `user_introduces` (`user_id`, `introduce_id`) VALUES (".$user.", ".$rowFunc['id'].");");
//            }
//        }
//    }
    if(!empty($introdution)){
        $introdution = implode(",", $introdution);
    } else {
        $introdution = '';
    }
    $result .= 'function '.$functionName.'() {';
    if($introdution != ''){
        $result .= 'var '.$variable.' = introJs();'.$variable.'.setOptions({steps:['.$introdution.']});'.$variable.'.start();';
    }
    $result .= '}';
    return $result;
}

function getDateByDateRange($dateRange, $format='Y-m-d'){
    $dateReturn = array();
    $dateNow =  date("Y-m-d");
    $week    =  date('W', strtotime($dateNow));
    $year    =  date('Y', strtotime($dateNow));
    switch ($dateRange){
        case "Today":
            $dateReturn[0] = date($format);
            $dateReturn[1] = date($format);
            break;
        case "ThisWeek":
            $dateReturn[0] = date($format, strtotime("{$year}-W{$week}-1". ' - 1 day'));
            $dateReturn[1] = date($format, strtotime("{$year}-W{$week}-7". ' - 1 day'));
            break;
        case "ThisWeekToDate":
            $dateReturn[0] = date($format, strtotime("{$year}-W{$week}-1". ' - 1 day'));
            $dateReturn[1] = date($format);
            break;
        case "ThisMonth":
            $dateReturn[0] = date($format, strtotime('first day of this month'));
            $dateReturn[1] = date($format, strtotime('last day of this month'));
            break;
        case "ThisQuarter":
            $getQuarterDate = getQuarter();
            $dateReturn[0]  = $getQuarterDate['start'];
            $dateReturn[1]  = $getQuarterDate['end'];
            break;
        case "ThisYear":
            $dateReturn[0] = date($format, strtotime("{$year}-01-01"));
            $dateReturn[1] = date($format, strtotime("{$year}-12-01"));
            break;
        case "LastWeek":
            $weekNum = $week - 1;
            $week = str_pad($weekNum, 2, '0', STR_PAD_LEFT);
            $dateReturn[0] = date($format, strtotime("{$year}-W{$week}-1". ' - 1 day'));
            $dateReturn[1] = date($format, strtotime("{$year}-W{$week}-7". ' - 1 day'));
            break;
        case "LastWeekToDate":
            $weekNum = $week - 1;
            $week = str_pad($weekNum, 2, '0', STR_PAD_LEFT);
            $dateReturn[0] = date($format, strtotime("{$year}-W{$week}-1". ' - 1 day'));
            $dateReturn[1] = date($format);
            break;
        case "LastMonth":
            $dateReturn[0] = date($format, strtotime('first day of last month'));
            $dateReturn[1] = date($format, strtotime('last day of last month'));
            break;
        case "LastQuarter":
            $getQuarterDate = getQuarter(1);
            $dateReturn[0]  = $getQuarterDate['start'];
            $dateReturn[1]  = $getQuarterDate['end'];
            break;
        case "LastYear":
            $year = $year - 1;
            $dateReturn[0] = date($format, strtotime("{$year}-01-01"));
            $dateReturn[1] = date($format, strtotime("{$year}-12-01"));
            break;
    }
    return $dateReturn;
}

function getQuarter($i=0, $year = '') {
        if($year == ''){
            $y = date('Y');
        } else {
            $y = $year;
        }
	$m = date('m');
	if($i > 0) {
            for($x = 0; $x < $i; $x++) {
                if($m <= 3) { $y--; }
                $diff = $m % 3;
                $m = ($diff > 0) ? $m - $diff:$m-3;
                if($m == 0) { $m = 12; }
            }
	}
	switch($m) {
            case $m >= 1 && $m <= 3:
                    $start = $y.'-01-01';
                    $end = $y.'-03-31';
                    break;
            case $m >= 4 && $m <= 6:
                    $start = $y.'-04-01';
                    $end = $y.'-06-30';
                    break;
            case $m >= 7 && $m <= 9:
                    $start = $y.'-07-01';
                    $end = $y.'-09-30';
                    break;
            case $m >= 10 && $m <= 12:
                    $start = $y.'-10-01';
                    $end = $y.'-12-31';
                    break;
	}
	return array(
		'start' => $start,
		'end' => $end						
	);
}

function listDays($startDate, $endDate, $format='Y-m-d'){  
    $sStartDate = date("Y-m-d", strtotime($startDate));  
    $sEndDate = date("Y-m-d", strtotime($endDate));  
    $aDays[] = $sStartDate;  
    $sCurrentDate = $sStartDate;  
    while($sCurrentDate < $sEndDate){  
        $sCurrentDate = date("Y-m-d", strtotime("+1 day", strtotime($sCurrentDate)));   
        $aDays[] = date($format, strtotime($sCurrentDate));  
    }   
    return $aDays;  
}

function calculateTotalAmountKh($totalAmount, $exchageRate) {
    $totalAmountKh = 0;
    $amountKh = $totalAmount * $exchageRate;
    $amountKh = $amountKh / 100;
    $numString = explode(".", $amountKh);
    if (@$numString[1] || @$numString[1] != '') {
        if ($numString[1] > 0) {
            $totalAmountKh = ($numString[0] + 1) * 100;
        } else {
            $totalAmountKh = $totalAmount * $exchageRate;
        }
    } else {
        $totalAmountKh = $totalAmount * $exchageRate;
    }
    return $totalAmountKh;
}

function checkDateTransaction($branchId){
    $timeNow    = (int) date("H");
    $sqlBranch  = mysql_query("SELECT HOUR(work_start) FROM branches WHERE id = ".$branchId);
    $rowBranch  = mysql_fetch_array($sqlBranch);
    $brachStart = (int) $rowBranch[0];
    $timeFrom   = array();
    for($i=$brachStart; $i < 24; $i++){
        $timeFrom[$i] = $i;
    }
    $return  = date("Y-m-d", strtotime("-1 day", strtotime(date("Y-m-d"))));
    if(array_key_exists($timeNow, $timeFrom)){
        $return  = date("Y-m-d");
    }
    return $return;
}

function displayQtyByUoM($totalQty, $uomId, $smallUomVal, $mainUomLabel){
    $display = "";
    if($smallUomVal == 1){
        $display = number_format($totalQty, 0).$mainUomLabel;
    } else {
        // Calculate Main
        $totalRemain = 0;
        $totalMain   = (int) ($totalQty / $smallUomVal);
        $display    .= number_format($totalMain, 0).$mainUomLabel;
        $checkRemain = (int) ($totalQty % $smallUomVal);
        if ($checkRemain != 0) {
            $totalRemain = ($totalQty - (int) ($totalMain * $smallUomVal));
            $sqlUom = mysql_query("SELECT uom_conversions.value, uoms.abbr FROM uom_conversions INNER JOIN uoms ON uoms.id = uom_conversions.to_uom_id WHERE uom_conversions.from_uom_id = ".$uomId." AND uom_conversions.is_active = 1 ORDER BY uom_conversions.value ASC");
            while($rowUom = mysql_fetch_array($sqlUom)){
                if($totalRemain > 0){
                    $smallVal  = $smallUomVal / $rowUom['value'];
                    $totalShow = (int) ($totalRemain / $smallVal);
                    if($totalShow > 0){
                        $checkRemain = (int) ($totalRemain % $smallVal);
                        if($checkRemain != 0){
                            $totalRemain = ($totalRemain - (int) ($totalShow * $smallVal));
                        }
                    }
                    $display .= " ".number_format($totalShow, 0).$rowUom['abbr'];
                } else {
                    $display .= " 0".$rowUom['abbr'];
                }
            }
        }
    }
    return $display;
}

function getProductRelation($pid, $typeId){
    $name = "N/A";
    if($typeId == 1){
        // Sub-Sub Category
        $queryPgroup = mysql_query("SELECT name FROM pgroups WHERE id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = '".$pid."') LIMIT 01");
        if(mysql_num_rows($queryPgroup)){
            $dataPgroup = mysql_fetch_array($queryPgroup);
            $name = $dataPgroup['name'];
        }
    }else if($typeId == 2){
        // Sub Category
        $queryPgroup = mysql_query("SELECT (SELECT p.name FROM pgroups AS p WHERE p.id = pgroups.parent_id LIMIT 01) AS name FROM pgroups WHERE id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = '".$pid."') LIMIT 01");
        if(mysql_num_rows($queryPgroup)){
            $dataPgroup = mysql_fetch_array($queryPgroup);
            $name = $dataPgroup['name'];
        }
    }else if($typeId == 3){
        // Category
        $queryPgroup = mysql_query("SELECT (SELECT p.parent_id FROM pgroups AS p WHERE p.id = pgroups.parent_id LIMIT 01) AS parent_id FROM pgroups WHERE id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = '".$pid."')");
        if(mysql_num_rows($queryPgroup)){
            $dataPgroup = mysql_fetch_array($queryPgroup);
            $queryCategory = mysql_query("SELECT name FROM pgroups WHERE id = '".$dataPgroup['parent_id']."' LIMIT 01");
            if(mysql_num_rows($queryCategory)){
                $dataCategory = mysql_fetch_array($queryCategory);
                $name = $dataCategory['name'];
            }
        }
    }else{
        // Department
        $queryPgroup = mysql_query("SELECT (SELECT name FROM departments WHERE id = department_id LIMIT 01) AS name FROM pgroups WHERE id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = '".$pid."') LIMIT 01");
        if(mysql_num_rows($queryPgroup)){
            $dataPgroup = mysql_fetch_array($queryPgroup);
            $name = $dataPgroup['name'];
        }
    }
    return $name;
}

?>
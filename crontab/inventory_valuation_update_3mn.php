<?php
$pid = getmypid();
date_default_timezone_set('Asia/Phnom_Penh');
$cn = mysql_connect('localhost', 'root', '') or die(mysql_error());
mysql_select_db('', $cn);
mysql_query("SET character_set_client=utf8", $cn);
mysql_query("SET character_set_connection=utf8", $cn);
mysql_query("SET NAMES 'utf8'", $cn);

function replaceThousand($value){
    $value = str_replace(",","",$value);
    return $value;
}

function inventoryValuation($pid) {
    $queryTrack = mysql_query("SELECT id,val,is_recalculate,is_recalculate_process,pid,date_start FROM tracks WHERE id=1");
    $dataTrack  = mysql_fetch_array($queryTrack);
    if ($dataTrack['is_recalculate_process'] == 0 && $dataTrack['is_recalculate'] == 1) {
        mysql_query("UPDATE tracks SET is_recalculate=0, pid = ".$pid.", is_recalculate_process = 1, date_start='".date("Y-m-d H:i:s")."', date_end=NULL WHERE id = 1");
        $cal_date             = $dataTrack['val'];
        $acc_total_cost       = array();
        $acc_total_qty        = array();
        $acc_total_qty_small  = array();
        $old_avg_cost         = array();
        $sqlCompany           = mysql_query("SELECT id FROM companies WHERE is_active = 1");
        while($rowCompany = mysql_fetch_array($sqlCompany)){
            $queryPid             = mysql_query("SELECT DISTINCT pid FROM inventory_valuations WHERE company_id = ".$rowCompany['id']." AND is_active = 1");
//            $count                = 0;
//            mysql_query("START TRANSACTION");
            while ($dataPid = mysql_fetch_array($queryPid)) {
                // Check Recalculate Set True(1)
                $recheck_cal_date_query = mysql_query("SELECT id FROM tracks WHERE id = 1 AND is_recalculate = 1");
                if(mysql_num_rows($recheck_cal_date_query)){
                    mysql_query("UPDATE tracks SET is_recalculate_process = 0, date_end='".date("Y-m-d H:i:s")."' WHERE id = 1");
                    break;
                }
//                $count++;
//                if ($count % 10000 == 0) {
//                    mysql_query("COMMIT");
//                    mysql_query("START TRANSACTION");
//                }
                // Get Value of Last Record
                $queryInit = mysql_query("SELECT pid,on_hand,on_hand_small,avg_cost,asset_value FROM inventory_valuations
                                          WHERE company_id = ".$rowCompany['id']." AND is_active=1
                                          AND date < '".$cal_date."'
                                          AND pid = ".$dataPid[0]."
                                          ORDER BY date DESC,created DESC, id DESC LIMIT 1");
                if (mysql_num_rows($queryInit)) {
                    $dataInit                   = mysql_fetch_array($queryInit);
                    $pid                        = "pid" . $dataInit['pid'];
                    $acc_total_cost[$pid]       = $dataInit['asset_value'];
                    $acc_total_qty[$pid]        = $dataInit['on_hand'];
                    $acc_total_qty_small[$pid]  = $dataInit['on_hand_small'];
                    $old_avg_cost[$pid]         = $dataInit['avg_cost'];
                }
                // List Record Calculate AVG Cost
                $query = mysql_query("SELECT inv.id AS id, inv.is_var_cost AS is_var_cost, inv.is_adjust_value AS is_adjust_value,inv.pid AS pid, inv.small_qty AS small_qty, inv.qty AS qty, inv.date AS date, inv.cost AS cost, inv.price AS price,
                                      inv.on_hand, inv.on_hand_small, inv.avg_cost, inv.asset_value, p.small_val_uom AS small_val_uom
                                      FROM inventory_valuations AS inv INNER JOIN products AS p ON p.id = inv.pid
                                      WHERE inv.company_id = ".$rowCompany['id']." AND inv.is_active=1
                                      AND inv.date >= '" . $cal_date . "' AND inv.pid   = '" . $dataPid[0] . "'
                                      ORDER BY inv.date,inv.created,inv.id");
                while ($data = mysql_fetch_array($query)) {
                    $pid = "pid" . $data['pid'];

                    if (!isset($acc_total_cost[$pid])) {
                        $acc_total_cost[$pid] = 0;
                    }

                    if (!isset($acc_total_qty[$pid])) {
                        $acc_total_qty[$pid] = 0;
                    }
                    if (!isset($acc_total_qty_small[$pid])) {
                        $acc_total_qty_small[$pid] = 0;
                    }

                    if (!isset($old_avg_cost[$pid])) {
                        $queryDefaultCost   = mysql_query("SELECT default_cost FROM products WHERE id=" . $data['pid']);
                        $dataDefaultCost    = mysql_fetch_array($queryDefaultCost);
                        $old_avg_cost[$pid] = $dataDefaultCost['default_cost'];
                    }

                    if ($data['is_adjust_value'] == 1) {
                        $acc_total_cost[$pid]       = $data['asset_value'];
                        $acc_total_qty[$pid]       += $data['qty'];
                        $acc_total_qty_small[$pid] += $data['small_qty'];
                        $onHand      = replaceThousand(number_format($acc_total_qty[$pid], 9));
                        $onHandSmall = replaceThousand(number_format($acc_total_qty_small[$pid], 9));
                        $cost        = replaceThousand(number_format(($acc_total_cost[$pid] / $acc_total_qty[$pid]), 9));
                        $avgCost     = replaceThousand(number_format(($acc_total_cost[$pid] / $acc_total_qty[$pid]), 9));
                        $assetVal    = replaceThousand(number_format($acc_total_cost[$pid], 9));
                        mysql_query("UPDATE inventory_valuations SET
                                     on_hand           = '" . $onHand . "',
                                     on_hand_small     = '" . $onHandSmall . "',
                                     cost              = '" . preg_replace('/[-?]/', '',$cost) . "',
                                     avg_cost          = '" . preg_replace('/[-?]/', '',$avgCost) . "',
                                     asset_value       = '" . $assetVal . "'
                                     WHERE id          = " . $data['id']) or die(mysql_error());
                    } else if ($data['is_var_cost'] == 1) {
                        $glDetailVal                 = replaceThousand(number_format(($data['qty'] * $old_avg_cost[$pid]), 12));
                        $acc_total_cost[$pid]       += $data['qty'] * $old_avg_cost[$pid];
                        $acc_total_qty[$pid]        += $data['qty'];
                        $acc_total_qty_small[$pid]  += $data['small_qty'];
                        $onHand      = replaceThousand(number_format($acc_total_qty[$pid], 9));
                        $onHandSmall = replaceThousand(number_format($acc_total_qty_small[$pid], 9));
                        $cost        = replaceThousand(number_format($old_avg_cost[$pid], 9));
                        $avgCost     = replaceThousand(number_format($old_avg_cost[$pid], 9));
                        $assetVal    = replaceThousand(number_format($acc_total_cost[$pid], 9));
                        mysql_query("UPDATE inventory_valuations SET
                                     on_hand           = '" . $onHand . "',
                                     on_hand_small     = '" . $onHandSmall . "',
                                     cost              = '" . preg_replace('/[-?]/', '',$cost) . "',
                                     avg_cost          = '" . preg_replace('/[-?]/', '',$avgCost) . "',
                                     asset_value       = '" . $assetVal . "'
                                     WHERE id          = " . $data['id']) or die(mysql_error());
                        mysql_query("UPDATE general_ledger_details SET credit='" . preg_replace('/[-?]/', '',$glDetailVal) . "',debit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=0 AND credit != '" . preg_replace('/[-?]/', '',$glDetailVal) . "'") or die(mysql_error());
                        if ($data['price'] != '') {
                            $cogs = replaceThousand(number_format(preg_replace('/[-?]/', '',$data['qty'] * $data['price']) - preg_replace('/[-?]/', '',$data['qty'] * $old_avg_cost[$pid]), 12));
                            if ($cogs > 0) {
                                mysql_query("UPDATE general_ledger_details SET credit='" . preg_replace('/[-?]/', '',$cogs) . "',debit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=1 AND credit != '" . preg_replace('/[-?]/', '',$cogs) . "'") or die(mysql_error());
                            } else if ($cogs < 0) {
                                mysql_query("UPDATE general_ledger_details SET debit='" . preg_replace('/[-?]/', '',$cogs) . "',credit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=1 AND debit != '" . preg_replace('/[-?]/', '',$cogs) . "'") or die(mysql_error());
                            } else {
                                mysql_query("UPDATE general_ledger_details SET debit=0,credit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=1 AND debit != '0'") or die(mysql_error());
                            }
                        } else {
                            $cogs = replaceThousand(number_format($data['qty'] * $old_avg_cost[$pid], 12));
                            mysql_query("UPDATE general_ledger_details SET debit='" .preg_replace('/[-?]/', '',$cogs). "',credit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit = 1 AND debit != '" .preg_replace('/[-?]/', '',$cogs). "'") or die(mysql_error());
                        }
                    } else {
                        $acc_total_cost[$pid]       += $data['qty'] * $data['cost'];
                        $acc_total_qty[$pid]        += $data['qty'];
                        $acc_total_qty_small[$pid]  += $data['small_qty'];
                        $onHand      = replaceThousand(number_format($acc_total_qty[$pid], 9));
                        $onHandSmall = replaceThousand(number_format($acc_total_qty_small[$pid], 9));
                        $avgCost     = replaceThousand(number_format(($acc_total_cost[$pid] / $acc_total_qty[$pid]), 9));
                        $assetVal    = replaceThousand(number_format($acc_total_cost[$pid], 9));
                        mysql_query("UPDATE inventory_valuations SET
                                     on_hand           ='" . $onHand . "',
                                     on_hand_small     ='" . $onHandSmall . "',
                                     avg_cost          ='" . preg_replace('/[-?]/', '',$avgCost) . "',
                                     asset_value       ='" . $assetVal . "'
                                     WHERE id    =" . $data['id']) or die(mysql_error());
                    }
                    if ($acc_total_cost[$pid] != 0 || $acc_total_qty[$pid] != 0) {
                        $old_avg_cost[$pid] = @($acc_total_cost[$pid] / $acc_total_qty[$pid]);
                    }
                }
            }
        }
//        mysql_query("COMMIT");
        $queryCheck = mysql_query("SELECT * FROM tracks WHERE id=1");
        $dataCheck  = mysql_fetch_array($queryCheck);
        if($dataCheck['is_recalculate'] == 0){
            mysql_query("UPDATE tracks SET val = '".date("Y-m-d")."', is_recalculate_process = 0, date_end='".date("Y-m-d H:i:s")."' WHERE id = 1");
        } else {
            mysql_query("UPDATE tracks SET is_recalculate_process = 0, date_end='".date("Y-m-d H:i:s")."' WHERE id = 1");
        }
    } else {
        if(!empty($is_processing[4])){
            exec("ps -p ".$is_processing[4], $output);
            if (count($output) > 1) {
                if(!empty($is_processing[5]) && $is_processing[5] != '0000-00-00 00:00:00'){
                    // Compare Proccess more then one hours
                    $timeNow   = strtotime(date("Y-m-d H:i:s")); 
                    $timeStart = strtotime($is_processing[5]) + 7200;
                    if($timeNow > $timeStart){
                        exec("kill -9 ".$is_processing[4]);
                        // Update Proccess Sync
                        mysql_query("UPDATE `tracks` SET `is_recalculate_process`=0, pid = NULL, `date_end` = '".date("Y-m-d H:i:s")."' WHERE id = 1;");
                    }
                }
                exit;
            } else {
                // Update Proccess Sync
                mysql_query("UPDATE `tracks` SET `is_recalculate_process`=0, pid = NULL, `date_end` = '".date("Y-m-d H:i:s")."' WHERE id = 1;");
            }
        }
    }
}

inventoryValuation($pid);
?>

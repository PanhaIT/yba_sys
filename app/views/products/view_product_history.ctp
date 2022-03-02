<table width="100%" class="table" cellspacing="0">
    <thead>
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th><?php echo TABLE_TYPE; ?></th>
            <th><?php echo TABLE_DATE; ?></th>
            <th><?php echo TABLE_CODE; ?></th>
            <th><?php echo TABLE_CREATED; ?></th>
            <th><?php echo TABLE_CREATED_BY; ?></th>
        </tr>
    </thead>
    <tbody>
        <?php         
        include("includes/function.php");
        $queryProductInUsed = mysql_query("SELECT SUM(qty) AS qty FROM inventories WHERE product_id=" . $productId." GROUP BY product_id");
        if (mysql_num_rows($queryProductInUsed)){
            ?>
            <td colspan="7" class="dataTables_empty first"><?php echo MESSAGE_DATA_HAS_ACTIVITY_STOCK; ?></td>
            <?php
        } else {
            $index = 1;
            // Quotation            
            $exc = mysql_query("SELECT quotations.*,(SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=quotations.created_by) created_name FROM quotations INNER JOIN quotation_details ON quotations.id = quotation_details.quotation_id WHERE quotations.status >= 1 AND quotation_details.product_id=" . $productId." GROUP BY quotations.id");
            while($data = mysql_fetch_array($exc)){
                ?>
                <tr>
                    <td class="first"><?php echo $index++; ?></td>
                    <td><?php echo "Quotation"; ?></td>
                    <td><?php echo dateShort($data['quotation_date']); ?></td>
                    <td><?php echo $data['quotation_code']; ?></td>                  
                    <td><?php echo $data['created']; ?></td>
                    <td><?php echo $data['created_name']; ?></td>
                </tr>
                <?php
                
            }
            
            // Sales Order            
            $exc = mysql_query("SELECT orders.*,(SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=orders.created_by) created_name FROM orders INNER JOIN order_details ON orders.id = order_details.order_id WHERE orders.status >= 1 AND order_details.product_id = " . $productId." GROUP BY orders.id");
            while($data = mysql_fetch_array($exc)){
                ?>
                <tr>
                    <td class="first"><?php echo $index++; ?></td>
                    <td><?php echo "Sales Order"; ?></td>
                    <td><?php echo dateShort($data['order_date']); ?></td>
                    <td><?php echo $data['order_code']; ?></td>                  
                    <td><?php echo $data['created']; ?></td>
                    <td><?php echo $data['created_name']; ?></td>
                </tr>
                <?php                
            }
            
            // Create Memo           
            $exc = mysql_query("SELECT credit_memos.*,(SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=credit_memos.created_by) created_name FROM credit_memos INNER JOIN credit_memo_details ON credit_memos.id = credit_memo_details.credit_memo_id WHERE credit_memos.status >= 1 AND credit_memo_details.product_id=" . $productId." GROUP BY credit_memos.id");
            while($data = mysql_fetch_array($exc)){
                ?>
                <tr>
                    <td class="first"><?php echo $index++; ?></td>
                    <td><?php echo "Credit Memo"; ?></td>
                    <td><?php echo dateShort($data['order_date']); ?></td>
                    <td><?php echo $data['cm_code']; ?></td>                  
                    <td><?php echo $data['created']; ?></td>
                    <td><?php echo $data['created_name']; ?></td>
                </tr>
                <?php
            }
            
            // Transfer Order         
            $exc = mysql_query("SELECT request_stocks.*,(SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=request_stocks.created_by) created_name FROM request_stocks INNER JOIN request_stock_details ON request_stocks.id = request_stock_details.request_stock_id WHERE request_stocks.status >= 1 AND request_stock_details.product_id=" . $productId." GROUP BY request_stocks.id");
            while($data = mysql_fetch_array($exc)){
                ?>
                <tr>
                    <td class="first"><?php echo $index++; ?></td>
                    <td><?php echo "Request Stock"; ?></td>
                    <td><?php echo dateShort($data['date']); ?></td>
                    <td><?php echo $data['code']; ?></td>                  
                    <td><?php echo $data['created']; ?></td>
                    <td><?php echo $data['created_name']; ?></td>
                </tr>
                <?php
            }
            
            // Inventory Adjustment       
            $exc = mysql_query("SELECT cycle_products.*,(SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=cycle_products.created_by) created_name FROM cycle_products INNER JOIN cycle_product_details ON cycle_products.id = cycle_product_details.cycle_product_id WHERE cycle_products.status >= 1 AND cycle_product_details.product_id=" . $productId." GROUP BY cycle_products.id");
            while($data = mysql_fetch_array($exc)){
                ?>
                <tr>
                    <td class="first"><?php echo $index++; ?></td>
                    <td><?php echo "Adjustment"; ?></td>
                    <td><?php echo dateShort($data['date']); ?></td>
                    <td><?php echo $data['reference']; ?></td>                  
                    <td><?php echo $data['created']; ?></td>
                    <td><?php echo $data['created_name']; ?></td>
                </tr>
                <?php
            }
            
            // Purchase Request    
            $exc = mysql_query("SELECT purchase_requests.*,(SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=purchase_requests.created_by) created_name FROM purchase_requests INNER JOIN purchase_request_details ON purchase_requests.id = purchase_request_details.purchase_request_id WHERE purchase_requests.status >= 1 AND purchase_request_details.product_id=" . $productId." GROUP BY purchase_requests.id");
            while($data = mysql_fetch_array($exc)){
                ?>
                <tr>
                    <td class="first"><?php echo $index++; ?></td>
                    <td><?php echo "Purchase Order"; ?></td>
                    <td><?php echo dateShort($data['order_date']); ?></td>
                    <td><?php echo $data['pr_code']; ?></td>                  
                    <td><?php echo $data['created']; ?></td>
                    <td><?php echo $data['created_name']; ?></td>
                </tr>
                <?php
            }
            
            // Purchase Bill
            $exc = mysql_query("SELECT purchase_orders.*,(SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=purchase_orders.created_by) created_name FROM purchase_orders INNER JOIN purchase_order_details ON  purchase_orders.id = purchase_order_details.purchase_order_id WHERE  purchase_orders.status >= 1 AND purchase_order_details.product_id=" . $productId." GROUP BY purchase_orders.id");
            while($data = mysql_fetch_array($exc)){
                ?>
                <tr>
                    <td class="first"><?php echo $index++; ?></td>
                    <td><?php echo "Purchase Bill"; ?></td>
                    <td><?php echo dateShort($data['order_date']); ?></td>
                    <td><?php echo $data['po_code']; ?></td>                  
                    <td><?php echo $data['created']; ?></td>
                    <td><?php echo $data['created_name']; ?></td>
                </tr>
                <?php
            }
        }
        ?>
    </tbody>
</table>
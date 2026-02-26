<?php
// product_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["action"]))
{
    // --- FETCH DATA FOR DATATABLE ---
    if($_POST["action"] == 'fetch')
    {
        $order_column = array('product_image', 'product_name', 'product_price', 'category_name', 'product_status');

        $main_query = "SELECT * FROM product_table ";
        $search_query = '';

        if(isset($_POST["search"]["value"]) && $_POST["search"]["value"] != '')
        {
            $search_query .= 'WHERE (category_name LIKE "%'.$_POST["search"]["value"].'%" ';
            $search_query .= 'OR product_name LIKE "%'.$_POST["search"]["value"].'%" ';
            $search_query .= 'OR product_price LIKE "%'.$_POST["search"]["value"].'%") ';
        }

        if(isset($_POST["order"]))
        {
            $order_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
        }
        else
        {
            $order_query = 'ORDER BY product_id DESC ';
        }

        $limit_query = '';
        if($_POST["length"] != -1)
        {
            $limit_query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
        }

        $object->query = $main_query . $search_query . $order_query;
        $object->execute();
        $filtered_rows = $object->row_count();

        $object->query .= $limit_query;
        $result = $object->get_result();

        $object->query = $main_query;
        $object->execute();
        $total_rows = $object->row_count();

        $data = array();

        foreach($result as $row)
        {
            $sub_array = array();
            
            // 1. Image Preview
            if(!empty($row["product_image"]))
            {
                $sub_array[] = '<img src="images/'.$row["product_image"].'" class="product-img-circle" style="width:50px; height:50px; border-radius:10px; object-fit:cover;" />';
            }
            else
            {
                $sub_array[] = '<div style="width:50px; height:50px; background:#333; border-radius:10px; display:flex; align-items:center; justify-content:center;"><i class="fas fa-image text-muted"></i></div>';
            }

            // 2. Name
            $sub_array[] = html_entity_decode($row["product_name"]);

            // 3. Price
            $sub_array[] = $object->cur . $row["product_price"];

            // 4. Category
            $sub_array[] = $row["category_name"];

            // 5. Status
            $status = ($row["product_status"] == 'Enable') ? 
                '<button type="button" class="btn btn-primary btn-sm status_button" data-id="'.$row["product_id"].'" data-status="Enable">Enable</button>' : 
                '<button type="button" class="btn btn-danger btn-sm status_button" data-id="'.$row["product_id"].'" data-status="Disable">Disable</button>';
            $sub_array[] = $status;

            // 6. Action
            $sub_array[] = '
            <div align="center">
                <button type="button" class="btn btn-warning btn-circle btn-sm edit_button" data-id="'.$row["product_id"].'"><i class="fas fa-edit"></i></button>
                &nbsp;
                <button type="button" class="btn btn-danger btn-circle btn-sm delete_button" data-id="'.$row["product_id"].'"><i class="fas fa-times"></i></button>
            </div>';
            
            $data[] = $sub_array;
        }

        $output = array(
            "draw"              =>  intval($_POST["draw"]),
            "recordsTotal"      =>  $total_rows,
            "recordsFiltered"   =>  $filtered_rows,
            "data"              =>  $data
        );
        
        ob_clean(); 
        echo json_encode($output);
        exit;
    }

    // --- FETCH SINGLE PRODUCT FOR EDIT (FIXED WARNING) ---
    if($_POST["action"] == 'fetch_single')
    {
        ob_clean();
        $object->query = "SELECT * FROM product_table WHERE product_id = '".$_POST["product_id"]."'";
        $result = $object->get_result();
        $data = array();
        foreach($result as $row)
        {
            $data['category_name'] = $row['category_name'];
            $data['product_name']  = html_entity_decode($row['product_name']);
            $data['product_price'] = $row['product_price'];
            $data['product_image'] = $row['product_image'];
            // Fixed: Null coalescing to prevent "Undefined array key" warning
            $data['tax_ids']       = $row['product_tax'] ?? ''; 
        }
        echo json_encode($data);
        exit;
    }

    // --- ADD or EDIT PRODUCT ---
    if($_POST["action"] == 'Add' || $_POST["action"] == 'Edit')
    {
        $error = '';
        $success = '';

        $product_name  = $object->clean_input($_POST["product_name"]);
        $category_name = $_POST["category_name"];
        $product_price = $_POST["product_price"];
        $tax_ids       = isset($_POST["tax_ids"]) ? implode(",", $_POST["tax_ids"]) : '';
        
        if($_POST["action"] == 'Add')
        {
            $object->query = "SELECT * FROM product_table WHERE category_name = '$category_name' AND product_name = '$product_name'";
            $object->execute();

            if($object->row_count() > 0)
            {
                $error = 'Product Already Exists';
            }
            else
            {
                $product_image = '';
                if(isset($_FILES["product_image"]) && $_FILES["product_image"]["name"] != '')
                {
                    $product_image = time() . '_' . $_FILES["product_image"]["name"];
                    move_uploaded_file($_FILES["product_image"]["tmp_name"], 'images/' . $product_image);
                }

                $object->query = "
                INSERT INTO product_table 
                (category_name, product_name, product_price, product_tax, product_status, product_image) 
                VALUES ('$category_name', '$product_name', '$product_price', '$tax_ids', 'Enable', '$product_image')
                ";
                $object->execute();
                $success = 'Product Added';
            }
        }

        if($_POST["action"] == 'Edit')
        {
            $product_id = $_POST["hidden_id"];
            $image_update = "";
            if(isset($_FILES["product_image"]) && $_FILES["product_image"]["name"] != '')
            {
                $product_image = time() . '_' . $_FILES["product_image"]["name"];
                move_uploaded_file($_FILES["product_image"]["tmp_name"], 'images/' . $product_image);
                $image_update = ", product_image = '".$product_image."'";
            }

            $object->query = "
            UPDATE product_table 
            SET category_name = '$category_name', 
                product_name = '$product_name', 
                product_price = '$product_price', 
                product_tax = '$tax_ids' 
                $image_update
            WHERE product_id = '$product_id'
            ";
            $object->execute();
            $success = 'Product Updated';
        }

        ob_clean();
        echo json_encode(['error' => $error, 'success' => $success]);
        exit;
    }

    // --- CHANGE STATUS ---
    if($_POST["action"] == 'change_status')
    {
        $object->query = "UPDATE product_table SET product_status = '".$_POST['next_status']."' WHERE product_id = '".$_POST["id"]."'";
        $object->execute();
        echo '<div class="alert alert-success">Status changed to '.$_POST['next_status'].'</div>';
        exit;
    }

    // --- DELETE PRODUCT ---
    if($_POST["action"] == 'delete')
    {
        $object->query = "DELETE FROM product_table WHERE product_id = '".$_POST["id"]."'";
        $object->execute();
        echo '<div class="alert alert-success">Product Deleted</div>';
        exit;
    }
}
?>
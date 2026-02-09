<?php
// category_action.php

include('rms.php');
$object = new rms();

if (isset($_POST["action"])) {

    /* ================= FETCH ================= */
    if ($_POST["action"] == 'fetch') {

        $order_column = array('category_name', 'category_status');
        $output = array();

        $main_query = "SELECT * FROM product_category_table ";
        $search_query = '';
        $order_query = '';
        $limit_query = '';

        if (!empty($_POST["search"]["value"])) {
            $search_query .= 'WHERE (category_name LIKE "%' . $_POST["search"]["value"] . '%" ';
            $search_query .= 'OR category_status LIKE "%' . $_POST["search"]["value"] . '%") ';
        }

        if (isset($_POST["order"])) {
            $order_query = 'ORDER BY ' . $order_column[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'] . ' ';
        } else {
            $order_query = 'ORDER BY category_id DESC ';
        }

        if ($_POST["length"] != -1) {
            $limit_query = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
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

        foreach ($result as $row) {
            $sub_array = array();
            $sub_array[] = html_entity_decode($row["category_name"]);

            if ($row["category_status"] == 'Enable') {
                $status = '<button class="btn btn-primary btn-sm status_button" data-id="' . $row["category_id"] . '" data-status="Enable">Enable</button>';
            } else {
                $status = '<button class="btn btn-danger btn-sm status_button" data-id="' . $row["category_id"] . '" data-status="Disable">Disable</button>';
            }

            $sub_array[] = $status;

            $sub_array[] = '
            <div align="center">
                <button class="btn btn-warning btn-circle btn-sm edit_button" data-id="' . $row["category_id"] . '"><i class="fas fa-edit"></i></button>
                <button class="btn btn-danger btn-circle btn-sm delete_button" data-id="' . $row["category_id"] . '"><i class="fas fa-times"></i></button>
            </div>';

            $data[] = $sub_array;
        }

        echo json_encode(array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => $total_rows,
            "recordsFiltered" => $filtered_rows,
            "data" => $data
        ));
    }

    /* ================= ADD ================= */
    if ($_POST["action"] == 'Add') {

        $error = '';
        $success = '';

        $data = array(
            ':category_name' => $_POST["category_name"]
        );

        $object->query = "SELECT * FROM product_category_table WHERE category_name = :category_name";
        $object->execute($data);

        if ($object->row_count() > 0) {
            $error = '<div class="alert alert-danger">Category Already Exists</div>';
        } else {

            $object->query = "
                INSERT INTO product_category_table (category_name, category_status)
                VALUES (:category_name, :category_status)
            ";

            $object->execute(array(
                ':category_name' => $object->clean_input($_POST["category_name"]),
                ':category_status' => 'Enable'
            ));

            $success = '<div class="alert alert-success">Category Added</div>';
        }

        echo json_encode(array('error' => $error, 'success' => $success));
    }

    /* ================= FETCH SINGLE ================= */
    if ($_POST["action"] == 'fetch_single') {

        $object->query = "
            SELECT category_name 
            FROM product_category_table 
            WHERE category_id = :category_id
        ";

        $object->execute(array(':category_id' => $_POST["category_id"]));
        $result = $object->statement_result();

        echo json_encode($result[0] ?? []);
    }

    /* ================= EDIT ================= */
    if ($_POST["action"] == 'Edit') {

        $error = '';
        $success = '';

        $object->query = "
            SELECT * FROM product_category_table 
            WHERE category_name = :category_name 
            AND category_id != :category_id
        ";

        $object->execute(array(
            ':category_name' => $_POST["category_name"],
            ':category_id' => $_POST["hidden_id"]
        ));

        if ($object->row_count() > 0) {
            $error = '<div class="alert alert-danger">Category Already Exists</div>';
        } else {

            $object->query = "
                UPDATE product_category_table 
                SET category_name = :category_name 
                WHERE category_id = :category_id
            ";

            $object->execute(array(
                ':category_name' => $object->clean_input($_POST["category_name"]),
                ':category_id' => $_POST["hidden_id"]
            ));

            $success = '<div class="alert alert-success">Category Updated</div>';
        }

        echo json_encode(array('error' => $error, 'success' => $success));
    }

    /* ================= CHANGE STATUS ================= */
    if ($_POST["action"] == 'change_status') {

        $object->query = "
            UPDATE product_category_table 
            SET category_status = :category_status 
            WHERE category_id = :category_id
        ";

        $object->execute(array(
            ':category_status' => $_POST["next_status"],
            ':category_id' => $_POST["id"]
        ));

        echo '<div class="alert alert-success">Category Status changed</div>';
    }

    /* ================= DELETE ================= */
    if ($_POST["action"] == 'delete') {

        $object->query = "
            DELETE FROM product_category_table 
            WHERE category_id = :category_id
        ";

        $object->execute(array(':category_id' => $_POST["id"]));

        echo '<div class="alert alert-success">Category Deleted</div>';
    }
}
?>

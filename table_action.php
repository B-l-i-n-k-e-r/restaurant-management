<?php
// table_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["action"]))
{
    /* ================= FETCH ALL TABLES ================= */
    if($_POST["action"] == 'fetch')
    {
        $order_column = array('table_data.table_name', 'table_data.table_capacity', 'user_table.user_name', 'table_data.table_status');

        $main_query = "
        SELECT table_data.*, user_table.user_name 
        FROM table_data 
        LEFT JOIN user_table ON user_table.user_id = table_data.waiter_id 
        ";

        $search_query = '';
        if(!empty($_POST["search"]["value"]))
        {
            $search_query .= '
            WHERE table_data.table_name LIKE "%'.$_POST["search"]["value"].'%" 
            OR table_data.table_capacity LIKE "%'.$_POST["search"]["value"].'%" 
            OR user_table.user_name LIKE "%'.$_POST["search"]["value"].'%" 
            OR table_data.table_status LIKE "%'.$_POST["search"]["value"].'%" ';
        }

        if(isset($_POST["order"])) {
            $order_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
        } else {
            $order_query = 'ORDER BY table_data.table_id DESC ';
        }

        $limit_query = '';
        if($_POST["length"] != -1) {
            $limit_query = 'LIMIT '.$_POST['start'].', '.$_POST['length'];
        }

        $object->query = $main_query . $search_query . $order_query;
        $object->execute();
        $filtered_rows = $object->row_count();

        $object->query .= $limit_query;
        $object->execute();
        $result = $object->get_result();

        $object->query = $main_query;
        $object->execute();
        $total_rows = $object->row_count();

        $data = array();
        foreach($result as $row)
        {
            $status_btn = ($row["table_status"] == 'Enable')
                ? '<button class="btn btn-primary btn-sm status_button" data-id="'.$row["table_id"].'" data-status="Enable">Enable</button>'
                : '<button class="btn btn-danger btn-sm status_button" data-id="'.$row["table_id"].'" data-status="Disable">Disable</button>';

            $waiter_name = ($row["user_name"] != '') ? $row["user_name"] : '<span class="text-muted">Not Assigned</span>';

            $data[] = array(
                html_entity_decode($row["table_name"]),
                $row["table_capacity"].' Person',
                $waiter_name,
                $status_btn,
                '
                <div align="center">
                    <button class="btn btn-warning btn-circle btn-sm edit_button" data-id="'.$row["table_id"].'"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-danger btn-circle btn-sm delete_button" data-id="'.$row["table_id"].'"><i class="fas fa-times"></i></button>
                </div>'
            );
        }

        echo json_encode(array(
            "draw"            => intval($_POST["draw"]),
            "recordsTotal"    => $total_rows,
            "recordsFiltered" => $filtered_rows,
            "data"            => $data
        ));
    }

    /* ================= ADD NEW TABLE ================= */
    if($_POST["action"] == 'Add')
    {
        $object->query = "SELECT * FROM table_data WHERE table_name = :table_name";
        $object->execute([':table_name' => $_POST["table_name"]]);

        if($object->row_count() > 0) {
            echo json_encode(['error' => '<div class="alert alert-danger">Table Already Exists</div>', 'success' => '']);
            exit;
        }

        $object->query = "
        INSERT INTO table_data (table_name, table_capacity, waiter_id, table_status) 
        VALUES (:table_name, :table_capacity, :waiter_id, :table_status)";
        
        $object->execute([
            ':table_name'     => $object->clean_input($_POST["table_name"]),
            ':table_capacity' => $_POST["table_capacity"],
            ':waiter_id'      => $_POST["waiter_id"],
            ':table_status'   => 'Enable'
        ]);

        echo json_encode(['error' => '', 'success' => '<div class="alert alert-success">Table Added Successfully</div>']);
    }

    /* ================= FETCH SINGLE (FOR EDIT MODAL) ================= */
    if($_POST["action"] == 'fetch_single')
    {
        $object->query = "SELECT * FROM table_data WHERE table_id = :table_id";
        $object->execute([':table_id' => $_POST["table_id"]]);
        $result = $object->statement_result(); // <-- fixed

        $output = array();
        if(count($result) > 0) {
            $output['table_name'] = $result[0]['table_name'];
            $output['table_capacity'] = $result[0]['table_capacity'];
            $output['waiter_id'] = $result[0]['waiter_id'];
        }

        echo json_encode($output);
    }

    /* ================= EDIT TABLE ================= */
    if($_POST["action"] == 'Edit')
    {
        $object->query = "
        UPDATE table_data 
        SET table_name = :table_name, table_capacity = :table_capacity, waiter_id = :waiter_id 
        WHERE table_id = :table_id";

        $object->execute([
            ':table_name'     => $object->clean_input($_POST["table_name"]),
            ':table_capacity' => $_POST["table_capacity"],
            ':waiter_id'      => $_POST["waiter_id"],
            ':table_id'       => $_POST['hidden_id']
        ]);
        echo json_encode(['error' => '', 'success' => '<div class="alert alert-success">Table Updated Successfully</div>']);
    }

    /* ================= CHANGE STATUS ================= */
    if($_POST["action"] == 'change_status')
    {
        $object->query = "UPDATE table_data SET table_status = :status WHERE table_id = :id";
        $object->execute([':status' => $_POST['next_status'], ':id' => $_POST['id']]);
        echo '<div class="alert alert-success">Status Updated</div>';
    }

    /* ================= DELETE TABLE ================= */
    if($_POST["action"] == 'delete')
    {
        $object->query = "DELETE FROM table_data WHERE table_id = :id";
        $object->execute([':id' => $_POST["id"]]);
        echo '<div class="alert alert-success">Table Removed</div>';
    }
}
?>

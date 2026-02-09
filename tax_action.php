<?php
// tax_action.php
include('rms.php');
$object = new rms();

// Force clean output
header('Content-Type: application/json');

if(isset($_POST["action"]))
{
    /* ================= FETCH TAXES ================= */
    if($_POST["action"] == 'fetch')
    {
        $order_column = array('tax_name', 'tax_percentage', 'tax_status');

        $main_query = "SELECT * FROM tax_table WHERE 1";

        $search_query = '';
        $params = [];

        if(!empty($_POST["search"]["value"])) {
            $search_val = $_POST["search"]["value"];
            $search_query .= " AND (tax_name LIKE :search OR tax_percentage LIKE :search OR tax_status LIKE :search)";
            $params['search'] = "%$search_val%";
        }

        if(isset($_POST["order"])) {
            $order_query = ' ORDER BY '.$order_column[intval($_POST['order']['0']['column'])].' '.$_POST['order']['0']['dir'];
        } else {
            $order_query = ' ORDER BY tax_id DESC';
        }

        $limit_query = ($_POST["length"] != -1) ? ' LIMIT ' . intval($_POST['start']) . ', ' . intval($_POST['length']) : '';

        // Get filtered rows
        $object->query = $main_query . $search_query;
        $object->execute($params);
        $filtered_rows = $object->row_count();

        // Get actual data
        $object->query .= $order_query . $limit_query;
        $object->execute($params);
        $result = $object->statement_result();
        if(!$result) $result = [];

        // Total rows without filters
        $object->query = "SELECT * FROM tax_table";
        $object->execute();
        $total_rows = $object->row_count();

        $data = [];
        foreach($result as $row)
        {
            $status_btn = ($row["tax_status"] == 'Enable')
                ? '<button class="btn btn-primary btn-sm status_button" data-id="'.$row["tax_id"].'" data-status="Enable">Enable</button>'
                : '<button class="btn btn-danger btn-sm status_button" data-id="'.$row["tax_id"].'" data-status="Disable">Disable</button>';

            $data[] = [
                htmlspecialchars($row["tax_name"]),
                htmlspecialchars($row["tax_percentage"]).'%',
                $status_btn,
                '<div align="center">
                    <button class="btn btn-warning btn-sm edit_button" data-id="'.$row["tax_id"].'"><i class="fas fa-edit"></i></button>
                    &nbsp;
                    <button class="btn btn-danger btn-sm delete_button" data-id="'.$row["tax_id"].'"><i class="fas fa-times"></i></button>
                </div>'
            ];
        }

        echo json_encode([
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => $total_rows,
            "recordsFiltered" => $filtered_rows,
            "data" => $data
        ]);
        exit();
    }

    /* ================= ADD TAX ================= */
    if($_POST["action"] == 'Add')
    {
        $tax_name = $object->clean_input($_POST["tax_name"]);
        $tax_percentage = floatval($_POST["tax_percentage"]);

        // Optional: Check duplicate tax name
        $object->query = "SELECT tax_id FROM tax_table WHERE tax_name = :tax_name";
        $object->execute(['tax_name' => $tax_name]);
        if($object->row_count() > 0) {
            echo json_encode(['error'=>'Tax Name Already Exists', 'success'=>'']);
            exit();
        }

        $object->query = "INSERT INTO tax_table (tax_name, tax_percentage, tax_status) 
                          VALUES (:tax_name, :tax_percentage, 'Enable')";
        $object->execute([
            'tax_name' => $tax_name,
            'tax_percentage' => $tax_percentage
        ]);

        echo json_encode(['error'=>'', 'success'=>'Tax Added Successfully']);
        exit();
    }

    /* ================= FETCH SINGLE TAX ================= */
    if($_POST["action"] == 'fetch_single')
    {
        $object->query = "SELECT * FROM tax_table WHERE tax_id = :tax_id";
        $object->execute(['tax_id' => $_POST["tax_id"]]);
        $result = $object->statement_result();
        if(!$result) $result = [];
        echo json_encode($result[0] ?? []);
        exit();
    }

    /* ================= EDIT TAX ================= */
    if($_POST["action"] == 'Edit')
    {
        $tax_id = $_POST['hidden_id'];
        $tax_name = $object->clean_input($_POST["tax_name"]);
        $tax_percentage = floatval($_POST["tax_percentage"]);

        // Optional: Check duplicate tax name excluding current
        $object->query = "SELECT tax_id FROM tax_table WHERE tax_name = :tax_name AND tax_id != :tax_id";
        $object->execute(['tax_name'=>$tax_name, 'tax_id'=>$tax_id]);
        if($object->row_count() > 0) {
            echo json_encode(['error'=>'Tax Name Already Exists', 'success'=>'']);
            exit();
        }

        $object->query = "UPDATE tax_table SET tax_name = :tax_name, tax_percentage = :tax_percentage 
                          WHERE tax_id = :tax_id";
        $object->execute([
            'tax_name'=>$tax_name,
            'tax_percentage'=>$tax_percentage,
            'tax_id'=>$tax_id
        ]);

        echo json_encode(['error'=>'', 'success'=>'Tax Updated Successfully']);
        exit();
    }

    /* ================= CHANGE TAX STATUS ================= */
    if($_POST["action"] == 'change_status')
    {
        $tax_id = $_POST['id'];
        $next_status = $_POST['next_status']; // Enable or Disable

        $object->query = "UPDATE tax_table SET tax_status = :status WHERE tax_id = :id";
        $object->execute([
            'status'=>$next_status,
            'id'=>$tax_id
        ]);

        echo json_encode(['success'=>'Tax status updated to '.$next_status]);
        exit();
    }

    /* ================= DELETE TAX ================= */
    if($_POST["action"] == 'delete')
    {
        $tax_id = $_POST['id'];
        $object->query = "DELETE FROM tax_table WHERE tax_id = :id";
        $object->execute(['id'=>$tax_id]);

        echo json_encode(['success'=>'Tax Removed Successfully']);
        exit();
    }
}
?>

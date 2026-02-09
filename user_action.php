<?php
// user_action.php
include('rms.php');
$object = new rms();

if(isset($_POST["action"]))
{
    // FETCH USERS
    if($_POST["action"] == 'fetch')
    {
        $order_column = array('user_name', 'user_contact_no', 'user_email', 'user_password', 'user_type', 'user_created_on', 'user_status');
        $main_query = "SELECT * FROM user_table WHERE 1=1 "; 
        $search_query = '';
        $params = [];

        if(isset($_POST["search"]["value"]) && $_POST["search"]["value"] != '') {
            $search_val = $_POST["search"]["value"];
            $search_query .= " AND (user_name LIKE :search OR user_email LIKE :search OR user_type LIKE :search)";
            $params['search'] = "%$search_val%";
        }

        if(isset($_POST["order"]))
            $order_query = ' ORDER BY '.$order_column[intval($_POST['order']['0']['column'])].' '.$_POST['order']['0']['dir'];
        else
            $order_query = ' ORDER BY user_id DESC ';

        $limit_query = ($_POST["length"] != -1) ? ' LIMIT ' . intval($_POST['start']) . ', ' . intval($_POST['length']) : '';

        $object->query = $main_query . $search_query . $order_query;
        $object->execute($params);
        $filtered_rows = $object->row_count();

        $object->query .= $limit_query;
        $object->execute($params);
        $result = $object->statement_result();

        $object->query = $main_query;
        $object->execute();
        $total_rows = $object->row_count();

        $data = [];
        foreach($result as $row) {
            $sub_array = [];
            $sub_array[] = '<img src="'.$row["user_profile"].'" class="img-thumbnail" width="50" />';
            $sub_array[] = htmlspecialchars($row["user_name"]);
            $sub_array[] = htmlspecialchars($row["user_contact_no"]);
            $sub_array[] = htmlspecialchars($row["user_email"]);
            $sub_array[] = '
                <div class="d-flex align-items-center justify-content-between">
                    <span id="pass_'.$row["user_id"].'">********</span>
                    <button type="button" class="btn btn-link btn-sm view_password" data-id="'.$row["user_id"].'" data-password="'.$row["user_password"].'">
                        <i class="fas fa-eye text-info"></i>
                    </button>
                </div>';
            $sub_array[] = htmlspecialchars($row["user_type"]);
            $sub_array[] = $row["user_created_on"];
            
            $next_status = ($row["user_status"] == 'Enable') ? 'Disable' : 'Enable';
            $btn_class = ($row["user_status"] == 'Enable') ? 'btn-primary' : 'btn-danger';
            $sub_array[] = '<button type="button" class="btn btn-sm '.$btn_class.' status_button" data-id="'.$row["user_id"].'" data-status="'.$next_status.'">'.$row["user_status"].'</button>';
            
            $sub_array[] = '<div align="center">
                <button type="button" class="btn btn-warning btn-sm edit_button" data-id="'.$row["user_id"].'"><i class="fas fa-edit"></i></button>
                &nbsp;
                <button type="button" class="btn btn-danger btn-sm delete_button" data-id="'.$row["user_id"].'"><i class="fas fa-times"></i></button>
            </div>';
            $data[] = $sub_array;
        }

        echo json_encode([
            "draw" => intval($_POST["draw"]), 
            "recordsTotal" => $total_rows, 
            "recordsFiltered" => $filtered_rows, 
            "data" => $data
        ]);
    }

    // ADD USER
    if($_POST["action"] == 'Add')
    {
        $error = ''; $success = '';
        $object->query = "SELECT user_id FROM user_table WHERE user_email = :user_email";
        $object->execute(['user_email' => $_POST["user_email"]]);

        if($object->row_count() > 0) {
            $error = 'User Email Already Exists';
        } else {
            // Check if image uploaded, else generate avatar
            if(!empty($_FILES["user_image"]["name"])) {
                $user_image = upload_image();
            } else {
                $first_letter = strtoupper(substr($_POST["user_name"], 0, 1));
                $user_image = make_avatar($first_letter);
            }

            $object->query = "INSERT INTO user_table (user_name, user_contact_no, user_email, user_password, user_profile, user_type, user_status, user_created_on) 
                              VALUES (:user_name, :user_contact_no, :user_email, :user_password, :user_profile, :user_type, 'Enable', :user_created_on)";
            $object->execute([
                'user_name' => $_POST["user_name"], 
                'user_contact_no' => $_POST["user_contact_no"],
                'user_email' => $_POST["user_email"], 
                'user_password' => password_hash($_POST["user_password"], PASSWORD_DEFAULT),
                'user_profile' => $user_image, 
                'user_type' => $_POST["user_type"], 
                'user_created_on' => $object->get_datetime()
            ]);
            $success = 'User Added Successfully';
        }
        echo json_encode(['error'=>$error, 'success'=>$success]);
    }

    // EDIT USER
    if($_POST["action"] == 'Edit')
    {
        $error = ''; $success = '';
        $user_id = $_POST['hidden_id'];
        
        $object->query = "SELECT user_id FROM user_table WHERE user_email = :user_email AND user_id != :user_id";
        $object->execute(['user_email' => $_POST["user_email"], 'user_id' => $user_id]);

        if($object->row_count() > 0) {
            $error = 'Email already exists';
        } else {
            $user_image = $_POST["hidden_user_image"];
            if(!empty($_FILES["user_image"]["name"])) {
                if(file_exists($user_image) && strpos($user_image, 'default') === false) { @unlink($user_image); }
                $user_image = upload_image();
            }

            $password_query = "";
            $params = [
                'user_name' => $_POST["user_name"], 
                'user_contact_no' => $_POST["user_contact_no"],
                'user_email' => $_POST["user_email"], 
                'user_profile' => $user_image,
                'user_type' => $_POST["user_type"], 
                'user_id' => $user_id
            ];

            if(!empty($_POST["user_password"])) {
                $params['user_password'] = password_hash($_POST["user_password"], PASSWORD_DEFAULT);
                $password_query = "user_password = :user_password, ";
            }

            $object->query = "UPDATE user_table SET user_name = :user_name, user_contact_no = :user_contact_no, 
                              user_email = :user_email, $password_query user_profile = :user_profile, 
                              user_type = :user_type WHERE user_id = :user_id";
            $object->execute($params);
            $success = 'User Updated Successfully';
        }
        echo json_encode(['error'=>$error, 'success'=>$success]);
    }

    // CHANGE STATUS
    if($_POST["action"] == 'change_status')
    {
        $object->query = "UPDATE user_table SET user_status = :user_status WHERE user_id = :user_id";
        $object->execute(['user_status' => $_POST['next_status'], 'user_id' => $_POST["id"]]);
        echo json_encode(['success'=>'Status updated']);
    }

    // DELETE
    if($_POST["action"] == 'true_delete')
    {
        $object->query = "SELECT user_profile FROM user_table WHERE user_id = :id";
        $object->execute(['id' => $_POST["id"]]);
        $row = $object->statement_result();
        if(file_exists($row[0]['user_profile'])) { @unlink($row[0]['user_profile']); }

        $object->query = "DELETE FROM user_table WHERE user_id = :id";
        $object->execute(['id' => $_POST["id"]]);
        echo json_encode(['success' => 'User deleted']);
    }

    // FETCH SINGLE
    if($_POST["action"] == 'fetch_single')
    {
        $object->query = "SELECT * FROM user_table WHERE user_id = :user_id";
        $object->execute(['user_id' => $_POST["user_id"]]);
        $result = $object->statement_result();
        echo json_encode($result[0]);
    }
}

function upload_image() {
    $extension = pathinfo($_FILES['user_image']['name'], PATHINFO_EXTENSION);
    $new_name = rand() . '.' . $extension;
    $destination = 'img/' . $new_name;
    move_uploaded_file($_FILES['user_image']['tmp_name'], $destination);
    return $destination;
}

function make_avatar($character) {
    $path = "img/" . time() . rand(10,99) . ".png";
    if (!is_dir('img')) { mkdir('img', 0777, true); }
    
    $image = imagecreate(200, 200);
    $bg = imagecolorallocate($image, rand(0,100), rand(0,100), rand(0,100));
    $white = imagecolorallocate($image, 255, 255, 255);
    
    // Using built-in font (5 is largest)
    $font = 5;
    $text_width = imagefontwidth($font) * strlen($character);
    $text_height = imagefontheight($font);
    $x = (200 - $text_width) / 2;
    $y = (200 - $text_height) / 2;

    imagestring($image, $font, $x, $y, $character, $white);
    imagepng($image, $path);
    imagedestroy($image);
    return $path;
}
?>
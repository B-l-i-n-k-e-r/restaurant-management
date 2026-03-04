<?php
// user_action.php
include('rms.php');
$object = new rms();

if(isset($_POST["action"]))
{
    // ==========================================
    // NEW: PROFILE UPDATE LOGIC (For profile.php)
    // ==========================================
    if($_POST["action"] == 'profile')
    {
        $error = ''; $success = ''; $user_profile = $_POST["hidden_user_profile"];

        // Check if email already exists for another user
        $object->query = "SELECT * FROM user_table WHERE user_email = :user_email AND user_id != :user_id";
        $object->execute([
            ':user_email' => $_POST["user_email"],
            ':user_id'    => $_SESSION["user_id"]
        ]);

        if($object->row_count() > 0) {
            $error = 'Email already exists';
        } else {
            // Handle Image Upload
            if($_FILES["user_image"]["name"] != '') {
                // Delete old image if it's not the default avatar
                if(file_exists($user_profile) && strpos($user_profile, 'undraw_profile') === false) {
                    @unlink($user_profile);
                }
                $user_profile = upload_image();
            }

            // Update Database
            $object->query = "
            UPDATE user_table 
            SET user_name = :user_name, 
            user_contact_no = :user_contact_no, 
            user_email = :user_email, 
            user_password = :user_password, 
            user_profile = :user_profile 
            WHERE user_id = :user_id
            ";

            $object->execute([
                ':user_name'       => $_POST["user_name"],
                ':user_contact_no' => $_POST["user_contact_no"],
                ':user_email'      => $_POST["user_email"],
                ':user_password'   => $_POST["user_password"], // Note: If using hashing, use password_hash() here
                ':user_profile'    => $user_profile,
                ':user_id'         => $_SESSION["user_id"]
            ]);

            $success = 'Profile Updated Successfully';
        }

        $output = array(
            'error'           => $error,
            'success'         => $success,
            'user_name'       => $_POST["user_name"],
            'user_contact_no' => $_POST["user_contact_no"],
            'user_email'      => $_POST["user_email"],
            'user_password'   => $_POST["user_password"],
            'user_profile'    => $user_profile
        );

        echo json_encode($output);
    }

    // FETCH USERS (Admin Table)
    if($_POST["action"] == 'fetch')
    {
        $order_column = array('user_name', 'user_contact_no', 'user_email', 'user_password', 'user_type', 'user_created_on', 'user_status');
        $main_query = "SELECT * FROM user_table WHERE 1=1 "; 
        $search_query = '';
        $params = [];

        if(isset($_POST["search"]["value"]) && $_POST["search"]["value"] != '') {
            $search_val = $_POST["search"]["value"];
            $search_query .= " AND (user_name LIKE :search OR user_email LIKE :search OR user_type LIKE :search OR user_contact_no LIKE :search)";
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
            $sub_array[] = '<img src="'.$row["user_profile"].'" class="user-profile-img" width="40" height="40" style="border-radius:50%; object-fit:cover;" />';
            
            $userNameHTML = '<b>' . htmlspecialchars($row["user_name"]) . '</b>';
            if(isset($row["reset_request"]) && $row["reset_request"] == 1) {
                $userNameHTML .= '<br><span class="badge badge-danger" style="font-size:10px;"><i class="fas fa-key"></i> Reset Requested</span>';
            }
            $sub_array[] = $userNameHTML;
            $sub_array[] = htmlspecialchars($row["user_contact_no"]);
            $sub_array[] = htmlspecialchars($row["user_email"]);
            $sub_array[] = '
                <div class="d-flex align-items-center justify-content-between" style="min-width:100px;">
                    <span id="pass_'.$row["user_id"].'" class="text-monospace">********</span>
                    <button type="button" class="btn btn-link btn-sm view_password" data-id="'.$row["user_id"].'" data-password="'.$row["user_password"].'">
                        <i class="fas fa-eye text-info"></i>
                    </button>
                </div>';
            
            $role = $row["user_type"];
            if($role == 'Kitchen') {
                $role_badge = '<span class="badge badge-warning text-dark"><i class="fas fa-fire-alt mr-1"></i>Kitchen</span>';
            } elseif($role == 'Master' || $role == 'Admin') {
                $role_badge = '<span class="badge badge-info"><i class="fas fa-user-shield mr-1"></i>Master</span>';
            } elseif($role == 'Waiter') {
                $role_badge = '<span class="badge badge-primary"><i class="fas fa-concierge-bell mr-1"></i>Waiter</span>';
            } elseif($role == 'Cashier') {
                $role_badge = '<span class="badge badge-success"><i class="fas fa-cash-register mr-1"></i>Cashier</span>';
            } else {
                $role_badge = '<span class="badge badge-secondary">'.$role.'</span>';
            }
            $sub_array[] = $role_badge;
            $sub_array[] = date('M d, Y', strtotime($row["user_created_on"]));
            
            if(isset($row["reset_request"]) && $row["reset_request"] == 1) {
                $sub_array[] = '<button type="button" class="btn btn-sm btn-warning edit_button" data-id="'.$row["user_id"].'">Pending Reset</button>';
            } else {
                $next_status = ($row["user_status"] == 'Enable') ? 'Disable' : 'Enable';
                $btn_class = ($row["user_status"] == 'Enable') ? 'btn-success' : 'btn-danger';
                $sub_array[] = '<button type="button" class="btn btn-sm '.$btn_class.' status_button" data-id="'.$row["user_id"].'" data-status="'.$next_status.'">'.$row["user_status"].'</button>';
            }
            
            $sub_array[] = '
                <div class="btn-group">
                    <button type="button" class="btn btn-warning btn-sm edit_button" data-id="'.$row["user_id"].'"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-danger btn-sm delete_button" data-id="'.$row["user_id"].'"><i class="fas fa-trash"></i></button>
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
            if(!empty($_FILES["user_image"]["name"])) {
                $user_image = upload_image();
            } else {
                $first_letter = strtoupper(substr($_POST["user_name"], 0, 1));
                $user_image = make_avatar($first_letter);
            }

            $object->query = "INSERT INTO user_table (user_name, user_contact_no, user_email, user_password, user_profile, user_type, user_status, user_created_on, reset_request) 
                              VALUES (:user_name, :user_contact_no, :user_email, :user_password, :user_profile, :user_type, 'Enable', :user_created_on, 0)";
            $object->execute([
                'user_name' => $_POST["user_name"], 
                'user_contact_no' => $_POST["user_contact_no"],
                'user_email' => $_POST["user_email"], 
                'user_password' => $_POST["user_password"],
                'user_profile' => $user_image, 
                'user_type' => $_POST["user_type"], 
                'user_created_on' => $object->get_datetime()
            ]);
            $success = 'User Added Successfully';
        }
        echo json_encode(['error'=>$error, 'success'=>$success]);
    }

    // EDIT USER (From Admin Panel)
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

            $object->query = "UPDATE user_table SET 
                                user_name = :user_name, 
                                user_contact_no = :user_contact_no, 
                                user_email = :user_email, 
                                user_password = :user_password,
                                user_profile = :user_profile, 
                                user_type = :user_type 
                              WHERE user_id = :user_id";
            $object->execute([
                'user_name' => $_POST["user_name"], 
                'user_contact_no' => $_POST["user_contact_no"],
                'user_email' => $_POST["user_email"], 
                'user_password' => $_POST["user_password"],
                'user_profile' => $user_image,
                'user_type' => $_POST["user_type"], 
                'user_id' => $user_id
            ]);
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
        if(isset($row[0]) && file_exists($row[0]['user_profile']) && strpos($row[0]['user_profile'], 'undraw') === false) { @unlink($row[0]['user_profile']); }

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

// Helper Functions
function upload_image() {
    $extension = pathinfo($_FILES['user_image']['name'], PATHINFO_EXTENSION);
    $new_name = rand() . '.' . $extension;
    $destination = 'img/' . $new_name;
    if (!is_dir('img')) { mkdir('img', 0777, true); }
    move_uploaded_file($_FILES['user_image']['tmp_name'], $destination);
    return $destination;
}

function make_avatar($character) {
    $path = "img/" . time() . rand(10,99) . ".png";
    if (!is_dir('img')) { mkdir('img', 0777, true); }
    
    $image = imagecreate(200, 200);
    $bg = imagecolorallocate($image, rand(0,100), rand(0,100), rand(0,100));
    $white = imagecolorallocate($image, 255, 255, 255);
    
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
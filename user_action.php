<?php
// user_action.php
include('rms.php');
$object = new rms();

if(isset($_POST["action"]))
{
    // ==========================================
    // PROFILE UPDATE LOGIC (For profile.php)
    // ==========================================
    if($_POST["action"] == 'profile')
    {
        $error = ''; $success = ''; $user_profile = $_POST["hidden_user_profile"];

        // Allow shared emails by checking Name + Email + ID
        $object->query = "SELECT * FROM user_table WHERE user_email = :user_email AND user_name = :user_name AND user_id != :user_id";
        $object->execute([
            ':user_email' => $_POST["user_email"],
            ':user_name'  => $_POST["user_name"],
            ':user_id'    => $_SESSION["user_id"]
        ]);

        if($object->row_count() > 0) {
            $error = 'This identity already exists.';
        } else {
            if($_FILES["user_image"]["name"] != '') {
                if(file_exists($user_profile) && strpos($user_profile, 'undraw') === false) {
                    @unlink($user_profile);
                }
                $user_profile = upload_image();
            }

            // --- PASSWORD LOGIC ---
            $password_query = "";
            $params = [
                ':user_name'        => $_POST["user_name"],
                ':user_contact_no'  => $_POST["user_contact_no"],
                ':user_email'       => $_POST["user_email"],
                ':user_profile'     => $user_profile,
                ':user_id'          => $_SESSION["user_id"]
            ];

            // Only update password if not empty
            if(!empty($_POST["user_password"])) {
                $password_query = ", user_password = :user_password";
                $params[':user_password'] = $_POST["user_password"];
            }

            $object->query = "
            UPDATE user_table 
            SET user_name = :user_name, 
            user_contact_no = :user_contact_no, 
            user_email = :user_email, 
            user_profile = :user_profile 
            $password_query
            WHERE user_id = :user_id
            ";

            $object->execute($params);

            $success = 'Profile Synchronized Successfully';
        }

        echo json_encode([
            'error' => $error,
            'success' => $success,
            'user_name' => $_POST["user_name"],
            'user_profile' => $user_profile
        ]);
    }

    // ==========================================
    // FETCH USERS (With Active Filter Logic)
    // ==========================================
    if($_POST["action"] == 'fetch')
    {
        $order_column = array(null, 'user_name', 'user_contact_no', 'user_email', null, 'user_type', 'user_created_on', 'user_status', null);
        
        $main_query = "SELECT * FROM user_table WHERE 1=1 "; 
        $search_query = '';
        $params = [];

        if(isset($_POST["filter_role"]) && $_POST["filter_role"] != '') {
            $search_query .= " AND user_type = :filter_role";
            $params['filter_role'] = $_POST["filter_role"];
        }

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
            
            $sub_array[] = '<img src="'.$row["user_profile"].'" class="user-profile-img" />';
            $sub_array[] = '<span class="text-white font-weight-bold">' . htmlspecialchars($row["user_name"]) . '</span>';
            $sub_array[] = '<span class="text-white-50 small">'.htmlspecialchars($row["user_contact_no"]).'</span>';
            $sub_array[] = '<span class="text-info small">'.htmlspecialchars($row["user_email"]).'</span>';

            $sub_array[] = '
                <div class="d-flex align-items-center justify-content-between bg-dark-50 p-1 px-2 rounded" style="min-width:110px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05);">
                    <span id="pass_'.$row["user_id"].'" class="text-monospace small" style="letter-spacing:2px;">********</span>
                    <button type="button" class="btn btn-link btn-sm view_password p-0 ml-2" data-id="'.$row["user_id"].'" data-password="'.$row["user_password"].'">
                        <i class="fas fa-eye text-info" style="font-size:12px;"></i>
                    </button>
                </div>';
            
            $role = $row["user_type"];
            $badge_class = 'badge-secondary';
            $icon = 'fa-user';
            $display_name = $role;
            
            if($role == 'Master') { $badge_class = 'badge-info'; $icon = 'fa-user-shield'; $display_name = 'Admin'; }
            elseif($role == 'Kitchen') { $badge_class = 'badge-warning text-dark'; $icon = 'fa-fire'; $display_name = 'Kitchen Staff'; }
            elseif($role == 'Waiter') { $badge_class = 'badge-primary'; $icon = 'fa-concierge-bell'; }
            elseif($role == 'Cashier') { $badge_class = 'badge-success'; $icon = 'fa-cash-register'; }

            $sub_array[] = '<span class="badge '.$badge_class.'"><i class="fas '.$icon.' mr-1"></i>'.$display_name.'</span>';
            $sub_array[] = '<span class="text-white-50 small">'.date('M d, Y', strtotime($row["user_created_on"])).'</span>';

            $btn_class = ($row["user_status"] == 'Enable') ? 'btn-success' : 'btn-danger';
            $next_status = ($row["user_status"] == 'Enable') ? 'Disable' : 'Enable';
            $sub_array[] = '<button type="button" class="btn btn-sm '.$btn_class.' status_button py-0 px-2" style="font-size:10px; border-radius:4px;" data-id="'.$row["user_id"].'" data-status="'.$next_status.'">'.strtoupper($row["user_status"]).'</button>';
            
            $sub_array[] = '
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-warning btn-sm edit_button" data-id="'.$row["user_id"].'"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-outline-danger btn-sm delete_button" data-id="'.$row["user_id"].'"><i class="fas fa-trash"></i></button>
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

    // ==========================================
    // ADD USER (Assigning Role-Based Emails)
    // ==========================================
    if($_POST["action"] == 'Add')
    {
        $error = ''; $success = '';
        $role = $_POST["user_type"];
        $assigned_email = '';

        if($role == 'Master') { $assigned_email = 'admin@gmail.com'; }
        elseif($role == 'Waiter') { $assigned_email = 'waiter@gmail.com'; }
        elseif($role == 'Cashier') { $assigned_email = 'cashier@gmail.com'; }
        elseif($role == 'Kitchen') { $assigned_email = 'kitchen@gmail.com'; }
        else { $assigned_email = $_POST["user_email"]; }

        $object->query = "SELECT user_id FROM user_table WHERE user_email = :user_email AND user_name = :user_name";
        $object->execute(['user_email' => $assigned_email, 'user_name' => $_POST["user_name"]]);

        if($object->row_count() > 0) {
            $error = 'This name is already registered for this role.';
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
                'user_email' => $assigned_email, 
                'user_password' => $_POST["user_password"],
                'user_profile' => $user_image, 
                'user_type' => $role, 
                'user_created_on' => $object->get_datetime()
            ]);
            $success = 'Staff identity created for ' . $role;
        }
        echo json_encode(['error'=>$error, 'success'=>$success]);
    }

    // ==========================================
    // EDIT USER (Maintains Role-Based Emails & Password Safety)
    // ==========================================
    if($_POST["action"] == 'Edit')
    {
        $error = ''; $success = '';
        $user_id = $_POST['hidden_id'];
        $role = $_POST["user_type"];
        
        if($role == 'Master') { $assigned_email = 'admin@gmail.com'; }
        elseif($role == 'Waiter') { $assigned_email = 'waiter@gmail.com'; }
        elseif($role == 'Cashier') { $assigned_email = 'cashier@gmail.com'; }
        elseif($role == 'Kitchen') { $assigned_email = 'kitchen@gmail.com'; }
        else { $assigned_email = $_POST["user_email"]; }

        $object->query = "SELECT user_id FROM user_table WHERE user_email = :user_email AND user_name = :user_name AND user_id != :user_id";
        $object->execute(['user_email' => $assigned_email, 'user_name' => $_POST["user_name"], 'user_id' => $user_id]);

        if($object->row_count() > 0) {
            $error = 'Identity conflict detected.';
        } else {
            $user_image = $_POST["hidden_user_image"];
            if(!empty($_FILES["user_image"]["name"])) {
                if(file_exists($user_image) && strpos($user_image, 'default') === false) { @unlink($user_image); }
                $user_image = upload_image();
            }

            // --- PASSWORD LOGIC ---
            $password_query = "";
            $params = [
                ':user_name'        => $_POST["user_name"], 
                ':user_contact_no'  => $_POST["user_contact_no"],
                ':user_email'       => $assigned_email, 
                ':user_profile'     => $user_image,
                ':user_type'        => $role, 
                ':user_id'          => $user_id
            ];

            // Only update password if not empty
            if(!empty($_POST["user_password"])) {
                $password_query = ", user_password = :user_password";
                $params[':user_password'] = $_POST["user_password"];
            }

            $object->query = "UPDATE user_table SET 
                                user_name = :user_name, 
                                user_contact_no = :user_contact_no, 
                                user_email = :user_email, 
                                user_profile = :user_profile, 
                                user_type = :user_type 
                                $password_query
                              WHERE user_id = :user_id";
            
            $object->execute($params);
            $success = 'Staff profile updated.';
        }
        echo json_encode(['error'=>$error, 'success'=>$success]);
    }

    if($_POST["action"] == 'change_status')
    {
        $object->query = "UPDATE user_table SET user_status = :user_status WHERE user_id = :user_id";
        $object->execute(['user_status' => $_POST['next_status'], 'user_id' => $_POST["id"]]);
        echo json_encode(['success'=>'Status updated']);
    }

    if($_POST["action"] == 'true_delete')
    {
        $object->query = "SELECT user_profile FROM user_table WHERE user_id = :id";
        $object->execute(['id' => $_POST["id"]]);
        $row = $object->statement_result();
        if(isset($row[0]) && file_exists($row[0]['user_profile']) && strpos($row[0]['user_profile'], 'default') === false) { @unlink($row[0]['user_profile']); }

        $object->query = "DELETE FROM user_table WHERE user_id = :id";
        $object->execute(['id' => $_POST["id"]]);
        echo json_encode(['success' => 'Purged from system']);
    }

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
    if (!is_dir('img')) { mkdir('img', 0777, true); }
    move_uploaded_file($_FILES['user_image']['tmp_name'], $destination);
    return $destination;
}

function make_avatar($character) {
    $path = "img/" . time() . rand(10,99) . ".png";
    if (!is_dir('img')) { mkdir('img', 0777, true); }
    $image = imagecreate(200, 200);
    $bg = imagecolorallocate($image, rand(0,80), rand(0,80), rand(0,80));
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
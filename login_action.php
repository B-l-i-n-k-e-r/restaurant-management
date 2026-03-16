<?php

// login_action.php

include('rms.php');

$object = new rms();

// ==========================================
// NEW: AUTO-FILL ROLE EMAIL LOGIC
// ==========================================
if(isset($_POST["action"]) && $_POST["action"] == 'check_role_email')
{
    $email = '';
    $user_name = $object->clean_input($_POST["user_name"]);

    $object->query = "
        SELECT user_email, user_type FROM user_table 
        WHERE user_name = :user_name 
        AND user_type IN ('Master', 'Cashier', 'Waiter', 'Kitchen')
        LIMIT 1
    ";

    $object->execute([':user_name' => $user_name]);

    if($object->row_count() > 0)
    {
        $result = $object->statement_result();
        foreach($result as $row)
        {
            $email = $row["user_email"];
        }
    }

    echo json_encode(['email' => $email]);
    exit; 
}

// ==========================================
// LOGIN LOGIC (FIXED FOR SHARED EMAILS)
// ==========================================
if(isset($_POST["user_email"]))
{
    $error = '';
    $url = ''; 
    
    // Clean inputs
    $user_email = $object->clean_input($_POST["user_email"]);
    $user_password = $_POST["user_password"]; 
    
    // Distinguish between users sharing the same email by using the provided Name
    $user_name = isset($_POST["user_name"]) ? $object->clean_input($_POST["user_name"]) : '';

    $object->query = "
        SELECT * FROM user_table 
        WHERE user_email = :user_email 
        AND user_name = :user_name
    ";

    $object->execute([
        ':user_email' => $user_email,
        ':user_name'  => $user_name
    ]);

    if($object->row_count() > 0)
    {
        // Get the single unique record
        $result = $object->statement_result();
        $row = $result[0];

        if($row["user_status"] == 'Enable')
        {
            // Supporting both hashed passwords and plain text
            if(password_verify($user_password, $row["user_password"]) || $user_password == $row["user_password"])
            {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['user_type'] = $row['user_type'];
                $_SESSION['user_name'] = $row['user_name'];

                // --- ROLE-BASED REDIRECTION ---
                if($row['user_type'] == 'Kitchen')
                {
                    $url = 'kitchen_dashboard.php';
                }
                else if(in_array($row['user_type'], ['Master', 'Admin', 'Cashier', 'Waiter']))
                {
                    $url = 'dashboard.php';
                }
                else if($row['user_type'] == 'User')
                {
                    $url = 'user_dashboard.php';
                }
                else
                {
                    $url = 'dashboard.php';
                }
            }
            else
            {
                $error = 'Wrong Password';
            }
        }
        else
        {
            $error = 'Your account is disabled. Please contact the Administrator.';
        }
    }
    else
    {
        // If query returns 0, the combination of name + email is wrong
        $error = 'Identity not found. Please verify your Name and Role.';
    }

    $output = array(
        'error' => $error,
        'url'   => $url 
    );

    echo json_encode($output);
}
?>
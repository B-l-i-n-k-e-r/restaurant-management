<?php

// login_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["user_email"]))
{
    $error = '';
    $url = ''; 
    
    // Clean inputs
    $user_email = $object->clean_input($_POST["user_email"]);
    $user_password = $_POST["user_password"]; 

    $data = array(
        ':user_email' => $user_email
    );

    $object->query = "
        SELECT * FROM user_table 
        WHERE user_email = :user_email
    ";

    $object->execute($data);

    if($object->row_count() > 0)
    {
        $result = $object->statement_result();

        foreach($result as $row)
        {
            if($row["user_status"] == 'Enable')
            {
                // Supporting both hashed passwords and plain text (for development)
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
                    else if($row['user_type'] == 'Master' || $row['user_type'] == 'Admin' || $row['user_type'] == 'Cashier' || $row['user_type'] == 'Waiter')
                    {
                        $url = 'dashboard.php';
                    }
                    else if($row['user_type'] == 'User')
                    {
                        $url = 'user_dashboard.php';
                    }
                    else
                    {
                        $url = 'dashboard.php'; // Default landing
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
    }
    else
    {
        $error = 'Email address not found';
    }

    if($error != '')
    {
        $error = '<div class="alert alert-danger">'.$error.'</div>';
    }

    $output = array(
        'error' => $error,
        'url'   => $url 
    );

    echo json_encode($output);
}
?>
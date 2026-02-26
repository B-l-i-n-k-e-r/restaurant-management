<?php

// login_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["user_email"]))
{
    $error = '';
    $url = ''; 
    
    // Clean inputs using the method we refined in rms.php
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
                if(password_verify($user_password, $row["user_password"]) || $user_password == $row["user_password"])
                {
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['user_type'] = $row['user_type'];
                    $_SESSION['user_name'] = $row['user_name'];

                    // --- UPDATED ROLE-BASED REDIRECTION ---
                    
                    // Staff (Master, Cashier, Waiter) go to the management dashboard
                    if($row['user_type'] == 'Master' || $row['user_type'] == 'Cashier' || $row['user_type'] == 'Waiter')
                    {
                        $url = 'dashboard.php';
                    }
                    // Regular Customers go to the beautiful menu dashboard
                    else if($row['user_type'] == 'User')
                    {
                        $url = 'user_dashboard.php';
                    }
                    else
                    {
                        $url = 'index.php'; // Fallback
                    }
                }
                else
                {
                    $error = 'Wrong Password';
                }
            }
            else
            {
                $error = 'Sorry, Your account has been disabled, please contact Admin';
            }
        }
    }
    else
    {
        $error = 'Wrong Email Address';
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
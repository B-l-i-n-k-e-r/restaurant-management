<?php

//login_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["user_email"]))
{
    // Artificial delay to prevent brute-force attacks
    sleep(2);
    
    $error = '';
    $data = array(
        ':user_email' => $_POST["user_email"]
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
                // Checks for hashed password OR plain text for compatibility
                if(password_verify($_POST["user_password"], $row["user_password"]) || $_POST["user_password"] == $row["user_password"])
                {
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['user_type'] = $row['user_type'];
                    $_SESSION['user_name'] = $row['user_name'];
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

    // Wrap the error message in Bootstrap alert formatting for index.php
    if($error != '')
    {
        $error = '<div class="alert alert-danger">'.$error.'</div>';
    }

    $output = array(
        'error' => $error
    );

    echo json_encode($output);
}

?>
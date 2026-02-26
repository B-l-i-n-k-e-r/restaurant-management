<?php
//register_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["user_email"]))
{
    sleep(1); // small delay for UX
    $error = '';

    // Check if email already exists
    $object->query = "SELECT * FROM user_table WHERE user_email = :user_email";
    $object->execute(array(':user_email' => $_POST["user_email"]));

    if($object->row_count() > 0)
    {
        $error = 'Email Already Exists'; // Simplified for JSON response
    }
    else
    {
        // Determine if this is first setup
        $setup_done = $object->Is_set_up_done() ? 1 : 0;

        // Generate avatar using the first letter of the Name (better than email)
        $first_letter = strtoupper(substr($_POST["user_name"], 0, 1));
        $user_profile = $object->make_avatar($first_letter);

        // If first setup, insert restaurant and create Master user
        if(!$setup_done)
        {
            $restaurant_data = array(
                ':restaurant_name'       => $object->clean_input($_POST["restaurant_name"]),
                ':restaurant_tag_line'   => $object->clean_input($_POST["restaurant_tag_line"]),
                ':restaurant_address'    => $object->clean_input($_POST["restaurant_address"]),
                ':restaurant_contact_no' => $object->clean_input($_POST["restaurant_contact_no"]),
                ':restaurant_email'      => $_POST["user_email"],
                ':restaurant_currency'   => $_POST["restaurant_currency"],
                ':restaurant_timezone'   => $_POST["restaurant_timezone"],
                ':restaurant_logo'       => ''
            );

            $object->query = "
                INSERT INTO restaurant_table 
                (restaurant_name, restaurant_tag_line, restaurant_address, restaurant_contact_no, restaurant_email, restaurant_currency, restaurant_timezone, restaurant_logo) 
                VALUES (:restaurant_name, :restaurant_tag_line, :restaurant_address, :restaurant_contact_no, :restaurant_email, :restaurant_currency, :restaurant_timezone, :restaurant_logo)
            ";
            $object->execute($restaurant_data);

            $user_type = 'Master'; // first user is Master
        }
        else
        {
            $user_type = 'User'; // all other users are normal
        }

        // --- UPDATED USER DATA SECTION ---
        $user_data = array(
            ':user_name'        => $object->clean_input($_POST["user_name"]),
            ':user_contact_no'  => $object->clean_input($_POST["user_contact_no"]),
            ':user_email'       => $_POST["user_email"],
            ':user_password'    => password_hash($_POST["user_password"], PASSWORD_DEFAULT),
            ':user_profile'     => $user_profile,
            ':user_type'        => $user_type,
            ':user_status'      => 'Enable',
            ':user_created_on'  => $object->get_datetime()
        );

        $object->query = "
            INSERT INTO user_table 
            (user_name, user_contact_no, user_email, user_password, user_profile, user_type, user_status, user_created_on) 
            VALUES (:user_name, :user_contact_no, :user_email, :user_password, :user_profile, :user_type, :user_status, :user_created_on)
        ";
        $object->execute($user_data);

        $_SESSION['success'] = '<div class="alert alert-success">Your Account Created, Now you can Login</div>';
    }

    echo json_encode(array('error' => $error));
}
?>
<?php
// setting_action.php

include('rms.php');
$object = new rms();

if(isset($_POST["restaurant_name"]))
{
    $success = '';
    $restaurant_logo = $_POST["hidden_restaurant_logo"];
            
    // Handle Image Upload
    if($_FILES["restaurant_logo"]["name"] != '')
    {
        // Delete old logo if it exists and is a new upload
        if(file_exists($restaurant_logo) && is_file($restaurant_logo)) {
            // unlink($restaurant_logo); // Optional: uncomment to save server space
        }
        $restaurant_logo = upload_image();
    }

    $data = array(
        ':restaurant_name'       => $_POST["restaurant_name"],
        ':restaurant_tag_line'   => $_POST["restaurant_tag_line"],
        ':restaurant_address'    => $_POST["restaurant_address"],
        ':restaurant_contact_no' => $_POST["restaurant_contact_no"],
        ':restaurant_email'      => $_POST["restaurant_email"],
        ':restaurant_currency'   => $_POST["restaurant_currency"],
        ':restaurant_timezone'   => $_POST["restaurant_timezone"],
        ':restaurant_logo'       => $restaurant_logo
    );

    $object->query = "
    UPDATE restaurant_table 
    SET restaurant_name = :restaurant_name, 
    restaurant_tag_line = :restaurant_tag_line, 
    restaurant_address = :restaurant_address, 
    restaurant_contact_no = :restaurant_contact_no, 
    restaurant_email = :restaurant_email, 
    restaurant_currency = :restaurant_currency, 
    restaurant_timezone = :restaurant_timezone, 
    restaurant_logo = :restaurant_logo
    ";

    $object->execute($data);

    // Fetch updated data to send back to the AJAX request
    $object->query = "SELECT * FROM restaurant_table LIMIT 1";
    $result = $object->statement_result();

    $output = array();

    foreach($result as $row)
    {
        $output['restaurant_name']       = $row['restaurant_name'];
        $output['restaurant_tag_line']   = $row['restaurant_tag_line'];
        $output['restaurant_address']    = $row['restaurant_address'];
        $output['restaurant_contact_no'] = $row['restaurant_contact_no'];
        $output['restaurant_email']      = $row['restaurant_email'];
        $output['restaurant_currency']   = $row['restaurant_currency'];
        $output['restaurant_timezone']   = $row['restaurant_timezone'];
        $output['restaurant_logo']       = $row['restaurant_logo'];
    }

    $output['success'] = '<div class="alert alert-success alert-dismissible fade show shadow" role="alert">
        <strong>Success!</strong> Settings updated successfully.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>';

    echo json_encode($output);
}

function upload_image()
{
    if(isset($_FILES["restaurant_logo"]))
    {
        $extension = pathinfo($_FILES['restaurant_logo']['name'], PATHINFO_EXTENSION);
        $new_name = rand() . '.' . $extension;
        $destination = 'images/' . $new_name;
        
        // Ensure the images directory exists
        if (!is_dir('images')) {
            mkdir('images', 0777, true);
        }
        
        move_uploaded_file($_FILES['restaurant_logo']['tmp_name'], $destination);
        return $destination;
    }
}
?>
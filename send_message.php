<?php
include_once 'connection.php';

$message=$_POST['message'];
$room_name=$_POST['room_name'];
$message_timestamp=time();

//////////////////////////////// code to insert data in table below /////////////////////////////////////////////////
$query='INSERT
        INTO
        messages_table
        SET
        message=?,
        room_name=?,
        message_timestamp=?
        ';
$mysqli_prepare = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($mysqli_prepare, 'sss',$message,
                                              $room_name,
                                              $message_timestamp);
if(!mysqli_stmt_execute($mysqli_prepare)){
    $response_array=['response'=>'failure','message'=>'Failed To Insert Data'];
    $response_json=json_encode($response_array);
    echo $response_json;
    die;
}
$response_array=['response'=>'success','message'=>'Data Inserted Successfully'];
$response_json=json_encode($response_array);
echo $response_json;
die;
//////////////////////////////// code to insert data in table above /////////////////////////////////////////////////
?>
<?php
header('Content-Type:text/event-stream');
header('Cache-Control:no-cache');

include_once 'connection.php';

$room_name=$_GET['room_name'];
$user_timestamp=$_GET['user_timestamp'];
$timestamp_minus_7=time()-7;

//////////////////////// code to fetch data in table below /////////////////////////////////////////////////
$query='SELECT
        *
        FROM
        messages_table
        WHERE
        room_name = ?
        AND
        message_timestamp >= ?
        AND
        message_timestamp >= ?
        ';
$mysqli_prepare = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($mysqli_prepare, 'sss', $room_name,
                                               $user_timestamp,
                                               $timestamp_minus_7);
mysqli_stmt_execute($mysqli_prepare);
$mysqli_stmt_get_result = mysqli_stmt_get_result($mysqli_prepare);

$mysqli_fetch_all=mysqli_fetch_all($mysqli_stmt_get_result, MYSQLI_ASSOC);

$response=json_encode($mysqli_fetch_all);

//////////////////////// code to fetch data in table below /////////////////////////////////////////////////


echo "data:".$response."\n\n";

flush();

?>
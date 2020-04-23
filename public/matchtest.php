<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test</title>
</head>
<body>

<form method="POST">
    <span>handyman id</span>
    <input type='text' name="uid"></br>

    <span>username</span>
    <input type='text' name="username"></br>

    <span>start price</span>
    <input type='text' name="start_price"></br>

    <span>end price</span>
    <input type='text' name="end_price"></br>

    <span>Day</span>
    <select name="day_name">
        <option value="Mondays">Mondays</option>
        <option value="Tuesdays">Tuesdays</option>
        <option value="Wednesdays">Wednesdays</option>
        <option value="Thursdays">Thursdays</option>
        <option value="Fridays">Fridays</option>
        <option value="Saturdays">Saturdays</option>
        <option value="Sundays">Sundays</option>
    </select></br>

    <span>lat</span>
    <input type='text' name="lat"></br>

    <span>lng</span>
    <input type='text' name="lng"></br>

    <span>start time</span>
    <input type='text' name="start_time"></br>

    <span>end time</span>
    <input type='text' name="end_time"></br>

    <input type="submit">
<form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once "../src/config/DBTest.php";
    $db = new DB();

    $sql = "INSERT INTO users (uid, username, type) VALUES (?,?,?)";
    $insertedUser = $db->exec($sql, [$_POST['uid'], $_POST['username'], 'handyman']);
    print_r($insertedUser);

    $sql = "INSERT INTO `user_addresses`(`uid`, `address`, `lat`, `lng`)
            VALUES (?,?,?,?)";
    $insertedAddress = $db->exec($sql, [$_POST['uid'], 'test', $_POST['lat'], $_POST['lng']]);
    print_r($insertedAddress);

    $sql = "INSERT INTO handyman_services (`handyman_id`, `service_id`, `start_price`, `end_price`)
            VALUES (?,?,?,?)";
    $insertedService = $db->exec($sql, [$_POST['uid'], 1, $_POST['start_price'], $_POST['end_price']]);
    print_r($insertedService);

    $sql = "INSERT INTO handyman_working_days_time (`handyman_id`, `day_name`, `start_time`, `end_time`)
            VALUES (?,?,?,?)";
    $insertedDays = $db->exec($sql, [$_POST['uid'], $_POST['day_name'], $_POST['start_time'], $_POST['end_time']]);
    print_r($insertedDays);
    
}

?>
    
</body>
</html>
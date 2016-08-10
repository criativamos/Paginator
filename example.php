<?php
require 'Paginator.php';

//Set Database connection
$driver = 'mysql';
$host = 'localhost';
$user = 'root';
$password = '';

//for testing purposes use mock_data.sql file in that directory
$dbname = 'paginator';

try {
    $db = new \PDO($driver . ':host=' . $host . ';dbname=' . $dbname, $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch( \PDOException $e) {
    die($e->getMessage());
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Paginator by Diogo Brito</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <div class="col-md-12">
            <h2 class="text-center" style="margin-bottom: 40px">Paginator Example - by Criativamos</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    //Example case
                    $query = "SELECT * FROM mock_data";

                    $pg = new \Criativamos\Paginator\Paginator($db, $query, 10);
                    if($pg->rowCount() > 0){
                        foreach ($pg->getData() as $data){
                            echo '<tr>';
                            echo '<td>'.$data->id.'</td>';
                            echo '<td>'.$data->first_name.'</td>';
                            echo '<td>'.$data->last_name.'</td>';
                            echo '<td>'.$data->email.'</td>';
                            echo '</tr>';
                        }
                    }

                ?>
                </tbody>
            </table>
            <div class="text-right">
                <?php $pg->render() ?>
            </div>
        </div>
    </div>
</body>
</html>
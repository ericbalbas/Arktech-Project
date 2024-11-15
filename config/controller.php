<?php
// include 'database.php';
include 'component.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// $dbms = new Database();
$html = new HTMLBuilder();

// $dbms->doThis();

// # TEST 1
// $result = $dbms->table('tbl_jeramay')
//     ->where('firstName', ['ryo', 'eric', 'corriene']) // Updated condition
//     ->where('gender', 1) // Updated condition
//     ->orderBy('id')
//     ->limit(10)
//     ->execute('select');

// Database::display($result);
// $dbms->getGeneratedQuery();

// echo "<hr>";

$try = $html->setComponent('button')
    ->setAttribute("class", "btn btn-primary fw-bold text-uppercase")
    ->setAttribute("name", "testClick")
    ->setAttribute("onclick", "Swal.fire('Good job!', 'You clicked the button!', 'success')")
    ->setContent("name")
    ->build();

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?php echo HTMLBuilder::head(); ?>
</head>

<body>
    <?php echo $try ?>
</body>
<?php echo HTMLBuilder::script(); ?>

</html>
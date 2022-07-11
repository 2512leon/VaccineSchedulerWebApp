<?php
    session_start();
    $name = $_SESSION["fName"];
    $status = $_SESSION["status"];
?>
<!doctype html>
<html lang="en">

<head>
    <?php include 'meta_head.php';?>
    <title>BUR Drugs</title>
</head>

<body>
    <?php include 'header.php';?>
    <?php $show_modal = false?>

    <!-- Main Content -->
    <section class="bg-light text-dark p-5 p-lg-0 pt-lg-5 text-center text-sm-start">
        <div class="container">
            <h1>Hi, <?php print $name;?></h1>
            </br>
            <?php if($status == "waitlist"):?>
            <p>You are waitlisted for now, we will text you your tracking number after more doses become available</p>
            <?php else:?>
            <p>Your tracking number is: <?php print $status;?></p>
            <?php endif;?>
        </div>
    </section>

    <?php include 'footer.php';?>
    <?php include 'meta_scripts.php';?>
</body>

</html>

<?php
    session_destroy();
?>
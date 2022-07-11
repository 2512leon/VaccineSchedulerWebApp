<?php
  // Establish connection to database
  $value = "N/a";
  $manu = $status = "";

  $one_shot = $full_vac = 0;
  $fpfizer = $fmoderna = $fjj = 0;

  $date = date("Y-m-d");

  $conn = new mysqli('localhost', 'phpuser', 'phpwd', 'COVID');

  // Get customer vaccination stats
  $sql = "SELECT COUNT(DISTINCT cphone) FROM vaccination WHERE datereceived<'$date'";
  $result = mysqli_query($conn, $sql);
  $one_shot = mysqli_fetch_array($result)[0];

  ///// Get full vaccination stats /////

  // J&J
  $man = "J&J";
  $mindose = "0";
  $sql = "SELECT COUNT(DISTINCT cphone) FROM vaccination v JOIN dose d ON v.dosetrackingnum=d.dosetrackingnum JOIN batch b ON d.batchnr=b.batchnum WHERE v.datereceived<'$date' AND b.manufacturer='$man' AND v.currentdose>'$mindose'";
  $result = mysqli_query($conn, $sql);
  $fjj = (int)mysqli_fetch_array($result)[0];

  // Pfizer
  $man = "Pfizer";
  $mindose = "1";
  $sql = "SELECT COUNT(DISTINCT cphone) FROM vaccination v JOIN dose d ON v.dosetrackingnum=d.dosetrackingnum JOIN batch b ON d.batchnr=b.batchnum WHERE v.datereceived<'$date' AND b.manufacturer='$man' AND v.currentdose>'$mindose'";
  $result = mysqli_query($conn, $sql);
  $fpfizer = (int)mysqli_fetch_array($result)[0];

  // Moderna
  $man = "Moderna";
  $mindose = "1";
  $sql = "SELECT COUNT(DISTINCT cphone) FROM vaccination v JOIN dose d ON v.dosetrackingnum=d.dosetrackingnum JOIN batch b ON d.batchnr=b.batchnum WHERE v.datereceived<'$date' AND b.manufacturer='$man' AND v.currentdose>'$mindose'";
  $result = mysqli_query($conn, $sql);
  $fmoderna = (int)mysqli_fetch_array($result)[0];

  $full_vac = $fjj + $fmoderna + $fpfizer;

  if (isset($_POST["submit"])) {
    $manu = $_POST["brand"];
    $status = $_POST["status"];
    // Admin submitted request
    $sql = "SELECT COUNT(*) FROM dose d JOIN batch b ON d.batchnr=b.batchnum WHERE d.availability='$status' AND b.manufacturer='$manu'";
    $result = mysqli_query($conn, $sql);
    $value = mysqli_fetch_array($result)[0];
  }

  // Get expired doses
  $sql = "SELECT COUNT(*) FROM dose WHERE availability='e'";
  $result = mysqli_query($conn, $sql);
  $expired_doses = mysqli_fetch_array($result)[0];

  // Get available doses
  $sql = "SELECT COUNT(*) FROM dose WHERE availability='a'";
  $result = mysqli_query($conn, $sql);
  $available_doses = mysqli_fetch_array($result)[0];
  
  // Get used doses
  $sql = "SELECT COUNT(*) FROM dose WHERE availability='u'";
  $result = mysqli_query($conn, $sql);
  $used_doses = mysqli_fetch_array($result)[0];

  // Update waitlist
  $manufac = "";
  if (isset($_POST["enter"])) {
    $_SESSION["manufac"] = $manufac;

    $sql = "SELECT COUNT(*) FROM dose WHERE availability='a'";
    $result = mysqli_query($conn, $sql);
    $aDoses = mysqli_fetch_array($result)[0];

    while($aDoses > 0) {
        $sql = "SELECT dosetrackingnum FROM dose WHERE availability='a' ORDER BY RAND() LIMIT 1";
        $result = mysqli_query($conn, $sql);
        $avail_dose = mysqli_fetch_array($result)[0];

        $sql = "SELECT DISTINCT phonenumber FROM waitlist, customer, vaccination WHERE phonenumber=cphonenr AND age=(SELECT MAX(age) FROM customer JOIN waitlist on phonenumber=cphonenr WHERE currentdose=(SELECT MIN(currentdose) FROM vaccination)) ORDER BY RAND() LIMIT 1";
        $result = mysqli_query($conn, $sql);
        $next_customer = mysqli_fetch_array($result)[0];

        $sql = "SELECT DISTINCT currentdose FROM waitlist, customer, vaccination WHERE phonenumber=cphonenr AND age=(SELECT MAX(age) FROM customer JOIN waitlist on phonenumber=cphonenr WHERE currentdose=(SELECT MIN(currentdose) FROM vaccination)) ORDER BY RAND() LIMIT 1";
        $result = mysqli_query($conn, $sql);
        $cur_dose = mysqli_fetch_array($result)[0];

        $sql = "DELETE FROM customer WHERE phonenumber=$next_customer";
        $result = mysqli_query($conn, $sql);

        $sql = "DELETE FROM waitlist WHERE cphonenr=$next_customer";
        $result = mysqli_query($conn, $sql);

        $sql = "INSERT INTO vaccination VALUES ($next_customer, $avail_dose, $cur_dose, $date";
        $result = mysqli_query($conn, $sql);

        $sql = "UPDATE dose SET availability='u' WHERE dosetrackingnum=$avail_dose";
        $result = mysqli_query($conn, $sql);

        $aDoses--;
    }
  }
?>

<!doctype html>
<html lang="en">

<head>
    <?php include 'meta_head.php';?>
    <title>BUR Drugs | Admin</title>
</head>

<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Administration</a>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                    <div class="navbar-nav">
                        <a class="nav-link active" href="index.php" aria-current="page">Main Site</a>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <section class="bg-dark text-light p-5 p-lg-0 pt-lg-5 text-center text-sm-start vh-100">
        <div class="container text-center">
            <h1>Reports</h1>
            </br>
            <div class="row alight-items start">
                <div class="col">
                    <h2>Dose counts</h2>
                    </br>
                    <!-- Form -->
                    <hr>
                    <form action="" class="px-5 mx-5" method="post">
                        <!-- Choose Brand -->
                        <div class="mb-3">
                            <label for="brand" class="form-label">Brand</label>
                            <select name="brand" class="form-select" id="brand-select">
                                <option value="Pfizer" <?php if($manu == "Pfizer") { echo "selected"; }?>>Pfizer
                                </option>
                                <option value="Moderna" <?php if($manu == "Moderna") { echo "selected"; }?>>Moderna
                                </option>
                                <option value="J&J" <?php if($manu == "J&J") { echo "selected"; }?>>Johnson & Johnson
                                </option>
                            </select>

                        </div>

                        <!-- Choose Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-select" id="status-select">
                                <option value="u" <?php if($status == "u") { echo "selected"; }?>>Used</option>
                                <option value="a" <?php if($status == "a") { echo "selected"; }?>>Available</option>
                                <option value="e" <?php if($status == "e") { echo "selected"; }?>>Expired</option>
                        </div>
                        </br>

                        <div class="text-center py-5 mt-3">
                            <input type="submit" class="btn btn-light" name="submit" value="Gather">
                        </div>
                    </form>

                    <div class="container-fluid">
                        <h4>Result: <?php print $value;?></h4>
                    </div>
                </div>
                <div class="col">
                    <h2>Customer Numbers</h2>
                    </br>
                    <hr>
                    <div class="container row align-items start">
                        <div class="col">
                            <h4>At least one dose:</h4>
                            </br>
                            <h6 class="display-4"><b><?php print $one_shot;?></b></h6>
                        </div>
                        <div class="col">
                            <h4>Fully vaccinated count:</h4>
                            </br>
                            <h6 class="display-4"><b><?php print $full_vac;?></b></h6>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- Waitlist and batch import section -->
    <section class="bg-secondary text-light p-5 p-lg-0 pt-lg-5 text-center text-sm-start vh-100">
        <div class="container text-center">
            <div class="row align-items start">
                <!-- Read waitlist -->
                <div class="col">
                    <!-- Update waitlist button -->
                    <h2>Update Waitlist:</h2>
                    <form action="" class="px-5 mx-5" method="post">
                        <div class="mb-3">
                            <label for="manufac" class="form-label">Manufacturer<span style="color: red;">*</span>
                                <small> (15 char max)</small>
                            </label>
                            <input type="text" maxlength="15" class="form-control " id="manufac" name="Manufacturer" placeholder="Pfizer"
                        </div>
                        </br>
                        <div class="text-center pt-2">
                            <input type="submit" class="btn btn-light" name="enter" value="Update Waitlist">
                        </div>
                    </form>
                    </br>
                    </br>
                    <!-- Waitlist queue -->
                    <h2>Waitlist</h2>
                    <table class="table">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">First</th>
                                <th scope="col">Last</th>
                                <th scope="col">Manufacturer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Populate tables
                            $conn = new mysqli('localhost', 'phpuser', 'phpwd', 'COVID');
                            $sql = "SELECT fname, lname, manufacturer FROM customer c JOIN waitlist w ON c.phonenumber=w.cphonenr";
                            $count = 1;
                            $result = mysqli_query($conn, $sql);
                            
                            while ($row = mysqli_fetch_array($result)) {
                                echo "<tr>";
                                echo "<th scope=\"row\">$count</th>";
                                echo "<td>".$row[0]."</td>";
                                echo "<td>".$row[1]."</td>";
                                echo "<td>".$row[2]."</td>";
                                echo "</tr>";
                                $count += 1;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>


    <?php include 'meta_scripts.php';?>
</body>

</html>

<?php 
  mysqli_close($conn);
?>
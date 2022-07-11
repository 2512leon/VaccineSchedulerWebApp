<?php
    session_start();

    $dose_alias = array("1"=>"1st Dose", "2"=>"2nd Dose", "3"=>"Booster");

    // Only allow access if coming from valid registration
    if ($_SESSION["valid"]) {
        // Get session data
        $fName = $_SESSION["fName"];
        $mName = $_SESSION["mName"];
        $lName = $_SESSION["lName"];
        $age = $_SESSION["age"];
        $phone = $_SESSION["phone"];

        $manufacturer = $dose = false;
        // Get data from db
        
        $conn = new mysqli('localhost', 'phpuser', 'phpwd', 'COVID');
        $sql = "SELECT * FROM customer WHERE PhoneNumber='$phone'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) == 1) {
            // The customer exists
            
            // Get current dose and vaccine
            $sql = "SELECT MAX(CurrentDose), Manufacturer FROM customer c JOIN vaccination v ON c.phonenumber=v.cphone JOIN dose d ON v.dosetrackingnum=d.dosetrackingnum JOIN batch b ON d.batchnr=b.batchnum WHERE PhoneNumber='$phone'";
            $result = mysqli_query($conn, $sql);

            // See if they have dosage and vaccine on record
            if (mysqli_num_rows($result) > 0) {
                // Get that data
                $row = mysqli_fetch_array($result);
                $manufacturer = $row[1];
                if (!empty($row[0])) {
                    $dose = (int)$row[0];
                                   
                    // Increment dose to get next shot
                    $dose += 1;
                    if ($dose == 2 && $manufacturer == "J&J") {
                        $dose = 3;
                    }
                    if ($dose > 3) {
                        $dose = 3;
                    }
                    $dose = strval($dose);
                }
            }
        } else {
            // New customer, add them to the database
            $conn = new mysqli('localhost', 'phpuser', 'phpwd', 'COVID');
            $sql = "INSERT INTO customer VALUES ('$fName', '$mName', '$lName', $age, '$phone')";
            $result = mysqli_query($conn, $sql);
        }

        // Check if POST request was submitted
        if(isset($_POST["submit"])) {
            // Get information
            $brand = $_POST["brand"];
            $dose = $_POST["dose"];

            $conn = new mysqli('localhost', 'phpuser', 'phpwd', 'COVID');
            $sql = "SELECT DoseTrackingNum, ExpirationDate FROM dose d JOIN batch b ON d.batchnr=b.batchnum WHERE d.availability='a' AND b.manufacturer='$brand'";
            $result = mysqli_query($conn, $sql);

            $status = "waitlist";
            if (mysqli_num_rows($result) > 0) {
                // We have available doses for them
                // Get tracking number
                $row = mysqli_fetch_array($result);
                $tracking_num = $row[0];
                $exp_date = $row[1];
                // Add to vaccination
                $sql = "INSERT INTO vaccination VALUES ('$phone', '$tracking_num', '$dose', '$exp_date')";
                $result = mysqli_query($conn, $sql);
                $status = $tracking_num;
                // Change availability of dose to used
                $sql = "UPDATE dose SET availability='u' WHERE dosetrackingnum=$tracking_num";
                $result = mysqli_query($conn, $sql);
            } else {
                // Waitlist them
                $sql = "INSERT INTO waitlist VALUES ('$phone', '$brand')";
                $result = mysqli_query($conn, $sql);
            }

            // DEBUG: delete later
            if(!$result) {
                echo "Error: ".$sql."<br>".mysqli_error($conn);
            }
            $_SESSION["status"] = $status;
            header('Location: status.php');
        }

    } else {
        session_destroy();
        header('Location: index.php');
    }
?>
<!doctype html>
<html lang="en">

<head>
    <?php include 'meta_head.php';?>
    <title>BUR Drugs | Registration</title>
</head>

<body>
    <?php include 'header.php';?>

    <!-- Content -->
    <section class="bg-light text-dark p-5">
        <div class="container text-center">
            <h2>Schedule Your Vaccination!</h2>
            <br />
            <p>
                Hi <?php echo $_SESSION["fName"];?>, your information has been verified and now you're ready to
                schedule!<br>
                Pick the brand of vaccine you want and the dose you need and we'll get you signed up today!
            </p>
        </div>
    </section>

    <!-- Form -->
    <section class="bg-primary text-light p-5">
        <div class="container">
            <h3>Schedule:</h3>
            <?php if(!empty($manufacturer)):?>
                <div class="container-fluid bg-success py-1 text-center">
                    <h6>It looks like we already data on your last dosage, we'll fill out the rest for you!</h6>
                </div>
            <?php endif;?>
            <hr>
            <form action="" class="px-5 mx-5" method="post">
                <!-- Choose Brand -->
                <div class="mb-3">
                    <label for="brand" class="form-label">Brand<span style="color: red;">*</span></label>
                    <select name="brand" class="form-select" id="brand-select"
                        <?php if (!empty($manufacturer)) { echo "disabled"; }?>>
                        <?php if (empty($manufacturer)):?>
                        <option selected>Pick Brand</option>
                        <option value="Pfizer">Pfizer</option>
                        <option value="Moderna">Moderna</option>
                        <option value="J&J">J&J</option>
                        <?php else:?>
                        <option value="<?php print $manufacturer;?>" selected><?php print $manufacturer;?></option>
                        <?php endif;?>
                    </select>

                </div>

                <!-- Choose Dose -->
                <div class="mb-3" id="dose-select" <?php if (!empty($manufacturer)) { echo "display: none;"; }?>>
                    <label for="dose" class="form-label">Dose<span style="color: red;">*</span></label>
                    <select name="dose" class="form-select" id="dose-selector"
                        <?php if (!empty($dose)) { echo "disabled"; }?>>
                        <?php if (empty($dose)):?>
                        <option selected>Pick Dose</option>
                        <option value="1">1st Dose</option>
                        <option value="2" id="2nd-dose" style="display: show;">2nd Dose</option>
                        <option value="3">Booster</option>
                        <?php else:?>
                        <option value="<?php print $dose;?>" selected><?php print $dose_alias[$dose];?></option>
                        <?php endif;?>
                    </select>

                </div>

                <div class="text-center pt-5">
                    <input type="submit" class="btn btn-light" name="submit" value="Schedule">
                </div>
            </form>
        </div>
    </section>

    <?php include 'footer.php';?>

    <!-- JavaScript -->
    <script type="text/javascript">
    var brand = document.querySelector('#brand-select');

    brand.addEventListener('change', (e) => {
        var brand_chosen = e.target.value;
        var dose_form = document.getElementById("dose-select");
        var second_dose = document.getElementById("2nd-dose");
        if (brand_chosen == 'Pick Brand') {
            dose_form.style.display = "none";
        } else {
            dose_form.style.display = "";
            document.getElementById("dose-selector").selectedIndex = 0;
            if (brand_chosen == "J&J") {
                second_dose.style.display = "none";
            } else {
                second_dose.style.display = "";
            }
        }
    });
    </script>
    <?php include 'meta_scripts.php';?>
</body>

</html>
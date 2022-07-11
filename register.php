<?php

  /* Array to check validity of all fields
   * 0 means valid
   * 1 means invalid
   */
  $form_checks = array("submit"=>1, "fName"=>0, "mName"=>0, "lName"=>0, "age"=>0, "phone"=>0, "phoneValid"=>0);
  $fName = $mName = $lName = $age = $phone = "";

  // Run on form submission
  if(isset($_POST["submit"])) {
    // Get form values
    $form_checks["submit"] = 0;
    $fName = $_POST["fName"];
    $mName = $_POST["mName"];
    $lName = $_POST["lName"];
    $age = $_POST["age"];
    $phone = $_POST["phone"];

    $name_pattern = "/^[a-z ]*$/i";
    $phone_pattern = "/^[0-9]{10}$/";
    ///// Validate each field /////

    // First name
    $fName = trim($fName);

    if(!preg_match($name_pattern, $fName)) {
      // Invalid name: throw error
      $form_checks["fName"] = 1;
    }

    // Middle initial
    $mName = trim($mName);

    if (!preg_match("/[a-z]/i", $mName)) {
      // Invalid name: throw error
      $form_checks["mName"] = 1;
    }

    // Last name
    $lName = trim($lName);

    if (!preg_match($name_pattern, $lName)) {
      // Invalid name: throw error
      $form_checks["lName"] = 1;
    }

    // Age
    $age = trim($age);

    if ($age < 5 || $age > 120) {
      // Age out of range: throw error
      $form_checks["age"] = 1;
    }

    // Phone Number
    $phone = trim($phone);

    if (!preg_match($phone_pattern, $phone)) {
      // Invalid phone number
      $form_checks["phone"] = 1;
    }

  }

    // Verify phone number matches other info
    $conn = new mysqli('localhost', 'phpuser', 'phpwd', 'COVID');
    $sql = "SELECT * FROM customer WHERE PhoneNumber='$phone'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        // Phone number exists in database
        // Verify other info
        $row = mysqli_fetch_array($result);

        if ($row["Fname"] != $fName) $form_checks["phoneValid"] = 1;
        if ($row["Minit"] != $mName) $form_checks["phoneValid"] = 1;
        if ($row["Lname"] != $lName) $form_checks["phoneValid"] = 1;
        if ($row["Age"] != $age) $form_checks["phoneValid"] = 1;
    }

  // If all fields are valid
  if (!array_sum($form_checks)) {
    // Save data to session
    session_start();
    $_SESSION["valid"] = true;
    $_SESSION["fName"] = $fName;
    $_SESSION["mName"] = $mName;
    $_SESSION["lName"] = $lName;
    $_SESSION["age"] = $age;
    $_SESSION["phone"] = $phone;

    die(header('Location: schedule.php'));
    // MAYBE NEEDED: die();
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
            <h2>Register Now!</h2>
            <br />
            <p>
                In the form below simply fill in your information and we'll get you ready to schedule your vaccination!
            </p>
        </div>
    </section>

    <!-- Form -->
    <section class="bg-primary text-light p-5">
        <div class="container">
            <h3>Registration Form:</h3>
            <?php if($form_checks["phoneValid"]):?>
            <div class="container-fluid bg-warning">
                <p style="color: black;"><b>WARNING:</b> It looks like that phone number is already registered, but your
                    other information does not match. Please double check your input.
                    If you believe this is a mistake, please contact our support team at: (XXX) YYY-ZZZZ</p>
            </div>
            <?php endif;?>
            <hr>
            <form action="" class="px-5 mx-5" method="post">
                <div class="mb-3">
                    <label for="fName" class="form-label">First Name<span style="color: red;">*</span><small> (15 char
                            max)</small></label>
                    <input type="text" maxlength="15" class="form-control " id="fName" name="fName" placeholder="John"
                        <?php if (!$form_checks["fName"]) {echo "value=\"$fName\"";} ?>>
                    <?php if($form_checks["fName"]): ?>
                    <p style="color: red;">Invalid: Please ensure data is filled out and is only letters</p>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="mName" class="form-label">Middle Name<span style="color: red;">*</span><small> (1
                            letter)</small></label>
                    <input type="text" maxlength="1" class="form-control" id="mName" name="mName" placeholder="M"
                        <?php if (!$form_checks["mName"]) {echo "value=\"$mName\"";} ?>>
                    <?php if($form_checks["mName"]): ?>
                    <p style="color: red;">Invalid: Field should contain 1 letter</p>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="lName" class="form-label">Last Name<span style="color: red;">*</span><small> (15 char
                            max)</small></label>
                    <input type="text" maxlength="15" class="form-control" id="lName" name="lName" placeholder="Smith"
                        <?php if (!$form_checks["lName"]) {echo "value=\"$lName\"";} ?>>
                    <?php if($form_checks["lName"]): ?>
                    <p style="color: red;">Invalid: Please ensure data is filled out and is only letters</p>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="age" class="form-label">Age<span style="color: red;">*</span></label>
                    <input type="number" class="form-control" id="age" name="age" placeholder="26"
                        <?php if (!$form_checks["age"]) {echo "value=\"$age\"";} ?>>
                    <?php if($form_checks["age"]): ?>
                    <p style="color: red;">Invalid: Field should be a number in the range of [5-120]</p>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number<span style="color: red;">*</span><small> (In the
                            form
                            of:
                            XXXYYYZZZZ)</small></label>
                    <input type="tel" maxlength="10" class="form-control" id="phone" name="phone"
                        placeholder="1112223333" <?php if (!$form_checks["phone"]) {echo "value=\"$phone\"";} ?>>
                    <?php if($form_checks["phone"]): ?>
                    <p style="color: red;">Invalid: Field should contain 10 numbers without dashes or spaces</p>
                    <?php endif; ?>
                </div>

                <div class="text-center pt-5">
                    <input type="submit" class="btn btn-light" name="submit" value="Register">
                </div>
            </form>
        </div>
    </section>

    <?php include 'footer.php';?>
    <?php include 'meta_scripts.php';?>
</body>

</html>
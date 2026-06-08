<?php
session_start();
include '../Database/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $school_id = trim($_POST['school_id']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $year = trim($_POST['year']);
    $section = trim($_POST['section']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // VALIDATION
    if (
        !$school_id ||
        !$fullname ||
        !$email ||
        !$course ||
        !$year ||
        !$section ||
        !$password ||
        !$confirm_password
    ) {

        $message = "All fields are required.";

    } elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";

    } else {

        // CHECK EMAIL
        $stmt = $conn->prepare("SELECT id FROM usernamepass WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {

            $message = "Email already registered.";

        } else {

            // HASH PASSWORD
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // INSERT USER
            $stmt = $conn->prepare("
                INSERT INTO usernamepass
                (SchoolID, UserName, Email, Course, Year, Section, Password)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "sssssss",
                $school_id,
                $fullname,
                $email,
                $course,
                $year,
                $section,
                $hashed_password
            );

            if ($stmt->execute()) {

                $_SESSION['success'] = "Register Success!";

            } else {

                $message = "Error registering user.";

            }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register Form</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body.bg-img {
            background: url('https://cdn.wallpapersafari.com/52/64/MjEnvk.jpg') no-repeat center center/cover;
            position: relative;
        }

        body.bg-img::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: -1;
        }

        .form-wrapper {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-control,
        .form-select {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .form-control::placeholder {
            color: #ddd;
        }

        .form-select option {
            color: black;
        }

    </style>

</head>

<body class="d-flex align-items-center justify-content-center min-vh-100 text-white bg-img">

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-6">

            <div class="card-body p-4">

                <!-- ALERT -->
                <?php if (isset($_SESSION['success'])): ?>

                    <div class="alert alert-success text-center">
                        <?php echo $_SESSION['success']; ?>
                    </div>

                    <?php unset($_SESSION['success']); ?>

                <?php elseif (!empty($message)): ?>

                    <div class="alert alert-warning text-center">
                        <?php echo $message; ?>
                    </div>

                <?php endif; ?>

                <!-- FORM -->
                <form class="form-wrapper" action="" method="POST">

                    <h3 class="text-center mb-4">Register</h3>

                    <!-- SCHOOL ID -->
                    <div class="mb-3">
                        <label class="form-label">School ID</label>

                        <input type="text"
                               name="school_id"
                               class="form-control"
                               placeholder="Enter School ID"
                               required>
                    </div>

                    <!-- FULL NAME -->
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>

                        <input type="text"
                               name="fullname"
                               class="form-control"
                               placeholder="Enter your full name"
                               required>
                    </div>

                    <!-- EMAIL -->
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>

                        <input type="email"
                               name="email"
                               class="form-control"
                               placeholder="Enter your email"
                               required>
                    </div>

                    <!-- COURSE / YEAR / SECTION — 3 separate columns -->
                    <div class="row g-2 mb-3">

                        <!-- COURSE -->
                        <div class="col-md-4">
                            <label class="form-label">Course</label>

                            <select name="course" class="form-select" required>

                                <option value="">Course</option>
                                <option value="BSIT">BSIT</option>
                                <option value="BSCS">BSCS</option>
                                <option value="BSBA">BSBA</option>

                            </select>
                        </div>

                        <!-- YEAR -->
                        <div class="col-md-4">
                            <label class="form-label">Year</label>

                            <select name="year" class="form-select" required>

                                <option value="">Year</option>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>

                            </select>
                        </div>

                        <!-- SECTION -->
                        <div class="col-md-4">
                            <label class="form-label">Section</label>

                            <select name="section" class="form-select" required>

                                <option value="">Section</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="E">E</option>

                            </select>
                        </div>

                    </div>

                    <!-- PASSWORD -->
                    <div class="mb-3">

                        <label class="form-label">Password</label>

                        <input type="password"
                               name="password"
                               class="form-control"
                               placeholder="Enter password"
                               required>

                    </div>

                    <!-- CONFIRM PASSWORD -->
                    <div class="mb-3">

                        <label class="form-label">Confirm Password</label>

                        <input type="password"
                               name="confirm_password"
                               class="form-control"
                               placeholder="Confirm password"
                               required>

                    </div>

                    <!-- BUTTON -->
                    <div class="d-grid">

                        <button type="submit"
                                class="btn btn-info rounded-pill text-dark">

                            Register

                        </button>

                    </div>

                    <!-- LOGIN -->
                    <p class="text-center mt-3">

                        Already have an account?

                        <a href="index.php"
                           class="text-decoration-none text-info">

                            Login here

                        </a>

                    </p>

                </form>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
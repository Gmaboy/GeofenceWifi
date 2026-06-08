<?php
session_start();
include '../Database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT ID, UserName, Password, role FROM usernamepass WHERE Email='$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Support both MD5 (old) and password_hash (new)
        $match = password_verify($password, $user['Password'])
               || md5($password) === $user['Password']
               || $password      === $user['Password'];

        if ($match) {
            $_SESSION['user_id']  = $user['ID'];
            $_SESSION['fullname'] = $user['UserName'];
            $_SESSION['role']     = $user['role'] ?? 'student';

            if ($user['role'] === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: student_portal.php");
            }
            exit;
        }
    }

    $error = "Invalid email or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Page</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

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

        #charCount {
            font-size: 0.9rem;
            color: #ccc;
            margin-top: 0.25rem;
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
            transition: all 0.3s ease;
        }

    </style>
</head>

<body class="d-flex align-items-center justify-content-center vh-100 text-white bg-img">

<div class="container">

    <div class="row justify-content-center">

        <div class="col-md-6 col-lg-4">

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

            <form class="form-wrapper" action="" method="POST" id="loginForm">

                <div class="text-center mb-4">
                    <h3>Login</h3>
                </div>

                <!-- SCHOOL ID -->
                <div class="mb-3">
                    <label>School ID:</label>
                    <input type="text"
                           name="school_id"
                           class="form-control"
                           placeholder="Enter School ID"
                           >
                </div>

                <!-- EMAIL -->
                <div class="mb-3">
                    <label>Email:</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           placeholder="Enter email"
                           required>
                </div>

                <!-- PASSWORD -->
                <div class="mb-3">
                    <label>Password:</label>

                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control"
                           placeholder="Password"
                           required>

                    <div id="charCount">0 characters</div>
                </div>

                <!-- BUTTON -->
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-info rounded-pill">
                        SIGN IN
                    </button>
                </div>

                <!-- FOOTER -->
                <div class="d-flex justify-content-between">

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox">
                        <label class="form-check-label">
                            Remember Me
                        </label>
                    </div>

                    <a href="register.php"
                       class="link-light link-underline-opacity-0">
                        Register account
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

<script>

$(document).ready(function() {

    $("#password").keyup(function() {

        let length = $(this).val().length;

        $("#charCount").text(length + " characters");

        if (length >= 6 && length <= 15) {

            $(this)
                .removeClass("is-invalid")
                .addClass("is-valid")
                .css("border-color", "green");

        } else {

            $(this)
                .removeClass("is-valid")
                .addClass("is-invalid")
                .css("border-color", "red");

        }

    });

    $("#loginForm").submit(function(e) {

        let password = $("#password").val();

        if (password.length < 6 || password.length > 15) {

            e.preventDefault();

            alert("Password must be between 6 and 8 characters!");

        }

    });

});

</script>

</body>
</html>
<?php
// 1. Error Reporting (Keeping  this to show errors if they happen)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Database Connection Details
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ecommerce_db";

// 3. Establish Connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection and die if it fails (This prevents blank screens from connection issues)
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// 4. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if ALL required fields are set in the POST data
    if (isset($_POST["username"], $_POST["email"], $_POST["password"], $_POST["confirm_password"])) {
        
        $username = trim($_POST["username"]);
        $email = trim($_POST["email"]);
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];

        // Basic Validation
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
             $message = "<p class='error'>⚠ Please fill out all required fields.</p>";
        } elseif ($password !== $confirm_password) {
            $message = "<p class='error'>⚠ Passwords do not match!</p>";
        } else {
            // Hash the password for security
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

            // Prepare the statement
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);

            // <<< CRITICAL FIX: Check if prepare() failed >>>
            if ($stmt === false) {
                // If the SQL fails (e.g., table 'users' does not exist), show the MySQL error
                $message = "<p class='error'>Database Preparation Error: " . $conn->error . "</p>";
            } else {
                // Bind parameters and execute
                $stmt->bind_param("sss", $username, $email, $hashed_pass);

                if ($stmt->execute()) {
                    $message = "<p class='success'>🎉 Registration Successful!</p>";
                } else {
                    // Check for common errors like duplicate entry (error code 1062)
                    if ($conn->errno == 1062) {
                         $message = "<p class='error'>Database Error: Username or Email already exists.</p>";
                    } else {
                         // General execution error
                         $message = "<p class='error'>Database Error: " . $stmt->error . "</p>";
                    }
                }
                
                // Close the statement
                $stmt->close();
            }
        }
    } else {
        // Fallback for when the form was posted but some fields were missing (unlikely with required attributes)
        $message = "<p class='error'>⚠ Form submitted with missing data. Please try again.</p>";
    }
}

// Close the connection at the end of the script
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ecommerce Registration</title>
    <style>
        body {
            background: linear-gradient(135deg, #4e54c8, #8f94fb);
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            width: 350px;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0px 5px 20px rgba(0,0,0,0.2);
            text-align: center;
        }

        .container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #bbb;
            border-radius: 8px;
            font-size: 16px;
        }

        .password-box {
    position: relative;
    /* Ensure it takes up the full width available */
    width: 100%; 
    /* This centers the box within the container */
    margin: 10px auto; 
}

/* Ensure the input inside the box also uses full width */
.password-box input {
    width: 100%;
}

        .eye {
            position: absolute;
            right: 10px;
            top: 13px;
            cursor: pointer;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            background: #4e54c8;
            color: white;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #3b40a0;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        .success {
            color: green;
            font-weight: bold;
        }

    </style>
</head>
<body>

<div class="container">
    <h2>Create Account</h2>

    <?php echo $message; ?>

    <form action="" method="POST">
        <input type="text" name="username" placeholder="Enter Username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>

        <input type="email" name="email" placeholder="Enter Email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>

        <div class="password-box">
            <input type="password" id="password" name="password" placeholder="Password" required>
            <span class="eye" onclick="togglePassword('password')">👁</span>
        </div>

        <div class="password-box">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
            <span class="eye" onclick="togglePassword('confirm_password')">👁</span>
        </div>

        <button type="submit">Register</button>
    </form>
</div>

<script>
function togglePassword(id) {
    let field = document.getElementById(id);
    field.type = field.type === "password" ? "text" : "password";
}
</script>

</body>
</html>
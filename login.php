<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
  header("location: welcome.php");
  exit;
}
 
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $set_last_in_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $get_user = "SELECT id, username, password FROM users WHERE username = ?";

        if($stmt_get_user = mysqli_prepare($link, $get_user)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt_get_user, "s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt_get_user)){
                // Store result
                mysqli_stmt_store_result($stmt_get_user);
                
                // Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt_get_user) == 1){                    
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt_get_user, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt_get_user)){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, so start a new session
                            session_start();

                            // Get last singin date
                            $last_sign_in = "";

                            $get_last_in = "SELECT last_sign_in FROM users WHERE username = ?";

                            if($stmt_last_in = mysqli_prepare($link, $get_last_in)){

                                mysqli_stmt_bind_param($stmt_last_in, "s" ,$param_username);

                                if(mysqli_stmt_execute($stmt_last_in)){

                                    mysqli_stmt_store_result($stmt_last_in);

                                    if(mysqli_stmt_num_rows($stmt_last_in) == 1){

                                        mysqli_stmt_bind_result($stmt_last_in, $last_sign_in);
                                        mysqli_stmt_fetch($stmt_last_in);
                                    }
                                }else{

                                    $set_last_in_err = mysqli_stmt_error($stmt_last_in);

                                }
                            }else{

                                $set_last_in_err = mysqli_stmt_error($stmt_last_in);
                                
                            }
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["last_sign_in"] = $last_sign_in;

                            // update last signin 
                            $current = date_format(new DateTime(), 'Y-m-d H:i:s');

                            $set_last_in = "UPDATE users set last_sign_in=? WHERE username = ?";

                            if($stmt_set_last_in = mysqli_prepare($link, $set_last_in)){

                                mysqli_stmt_bind_param($stmt_set_last_in, "ss", $current ,$param_username);

                                if(mysqli_stmt_execute($stmt_set_last_in)){
                                    // Redirect user to welcome page
                                    header("location: welcome.php");

                                }else{

                                    $set_last_in_err = mysqli_stmt_error($stmt_set_last_in);

                                }
                            }else{

                                $set_last_in_err = mysqli_stmt_error($stmt_set_last_in);

                            }
                            
                        } else{
                            // Display an error message if password is not valid
                            $password_err = "The password you entered was not valid.";
                        }
                    }
                } else{
                    // Display an error message if username doesn't exist
                    $username_err = "No account found with that username.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; echo $set_last_in_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label><?php echo $upd_err; ?>
                <input type="password" name="password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
        </form>
    </div>    
</body>
</html>
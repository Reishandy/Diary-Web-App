<?php
/**
 * This is the login and sing up form page
 *
 * Template from: https://codepen.io/mamislimen/pen/jOwwLvy
 * Modified by to satisfy the project requirement by Reishandy
 *
 * @author Reishandy (isthisruxury@gmail.com)
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Authentication</title>
    <link rel="stylesheet" href="../../public/css/auth.css">

    <link rel="shortcut icon" href="../../assets/favlogo.svg" type="image/svg+xml">
</head>
<body>
<div class="main">
    <input type="checkbox" id="chk" aria-hidden="true"
        <?php if (isset($_GET["login"])) echo 'checked'; ?>
    >

    <div class="signup">
        <form action="../index/handler.php" method="post">
            <label for="chk" aria-hidden="true">Sign up</label>
            <input type="hidden" name="action" value="register">

            <input type="text" name="username" placeholder="User name" required="">
            <?php if (isset($_GET["register_username_error"])) echo "<p>" . $_GET["register_username_error"] . "</p>"; ?>

            <input type="password" name="password" placeholder="Password" required="">
            <?php if (isset($_GET["register_password_error"])) echo "<p>" . $_GET["register_password_error"] . "</p>"; ?>

            <input type="password" name="re-password" placeholder="Re input Password" required="">
            <?php if (isset($_GET["register_re-password_error"])) echo "<p>" . $_GET["register_re-password_error"] . "</p>"; ?>

            <button type="submit" class="btn btn-primary">Sign up</button>
        </form>
    </div>

    <div class="login">
        <form action="../index/handler.php" method="post">
            <label for="chk" aria-hidden="true">Sign in</label>
            <input type="hidden" name="action" value="login">

            <input type="text" name="username" placeholder="User Name" required>
            <?php if (isset($_GET["login_username_error"])) echo "<p>" . $_GET["login_username_error"] . "</p>"; ?>

            <input type="password" name="password" placeholder="Password" required>
            <?php if (isset($_GET["login_password_error"])) echo "<p>" . $_GET["login_password_error"] . "</p>"; ?>

            <button type="submit" class="btn btn-primary">Sign in</button>
        </form>
    </div>
</div>
</body>
<script>
    if (typeof window.history.pushState == 'function') {
        window.history.pushState({}, "Hide", '<?php echo $_SERVER['PHP_SELF'];?>');
    }
</script>
</html>

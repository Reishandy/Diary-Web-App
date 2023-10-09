<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>

    <style>
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            margin-bottom: 10px;
        }

        p {
            margin-top: -10px;
            color: red;
        }

        input {
            margin-bottom: 20px;
            padding: 5px;
            width: 200px;
        }

        button {
            padding: 5px 10px;
            margin-bottom: 20px;
        }
    </style>

    <?php session_start() ?>
</head>
<body>

<div class="container">
    <form action="../../public/index/index.php" method="post">
        <input type="hidden" name="action" value="register">

        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>
        <?php if (isset($_GET["username_error"])) echo "<p>" . $_GET["username_error"] . "</p>"; ?>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
        <?php if (isset($_GET["password_error"])) echo "<p>" . $_GET["password_error"] . "</p>"; ?>

        <label for="re-password">Re-input Password</label>
        <input type="password" name="re-password" id="re-password" required>
        <?php if (isset($_GET["re-password_error"])) echo "<p>" . $_GET["re-password_error"] . "</p>"; ?>

        <button type="submit">Submit</button>
    </form>

    <form action="../../public/authentication/login.php">
        <button type="submit">Login</button>
    </form>
</div>

</body>

<script>
    if (typeof window.history.pushState == 'function') {
        window.history.pushState({}, "Hide", '<?php echo $_SERVER['PHP_SELF'];?>');
    }
</script>

</html>
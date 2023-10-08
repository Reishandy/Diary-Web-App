<?php
/**
 * This is the web app's hub
 *
 * This index.php file will do all the logic changing and the entire program's flow, it can redirect and handle all
 * interpret communication and action
 *
 * @author Reishandy (isthisruxury@gmail.com)
 */

use JetBrains\PhpStorm\NoReturn;

require_once "../../src/database/database-user.php";
require_once "../../src/session/session.php";
require_once "../../src/config/config.php";

if (session_status() == PHP_SESSION_DISABLED) {
    ini_set("session.gc_maxlifetime", TIMEOUT);
    session_set_cookie_params(0);
    session_start();
}

/**
 * Main hub, used to handle action redirection and session checker
 */
$action = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST["action"];
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET["action"];
}

switch ($action) {
    case "login":
        loginHandler();
        break;
    case "register":
        registerHandler();
        break;
    case "":
        session();
        break;
    default:
        echo "<center><h1>ERROR</h1></center>";
}


/**
 * Action handler, collection of functions used to handle specific action
 */
function loginHandler(): void
{
    $username = $_POST["username"];
    $password = $_POST["password"];

    $status = getUser($username, $password);

    switch ($status) {
        case 0:
            // TODO: redirect to main menu
            echo "Login successful<br>";
            echo getUsername();
            break;
        case 1:
            header("Location: ../../public/authentication/login.php?username_error=true");
            exit();
        case 2:
            header("Location: ../../public/authentication/login.php?password_error=true");
            exit();
        case -1:
            databaseNotConnectedHandler();
    }
}

function registerHandler(): void
{
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $rePassword = filter_input(INPUT_POST, 're-password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (strlen($username) < 3) {
        header("Location: ../../public/authentication/register.php?username_error=Must be more than 3 character");
        exit();
    }

    if (strlen($password) < 8) {
        header("Location: ../../public/authentication/register.php?password_error=Must be more than 8 character");
        exit();
    }

    if ($rePassword != $password) {
        header("Location: ../../public/authentication/register.php?re-password_error=Password does not match");
        exit();
    }

    $status = addUser($username, $password);

    switch ($status) {
        case 0:
            header("Location: ../../public/authentication/login.php");
            exit();
        case 1:
            header("Location: ../../public/authentication/register.php?username_error=Username already taken");
            exit();
        case -1:
            databaseNotConnectedHandler();
    }
}


/**
 * Utility functions, collection of functions to support the main hub or action handler
 */
#[NoReturn] function databaseNotConnectedHandler(): void
{
    die("<center><h1>DATABASE NOT CONNECTED</h1></center>");
}

function session(): void
{
    if (!checkSession()) {
        destroySession();
        header("Location: ../../public/authentication/login.php");
        exit();
    }
}

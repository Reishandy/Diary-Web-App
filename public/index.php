<?php
/**
 * This is the web app's hub
 *
 * This index.php file will do all the logic changing and the entire program's flow, it can redirect and handle all
 * interpret communication and action
 *
 * @author Reishandy (isthisruxury@gmail.com)
 */
if (session_status() == PHP_SESSION_DISABLED) {
    session_start();
}
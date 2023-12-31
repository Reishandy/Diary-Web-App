<?php
/**
 * Config file containing constant variables for use in this project
 *
 * @author Reishandy (isthisruxury@gmail.com)
 */

// Security module's constants
const ENCRYPTION_ALGORITHM = "AES-256-CBC";
const HASH_ALGORITHM = "sha3-256";
const PRIMARY_SIZE = 32;
const SECONDARY_SIZE = 16;
const ITERATION = 10000;

// Database module's constant
const DB_USERNAME = "root";
const DB_PASSWORD = "";
const DATABASE = "diary";
const HOSTNAME = "localhost";

// Session module's constant
const TIMEOUT = 10800; // 3 Hour
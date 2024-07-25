<?php
/**
 * Bootstrap file for handling file uploads and analysis.
 *
 * This script initializes the FileController and handles incoming file upload requests.
 *
 * PHP version 8.2
 *
 * @category Example
 * @package  Filezer\Controller
 */

// Include Composer autoload file to load dependencies
require_once __DIR__ . "/../vendor/autoload.php";

use Filezer\Controller\FileController;

// Create a new instance of FileController
$fileController = new FileController();

// Handle the request
$fileController->handleRequest();
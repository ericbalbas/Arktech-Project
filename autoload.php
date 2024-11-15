<?php
spl_autoload_register(function ($class) {
    // Replace backslashes in the namespace with directory separators
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    // Define the base directory for your project (where classes are located)
    $baseDir = __DIR__ .'/';

    // Create the full path to the class file
    $file = $baseDir . $classPath . '.php';
    // echo $file . "_exist<br>";

    // Include the file if it exists
    if (file_exists($file)) {
        // echo $file . "_exist<br>"; 
        require_once $file;
    }
});

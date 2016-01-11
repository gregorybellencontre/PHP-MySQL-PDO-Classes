<?php
  // Database parameters
  define('DB_HOST','localhost');
  define('DB_USER','root');
  define('DB_PASSWORD','');
  define('DB_NAME','');
  
  // Class autoloading
  spl_autoload_register(function ($class) {
    include_once('classes/' . $class . '.class.php');
  });
  
  // Database connection
  $Db = new MySQL(array(
    'db_host' => DB_HOST,
    'db_user' => DB_USER,
    'db_password' => DB_PASSWORD,
    'db_name' => DB_NAME
  ));
  
  // PDO instance
  $Db = $Db->getInstance();
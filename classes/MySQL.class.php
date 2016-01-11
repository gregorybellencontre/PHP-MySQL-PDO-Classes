<?php
/**
 * MySQL Connection Class (using PDO)
 *
 * @author		Grégory Bellencontre
 * @link		https://github.com/gregorybellencontre
 */  

  final class MySQL {
    private $db = null;
    
    /**
     * Create a new instance
     *
     * @param array $params Parameters list
     * @return mixed
     */
    public function __construct($params)
    {
      if ($this->db === null) {
        $this->connect($params);
      }
      
      return $this->getInstance();
    }
    
    /**
     * Create a database connexion
     *
     * @param array $params Parameters list
     */
    private function connect($params)
    {
      try {
         $this->db = new PDO('mysql:host=' . $params['db_host'] . ';dbname=' . $params['db_name'], $params['db_user'],$params['db_password'],array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
         $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
      catch(Exception $e)
      {
        die('Database connection has failed : '.$e->getMessage());
      }
    }
    
    /**
     * Get PDO instance
     *
     * @return PDO object
     */
    public function getInstance()
    {
      return $this->db;
    }
  }
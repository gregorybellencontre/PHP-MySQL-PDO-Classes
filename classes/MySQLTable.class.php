<?php
/**
 * MySQL Table Class
 *
 * @author		Grégory Bellencontre
 * @link		https://github.com/gregorybellencontre
 */ 
  
  abstract class MySQLTable {
    private $db;
    private $class_name;
    protected $table_name;
    private $table_columns;
    protected $table_identifier = 'id';
    protected $blacklisted_columns;
    protected $search_columns;
    private $find_request;
    
    /**
     * Instanciate table with its columns
     *
     * @param PDO $Db PDO instance
     */
    public function __construct($Db)
    {
      $this->db = $Db;
      $this->class_name = get_class($this);
      
      if (empty($this->table_name)) {
        $this->table_name = strtolower(get_class($this));
      }
      
      $query = $this->db->query("SHOW COLUMNS FROM " . $this->table_name);
      
      if ($query->rowCount() > 0) {
        $query->setFetchMode(PDO::FETCH_OBJ);
        
        $this->table_columns = new stdClass();
        
        while ($result = $query->fetch()) {
          $property = $result->Field;
          $this->table_columns->$property = null;
        }
      }
    }
    
    /**
     * Get columns list
     *
     * @return stdClass Columns object
     */
    public function getColumns()
    {
      return $this->table_columns;
    }
    
    /**
     * Get a column value
     *
     * @param string $column_name Column name
     * @return mixed Column value
     */
    public function get($column_name)
    {
      return isset($this->table_columns->$column_name) ? $this->table_columns->$column_name : false;
    }
    
    /**
     * Set a new column value
     *
     * @param string $column_name Column name
     * @param mixed $column_value Column value
     * @return mixed
     */
    public function set($column_name,$column_value)
    {
      return isset($this->table_columns->$column_name) ? $this->table_columns->$column_name = $column_value : false;
    }
    
    /**
     * Hydrate record columns with an array of data
     *
     * @param array $data Array of key/value
     */
    public function hydrate($data)
    {
      if (!empty($data)) {
        foreach($data as $column_name=>$column_value) {
          if (isset($this->table_columns->$column_name)) {
            $this->table_columns->$column_name = $column_value;
          }
        }
      }
    }
    
    /**
     * Get last inserted ID
     *
     * @return integer ID
     */
    public function getLastId() {
      return $this->db->insert_id;
    }
    
    /**
     * Get the total number of records in the table
     *
     * @return integer Count number
     */
    public function getRecordsNb() {
      $req = $this->db->query("SELECT COUNT(" . $this->table_identifier . ") AS nb FROM " . $this->table_name);
      $data = $req->fetch_object();
      
      return $data->nb;
    }
    
    /**
     * Get a single record by ID
     *
     * @param mixed $id Record ID
     * @return object Record
     */
    public function findOne($id)
    {
      $query = $this->db->prepare("SELECT * FROM " . $this->table_name . " WHERE " . $this->table_identifier . "=:id");
      $query->bindParam('id',$id);
      $query->setFetchMode(PDO::FETCH_OBJ);
      $query->execute();
      
      if ($query->rowCount() == 1) {
        $result = $query->fetch();
        $Record = new $this->class_name($this->db);
        $Record->hydrate($result);
        
        return $Record;
      }
      
      return false;
    }
    
    /**
     * Setup a search request with a query string
     * /!\ Search columns need to be defined in the child class
     *
     * @param mixed $term Query string
     * @return object
     */
    public function search($term)
    {
      $this->find_request = null;
      
      if (!empty($this->search_columns) && is_array($this->search_columns)) {
        $this->find_request['where'] = "WHERE ";
        
        foreach($this->search_columns as $column_name) {
          $this->find_request['where'].= $column_name . ' LIKE :term OR ';
        }
        
        $this->find_request['params']['term'] = "%$term%";
        
        $this->find_request['where'] = trim($this->find_request['where'],'OR ');
      }
      
      return $this;
    }
    
    /**
     * Setup a search request without criterias
     *
     * @return object Objet enfant
     */
    public function findAll()
    {
      $this->find_request = null;
      
      return $this;
    }
    
    /**
     * Setup a search request with criterias
     *
     * @param array $criterias Search criterias
     * @return object
     */
    public function findWhere($criterias)
    {
      $this->find_request = null;
      
      if (!empty($criterias) && is_array($criterias)) {
        foreach($criterias as $key=>$criteria) {
          $where = empty($where) ? "WHERE " . $criteria : $where. " AND " . $criteria;
        }
      }
      
      $this->find_request['where'] = $where;
      
      return $this;
    }
    
    /**
     * Bind a parameter to the current search request
     *
     * @param string $key Parameter name
     * @param mixed $value Parameter value
     * @return object
     */
    public function bindParam($key,$value)
    {
      $this->find_request['params'][$key] = $value;
      
      return $this;
    }
    
    /**
     * Add a GROUP BY clause to the current request
     *
     * @param mixed $columns Column(s) to group by
     * @return object
     */
    public function groupBy($columns)
    {
      if (is_array($columns)) {
        $this->find_request['groupBy'] = "GROUP BY " . implode(',',$columns);
      }
      else {
        $this->find_request['groupBy'] = "GROUP BY " . $columns;
      }
      
      return $this;
    }
    
    /**
     * Add an ORDER BY clause to the current request
     *
     * @param string $column Column name
     * @param integer $order Order (1=ASC, -1=DESC)
     * @return object
     */
    public function orderBy($column,$order=null)
    {
      $this->find_request['order'] = "ORDER BY " . $column . (!empty($order) && $order === -1 ? ' DESC' : '');
      
      return $this;
    }
    
    /**
     * Add a LIMIT clause to the current request
     *
     * @param integer $page Page number
     * @param integer $per_page Number of results per page
     * @return object
     */
    public function limit($page,$per_page)
    {
      $start = ($page * $per_page) - $per_page;
      
      $this->find_request['limit'] = "LIMIT $start,$per_page";
      
      return $this;
    }
    
    /**
     * Execute the current search request
     *
     * @return array Search results
     */
    public function fetch()
    {
      $request = "SELECT * FROM " . $this->table_name;
      $request.= !empty($this->find_request['where']) ? " " . $this->find_request['where'] : "";
      $request.= !empty($this->find_request['groupBy']) ? " " . $this->find_request['groupBy'] : "";
      $request.= !empty($this->find_request['order']) ? " " . $this->find_request['order'] : "";
      $request.= !empty($this->find_request['limit']) ? " " . $this->find_request['limit'] : "";
      
      $query = $this->db->prepare($request);
      
      if (!empty($this->find_request['params'])) {
        foreach($this->find_request['params'] as $key=>&$value) {
          $query->bindParam($key,$value);
        }
      }
      
      $this->find_request = null;
      
      $query->execute();
      $query->setFetchMode(PDO::FETCH_OBJ);
      
      if ($query->rowCount() > 0) {
        $data = array();
        
        while ($result = $query->fetch()) {
          $Record = new $this->class_name($this->db);
          $Record->hydrate($result);
        
          $data[] = $Record;
        }
        
        return $data;
      }
      else {
        return array();
      }
    }
    
    /**
     * Save record values in the table
     *
     * @return mixed
     */
    public function save()
    {
      $identifier = $this->table_identifier;
      
      if (empty($this->table_columns->$identifier)) {
        $table_columns = (array) $this->table_columns;
        
        // Exclude blacklisted columns for insert
        if (!empty($this->blacklisted_columns['insert'])) {
          foreach($this->blacklisted_columns['insert'] as $column_key) {
            unset($table_columns[$column_key]);
          }
        }
        
        $table_columns_names = implode(',',array_keys($table_columns));
        $table_columns_params = implode(',',preg_filter('/^/', ':', array_keys($table_columns)));
        
        $query = $this->db->prepare("INSERT INTO " . $this->table_name . "($table_columns_names) VALUES($table_columns_params)");
        
        foreach($table_columns as $key=>&$value) {
          $query->bindParam(':'.$key,$value);
        }
        
        return $query->execute();
      }
      else {
        $table_identifier = $this->table_identifier;
        $table_columns = (array) $this->table_columns;
        unset($table_columns[$table_identifier]); // Exclude identifier for update
        
        // Exclude blacklisted columns for update
        if (!empty($this->blacklisted_columns['update'])) {
          foreach($this->blacklisted_columns['update'] as $column_key) {
            unset($table_columns[$column_key]);
          }
        }
        
        foreach($table_columns as $key=>$value) {
          $table_columns_list[$key] = $key . '=:' . $key;
        }
        
        if (!empty($table_columns_list)) {
          $table_columns_list = implode(',',$table_columns_list);
        }
        
        $query = $this->db->prepare("UPDATE " . $this->table_name . " SET $table_columns_list WHERE " . $this->table_identifier . "=:id");
        $query->bindParam('id',$this->table_columns->$table_identifier);
        
        foreach($table_columns as $key=>&$value) {
          $query->bindParam(':'.$key,$value);
        }
        
        return $query->execute();
      }
    }
    
    /**
     * Delete the record
     *
     * @return mixed
     */
    public function remove()
    {
      $table_identifier = $this->table_identifier;
      
      if (!empty($this->table_columns->$table_identifier)) {
        $query = $this->db->prepare("DELETE FROM " . $this->table_name . " WHERE " . $this->table_identifier . "=:id");
        $query->bindParam('id',$this->table_columns->$table_identifier);
        
        return $query->execute();
      }
      
      return false;
    }
    
  }
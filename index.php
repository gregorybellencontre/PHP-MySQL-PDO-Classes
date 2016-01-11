<?php
  require_once('config.php');
  
  $Demo = new Demo($Db);
  
  // Setting a new value
  $Demo->set('field_name','field_value');
  
  // Getting a value
  $field_name = $Demo->get('field_name');
  
  // Getting all record values
  $fields = $Demo->getColumns();
  
  // Searching a record by ID
  $Record = $Demo->findOne(1); // Returns an object
  
  // Searching records with criterias
  $records = $Demo->findWhere(array('field_name=:field_name'))
                  ->bindParam(':field_name','field_value')
                  ->groupBy(array('field_name'))
                  ->orderBy('field_name',-1) // Column name, Direction (1 = ASC, -1 = DESC)
                  ->limit(1,10) // Current page, Records per page
                  ->fetch();
                  
  // Searching records with a query string
  $records = $Demo->search('hello world')
                  ->limit(1,10) // Current page, Records per page
                  ->fetch();
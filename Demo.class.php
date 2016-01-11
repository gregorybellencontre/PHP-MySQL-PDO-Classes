<?php
  final class Demo extends MySQLTable {
    protected $table_name = 'demo';
    
    protected $blacklisted_columns = array(
      'update' => array('field_name')
    );
    
    protected $search_columns = array('field1','field2');
    
  }
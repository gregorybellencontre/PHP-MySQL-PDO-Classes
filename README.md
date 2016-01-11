# PHP-MySQL-PDO-Classes
Two PHP classes for connecting and sending requests to an existing MySQL database.

## Quick start

 - **MySQL.class.php** > To perform the database connection and handle the PDO instance.
 - **MySQLTable.class.php** > To send requests to the database using the PDO instance.

Your classes need to reflect the tables, and extend from MySQLTable Class.

You can edit class properties if it's necessary :

 - $table_name *(string)* > If your class name in lowercase doesn't match your table name
 - $table_identifier *(string)* > If your table identifier isn't named "id"
 - $blacklisted_columns *(array)* > If you need to blacklist some fields for insert or update requests.
 - $search_columns *(array)* > Fields to use in the search method. You have to fill this property if you want to use the search method.

You will find some examples in the Demo.class.php file.

## Using the classes methods

MySQLTable Class contains methods to work with your data.

 - hydrate(data) > Hydrate record columns with an array of data
 - set(column_name,column value) > Set a new column value
 - get (column_name) > Get a column value
 - getColumns() > Get an array containing record columns and values
 - getLastId() > Get the last inserted ID
 - getRecordsNb() > Get the total number of records in the table
 - findOne(id) > Return a single record by ID
 - search(term) > Start a search request with a query string *(search columns need to be defined)*
 - findAll() > Start a search request without criterias
 - findWhere(criterias) > Start a search request with criterias *(array)*
 - bindParam(key,value) > Bind a value to a given parameter
 - groupBy(columns) > Add a GROUP BY clause to the current request
 - orderBy(column,order) > Add an ORDER BY clause to the current request
 - limit(page,per_page) > Add a LIMIT clause to the current request
 - fetch() > Execute the current request
 - save() > Save record values (perform INSERT or UPDATE according to the identifier)
 - remove() > Remove the record

You will find some examples in the index.php file.
Classes are documented if you need more information about the methods.
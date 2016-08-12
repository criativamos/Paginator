# Paginator

A simple PHP library to create pagination.

### Installation

```sh
$ composer require criativamos/paginator
```

### How to use
You can see the full implementation in example.php
Do not forget to create the test database mock_data.sql
```
$query = "SELECT * FROM mock_data"
//the first parameter is a PDO instance with database connection
//the third parameter is the number of items per page. The default value is 15
$pg = new \Criativamos\Paginator\Paginator($pdoconnection, $query, 10);
//print result
if($pg->rowCount() > 0){
    foreach ($pg->getData() as $data){
        echo '<tr>';
        echo '<td>'.$data->id.'</td>';
        echo '<td>'.$data->first_name.'</td>';
        echo '<td>'.$data->last_name.'</td>';
        echo '<td>'.$data->email.'</td>';
        echo '</tr>';
    }
}
...
//pagination
$pg->render();
```

### Query with parameters
```
$query = "SELECT * FROM mock_data WHERE LOWER (first_name) LIKE :SEARCH";
$pg = new \Criativamos\Paginator\Paginator($pdoconnection, $query);
$pg->setParameters([
    ':SEARCH' => '%jo%'
]);
```
You can do the same like this
```
$query = "SELECT * FROM mock_data WHERE LOWER (first_name) LIKE :SEARCH";
$pg = new \Criativamos\Paginator\Paginator($pdoconnection);
$pg->setQuery($query, [
    ':SEARCH' => '%jo%'
]);
```

### Get detailed results
```
$query = "SELECT * FROM mock_data"
$pg = new \Criativamos\Paginator\Paginator($pdoconnection, $query);
print_r( $pg->results() );
```
Output
```
Array
(
    [currentPage] => 1
    [total] => 200
    [perPage] => 10
    [lastPage] => 19
    [nextPageUrl] => http://localhost/lab/pagination/example.php?page=2
    [prevPageUrl] => http://localhost/lab/pagination/example.php
    [currentUrl] => http://localhost/lab/pagination/example.php
    [from] => 1
    [to] => 10
    [data] => Array
        (
            ...
        )
)            
        
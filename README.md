# EasyPDO
A convenient wrapper for PDO. Supports only MySQL at the moment.
It is meant to simplify common queries in order to reduce the code to be written.

## Example

```php
// Insert row using prepared statement and get insert id
// PDO
$stmt = $db->prepare("INSERT INTO table(col1,col2) VALUES(:col1, :col2)");
$stmt->execute(["col1"=>"val1", "col2"=>"val2"]);
$id = $db->lastInsertId();

// EasyPDO
$id = $db->insert("table", ["col1" => "val1", "col2"=>"val2"]);


// Select rows from DB
//PDO
$stmt2 = $db->prepare("SELECT * FROM table WHERE col1 = :col1 AND col2 = :col2)");
$stmt2->execute(["col1"=>"val1", "col2"=>"val2"]);
$rows = $db->fetchAll( $stmt2 );

// EasyPDO
$rows = $db->select("table", ["col1"=>"val1", "col2"=>"val2"]);
```

## What Can EasyPDO Do?
1. Convenient wrappers for all CRUD operations
	1. SELECT
	2. UPDATE
	3. INSERT
	4. DELETE
	5. INSERT/UPDATE
2. Easily get the insertId for INSERT, affected rows for UPDATE/DELETE, and array of rows for SELECT statements.
```php
/*	Take note of the  the 2nd parameter:
*	A value of true ocauses the function to return the 'convenient data', 
*	while false returns the PDOStatement Object
*/

// Select Statement
$rows = $db->execute("SELECT id FROM table WHERE id < :max", ["max"=>3], true);
// Output: array of matched rows

$insertId = $db->execute("INSERT INTO table(col1, col2) VALUES(:col1, :col2)", ["col1"=>"val1", "col2"=>"val2"], true);
//Output: inserId

$affectedRows = $db->execute("DELETE FROM table WHERE id = :id", ["id"=>5], true);
//Output: affected rows

$affectedRows = $db->execute("UPDATE table SET col1 = :col1 WHERE id = :id", ["id"=>5, "col1"=>"valX"], true);
//Output: affected rows

```
3. Use plain old PDO anytime you want

## Installation

### Composer
```
composer require dekyfin/easypdo
```
### Manual

1. Download the zip file
2. Include `src/DB.php` into your pr

```PHP
require_once "/path/to/src/DB.php";
```

## Usage

```PHP

// Options for connecting to MySQL database
$options = [
	"host"=>"localhost",
	"user"=>"db_user",
	"db"=>"db_name",
	"pass"=>"s3cr3tp@ssw0rd"
];

// Create connection
$db = new DF\DB($options);

// Run queries
$rows = $db->query("SELECT * FROM table", true);

```

# Methods

### select( string $table, array $conditions = [] , mixed $modifiers ): array $rows
Used to select rows matching some `$conditions`. This method currently selects all columns. It will be modified in v2.x.x to allow specifying of columns

- `$conditions` An associative array of `$column => $value` to match against. Uses SQL `AND` operator to match all `$conditions`
- `$modifiers` int || string
	- An integer will limit the number of results. Results in `LIMIT BY $modifiers`
	- A string which will be put at the end of the query to modify the behaviour. Example: `ORDER BY id DESC`

```php
$data = $db->select("table", ["category"=>3] , 10); //Show only 10 results
$data = $db->select("table", ["category"=>3] , "ORDER BY id DESC");

```
### insert( string $table, array $values ): int $insertId

### update( string $table, array $values = [] [, array $conditions] ): int $rowCount

### insertUpdate( string $table, array $values ): int insertId
Note: This method will return the insertId of the row if insert or update worked successfully

### delete( string $table, array $conditions): int $rowCount

### query( string $sql , boolean $fetchAll = false )

This function is used to run an sql query.
Returns array of data if $fetchAll is true, and a PDOStatement object if false

```php
$data = $db->query("Select id FROM table", true); // [ [id=>1], [id=>2] ... ]

```

### execute( string $sql, array $values , boolean $fetchAll = false )
Used to prepare and execute a statement. Useful for running a one-off prepared statement.
Returns array of data if $fetchAll is true, and a PDOStatement object if false

```php
$data = $db->execute("Select id FROM table WHERE id < :max", ["max"=>3] , true); // [ [id=>1], [id=>2] ... ]

```

### prepare( string $sql ): PDOStatement

```php
$stmt = $db->prepare("INSERT INTO table(col1,col2) VALUES(:col1, :col1)");

$stmt->execute(["col1" => 3, "col2" => 4 ]);
$stmt->execute(["col1" => 5, "col2" => 6 ]);
$stmt->execute(["col1" => 50, "col2" => 2e6 ]);

```


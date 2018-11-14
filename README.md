# EasyPDO
A convenient wrapper for PDO. Supports only MySQL at the moment

## Installation

### Composer
```
composer require dekyfin/easypdo
```
### Manual

1. Download the zip file
2. Include src/DB.php into your pr

```PHP
require_once "/path/to/src/DB.php";
```

## Usage

```PHP
$options = [
	"host"=>"localhost",
	"user"=>"db_user",
	"db"=>"db_name",
	"pass"=>"s3cr3tp@ssw0rd"
];

$db = new DF\DB($options);
$data = $db->query("SELECT * FROM table", true);

```

# Methods

### getPDO(): PDO

### query( string $sql ): PDOStatement 

### query( string $sql , boolean $fetchAll = true ): array 


### execute( string $sql, $values ): PDOStatment 

### execute( string $sql, $values , boolean $fetchAll ): array

### prepare( string $sql ): PDOStatement

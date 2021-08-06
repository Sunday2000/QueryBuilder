QueryBuilder class help you to easly build your SQL queries.

Example:
We have 'products' table with (name, address, city) attributes.

$pdo = new PDO('sqlite:../products.db', null, null, [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION 
]);

//don't necessarly need php \PDO instance
$builder = new \App\QueryBuilder($pdo);

$query = $builder
        ->select("id", "name", "product")
        ->from("users");

$query will contain "SELECT id, name, product FROM users"

*************************************************************************************************

$query = $builder
        ->select("id", "name")
        ->from("users")
        ->select('product');

$query will contain "SELECT id, name, product FROM users".

*************************************************************************************************

$query = $builder
        ->from("users")
        ->orderBy("id", "ezaearz")
        ->orderBy("name", "DESC")
        ->toSQL();

$query will contain "SELECT * FROM users ORDER BY id, name DESC".

*************************************************************************************************

$query = $builder
            ->from("users")
            ->where("id > :id")
            ->setParam("id", 3)
            ->limit(10)
            ->orderBy("id", "DESC")
            ->toSQL();

$query will contain "SELECT * FROM users WHERE id > :id ORDER BY id DESC LIMIT 10".

*************************************************************************************************

NB: You need to construct QueryBuilder Object if you want use fetch(), fetchAll() and setParam() methods
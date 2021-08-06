<?php

namespace App;

use \Exception;

class QueryBuilder 
{

    private $from;

    private $orderBy = [];

    private $limit;

    private $offset = null;

    private $where;

    private $params = [];

    private $pdo;

    private $select = ["*"];
    
    /**
     * __construct
     *
     * @param  null|\PDO $pdo it's pdo instance or null
     * @return void
     */
    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Specify the table from where the request will be make 
     *
     * @param  string $table is table name where you would make your request
     * @param  string $alias is an alias name
     * @return self
     */
    public function from(string $table, string $alias = null):self
    {
        $this->from = $alias ? "$table $alias": "$table";
        return $this;
    }
    
    /**
     * Specify the order according to an attribute it's can be ASC or DESC
     *
     * @param  string $key is table attribute you would like to order
     * @param  string $order it's the order direction (ASC or DESC)
     * @return self
     */
    public function orderBY(string $key, string $order):self
    {
        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'])){
            $this->orderBy[] = $key;
        }else{
            $this->orderBy[] = "$key $order";
        }
        return $this;
    }
    
    /**
     * Help to specify a limit for your request
     *
     * @param  int $limit is number of record you would like
     * @return self
     */
    public function limit(int $limit):self
    {
        $this->limit = $limit;

        return $this;
    }
    
    /**
     * Make an offset for the request
     *
     * @param  int $offset is the number of rows to skip before starting to return rows from the query
     * @return self
     */
    public function offset(int $offset):self
    {
        $this->offset = $offset;

        return $this;
    }
    
    /**
     * Help to get a specific page record for pagination feature
     *
     * @param  int $page is page number you would like to reach
     * @return self
     */
    public function page(int $page):self
    {
        $this->offset($this->limit * $page - $this->limit);

        return $this;
    }
    
    /**
     * Make a where condition for your request
     *
     * @param  string $where is sql condition for the query 
     * @return self
     */    
    public function where(string $where):self
    {
        $this->where = $where;
        return $this;
    }
    
    /**
     * 
     * Set param to request for security (You need to construct object with \PDO instance)
     * Use this if you don't just build the query 
     * but also execute your request it.
     * 
     * @param  string $key is table attribute
     * @param  mixed $value is value for the key
     * @return self
     */
    public function setParam(string $key, $value):self
    {
        $this->params = array_merge($this->params, [$key => $value]);
        return $this;
    }
    
    /**
     * Specify attribute you would like to select.
     *
     * @param  string $keys is attribute(s) you would like to get
     * @return self
     */
    public function select(...$keys):self
    {
        if (is_array($keys[0])){
            $keys = $keys[0];
        }
        if ( $this->select === ["*"]){
            $this->select = $keys;
        } else {
            $this->select = array_merge($this->select, $keys);
        }
        return $this;
    }
    
    /**
     * Get a specific attribut (You need to construct object with \PDO instance)
     *
     * @param  string $city is attribute your are looking for.
     * @return mixed
     */
    public function fetch(string $city)
    {
        $query = $this->execute();
        return $query->fetch()[$city] ?? null;
    }

    
    /**
     * execute the query
     *
     * @return \PDOStatement|false
     */
    public function execute()
    {
        $sql = $this->toSQL();
        $query = $this->pdo->prepare($sql);
        $query->execute($this->params);
        return $query;
    }
    
    /**
     * Get all records
     *
     * @throws \Exception 
     * @return array|false
     */
    public function fetchAll()
    {
        try {
            $query = $query = $this->execute();
            return $query->fetchAll();
        } catch (Exception $e){
            throw new Exception("Impossible d'exécuter la requête ".$this->toSQL()." : ".$e->getMessage());
        }
    }
    
    /**
     * Get the number of records
     *
     * @return int
     */
    public function count():int
    {
        return (int)(clone $this)->select("COUNT(id) count")->fetch('count');
    }
    
    /**
     * Build the final SQL query
     *
     * @return string
     */
    public function toSQL():string
    {
        $fields = implode(', ', $this->select);
        $sql = "SELECT $fields FROM {$this->from}";
        if ($this->select){
            $keys = implode(", ", $this->select);
            $sql = str_replace("*", "{$keys}", $sql);
        }
        if ( $this->where ){
            $sql .= " WHERE ".$this->where;
        }
        if ( !empty($this->orderBy) ){
            $sql .= " ORDER BY ".implode(", ", $this->orderBy);
        }
        if ( $this->limit > 0 ){
            $sql .= " LIMIT {$this->limit}";
        }
        if ( !is_null($this->offset) ){
            if ($this->limit === null){
                throw new Exception("Impossible de définir un offset sans definir de limit");
            }
            $sql .= " OFFSET {$this->offset}";
        }
        $this->sql = $sql;
        return $sql;
    }
}
<?php

class Database
{
    public $query = '';
    public $table = '';
    function __construct($connection)
    {
        $this->connection = $connection;
    }

    function get_pk()
    {
        $query   = "SHOW KEYS FROM $this->table WHERE Key_name = 'PRIMARY'";
        $pk      = $this->connection->query($query);
        $pk      = $pk->fetch_object();
        return $pk->Column_name;
    }

    function insert($table, $val)
    {
        $this->table = $table;
        $this->query = "INSERT INTO $table";
        $fields = implode(',',array_keys($val));
        $values = "'".implode("','",array_values($val))."'";
        $this->query .= "($fields)VALUES($values)";
        return $this->exec('insert');
    }

    function update($table, $val, $clause)
    {
        $this->table = $table;
        $values = $this->build_values($val);
        $string = $this->build_clause($clause);
        $this->query = "UPDATE $table SET $values WHERE $string AND last_insert_id(".$this->get_pk().")";
        return $this->exec('update');
    }

    function updateall($table, $val)
    {
        $this->table = $table;
        $values = $this->build_values($val);
        $this->query = "UPDATE $table SET $values";
        return $this->exec('updateall');
    }

    function all($table, $clause = [], $order = [])
    {
        $this->table = $table;
        $conn = $this->connection;
        $this->query = "SELECT * FROM $table";
        $string = $this->build_clause($clause);
        $string_order = $this->build_order($order);
        if($string)
            $this->query .= ' WHERE '.$string;
        
        if($string_order)
            $this->query .= ' ORDER BY '.$string_order;
        return $this->exec('all');
    }

    function single($table, $clause = [], $order = [])
    {
        $this->table = $table;
        $conn = $this->connection;
        $this->query = "SELECT * FROM $table";
        $string = $this->build_clause($clause);
        $string_order = $this->build_order($order);
        if($string)
            $this->query .= ' WHERE '.$string;
        
        if($string_order)
            $this->query .= ' ORDER BY '.$string_order;
        return $this->exec('single');
    }

    function delete($table, $clause = [])
    {
        $this->table = $table;
        $conn = $this->connection;
        $this->query = "DELETE FROM $table";
        if(count($clause) > 0)
        {
            $string = http_build_query($clause, '', ' AND ');
            $this->query .= ' WHERE '.$string;
        }
        return $this->exec();
    }

    function truncate($table)
    {
        $this->table = $table;
        $conn = $this->connection;
        $this->query = "TRUNCATE TABLE $table";

        return $this->exec();
    }

    function exec($type = false)
    {
        $query_result = $this->connection->query($this->query);
        if($query_result)
        {
            if($type == 'all')
                return json_decode(json_encode($query_result->fetch_all(MYSQLI_ASSOC)));
            if($type == 'single')
                return $query_result->fetch_object();
            if(in_array($type,['insert','update']))
            {
                $last_id = $this->connection->insert_id;
                $pk = $this->get_pk();
                return $this->single($this->table,[$pk=>$last_id]);
            }
        }
        else
        {
            // echo $this->query;
            // echo "<br>";
            print_r($this->connection->error);
            die();
        }

        return $query_result;
    }

    function build_clause($clause)
    {
        $count_clause = count($clause);
        $string = "";
        if($count_clause > 0)
        {
            foreach($clause as $key => $value)
            {
                $value = $this->connection->real_escape_string($value);
                $string .= "$key='$value'";
                $last_iteration = !(--$count_clause);
                if(!$last_iteration)
                    $string .= ' AND ';
            }
        }

        return $string;
    }

    function build_values($values)
    {
        $count_values = count($values);
        $string = "";
        if($count_values > 0)
        {
            foreach($values as $key => $value)
            {
                $value = $this->connection->real_escape_string($value);
                $string .= "$key='$value'";
                $last_iteration = !(--$count_values);
                if(!$last_iteration)
                    $string .= ', ';
            }
        }

        return $string;
    }

    function build_order($order)
    {
        $count_order = count($order);
        $string = "";
        if($count_order > 0)
        {
            foreach($order as $key => $value)
            {
                $value = $this->connection->real_escape_string($value);
                $string .= "$key $value";
                $last_iteration = !(--$count_order);
                if(!$last_iteration)
                    $string .= ', ';
            }
        }

        return $string;
    }
}
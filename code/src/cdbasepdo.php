<?php
        // -------------------
        // cdbasepdo.php
        // -------------------

// ===============================
// ==
// ==  class CDbase (base for mariaDb access)
// ==
// ==
// ===============================

/*

CREATE USER 'txtproc'@'localhost' IDENTIFIED BY 'Pass-pass2000}';
Query OK, 0 rows affected (0,031 sec)

FLUSH PRIVILEGES;
Query OK, 0 rows affected (0,004 sec)

SELECT user FROM mysql.user;
+--------------+
| User         |
+--------------+
|              |
| fw           |
| mariadb.sys  |
| root         |
| txtproc      |
| utilisateur1 |
|              |
+--------------+
7 rows in set (0,017 sec)

CREATE DATABASE txtprocessor;
Query OK, 1 row affected (0,002 sec)

GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,ALTER ON txtprocessor.* TO txtproc@localhost  IDENTIFIED BY 'Pass-pass2000}';
Query OK, 0 rows affected (0,020 sec)

GRANT FILE ON *.* TO txtproc@localhost IDENTIFIED BY 'Pass-pass2000}';
Query OK, 0 rows affected (0,021 sec)

FLUSH PRIVILEGES;
Query OK, 0 rows affected (0,001 sec)

USE txtprocessor;
Database changed

SHOW FULL TABLES;
Empty set (0,000 sec)


CREATE TABLE users (
    email           VARCHAR(70)  PRIMARY KEY,
    firstname       VARCHAR(70),
    lastname        VARCHAR(70),
    passwd          VARCHAR(100),
    emailchecked    DATETIME,
    code            VARCHAR(30),
    codedate        DATETIME
);
Query OK, 0 rows affected (0,112 sec)


CREATE TABLE cookies ( 
    email    VARCHAR(70)  PRIMARY KEY,
    cname    VARCHAR(50), 
    value    BLOB
);
Query OK, 0 rows affected (0,097 sec)



//=====================================
//=====================================
//=====================================
//=====================================
*/


function toObject($array) {
    return (object) $array;
    if (is_array($array))  
        return json_decode( json_encode($array), false );
    return new stdClass();
}


class CDbase { 
    
    protected  string  $user        = 'txtproc';
    protected  string  $passwd      = 'Pass-pass2000}';
    protected  string  $dbname      = 'txtprocessor';
    protected  string  $dbase_host  = '[127.0.0.1]';        // '[::1]'
    protected  string  $dbase_port  = '3306';               
    protected  string  $dbase_sock  = '';

    public $conn=null;
    public $last_id=null; 
    



    function __construct() {
        $dsn = "mysql:host=".           $this->dbase_host.
                    ";port=".           $this->dbase_port.
                    ";unix_socket=".    $this->dbase_sock.
                    ";dbname=".         $this->dbname.
                    ";charset=".        "utf8mb4";

        $options = [
            PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
            PDO::MYSQL_ATTR_LOCAL_INFILE => true,     // security ??
            PDO::ATTR_EMULATE_PREPARES   => true,     // needed by query LOAD DATA LOCAL INFILE
        ];

        try {
            $this->conn = new PDO($dsn, $this->user, $this->passwd,  $options);
        } catch (Exception $e) {
            $this->conn = null;
            throw new Exception( 'CDbase::__construct  pdo constructor error : '.$e->getMessage() );
        }

    }   // CDbase::__construct

    
    function __destruct() {    
        /*   $this->conn->close();   */   
        $this->conn=null;
    }   // CDbase::__destruct


    function execute($sql, $params) {

      try {
        $sth = $this->conn->prepare($sql);
        if ($sth === false) return false;        
      } catch (Exception $e) {
        $sth = null;
        $this->_error_log('pdo prepare error : '.$e->getMessage());
        return false;        
      }

      try {
        $sth->execute( $params );
      } catch (Exception $e) {
        $sth = null;
        $this->_error_log('pdo execute error : '.$e->getMessage());
        $this->_error_log('sql = : '.$sql);
        return false;        
      }
      
      return $sth;      
    }

} // class CDbase 











class CDbTable {

    protected $dbase = null;
    protected $table = '';
    protected $fields = array();

    //======================
        protected function num_rows($r) { return $r->rowCount(); }
        protected function fetch_assoc($r) { return $r->fetch(PDO::FETCH_ASSOC); }
    //======================


    function __construct( $dbase, $table ) {
        $this->dbase = $dbase;
        $this->table = $table;
        $this->fields = $this->_getFields();
        $this->last_exception = '';
    }  // CDbTable::__construct

    function __destruct() {    
        /*   $this->conn->close();   */  
        unset ( $this->conn );
        $this->conn=null;
    }   // CDbTable::__destruct



    public function getRecord($field, $v) {
        $set = toObject( array( '_sql' => "SELECT * FROM :{tableName} WHERE ".$field."=:v" ) );
        $set->{ 'v' } = $v;
            /* if ($field2 != null && $v2 != null) {
            $set->_sql .= " AND ".$field2."=:v2";
            $set->{ 'v2' } = $v2;
            } */
        $ret = $this->query__($set, 'ROWS', $last_id);
        if (count($ret)>0)  return $ret[0];
        return false;
    }


    public function appendRecord($record, & $last_id = null) { 
        $i=0;
        $set = toObject( array( '_sql' => "INSERT INTO :{tableName} ( " ));
        foreach( $record as $fk => $fv ) {
            if ($i > 0) $set->_sql.=', ';
            $set->_sql .= $fk;
            $i++;
        }
        $i++;    $j = $i;
        $set->_sql .= " ) VALUES ( ";
        foreach( $record as $fk => $fv ) {
            if ($i > $j) $set->_sql.=', ';
            $set->_sql .= ':p'.$i;
            $set->{ 'p'.$i } = $fv;
            $i++;
        }
        $set->_sql .= " ) ";
        $ret = $this->query__($set, 'BOOL', $last_id);
        // if ($ret === false) {  $this->php_log( 'CDbTable::appendRecord error' );  }
        return $ret;
    }


    function query__($filter, $retType, & $last_id = null) {
        $arr = [];    
        $f = $this->filterToParams($filter->_sql, $filter, $arr);
        if (!$this->secure_query__valid($f, $arr)) {  return false;  }
        $ret = $this->query_($f, $arr, $retType);
        return $ret;
    }  // query__



    function filterToParams($sql, $filter, & $arr) {
    $arr = [];    
    if ($this->isNullFilter($filter)) {
    return $sql;      
    }
    $i=0;
    $s = preg_replace_callback('/(:)([a-zA-Z0-9\_]+)/', 
    function ($matches) use (&$arr, &$i, &$filter) {
    $i++;
    $v = $matches[2];
    $newp = $v.'_'.$i;
    $arr[$newp] = $filter->$v;
    return ':'.$newp;
    }, 
    $sql);

    return $s;    
    }  // filterToParams



    protected function isNullFilter($filter) {
        if (!isset($filter) || $filter===null) return true; 
        if (!isset($filter->_sql) || $filter->_sql == '') return true;
        return false;
    }  // isNullFilter



    protected function secure_query__valid($f, $arr) {
        $str = $f;
        $items = [];  $words = [];  $ponct=[];
        // find parameters
        $str = preg_replace_callback('/(:)([a-zA-Z0-9\{\_}]+)/', 
                function ($matches) use (&$items) {
                    $items[ $matches[2] ] = true;
                    return ' ';
                }, 
            $str);
        $str = preg_replace_callback('/\w+/', 
                function ($matches) use (&$words) {
                    $words[ $matches[0] ] = true;
                    return ' ';
                }, 
            $str);
        $str = preg_replace_callback('/[^\s]/', 
                function ($matches) use (&$ponct) {
                    $ponct[ $matches[0] ] = true;
                    return ' ';
                }, 
            $str);
        $str = trim($str);
        foreach( $items as $k => $v ) {
            if ($k == '{tableName}') continue;
            if ( array_key_exists( $k, $arr ) ) continue;
            return false;
        }
        $good = [   'FROM', 'WHERE', 'COUNT', 'COALESCE', 'SELECT', 'SUM', 'LIKE', 'DESCRIBE', 'ORDER', 'BY', 'DESC', 'LIMIT', 
                    'OFFSET', 'AND', 'OR', 'INTO', 'OUTFILE', 'CHARACTER', 'SET', 'UTF8', 'FIELDS', 'ENCLOSED', 'TERMINATED', 
                    'ESCAPED', 'LINES', 'N', 'R', 'INSERT', 'VALUES', 'UPDATE', 'TRUNCATE', 'TABLE', 'LOAD', 'DATA', 
                    'LOCAL', 'INFILE', 'OPTIONALLY', 'EXISTS', 'ANY', 'DELETE', 'NOT', 'NOW', 'INTERVAL', 'MINUTE' ];
        $good[] = strtoupper($this->table);
        $errWord = 0;
        foreach( $words as $k => $v ) {
            if (preg_match('/^[-+]?\d+$/', $k)) continue; 
            if (in_array(strtoupper($k), $good)) continue;
            if (array_key_exists($k, $this->fields)) continue;
            $this->php_log('CDbTable::secure_query__valid Bad word in query = ['.$k."]  \n");
            $errWord++;
        }
        if ($errWord>0) {
            $this->php_log('CDbTable::secure_query__valid  bad words = '. $f );
            return false;      
        }
        if ($str != '') {
          $this->php_log('CDbTable::secure_query__valid error string');
          return false;
        }
        return true;    
    } // secure_query__valid


    function php_log($s){
        $this->last_exception = $s;
        echo $s."\n";
    }


    function query_($sql, $params, $retType, & $last_id = null) {
        $sql =  str_replace(':{tableName}', '`'.$this->table.'`', $sql);
        $result = $this->dbase->execute($sql, $params);
        if ( $last_id !== null) {   $last_id = $this->dbase->getlastid();  }
        if ($retType == 'ROWS') {
            $a = array();
            if ($result===false) return $a;
            if ($this->num_rows($result) > 0) {
              while($row = $this->fetch_assoc($result))
                {  $a[] = $row;  }
            }
            $result = null;
            return $a;
        }
        if ($retType == 'ROWCOUNT') {
            if ($result===false) return 0;
            return $this->num_rows($result);
        }
        if ($retType == 'ONE') {
            if ($result===false) return 0;
            $data= $this->fetch_assoc($result);
            reset($data);     $r = current($data);    
            $result = null;
            return $r;
        }
        if ($retType == 'BOOL') {
            if ($result === false) return false; 
            return $result->errorCode() === '00000';
        }
        $this->php_log('cDbTable:query_ error: param $retType is not valid');
        return false;
    } // query_


    public function _getFields() {  
        $set = toObject( array( '_sql' => "DESCRIBE  :{tableName}  " ) );
        $fields = array();
        $ret = $this->query__($set, 'ROWS', $last_id);
        foreach ($ret as $key => $row) {
            // $fields[ $row["Field"] ] =  $row["Type"]; 
            $fields[ strtolower($row["Field"]) ] =  strtoupper($row["Type"]); 
        }
        return $fields;
    } // _getFields


}   // class CDbTable 




?>
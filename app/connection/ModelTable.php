<?php

/**
 * Funciones Privadas para acceder a los datos de la base de datos
 * @method  get
 * @method create
 * @method update
 * @method delete
 */
class ModelTable
{
    protected $dbname = 'cndash';
    private $user = 'root';
    private $password = '';
    private $conexion_pdo = null;
    private $error = array();
    private $sql = '';

    protected $table = '';
    protected $id_table = '';
    protected $campos = [];



    /**
     * Contructor de la clase, pide como parametro la base de datos a la que se conectara. Es necesario conectar a una BD por default
     * @var String $db Nombre de la base de datos a la cual se conectara
     */
    function __construct()
    {
        //$this->conexion_pdo = new PDO("mysql:host=localhost;dbname=" . $this->dbname, $this->user, $this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_PERSISTENT => true));
    }

    /**Retorna el nombre de la tabla */
    public function getTableName()
    {
        return $this->table;
    }
    /**Retorna el campo de la tabla registrado como la clave primaria */
    public function getTableId()
    {
        return $this->id_table;
    }

    /**Retorna los campos de la tabla que se especificaron en el modelo */
    public function getTableFields()
    {
        return $this->campos;
    }

    public function getError()
    {
        return $this->error;
    }
    public function getSQL()
    {
        return $this->sql;
    }

    public function find(int $id)
    {
        $return = (object) array();
        $find = $this->_get([], "WHERE $this->id_table = :id_", array('id_' => $id));
        if (count($find)) {
            $return = (object)$find[0];
        }
        return $return;
    }

    public function where(string $where = '1', array $data = array(), array $campos = [])
    {
        $response = array();
        foreach ($this->_get($campos, " WHERE $where", $data) as $result_) {

            $result_ = (object)$result_;
            $id = $this->id_table;
            array_push($response, $result_);
        }
        return  $response;
    }
    public function insert(array $values)
    {
        $return = (object) array();
        $insert_id = $this->_create($values);
        if ($insert_id != null) {
            $return = $this->find($insert_id);
        } else {
            // Aquí puedes registrar el error
            error_log("Error insertando en " . $this->table . ": " . print_r($this->getError(), true));
        }
        return $return;
    }


    public function update(int $id, array $values)
    {
        return $this->_update($id, $values);
    }
    public function delete(int $id)
    {
        return $this->_delete($id);
    }

    public function consult(array $campos = [], string $innerjoin = '', string $condicion = '1', array $replace = [], bool $removeId = true)
    {
        $data = $this->_get($campos, "WHERE $condicion", $replace, $innerjoin);
        // Aquí imprimes el SQL en error_log
        // error_log("SQL CONSULT: " . $this->sql);
        $response = [];
        foreach ($data as $result_) {
            $result_ = (object)$result_;
            $id = $this->id_table;
            if ($removeId && isset($result_->$id)) {
                unset($result_->$id);
            }
            $response[] = $result_;
        }
        return $response;
    }
    

    /**
     * Funcion GET, Obtiene un arreglo de registros de una tabla en especifico
     * dada por los parametros
     */
    private function _get(array $campos_ = [], string $condicion = "WHERE 1", array $data = array(), string  $innerjoin = '')
    {
        $this->conexion_pdo = new PDO("mysql:host=localhost;dbname=" . $this->dbname, $this->user, $this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_PERSISTENT => true));
        $return = [];
        $rename = '';
        if ($innerjoin != '') {
            $tokens = explode(' ', $innerjoin);
            $rename =   $tokens[0] . '.';
        }
        try {
            $fields = '';
            $campos = count($campos_) > 0 ? $campos_ : $this->campos;
            if (count($campos) > 0) {
                for ($i = 0; $i < count($campos); $i++) {
                    $fields .= $campos[$i] . ',';
                }
                $fields = trim($fields, ",");
            } else {
                $fields = '*';
            }

            $sql = "SELECT $fields, $rename$this->id_table AS id FROM $this->table $innerjoin $condicion";
            $result = $this->conexion_pdo->prepare($sql);
            count($data) > 0 ? $result->execute($data) : $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            $return = $result;
            $this->sql = $sql;
        } catch (PDOException $e) {
            array_push($this->error, $e);
            array_push($this->error, $sql);
            $return = [];
        }
        $this->conexion_pdo = null;
        return  $return;
    }

    /**
     * Funcion CREATE, Crea un registro y devuelve el id generado
     */
    private function _create(array $datos)
    {
        $this->conexion_pdo = new PDO("mysql:host=localhost;dbname=" . $this->dbname, $this->user, $this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_PERSISTENT => true));
        $return = null;
        $campos_insert = '';
        $campos_execute = '';
        try {
            error_log("[ModelTable::_create] CREANDO EN {$this->table}");
            foreach ($datos as $campos => $valor) {
                $campos_insert .= $campos . ',';
                $campos_execute .= ':' . $campos . ',';
            }
            $campos_insert = trim($campos_insert, ',');
            error_log("[ModelTable::_create] CAMPOS INSERT EN {$this->table}: {$campos_insert}");
            $campos_execute = trim($campos_execute, ',');
            error_log("[ModelTable::_create] DATOS A INSERTAR: " . print_r($datos, true));

            error_log("[ModelTable::_create] CAMPOS EXECUTE EN {$this->table}: {$campos_execute}");
            $sql = "INSERT INTO $this->table($campos_insert) VALUES($campos_execute)";
            error_log("[ModelTable::_create] INSER SQL EN  {$this->table}: {$sql}");
            $result = $this->conexion_pdo->prepare($sql);
            $result->execute($datos);
            $return = $this->conexion_pdo->lastInsertId();
            error_log("[ModelTable::_create] CREADO CON ID {$return} EN {$this->table}");
            $this->sql = $sql;
        } catch (PDOException $e) {
            array_push($this->error, $e);
            array_push($this->error, $sql);
            array_push($datos);
            error_log("[ModelTable::_create] ERROR al insertar en {$this->table}");
            error_log("[ModelTable::_create] Mensaje: " . $e->getMessage());
            error_log("[ModelTable::_create] SQL: " . $sql);
            error_log("[ModelTable::_create] Datos: " . json_encode($datos));
            $return = null;
        }
        $this->conexion_pdo = null;

        return $return;
    }

    /**
     * Funcion UPDATE, Actualiza un registro especifico dado por el ID
     */
    private function _update(int $id, array $datos = array())
    {
        $this->conexion_pdo = new PDO("mysql:host=localhost;dbname=" . $this->dbname, $this->user, $this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_PERSISTENT => true));
        $return = false;
        $sentencias  = '';
        try {
            foreach ($datos as $campo => $value) {
                $sentencias .= "$campo = :$campo,";
            }
            $sentencias = trim($sentencias, ',');

            $sql = "UPDATE $this->table SET $sentencias WHERE $this->id_table = :id";
            $stmt = $this->conexion_pdo->prepare($sql);
            $datos['id'] = $id;

            error_log("[ModelTable::_update] SQL: $sql");
            error_log("[ModelTable::_update] DATA: " . json_encode($datos));

            $ok = $stmt->execute($datos);

            if (!$ok) {
                $error = $stmt->errorInfo();
                error_log("[ModelTable::_update] ERROR: " . implode(' | ', $error));
                $return = false;
            } else {
                $return = true;
            }

            $this->sql = $sql;
        } catch (PDOException $e) {
            error_log("[ModelTable::_update] EXCEPCIÓN: " . $e->getMessage());
            error_log("[ModelTable::_update] SQL: $sql");
            $return = false;
        }
        $this->conexion_pdo = null;
        return $return;
    }


    /**
     * Funcion delete, Elimina un registro especifico dado por el ID
     */
    private function _delete(int $id)
    {
        $this->conexion_pdo = new PDO("mysql:host=localhost;dbname=" . $this->dbname, $this->user, $this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_PERSISTENT => true));
        $return = false;
        try {
            $sql =  "DELETE FROM $this->table WHERE $this->id_table = $id";
            $result = $this->conexion_pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            $return = true;
            $this->sql = $sql;
        } catch (PDOException $e) {
            array_push($this->error, $e);
            array_push($this->error, $sql);
            $return = false;
        }
        $this->conexion_pdo = null;
        return $return;
    }

    public static function SqlQuery(array $config_server, string $sql, array $replace = [])
    {
        $return = [];
        $string_conect = "mysql:";
        if (isset($config_server['host'])) $string_conect .= "host=" . $config_server['host'] . ";";
        if (isset($config_server['dbname'])) $string_conect .= "dbname=" . $config_server['dbname'] . ";";
        $string_conect = trim($string_conect, ';');

        try {
            $test_conexion = new PDO($string_conect, $config_server['user'], $config_server['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_PERSISTENT => true));
            $result = $test_conexion->prepare($sql);
            count($replace) > 0 ? $result->execute($replace) : $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            $return = $result;
            $test_conexion = null;
            $result = null;
        } catch (PDOException $e) {
            $return = [];
        }
        return   $return;
    }
}

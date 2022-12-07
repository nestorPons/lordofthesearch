<?php

namespace app\core;
/**
 * La clase maestra para el acceso a la base de datos 
 * aquí se realiza la conexión y se dota de todos los atributos y métodos a las clases hijas  
 * para la conexion y tratamiento de datos
 */
class Conn extends BaseClass
{
    private
        $params = [],
        $sql,
        $sqlPrepare,
        $order = 'ORDER BY id';

    protected
        $pdo,
        $table;

    function __construct(string $db_name, string $table, ?bool $log = false)
    {
        if ($table) $this->table($table);

        $this->connect($db_name);
    }
    public function table(string $table = null): string
    {
        if ($table) $this->table = $table;
        return $this->table;
    }
    /**
     *	Genera la conexión a a la base de datos
     */
    private function connect(string $db_name = null): self
    {

        // Carga de la libreria para las variables de entorno
        $credentials = Confiles::get_env(self::FOLDER_ROOT());
        $dsn = 'mysql:dbname=' . $db_name . ';host=' . $credentials['DATABASE_HOST'] . ';port=' . $credentials['DATABASE_PORT'];
        
    
        $this->pdo = new \PDO(
            $dsn,
            $credentials['DATABASE_USER'],
            $credentials['DATABASE_PASS'],
            [
                \PDO::ATTR_PERSISTENT => false, //sirve para usar conexiones persistentes https://es.stackoverflow.com/a/50097/29967
                \PDO::ATTR_EMULATE_PREPARES => false, //Se usa para desactivar emulación de consultas preparadas
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, //correcto manejo de las excepciones https://es.stackoverflow.com/a/53280/29967
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'" //establece el juego de caracteres a utf8mb4 https://es.stackoverflow.com/a/59510/29967
            ]
        );

        return $this;
    }
    /**
     *	@void
     *	
     *	Agrega más parámetros al arreglo de parámetros
     *	@param array $parray
     */
    private function bindMore(array $parray = null): void
    {
        if ($parray) {
            $columns = array_keys($parray);
            foreach ($columns as $i => &$column) {
                $this->params[sizeof($this->params)] = [":" . $column, $parray[$column]];
            }
        }
    }
    /**
     *  Si la consulta SQL contiene un SELECT o SHOW, devolverá un arreglo conteniendo todas las filas del resultado
     *	Si la consulta SQL es un DELETE, INSERT o UPDATE, retornará el número de filas afectadas
     *
     *  @param  string $sql
     *	@param  array  $params
     *	@param  int    $fetchmode
     *	@return mixed
     */

    public function query(string $sql, $params = null) : self
    {
        $sql = str_replace('order_by', $this->order, $sql);
        $this->sql = trim(str_replace("\r", " ", $sql));
        // Prepara la sentencia con sus parametros y la inicia
        $this->sqlPrepare = $this->pdo->prepare($this->sql);
        $this->bindMore($params);

        if (!empty($this->params)) {
            foreach ($this->params as $param => $value) {
                if (is_numeric($value[1])) {
                    $type = \PDO::PARAM_INT;
                } else if (is_bool($value[1])) {
                    $type = \PDO::PARAM_BOOL;
                } else if (is_null($value[1]) || $value[1] == '') {
                    $value[1] = null;
                    $type = \PDO::PARAM_NULL;
                } else {
                    $type = \PDO::PARAM_STR;
                }
                $this->return = $this->sqlPrepare->bindValue($value[0], $value[1], $type);
            }
        }
        $this->sqlPrepare->execute();
        $rawStatement = explode(" ", preg_replace("/\s+|\t+|\n+/", " ", $this->sql));
        # Determina el tipo de SQL 
        $statement = strtolower($rawStatement[0]);
        if ($statement === 'select' || $statement === 'show') {
            $this->return = $this->sqlPrepare->fetchAll(\PDO::FETCH_ASSOC);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            $this->return = $this->sqlPrepare->rowCount();
        } 

        return $this;
    }
    // Devuelve todos los registros de una tabla
    public function getAll() : self
    {
        $this->query("SELECT * FROM {$this->table} ORDER BY id ASC;");
        return $this; 
    }
    /** Devuelve datos de una peticion por id
     * param puede ser array con una clave id o un integer que hace referencia a un id
     * @param Puede ser un array con un id o un id
     */
    public function getById($id) : self
    {
        $this->query("SELECT * FROM {$this->table} WHERE id = $id LIMIT 1;");
        return $this;
    }
    /**
     * Devuelve datos de una peticion por algun campo del registro
     * $field → String (1.1) | array clave =>valor (1.0)
     */
    public function getBy(string|array $field): self
    {
        $filters = '';

        if (is_string($field)) $filters = $field;
        else {
            foreach ($field as $column => $value) {
                $filters .= (string) $column . " = '" . (string) $value . "' AND ";
            }
            $filters = trim($filters, "AND ");
        }
        $sql = "SELECT * FROM {$this->table} WHERE $filters  order_by;";
        $this->query($sql);
        if(isset($this->return[0])) $this->return = $this->return[0];
        return $this;
    }
    // Devuelve datos de una peticion por una consulta sql
    public function getBySQL(string $sql)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE $sql order_by");
    }
    // Devuelve el último registro
    public function getLast()
    {
        $this->desc();
        $r = $this->query("SELECT * FROM {$this->table} order_by LIMIT 1;");
        return $r ? $r[0] : null;
    }
    // Devuelve los registros con el valor entre los dos valores proporcionados de un campo
    public function getBetween(string $column, $val1, $val2, string $filters = null)
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE $column BETWEEN '$val1' AND '$val2' $filters order_by;"
        );
    }

    public function insert(array $params, $del_id = true) : self
    {
        if ($del_id) unset($params['id']);
        $strCol = '';
        $strPre = '';
        foreach ($params as $col => $val) {
            $strCol .=  $col . ',';
            $strPre .= ':' . $col . ',';
        }
        $strCol = trim($strCol, ',');
        $strPre = trim($strPre, ',');

        if ($this->query("INSERT INTO {$this->table} ($strCol) VALUES ($strPre);", $params)) {
            $this->return = ['success' => true, 'id' => (int)$this->pdo->lastInsertId()];
        } else {
            $this->return =  ['success' => false ];
        }

        return $this;
    }
    // Funcion para mostrar solo los atributos publicos desde dentro de la clase
    public function getVars(): array
    {
        return array_diff_key(get_object_vars($this), get_class_vars(get_parent_class($this)));
    }
    /**
     * Guarda el registro mediante el id 
     */
    public function saveById(int $id, array $args = null) : self
    {
        if (!$args) $args = $this->getVars();
        $sql = $this->getSQLUpdate($args, "id=" . $id);

        $this->return = $this->query($sql, $args);
        return $this;
    }
    /**
     * Guarda usando como filtro alguno/s de los campos de la base de datos 
     * comprobamos si existe y si existe editamos si no creamos uno nuevo
     */
    public function saveBy(array $filter, array $args)
    {
        $columns = "";
        $values = "";
        $fName = key($filter);
        $fValue = $filter[$fName];
        $sql = '';

        if ($id = $this->getOneBy($filter))
            $sql = $this->getSQLUpdate($args, "$fName=$fValue");
        else {
            foreach ($args as $column => $value) {
                $columns .=  $column . ',';
                $values .= '"' . $value . '",';
            }
            $columns = trim($columns, ',');
            $values = trim("'" . $values, "',");
            $sql .= "INSERT INTO {$this->table} ($columns) VALUES ($values);";
        }
        return $this->query($sql, $args);
    }
    // Edita todos los campos de la tabla
    public function saveAll(array $args = null)
    {
        return $this->query($this->getSQLUpdate($args), $args);
    }
    // Eliminamos mediante id
    public function deleteById($id)
    {
        $id = $data['id'] ?? $data;

        return $this->query("DELETE FROM {$this->table} WHERE id = $id;");
    }
    // Eliminamos mediante un campo concreto
    public function deleteBy(array $params)
    {
        $prepared = $this->getPrepareParams($params);
        return $this->query("DELETE FROM {$this->table} WHERE $prepared;", $params);
    }
    private function getSQLUpdate(array $args, String $filter = '')
    {
        $params = $this->getPrepareParams($args);
        return "UPDATE {$this->table} SET $params WHERE $filter;";
    }
    private function getPrepareParams(array $args)
    {
        $sql = '';
        foreach ($args as $key => $value) {
            $sql .= $key . '= :' . $key . ',';
        }
        return trim($sql, ',');
    }
    // setter genérico para la inserción de datos en los atributos de la clase hija
    function loadData($data)
    {
        if ($data) {
            // Normalización de los datos para direfentes casos de uso
            if (is_object($data)) $data = (array) $data;
            if (isset($data[0])) $data = $data[0];
            // Agregacion de los datos a los atributos de clase
            $public_props = $this->getVars();
            foreach ($data as $key => $val) {
                if (array_key_exists($key, $public_props)) {
                    $this->{$key} = $val ?? null;
                }
            }
            return true;
        }
        return false;
    }
    // El método de eliminación genérico para las clases hijas
    // Método genérico de eliminación de registros
    function del() : self
    {
        $this->saveById(['estado' => 0]);
        return $this;
    }
    function isConnected() : self
    {
        $this->result = !is_null($this->pdo);
        return $this;
    }
    // Indicamos los parametros que queremos que nos devuelva la futura consulta
    public function return(string $arg = '*'): self
    {
        $this->return = $arg;
        return $this;
    }
    // Accion que ordena de forma descendiente la futura consulta
    public function desc(): self
    {
        $this->order = 'ORDER BY id DESC';
        return $this;
    }
    function __destruct()
    {
        $this->pdo = null;
    }
}

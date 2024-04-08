<?php
// Se incluye la clase para trabajar con la base de datos.
require_once('../../helpers/database.php');
/*
 *  Clase para manejar el comportamiento de los datos de la tabla administrador.
 */
class direccion_handler
{
    /*
     *  Declaración de atributos para el manejo de datos.
     */
    protected $id = null;
    protected $nombre = null;
    protected $apellido = null;
    protected $correo = null;
    protected $alias = null;
    protected $clave = null;
    protected $nivel = null;

    /*
     *  Métodos para gestionar la cuenta del administrador.
     */
    public function checkUser($username, $password)
    {
        $sql = 'SELECT id_administrador, user_administrador, clave_administrador
                FROM tbAdmins
                WHERE  user_administrador = ?';
        $params = array($username);
        $data = Database::getRow($sql, $params);
        if (password_verify($password, $data['clave_administrador'])) {
            $_SESSION['idAdministrador'] = $data['id_administrador'];
            $_SESSION['aliasAdministrador'] = $data['user_administrador'];
            return true;
        } else {
            return false;
        }
    }

    public function checkPassword($password)
    {
        $sql = 'SELECT clave_administrador
                FROM tbAdmins
                WHERE id_administrador = ?';
        $params = array($_SESSION['idAdministrador']);
        $data = Database::getRow($sql, $params);
        // Se verifica si la contraseña coincide con el hash almacenado en la base de datos.
        if (password_verify($password, $data['clave_administrador'])) {
            return true;
        } else {
            return false;
        }
    }

    public function changePassword()
    {
        $sql = 'UPDATE administrador
                SET clave_administrador = ?
                WHERE id_administrador = ?';
        $params = array($this->clave, $_SESSION['idadministrador']);
        return Database::executeRow($sql, $params);
    }

    public function readProfile()
    {
        $sql = 'SELECT id_administrador, nombre_administrador, apellido_administrador, correo_administrador, alias_administrador
                FROM administrador
                WHERE id_administrador = ?';
        $params = array($_SESSION['idAdministrador']);
        return Database::getRow($sql, $params);
    }

    public function editProfile()
    {
        $sql = 'UPDATE administrador
                SET nombre_administrador = ?, apellido_administrador = ?, correo_administrador = ?, alias_administrador = ?
                WHERE id_administrador = ?';
        $params = array($this->nombre, $this->apellido, $this->correo, $this->alias, $_SESSION['idAdministrador']);
        return Database::executeRow($sql, $params);
    }

    /*
     *  Métodos para realizar las operaciones SCRUD (search, create, read, update, and delete).
     */
    public function searchRows()
    {
        $value = '%' . Validator::getSearchValue() . '%';
        $sql = 'SELECT id_administrador, nombre_administrador, apellido_administrador, correo_administrador, alias_administrador
                FROM administrador
                WHERE apellido_administrador LIKE ? OR nombre_administrador LIKE ?
                ORDER BY apellido_administrador';
        $params = array($value, $value);
        return Database::getRows($sql, $params);
    }

    public function createRow()
    {
        // Verificar si la tabla está vacía
        $sql = 'SELECT COUNT(*) AS count FROM tbAdmins';
        $result = Database::getRow($sql);
    
        // Si la tabla está vacía, asignar el nivel de usuario "Administrador" por defecto
        if ($result['count'] == 0) {
            // Obtener el ID del nivel de usuario correspondiente a "Administrador"
            $sql = 'SELECT id_nivel_usuario FROM tbniveles_usuario WHERE nombre_nivel = "Administrador"';
            $nivelAdministrador = Database::getRow($sql);
    
            // Verificar si se obtuvo el ID del nivel de usuario
            if ($nivelAdministrador && isset($nivelAdministrador['id_nivel_usuario'])) {
                // Insertar el administrador con el nivel correspondiente
                $sql = 'INSERT INTO tbAdmins(nombre_administrador, user_administrador, correo_administrador, clave_administrador, id_nivel_usuario)
                        VALUES(?, ?, ?, ?, ?)';
                $params = array($this->nombre, $this->alias, $this->correo, $this->clave, $nivelAdministrador['id_nivel_usuario']);
                return Database::executeRow($sql, $params);
            } else {
                // Manejar el caso en el que no se encontró el ID del nivel de usuario
                return false; // O mostrar un mensaje de error, lanzar una excepción, etc.
            }
        } else {
            // Si la tabla no está vacía, insertar el administrador sin modificar el nivel de usuario
            $sql = 'INSERT INTO tbAdmins(nombre_administrador, user_administrador, correo_administrador, clave_administrador, id_nivel_usuario)
                    VALUES(?, ?, ?, ?, ?)';
            $params = array($this->nombre, $this->alias, $this->correo, $this->clave, $this->nivel);
            return Database::executeRow($sql, $params);
        }
    }
    
    
    
    

    public function readAll()
{
    $sql = 'SELECT d.id_direccion, d.departamento, d.descripcion_direccion, d.id_usuario, d.id_distrito, t.distrito
            FROM tbdirecciones AS d
            INNER JOIN tbdistritos AS t ON d.id_distrito = t.id_distrito';
    return Database::getRows($sql);
}

public function readOne()
{
    $sql = 'SELECT d.id_direccion, d.departamento, d.descripcion_direccion, d.id_usuario, d.id_distrito, t.distrito
            FROM tbdirecciones AS d
            INNER JOIN tbdistritos AS t ON d.id_distrito = t.id_distrito
            WHERE d.id_direccion = ?';
    $params = array($this->id);
    return Database::getRow($sql, $params);
}


    public function updateRow()
    {
        $sql = 'UPDATE administrador
                SET nombre_administrador = ?, apellido_administrador = ?, correo_administrador = ?
                WHERE id_administrador = ?';
        $params = array($this->nombre, $this->apellido, $this->correo, $this->id);
        return Database::executeRow($sql, $params);
    }

    public function deleteRow()
    {
        $sql = 'DELETE FROM administrador
                WHERE id_administrador = ?';
        $params = array($this->id);
        return Database::executeRow($sql, $params);
    }
}
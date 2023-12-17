<?php
    require_once((dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . "/inc/config.php");
    
    class Conexion
    {

        //atributos

        private $_servidor;
        private $_usuario;
        private $_password;
        private $_bd;

        //variable global: todos los objetos de tipo Conexion, acceden al mismo atributo $_conexion
        private static $_conexion;
        
        private $_consulta;
        private $_tabla;

        //constructor

        public function __construct($unaTabla = "")
        {
            try{
                $this->_servidor = BD_SERVIDOR;
                $this->_usuario = BD_USUARIO;
                $this->_password = BD_PASSWORD;
                $this->_bd = BD_NOMBRE_BD;
                $this->_tabla = strtolower($unaTabla);
                if(self::$_conexion === null){
                    self::$_conexion = new mysqli($this->_servidor, $this->_usuario, $this->_password, $this->_bd);
                    if(self::$_conexion === null){
                        throw new Exception("No hay conexión");
                    }
                    if (self::$_conexion->connect_error) {
                        throw new Exception('Error en la conexión: ' . self::$_conexion->connect_error);
                    }
                }
            }catch(Exception $e){
                throw new Exception("error: " . $e->getMessage() . " - " . $e->getFile() . " (" . $e->getLine() . ")\n");
            }
        }

        //setters
        public function setTabla($unaTabla){
            $this->_tabla = strtolower($unaTabla);
        }

        public function setConsulta($unaConsulta){
            $this->_consulta = $unaConsulta;
        }

        //getters
        public function getTabla(){
            return $this->_tabla;
        }

        public function getConsulta(){
            return $this->_consulta;
        }

        public function getConexion(){
            return self::$_conexion;
        }

        //metodos estaticos

        //metodos para SQL

        public function select()
        {
            //hacemos la consulta
            $this->_consulta = "SELECT * FROM " . $this->_tabla . ";";

            //guardamos el resultado
            $resultado = mysqli_query(self::$_conexion, $this->_consulta);
            if (!$resultado) {
                throw new Exception('Error en la consulta: ' . $this->_consulta);
            }
            $filasResultado = $this->obtenerFilas($resultado);
            return $filasResultado;
        }

        
        public function selectAtributos($atributos)
        {   
            $this->_consulta = "SELECT " . $this->arrayToString($atributos) . " FROM " . $this->_tabla . ";";
            $resultado = mysqli_query(self::$_conexion, $this->_consulta);
            if (!$resultado) {
                throw new Exception('Error en la consulta: ' . $this->_consulta);
            }
            $filasResultado = $this->obtenerFilas($resultado);
            return $filasResultado;
        }

        public function selectWhere($atributosCondicion, $valoresCondicion, $losTipos, $operadorLogico = "")
        { 
            $cantidadAtributosCondicion = count($atributosCondicion);
            $cantidadValoresCondicion = count($valoresCondicion);
            $cantidadTipos = strlen($losTipos);

            //verificamos que la cantidad de atributos sea igual a la cantidad de valores y la cantidad de tipos
            if ($cantidadAtributosCondicion == $cantidadValoresCondicion && $cantidadValoresCondicion == $cantidadTipos) {
                if (count($atributosCondicion) > 1) {
                    $operadoresAdmitidos = ["&&", "||"];
                } else {
                    $operadoresAdmitidos = [""];
                }
                if (in_array($operadorLogico, $operadoresAdmitidos)) {
                    $condicion = "";

                    //por cada atributo
                    foreach ($atributosCondicion as $atributo) {
                        //agrega un signo de interrogación junto al operador logico
                        $condicion .= " " . $atributo . "=? " . $operadorLogico;
                    }
                    if (count($atributosCondicion) > 1) {
                        //quitamos el ultimo operador logico que conlleva dos caracteres
                        $condicion = substr($condicion, 0, strlen($condicion) - 2);
                    } else {
                        //eliminamos el último espacio en blanco
                        $condicion = rtrim($condicion);
                    }

                    try {
                        $filasResultado = [];
                        if(self::$_conexion && self::$_conexion instanceof mysqli){
                            $this->_consulta = self::$_conexion->prepare(
                                "SELECT * FROM " . $this->_tabla . " WHERE " . $condicion . ";"
                            );
                            //'...' es para pasar los valores del array por separado (como argumentos individuales)
                            $this->_consulta->bind_param($losTipos, ...$valoresCondicion);
                            $this->_consulta->execute();
                            $resultado = $this->_consulta->get_result();
                            if (!$resultado) {
                                throw new Exception('Error en la consulta: ' . $this->_consulta->error);
                            }
                            $filasResultado = $this->obtenerFilas($resultado);
                        }
                        
                    } catch (Exception $e) {
                        echo "Hubo un error en la consulta: " . $e->getMessage();
                    }
                    return $filasResultado;
                }else{
                    throw new Exception('Operador ' . $operadorLogico . ' no admitido');
                }
            }
        }

        public function ultimaIdTorneo()
        {
            $this->_consulta = "SELECT MAX(id_torneo) FROM torneo;";

            $resultado = mysqli_query(self::$_conexion, $this->_consulta);
            if (!$resultado) {
                throw new Exception('Error en la consulta: ' . $this->_consulta);
            }
            $fila = $resultado->fetch_row();
            $id = $fila[0];
            return $id;
        }

        public function ultimoIdGrupo($idTorneo, $funcion)
        {
            if(!$this->torneoValido($idTorneo)){
                return null;
            }
            $funcionesAdmitidas = ['MIN', 'MAX'];
            if(!in_array($funcion, $funcionesAdmitidas)){
                throw new Exception('Función no admitida');
            }
            $this->_consulta = self::$_conexion->prepare("SELECT " . $funcion . "(id_grupo) FROM grupo WHERE id_torneo=?");

            try {
                $this->_consulta->bind_param('i', $idTorneo);
                $this->_consulta->execute();
                $resultado = $this->_consulta->get_result();
                if (!$resultado) {
                    throw new Exception('Error en la consulta: ' . $this->_consulta->error);
                }
                $fila = $resultado->fetch_row();
                $id = $fila[0];
                $this->_consulta->close();
                return (int)$id;
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }    
        }

        public function ultimoIdEnfrentamiento(){
            $this->_consulta = "SELECT MAX(id_enfrentamiento) FROM enfrentamiento;";

            $resultado = mysqli_query(self::$_conexion, $this->_consulta);
            if (!$resultado) {
                throw new Exception('Error en la consulta: ' . $this->_consulta);
            }
            $fila = $resultado->fetch_row();
            $id = $fila[0];
            return $id;
        }

        //metodos para DML


        public function insert($losAtributos, $losValores, $losTipos)
        {
            $signosInterrogacion = "";

            //por cada atributo
            foreach ($losAtributos as $atributo) {
                //agrega un signo de interrogación
                $signosInterrogacion .= "?,";
            }
            //hacemos un substring para quitar la última ','. Ya que le decimos que guarde lo que tiene desde el caracter 0 hasta el penúltimo
            $signosInterrogacion = substr($signosInterrogacion, 0, strlen($signosInterrogacion) - 1);

            $this->_consulta = self::$_conexion->prepare(
                "INSERT INTO " . $this->_tabla . "(" . $this->arrayToString($losAtributos) . ")
                    VALUES (" . $signosInterrogacion . ");"
            );

            try {
                //el '...' es para pasar los valores del array por separado (como argumentos individuales)
                $this->_consulta->bind_param($losTipos, ...$losValores);
                $this->_consulta->execute();
            } catch (Exception $e) {
                throw new Exception("Hubo un error en la consulta: " . $e->getMessage());
            }
            $this->_consulta->close();
        }


        public function update($losAtributos, $losValores, $losTipos, $atributoCondicion, $valorCondicion, $tipoAtributoCondicion)
        {
            $atributosConValoresModificados = "";

            //por cada atributo
            foreach ($losAtributos as $atributo) {
                //agrega un 'atributo=?',
                $atributosConValoresModificados .= $atributo . "=?,";
            }
            //hacemos un substring para quitar la última ','. Ya que le decimos que guarde lo que tiene desde el caracter 0 hasta el penúltimo
            $atributosConValoresModificados = substr($atributosConValoresModificados, 0, strlen($atributosConValoresModificados) - 1);

            $this->_consulta = self::$_conexion->prepare(
                "UPDATE " . $this->_tabla . " SET " . $atributosConValoresModificados . " 
                    WHERE " . $atributoCondicion . "=?"
            );
            //agrego el valor de la condicion a la lista de valores
            $losValores[] = $valorCondicion;
            //agrego el tipo de dato de la condición a la lista de tipos
            $losTipos .= $tipoAtributoCondicion;

            try {
                //el '...' es para pasar los valores del array por separado (como argumentos individuales)
                $this->_consulta->bind_param($losTipos, ...$losValores);
                $this->_consulta->execute();
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
            $this->_consulta->close();
        }

        public function delete($atributoCondicion, $valorCondicion, $tipoAtributoCondicion)
        {
            //eventualmente utilizar array para 'atributoCondicion' y 'valorCondicion'. Asi, se puede usar con clave compuesta
            $this->_consulta = self::$_conexion->prepare(
                "DELETE FROM " . $this->_tabla . " WHERE " . $atributoCondicion . "=?;"
            );
            try {
                $this->_consulta->bind_param($tipoAtributoCondicion, $valorCondicion);
                $this->_consulta->execute();
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
        }

        //metodos de validacion

        //SUSTITUIR POR 'existe2()'
        public function existe($atributoClave, $unValor)
        {
            $filas = $this->listarArray();
            foreach ($filas as $fila) {
                if ($fila[$atributoClave] == $unValor) {
                    return true;
                }
            }
            return false;
        }

        public function existe2($atributosClave, $valores, $operadorLogico)
        {
            //checkea count($atributosClave) == count($valores)
            //si count() > 1, $operadoresAdmitidos = ["&&", "||"];
            //si count() == 1, $operadoresAdmitidos = [""]; 
            //a partir de los atributos, asigna mediante DESC, los tipos.
            //return selectWhere(...) > 0
        }

        public function torneoValido($unaIdTorneo)
        {
            $this->_consulta = self::$_conexion->prepare(
                "SELECT * FROM torneo WHERE id_torneo=?;"
            );
            try {
                $this->_consulta->bind_param('i', $unaIdTorneo);
                $this->_consulta->execute();
                $resultado = $this->_consulta->get_result();
                if (!$resultado) {
                    throw new Exception('Error en la consulta: ' . $this->_consulta->error);
                }
                $filasResultado = $this->obtenerFilas($resultado);
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
            $this->_consulta->close();
            //checkeamos que haya alguna fila en el resultado de la consulta
            //si la hay, es porque existe el Torneo
            return count($filasResultado) > 0;
        }

        public function sexoValido($unSexo)
        {
            $sexosValidos = array('masculino', 'femenino');
            return in_array($unSexo, $sexosValidos);
        }

        public function estaVacio($unString)
        {
            //sacamos los espacios en blanco
            $unString = str_replace(' ', '', $unString);
            //si esta vacio -> true, 
            //si no -> false
            return strlen($unString) == 0;
        }

        //otros metodos

        public function obtenerFilas($unResultado)
        {
            //guardará cada fila en una posición distinta del array
            $filas = array();
            //el contador servirá para indicar en qué posición del array se guardará dicha fila
            $contador = 0;
            //recorre las filas del resultado de la consulta
            while ($fila = $unResultado->fetch_assoc()) {
                //guarda la fila en el array 'filas', en la posición 'contador'
                $filas[$contador] = $fila;
                $contador++;
            }
            return $filas;
        }

        public function listarArray()
        {
            return $this->select();
        }


        public function arrayToString($unArray, $nivel = 0)
        {
            $stringRetorno = "";
            foreach ($unArray as $elemento) {
                if (is_array($elemento)) {
                //si el elemento es un array, llamamos recursivamente a la función con el nuevo nivel
                $stringRetorno .= "[" . $this->arrayToString($elemento, $nivel + 1) . "], ";
                } else {
                    //convertimos el elemento a string
                    $stringRetorno .= strval($elemento) . ", ";
                }
            }
            if ($nivel === 0) {
                //quitamos la última ', ' solo si estamos en el nivel superior
                $stringRetorno = rtrim($stringRetorno, ', ');
            }
            return $stringRetorno;
        }

        public function arrayToStringSinComa($unArray)
        {
            $stringRetorno = "";
            foreach ($unArray as $elemento) {
                if (is_string($elemento)) {
                    $stringRetorno .= $elemento;
                } else {
                    echo 'El array no tiene datos de tipo string';
                }
            }
            return $stringRetorno;
        }

        public function tieneTabla($unaTabla = ""){
            $consulta = "SHOW TABLES LIKE '" . $unaTabla . "'";
            $resultado = self::$_conexion->query($consulta);
            if ($resultado) {
                return $resultado->num_rows > 0;
            } else {
                return false;
            }
        }

        public function tipoAtributo($unAtributo) {
            self::validarTabla();
            $consulta = "SHOW COLUMNS FROM " . $this->_tabla . " LIKE '" . $unAtributo . "'";
            $resultado = self::$_conexion->query($consulta);
        
            if ($resultado && $resultado->num_rows > 0) {
                $fila = $resultado->fetch_assoc();
                $tipo = $fila['Type'];
        
                //devuelve 's' para cadenas o 'i' para datos numéricos
                //strpos($cadenaPrincipal, $subCadena) => devuelve la posicion de $subCadena dentro de $cadenaPrincipal
                if (strpos($tipo, 'date') !== false || strpos($tipo, 'datetime') !== false || strpos($tipo, 'char') !== false || strpos($tipo, 'varchar') !== false || strpos($tipo, 'enum') !== false || strpos($tipo, 'set') !== false) {
                    return 's';
                } else {
                    return 'i';
                }
            }else {
                throw new Exception("El atributo " . $unAtributo . " no existe en la tabla " . $this->_tabla);
            }
        }

        private function validarTabla(){
            if(!$this->tieneTabla($this->_tabla)){
                throw new Exception("La tabla " . $this->_tabla . " no existe");
            }
        }
    }
?>
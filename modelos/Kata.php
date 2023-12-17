<?php
    require_once((dirname(__DIR__)) . '/inc/bootstrap.php');
    
    class Kata{
        //atributos
        private $_id_kata;
        private $_nombre_kata;
        private static $_id_minimo = 1;
        private static $_id_maximo = 102;
        private $_atributos;
        private $_atributoClave;
        private $_conexion;
        //constructor
        public function __construct($id, $nombre){
            $this->_id_kata = $id;
            $this->_nombre_kata = $nombre;
            /*
            array asociativo que relaciona los atributos con los tipos de datos
            con array_keys($this->_atributos) se podrá acceder a los nombres de los atributos
            y con $this->_atributos se podrá acceder a los tipos de dato de dichos atributos
            LOS NOMBRES SON LOS QUE TIENEN EN LA BD
            */
            $this->_atributos = array(
                'id_kata' => 'i',
                'nombre_kata' => 's',
            );
            $this->_atributoClave = 'id_kata';
            $this->_conexion = new Conexion('Kata');
        }

        //getters
        public function getId(){
            return $this->_id_kata;
        }
        public function getNombre(){
            return $this->_nombre_kata;
        }

        //metodos estaticos
        public static function getById($id_kata){
            $kata = null;
            $conexion = new Conexion('Kata');
            $filas = $conexion->selectWhere(['id_kata'], [$id_kata], 'i', '');
            if (count($filas) > 0){
                $id = $filas[0]['id_kata'];
                $nombre = $filas[0]['nombre_kata'];
                $kata = new Kata($id, $nombre);
            }
            return $kata;
        }

        public static function getAll(){
            $retorno = array();
            for($i = self::$_id_minimo; $i <= self::$_id_maximo; $i++){
                $retorno[]=self::getById($i);
            }
            return $retorno;
        }

        public static function listar($id_kata){
            $kata = self::getById($id_kata);
            $retorno = $kata === null ? null : $kata->toJSON();
            return $retorno;
        }

        public static function listarTodos(){
            $retorno = array();
            $katas = self::getAll();
            foreach($katas as $kata){
                $retorno[] = $kata->toJSON();
            }
            return $retorno;
        }
        
        //metodos publicos
        public function guardar(){
            self::validar();
            $this->_conexion->insert(array_keys($this->_atributos), [$this->_id_kata, $this->_nombre_kata], $this->_conexion->arrayToStringSinComa($this->_atributos));
        }

        public function eliminar($unaId){
            if($this->_conexion->existe($this->_atributoClave, $unaId)){
                $this->_conexion->delete($this->_atributoClave, $unaId, $this->_atributos[$this->_atributoClave]);
            }else{
                throw new Exception('La Kata no existe');
            }
        }

        public function toJSON(){
            $kata = [
                'id_kata' => $this->getId(),
                'nombre_kata' => $this->getNombre()
            ];
            return $kata;
        }

        //metodos privados
        private function validar(){
            if(!self::idValida($this->_id_kata)){
                throw new Exception('Id no válida');
            }
            if(!self::nombreValido($this->_nombre_kata)){
                throw new Exception('Nombre no válido');
            }
        }

        private function idValida($unaId){
            return (($unaId >= 1) && ($unaId <= 102));
        }

        private function nombreValido($unNombre){
            //eventualmente evaluar si se encuentra en la lista oficial de katas
            return true;
        }
    }
?>
<?php
    require_once((dirname(__DIR__)) . '/inc/bootstrap.php');

    class TecnicoSoftware{
        //atributos
        private $_nombreUsuario_tecnicoSoftware;
        private $_clave_tecnicoSoftware;
        private $_atributos;
        private $_atributoClave;
        private $_tipoAtributoClave;
        private $_conexion;

        //constructor
        public function __construct($nombreUsuario, $clave){
            $this->_nombreUsuario_tecnicoSoftware = $nombreUsuario;
            $this->_clave_tecnicoSoftware = $clave;
            /*
            array asociativo que relaciona los atributos con los tipos de datos
            con array_keys($this->_atributos) se podrá acceder a los nombres de los atributos
            y con $this->_atributos se podrá acceder a los tipos de dato de dichos atributos
            LOS NOMBRES SON LOS QUE TIENEN EN LA BD
            */
            $this->_atributos = array(
                'nombreUsuario_tecnicoSoftware' => 's',
                'clave_tecnicoSoftware' => 's',
            );
            $this->_atributoClave = 'nombreUsuario_tecnicoSoftware';
            $this->_tipoAtributoClave = 's';
            $this->_conexion = new Conexion('TecnicoSoftware');
        }

        //metodos publicos
        public function guardar(){
            self::validar();
            if($this->existe()){
                throw new Exception("Técnico de software ya existente");
            }
            $this->_conexion->insert(array_keys($this->_atributos), [$this->_nombreUsuario_tecnicoSoftware, $this->_clave_tecnicoSoftware], $this->_conexion->arrayToStringSinComa($this->_atributos));
        }

        public function actualizar(){
            self::validar();
            $atributos = $this->_atributos;
            unset($atributos["nombreUsuario_tecnicoSoftware"]);
            $this->_conexion->update(array_keys($atributos), [$this->_clave_tecnicoSoftware], $this->_conexion->arrayToStringSinComa($atributos), $this->_atributoClave, $this->_nombreUsuario_tecnicoSoftware, $this->_tipoAtributoClave);
        }

        public function eliminar($unNombreUsuario){
            if($this->_conexion->existe($this->_atributoClave, $unNombreUsuario)){
                $this->_conexion->delete($this->_atributoClave, $unNombreUsuario, $this->_atributos[$this->_atributoClave]);
            }else{
                throw new Exception('El Técnico de Software no existe');
            }
        }

        public function existe(){
            return $this->_conexion->existe($this->_atributoClave, $this->_nombreUsuario_tecnicoSoftware);
        }

        public function tieneClave($unaClave){
            if($this->existe()){
                $filasResultado = $this->_conexion->selectWhere(['nombreUsuario_tecnicoSoftware', 'clave_tecnicoSoftware'], [$this->_nombreUsuario_tecnicoSoftware, $unaClave], 'ss', '&&');
                return count($filasResultado) > 0;
            }else{
                return false;
            }
        }

        //metodos privados
        private function validar(){
            if(!self::nombreUsuarioValido($this->_nombreUsuario_tecnicoSoftware)){
                throw new Exception('Nombre de usuario no válido');   
            }
            if(!self::claveValida($this->_clave_tecnicoSoftware)){
                throw new Exception('Clave no válida');   
            }
        }

        private function nombreUsuarioValido($unNombreUsuario){
            return !$this->_conexion->estaVacio($unNombreUsuario);
        }

        private function claveValida($unaClave){
            return !$this->_conexion->estaVacio($unaClave);
        }
    }
?>
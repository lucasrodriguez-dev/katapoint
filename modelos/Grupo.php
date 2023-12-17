<?php
    require_once((dirname(__DIR__)) . '/inc/bootstrap.php');
    
    class Grupo{
        //atributos
        private $_id_torneo;
        private $_id_grupo;
        private $_cantidadEquipos_grupo;
        private $_equipos;
        private $_atributos;
        private $_atributoClave;
        private $_tipoAtributoClave;
        private $_conexion;

        //constructor
        public function __construct($idTorneo, $cantidadEquipos){
            $this->_id_torneo = $idTorneo;
            $this->_cantidadEquipos_grupo = $cantidadEquipos;
            $this->_equipos = array();

            /*
            array asociativo que relaciona los atributos con los tipos de datos
            con array_keys($this->_atributos) se podrá acceder a los nombres de los atributos
            y con $this->_atributos se podrá acceder a los tipos de dato de dichos atributos
            LOS NOMBRES SON LOS QUE TIENEN EN LA BD
            */
            $this->_atributos = array(
                'id_torneo' => 'i',
                'id_grupo' => 'i',
                'cantidadEquipos_grupo' => 'i'
            );
            $this->_atributoClave= array('id_torneo', 'id_grupo');
            $this->_tipoAtributoClave='ii';
            $this->_conexion = new Conexion('Grupo');
        }

        //getters
        public function getIdGrupo(){
            return $this->_id_grupo;
        }
        public function getIdTorneo(){
            return $this->_id_torneo;
        }
        public function getCantidadEquipos(){
            return $this->_cantidadEquipos_grupo;
        }

        //setters
        public function setIdGrupo($unaId){
            $this->_id_grupo = $unaId;
        }

        //metodos estaticos
        public static function getByTorneo($unaId){
            $conexion = new Conexion('Grupo');
            $listaRetorno = array();
            $filas = $conexion->selectWhere(['id_torneo'], [$unaId], 'i', '');
            for($i = 0; $i < count($filas); $i++){
                $id_torneo = $filas[$i]['id_torneo'];
                $id_grupo = $filas[$i]['id_grupo'];
                $cantidadEquipos = $filas[$i]['cantidadEquipos_grupo'];
                $grupo = new Grupo($id_torneo, $cantidadEquipos);
                $grupo->setIdGrupo($id_grupo);
                $grupo->_equipos = Equipo::getByGrupo($grupo->_id_torneo, $grupo->_id_grupo);
                $listaRetorno[$i] = $grupo;
            }
            return $listaRetorno;
        }

        public static function getByIdGrupo($id_torneo, $id_grupo){
            $grupo = null;
            $conexion = new Conexion('Grupo');
            $filas = $conexion->selectWhere(['id_torneo', 'id_grupo'], [$id_torneo, $id_grupo], 'ii', '&&');
            if(count($filas) > 0){
                $id_torneo = $filas[0]['id_torneo'];
                $id_grupo = $filas[0]['id_grupo'];
                $cantidadEquipos = $filas[0]['cantidadEquipos_grupo'];
                $grupo = new Grupo($id_torneo, $cantidadEquipos);
                $grupo->_id_grupo = $id_grupo;
                $grupo->_equipos = Equipo::getByGrupo($grupo->getIdTorneo(), $grupo->getIdGrupo());
                
            }
            return $grupo;
        }

        public static function listar($id_torneo){
            $retorno=[];
            if($id_torneo !== null){
                $grupos = self::getByTorneo($id_torneo);
                foreach($grupos as $grupo){
                    $retorno[]=$grupo->toJSON();
                }
            }
            return $retorno;
        }

        public static function listarGrupo($id_torneo, $id_grupo){
            $retorno = [];
            $grupo = self::getByIdGrupo($id_torneo, $id_grupo);
            $retorno[] = $grupo === null ? null : $grupo->toJSON();
            return $retorno;
        }

        public static function eliminarDeTorneo($id_torneo){
            self::validarEliminacion($id_torneo);
            $torneo = Torneo::getById($id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            $grupos = $torneo->listarGrupos();
            foreach($grupos as $grupo){
                $grupo->eliminar();
            }
        }

        public static function validarEliminacion($id_torneo){
            Torneo::validarPosibilidadDeCambios($id_torneo);
            $torneo = Torneo::getById($id_torneo);
            if(count($torneo->listarEquipos()) > 0){
                throw new Exception("No se puede realizar cambios, debido a que ya se registraron equipos en el torneo");
            }
        }

        public static function minId($idTorneo){
            $conexion = new Conexion('Grupo');
            return $conexion->ultimoIdGrupo($idTorneo, 'MIN');
        }

        public static function maxId($idTorneo){
            $conexion = new Conexion('Grupo');
            return $conexion->ultimoIdGrupo($idTorneo, 'MAX');
        }

        //metodos publicos
        public function listarEquipos(){
            return $this->_equipos;
        }

        public function listarEquiposJSON(){
            $equipos = $this->listarEquipos();
            $losEquipos = array();
            foreach($equipos as $equipo){
                $losEquipos[]=$equipo->toJSON();
            }
            return $losEquipos;
        }

        public function guardar(){
            self::validar();
            $ultimoId = self::maxId($this->_id_torneo);
            //si $ultimoId === null, $this->_id_grupo = 1, sino $this->_id_grupo = ($ultimoId + 1)
            $this->_id_grupo = $ultimoId === null ? 1 : ($ultimoId + 1);
            $this->_conexion->insert(array_keys($this->_atributos), [$this->_id_torneo, $this->_id_grupo, $this->_cantidadEquipos_grupo], $this->_conexion->arrayToStringSinComa($this->_atributos));
            $this->_id_grupo = self::maxId($this->_id_torneo);
        }

        public function eliminar(){
            $this->_conexion->setConsulta($this->_conexion->getConexion()->prepare(
                "DELETE 
                FROM grupo
                WHERE id_torneo = ? AND id_grupo = ?;"
            ));
            try {
                $this->_conexion->getConsulta()->bind_param('ii', $this->_id_torneo, $this->_id_grupo);
                $this->_conexion->getConsulta()->execute();
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
        }

        public function toJSON(){
            $grupo = [
                'id_torneo' => $this->getIdTorneo(),
                'id_grupo' => $this->getIdGrupo(),
                'cantidadEquipos_grupo' => $this->getCantidadEquipos(),
                'equipos' => $this->listarEquiposJSON()   
            ];
            return $grupo;
        }

        //metodos privados
        private function validar(){
            if(!$this->_conexion->torneoValido($this->_id_torneo)){
                throw new Exception('Id de Torneo no válida');
            }
            if(!self::cantidadEquiposValida($this->_cantidadEquipos_grupo)){
                throw new Exception('Cantidad de Equipos no válida');
            }
        }

        private function cantidadEquiposValida($unaCantidad){
            $cantidadMinima = 2;
            return $unaCantidad >= $cantidadMinima;
        }
    }
?>
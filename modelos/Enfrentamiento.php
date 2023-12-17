<?php
    require_once((dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/Equipo.php');
    
    class Enfrentamiento{
        //atributos
        private $_id_enfrentamiento;
        private $_fecha_enfrentamiento;
        private $_ronda_enfrentamiento;
        private $_equipos;
        private $_atributos;
        private $_atributoClave;
        private $_tipoAtributoClave;
        private $_conexion;
        
        //constructor
        public function __construct($fecha, $ronda){
            $this->_fecha_enfrentamiento = $fecha;
            $this->_ronda_enfrentamiento = $ronda;
            $this->_equipos = array();

            /*
            array asociativo que relaciona los atributos con los tipos de datos
            con array_keys($this->_atributos) se podrá acceder a los nombres de los atributos
            y con $this->_atributos se podrá acceder a los tipos de dato de dichos atributos
            LOS NOMBRES SON LOS QUE TIENEN EN LA BD
            */
            $this->_atributos = array(
                'fecha_enfrentamiento' => 's',
                'ronda_enfrentamiento' => 'i'
            );
            $this->_atributoClave= 'id_enfrentamiento';
            $this->_tipoAtributoClave='i';
            $this->_conexion = new Conexion('Enfrentamiento');
        }

        //getters
        public function getId(){
            return $this->_id_enfrentamiento;
        }
        public function getFecha(){
            return $this->_fecha_enfrentamiento;
        }
        public function getRonda(){
            return $this->_ronda_enfrentamiento;
        }

        //setters
        public function setFecha($fecha){
            $this->_fecha_enfrentamiento = $fecha;
        }
        
        //metodos estaticos
        public static function getById($unaId){
            $enfrentamiento = null;
            $conexion = new Conexion('Enfrentamiento');
            $filas = $conexion->selectWhere(['id_enfrentamiento'], [$unaId], 'i', '');
            if (count($filas) > 0){
                $fecha = $filas[0]['fecha_enfrentamiento'];
                $ronda = $filas[0]['ronda_enfrentamiento'];
                $enfrentamiento = new Enfrentamiento($fecha, $ronda);
                $enfrentamiento->_id_enfrentamiento = $filas[0]['id_enfrentamiento'];
                $enfrentamiento->_equipos = Equipo::getByEnfrentamiento($enfrentamiento->getId());
                $enfrentamiento;
            }
            return $enfrentamiento;
        }

        public static function getByRonda($ronda){
            $conexion = new Conexion('');
            $listaRetorno = array();
            $conexion->setConsulta($conexion->getConexion()->prepare(
                    "SELECT id_enfrentamiento
                    FROM enfrentamiento
                    WHERE ronda_enfrentamiento=?;"
            )); 
            try {
                $conexion->getConsulta()->bind_param('i', $ronda);
                $conexion->getConsulta()->execute();
                $resultado = $conexion->getConsulta()->get_result();
                if (!$resultado) {
                    throw new Exception('Error en la consulta: ' . $conexion->getConsulta()->error);
                }
                $filasResultado = $conexion->obtenerFilas($resultado);
                $cantidadFilas = count($filasResultado);
                if($cantidadFilas > 0){
                    for($i = 0; $i < $cantidadFilas; $i++){
                        $id_enfrentamiento = $filasResultado[$i]['id_enfrentamiento'];
                        $elEnfrentamiento = self::getById($id_enfrentamiento);
                        $listaRetorno[$i]=$elEnfrentamiento;
                    }
                }
                return $listaRetorno;
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
        }

        public static function getByTorneo($unaIdTorneo){
            $conexion = new Conexion('');
            $listaRetorno = array();
            $conexion->setConsulta($conexion->getConexion()->prepare(
                    "SELECT tiene.id_enfrentamiento
                    FROM tiene JOIN equipo
                    ON tiene.nombre_equipo = equipo.nombre_equipo
                    WHERE equipo.id_torneo = ?
                    GROUP BY tiene.id_enfrentamiento;"
            )); 
            try {
                $conexion->getConsulta()->bind_param('i', $unaIdTorneo);
                $conexion->getConsulta()->execute();
                $resultado = $conexion->getConsulta()->get_result();
                if (!$resultado) {
                    throw new Exception('Error en la consulta: ' . $conexion->getConsulta()->error);
                }
                $filasResultado = $conexion->obtenerFilas($resultado);
                $cantidadFilas = count($filasResultado);
                if($cantidadFilas > 0){
                    for($i = 0; $i < $cantidadFilas; $i++){
                        $id_enfrentamiento = $filasResultado[$i]['id_enfrentamiento'];
                        $elEnfrentamiento = self::getById($id_enfrentamiento);
                        $listaRetorno[$i]=$elEnfrentamiento;
                    }
                }
                return $listaRetorno;
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
        }

        public static function getByRondaTorneo($ronda, $id_torneo){
            $torneo = Torneo::getById($id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            return $torneo->listarEnfrentamientosRonda($ronda);
        }

        //a partir de un equipo, devuelve su ultimo enfrentamiento
        public static function getIdByEquipo($nombre_equipo){
            $conexion = new Conexion();
            $conexion->setConsulta($conexion->getConexion()->prepare(
                    "SELECT MAX(id_enfrentamiento)'id_enfrentamiento'
                    FROM tiene
                    WHERE nombre_equipo=?"
            )); 
            try {
                $conexion->getConsulta()->bind_param('s', $nombre_equipo);
                $conexion->getConsulta()->execute();
                $resultado = $conexion->getConsulta()->get_result();
                if (!$resultado) {
                    throw new Exception('Error en la consulta: ' . $conexion->getConsulta()->error);
                }
                $filasResultado = $conexion->obtenerFilas($resultado);
                return $filasResultado[0]['id_enfrentamiento'];
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
        }

        public static function listar($datos, $parametro){
            $retorno=[];
            switch($parametro){
                case "id_torneo":
                    $enfrentamientos = self::getByTorneo($datos);
                    foreach($enfrentamientos as $enfrentamiento){
                        $retorno[]=$enfrentamiento->toJSON();
                    }
                    break;
                case "ronda_enfrentamiento":
                    $enfrentamientos = self::getByRonda($datos);
                    foreach($enfrentamientos as $enfrentamiento){
                        $retorno[]=$enfrentamiento->toJSON();
                    }
                    break;
                case "id_enfrentamiento":
                    $enfrentamiento = self::getById($datos);
                    $retorno[]= $enfrentamiento === null ? null : $enfrentamiento->toJSON();
                    break;
                default:
                    break;
            }
            return $retorno;
        }

        public static function listarRondaTorneo($id_torneo, $ronda_enfrentamiento){
            $retorno = [];
            $enfrentamientos = self::getByRondaTorneo($ronda_enfrentamiento, $id_torneo);
            foreach($enfrentamientos as $enfrentamiento){
                $retorno[]=$enfrentamiento->toJSON();
            }
            return $retorno;
        }

        public static function eliminarDeTorneo($id_torneo){
            self::validarEliminacion($id_torneo);
            $enfrentamientos = self::getByTorneo($id_torneo);
            foreach($enfrentamientos as $enfrentamiento){
                $enfrentamiento->eliminar();
            }
        }

        public static function validarEliminacion($id_torneo){
            $torneo = Torneo::getById($id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            $enfrentamientos = $torneo->listarEnfrentamientos();
            foreach($enfrentamientos as $enfrentamiento){
                if(count($enfrentamiento->listarEquipos()) > 0){
                    throw new Exception("Ya hay equipos relacionados a los enfrentamientos");
                }
            }
        }

        public static function listarKatasRealizados($id_enfrentamiento){
            $enfrentamiento = self::getById($id_enfrentamiento);
            if($enfrentamiento === null){
                throw new Exception("Enfrentamiento no existente");
            }
            return $enfrentamiento->listarRealizaciones();
        }

        //metodos publicos
        public function listarRealizaciones(){
            $retorno = array();
            $this->_conexion->setConsulta($this->_conexion->getConexion()->prepare(
                "SELECT *
                FROM realiza
                WHERE id_enfrentamiento = ?;"
            ));
            try {
                $this->_conexion->getConsulta()->bind_param('i', $this->_id_enfrentamiento);
                $this->_conexion->getConsulta()->execute();
                $resultado = $this->_conexion->getConsulta()->get_result();
                if (!$resultado) {
                    throw new Exception('Error en la consulta: ' . $this->_conexion->getConsulta()->error);
                }
                $filas = $this->_conexion->obtenerFilas($resultado);
                if(count($filas) > 0){
                    return $filas;
                }
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
            return $retorno;
        }

        public function agregarEquipo($equipo){
            if(!$this->tieneEquipo($equipo->getNombre())){
                $this->_equipos[]=$equipo;
            }else{
                echo "El Equipo ya fue agregado";
            }
        }
        public function quitarEquipo($nombre_equipo){
            if($this->tieneEquipo($nombre_equipo)){
                $indice = $this->indexEquipo($nombre_equipo);
                unset($this->_equipos[$indice]);
            }else{
                echo "El Equipo no está en este Enfrentamiento";
            }
        }
        public function tieneEquipo($nombre_equipo){
            $equipos = $this->listarEquipos();
            foreach($equipos as $equipo){
                if($equipo->getNombre() === $nombre_equipo){
                    return true;
                }
            }
            return false;
        }
        public function indexEquipo($nombre_equipo){
            if($this->tieneEquipo($nombre_equipo)){
                $equipos = $this->listarEquipos();
                $cantidadEquipos = count($equipos);
                for($i = 0; $i < $cantidadEquipos; $i++){
                    if($equipos[$i]->getNombre() == $nombre_equipo){
                        return $i;
                    }
                }
            }
            return -1;
        }
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

        public function getTorneo(){
            if(count($this->_equipos) === 0){
                return null;
            }
            //evaluamos que sus equipos sean del mismo torneo
            $id_torneo = $this->_equipos[0]->getIdTorneo();
            foreach($this->_equipos as $equipo){
                if($equipo->getIdTorneo() !== $id_torneo){
                    throw new Exception("El enfrentamiento " . $this->_id_enfrentamiento . " tiene equipos de distintos torneos");
                }
            }
            return $id_torneo;
        }

        //devuelve la lista de clasificados
        public function clasificados(){
            $cantidadClasificados = $this->cuantosClasifican();
            if(!isset($cantidadClasificados) || !is_numeric($cantidadClasificados)){
                throw new Exception("No se ha recibido una cantidad adecuada de equipos clasificados: " . $cantidadClasificados);
            }
            if($cantidadClasificados === count($this->_equipos)){
                $retorno = $this->_equipos;
            }else{
                $retorno = [];
                for($i = 0; $i <= $cantidadClasificados; $i++){
                    $equipo = $this->_equipos[$i];
                    if($equipo->getPosicion() <= $cantidadClasificados){
                        $retorno[]=$equipo;
                    }
                }
            }
            return $retorno;
        }

        public function cuantosClasifican(){
            $torneo = Torneo::getById($this->getTorneo());
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            $rondaActual = $torneo->rondaActual();
            $enfrentamientosActuales = $torneo->listarEnfrentamientosRonda($rondaActual);
            $cantidadEnfrentamientosActuales = count($enfrentamientosActuales);
            $cantidadEquiposEnfrentamiento = count($this->_equipos);

            $retorno = null;

            switch($cantidadEnfrentamientosActuales){
                case 1:
                    $retorno = 0;
                    break;
                case 2:
                    if($cantidadEquiposEnfrentamiento === 2){
                        $retorno = 1;
                    }else{
                        $retorno = 3;
                    }
                    break;
                case 4:
                    if($cantidadEquiposEnfrentamiento >= 3 && $cantidadEquiposEnfrentamiento <= 6){
                        $retorno = 3;
                    }else{
                        $retorno = 4;
                    }
                    break;
                case 8:
                    $retorno = 3;
                    break;
                default:
                    throw new Exception("Ha ocurrido algo inesperado. Cantidad de enfrentamientos actuales: " . $cantidadEnfrentamientosActuales);
                    break;
            }
            return $retorno;
        }
        
        public function maxId(){
            return $this->_conexion->ultimoIdEnfrentamiento();
        }

        public function guardar(){
            self::validar();
            $this->_conexion->insert(array_keys($this->_atributos), [$this->_fecha_enfrentamiento, $this->_ronda_enfrentamiento], $this->_conexion->arrayToStringSinComa($this->_atributos));
            $this->_id_enfrentamiento = $this->maxId();
        }

        public function actualizar(){
            self::validarFecha();
            $this->_conexion->update(["fecha_enfrentamiento"], [$this->_fecha_enfrentamiento], "s", $this->_atributoClave, $this->_id_enfrentamiento, $this->_tipoAtributoClave);
        }

        public function eliminar(){
            if($this->_conexion->existe($this->_atributoClave, $this->_id_enfrentamiento)){
                $this->_conexion->delete($this->_atributoClave, $this->_id_enfrentamiento, $this->_tipoAtributoClave);
            }else{
                throw new Exception('El Enfrentamiento no existe');
            }
        }

        public function toJSON(){
            $enfrentamiento = [
                'id' => $this->getId(),
                'fecha' => $this->getFecha(),
                'ronda' => $this->getRonda(),
                'equipos' => $this->listarEquiposJSON()
            ];
            return $enfrentamiento;
        }
        
        //metodos privados
        private function validarId(){
            if(!$this->_conexion->existe($this->_atributoClave, $this->_id_enfrentamiento)){
                throw new Exception('El Enfrentamiento no existe');
            }
        }

        /*
        //devuelve equipos ordenados segun sus puntajes en el enfrentamiento
        private function ordenarEquipos($equiposConPuntajes){
            for($i = 0; $i < (count($equiposConPuntajes)-1); $i++){
                for($j = ($i+1); $j < count($equiposConPuntajes); $j++){
                    if($equiposConPuntajes[$j]["puntaje"] < $equiposConPuntajes[$i]["puntaje"]){
                        //cambiamos de lugar a los equipos
                        $aux = $equiposConPuntajes[$j];
                        $equiposConPuntajes[$j] = $equiposConPuntajes[$i];
                        $equiposConPuntajes[$i] = $aux;
                    }
                }
            }
            return $equiposConPuntajes;
        }
        */

        private function validar(){
            if(!self::rondaValida($this->_ronda_enfrentamiento)){
                throw new Exception('Ronda no válida');
            }
            if(!self::fechaValida($this->_fecha_enfrentamiento)){
                throw new Exception('Fecha no válida');
            }
        }

        private function rondaValida($unaRonda){
            $valorMinimo = 1;
            return $unaRonda >= $valorMinimo;
        }
        private function fechaValida($unaFecha){
            //validar formato fecha
            return true;
        }
        private function validarFecha(){
            if(!self::fechaValida($this->_fecha_enfrentamiento)){
                throw new Exception('Fecha no válida');
            }
        }
    }
?>
<?php
    require_once((dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/Torneo.php');
    require_once(RUTA_RAIZ . 'modelos/Equipo.php');
    
    class Competidor{
        //atributos
        private $_id_competidor;
        private $_ci_competidor;
        private $_sexo_competidor;
        private $_fechaNacimiento_competidor;
        private $_escuela_competidor;
        private $_nombreCompleto_competidor;
        private $_nombre_equipo;
        private $_id_torneo;    
        private $_atributos;
        private static $_atributoClave='id_competidor';
        private static $_tipoAtributoClave='i';
        private $_conexion;

        //constructor
        public function __construct($ci, $sexo, $fecha, $escuela, $nombre, $equipo, $id_torneo = null, $id = null){
            $this->_ci_competidor = $ci;
            $this->_sexo_competidor = $sexo;
            $this->_fechaNacimiento_competidor = $fecha;
            $this->_escuela_competidor = $escuela;
            $this->_nombreCompleto_competidor = $nombre;
            $this->_nombre_equipo = $equipo;
            $this->_id_torneo= $id_torneo;

            $this->_atributos = array(
                'ci_competidor' => 'i',
                'sexo_competidor' => 's',
                'fechaNacimiento_competidor' => 's',
                'escuela_competidor' => 's',
                'nombreCompleto_competidor' => 's',
                'nombre_equipo' => 's',
            );
            $this->_conexion = new Conexion('Competidor');

            $this->_id_competidor = ($id === null) ? self::ultimoIdCompetidor() + 1 : $id;
        }

        //getters
        public function getId(){
            return $this->_id_competidor;
        }
        public function getSexo(){
            return $this->_sexo_competidor;
        }
        public function getFechaNacimiento(){
            return $this->_fechaNacimiento_competidor;
        }
        public function getEscuela(){
            return $this->_escuela_competidor;
        }
        public function getNombre(){
            return $this->_nombreCompleto_competidor;
        }
        public function getEquipo(){
            return $this->_nombre_equipo;
        }
        public function getCi(){
            return $this->_ci_competidor;
        }
        public function getIdTorneo(){
            return $this->_id_torneo;
        }

        //setters
        public function setEquipo($unNombreEquipo){
            $this->_nombre_equipo = $unNombreEquipo;
        }

        //metodos estaticos
        public static function ultimoIdCompetidor(){
            $conexion = new Conexion("Competidor");
            $conexion->setConsulta("SELECT MAX(id_competidor) FROM competidor;");
    
            $resultado = mysqli_query($conexion->getConexion(), $conexion->getConsulta());
            if (!$resultado) {
                throw new Exception('Error en la consulta: ' . $conexion->getConsulta());
            }
            $fila = $resultado->fetch_row();
            $id = $fila[0];
            return $id;
        }

        public static function getById($idCompetidor){
            $competidor = null;
            $conexion = new Conexion('Competidor');
            $filas = $conexion->selectWhere(['id_competidor'], [$idCompetidor], 'i', '');
            if (count($filas) > 0){
                $id = $filas[0]['id_competidor'];
                $ci = $filas[0]['ci_competidor'];
                $sexo = $filas[0]['sexo_competidor'];
                $fecha = $filas[0]['fechaNacimiento_competidor'];
                $escuela = $filas[0]['escuela_competidor'];
                $nombre = $filas[0]['nombreCompleto_competidor'];
                $equipo = $filas[0]['nombre_equipo'];
                $equipo = Equipo::getByNombre($equipo);
                if($equipo === null){
                    throw new Exception("Equipo no existente");
                }
                $id_torneo = $equipo->getIdTorneo();
                $competidor = new Competidor($ci, $sexo, $fecha, $escuela, $nombre, $equipo->getNombre(), $id_torneo, $id);
            }
            return $competidor;
        }

        public static function getByCi($ciCompetidor){
            $competidor = null;
            $conexion = new Conexion('Competidor');
            $filas = $conexion->selectWhere(['ci_competidor'], [$ciCompetidor], 'i', '');
            if (count($filas) > 0){
                $id = $filas[0]['id_competidor'];
                $ci = $filas[0]['ci_competidor'];
                $sexo = $filas[0]['sexo_competidor'];
                $fecha = $filas[0]['fechaNacimiento_competidor'];
                $escuela = $filas[0]['escuela_competidor'];
                $nombre = $filas[0]['nombreCompleto_competidor'];
                $equipo = $filas[0]['nombre_equipo'];
                $equipo = Equipo::getByNombre($equipo);
                if($equipo === null){
                    throw new Exception("Equipo no existente");
                }
                $id_torneo = $equipo->getIdTorneo();
                $competidor = new Competidor($ci, $sexo, $fecha, $escuela, $nombre, $equipo->getNombre(), $id_torneo, $id);
            }
            return $competidor;
        }

        public static function getByEquipo($nombreEquipo){
            $conexion = new Conexion('Competidor');
            $listaRetorno = array();
            $filas = $conexion->selectWhere(['nombre_equipo'], [$nombreEquipo], 's', '');
            $cantidadCompetidores = count($filas);
            for($i = 0; $i < $cantidadCompetidores; $i++){
                $id = $filas[$i]['id_competidor'];
                $ci = $filas[$i]['ci_competidor'];
                $sexo = $filas[$i]['sexo_competidor'];
                $fecha = $filas[$i]['fechaNacimiento_competidor'];
                $escuela = $filas[$i]['escuela_competidor'];
                $nombre = $filas[$i]['nombreCompleto_competidor'];
                $equipo = $filas[$i]['nombre_equipo'];
                $competidor = new Competidor($ci, $sexo, $fecha, $escuela, $nombre, $equipo, null, $id);
                $listaRetorno[$i] = $competidor;
            }
            return $listaRetorno;
        }

        public static function getByTorneo($idTorneo){
            $conexion = new Conexion();
            $listaRetorno = array();
            $conexion->setConsulta($conexion->getConexion()->prepare(
                    "SELECT competidor.*
                    FROM competidor JOIN equipo
                    ON competidor.nombre_equipo = equipo.nombre_equipo
                    WHERE equipo.id_torneo = ?;"
            )); 
            try {
                $conexion->getConsulta()->bind_param('i', $idTorneo);
                $conexion->getConsulta()->execute();
                $resultado = $conexion->getConsulta()->get_result();
                if (!$resultado) {
                    throw new Exception('Error en la consulta: ' . $conexion->getConsulta()->error);
                }
                $filasResultado = $conexion->obtenerFilas($resultado);
                $cantidadFilas = count($filasResultado);
                if($cantidadFilas > 0){
                    for($i = 0; $i < $cantidadFilas; $i++){
                        $id_competidor = $filasResultado[$i]['id_competidor'];
                        $elCompetidor = self::getById($id_competidor);
                        $listaRetorno[$i]=$elCompetidor;
                    }
                }
                return $listaRetorno;
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
        }

        public static function listar($datos, $parametro){
            $retorno=[];
            switch($parametro){
                case "id_torneo":
                    $competidores = self::getByTorneo($datos);
                    foreach($competidores as $competidor){
                        $retorno[]=$competidor->toJSON();
                    }
                    break;
                case "nombre_equipo":
                    $competidores = self::getByEquipo($datos);
                    foreach($competidores as $competidor){
                        $retorno[]=$competidor->toJSON();
                    }
                    break;
                case "id_competidor":
                    $competidor = self::getById($datos);
                    $retorno[]= $competidor === null ? null : $competidor->toJSON();
                    break;
                case "ci_competidor":
                    $competidor = self::getByCi($datos);
                    $retorno[]= $competidor === null ? null : $competidor->toJSON();
                    break;
                default:
                    break;
            }
            return $retorno;
        }

        public static function eliminar($unaId){
            $conexion = new Conexion("Competidor");
            $competidor = self::getById($unaId);
            if($competidor === null){
                throw new Exception("Competidor no existente");
            }
            $equipo = Equipo::getByNombre($competidor->getEquipo());
            if($equipo === null){
                throw new Exception("Equipo no existente");
            }
            $torneo = Torneo::getById($equipo->getIdTorneo());
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            Torneo::validarPosibilidadDeCambios($equipo->getIdTorneo());
            if($conexion->existe(self::$_atributoClave, $unaId)){
                //si es individual, borrar a su equipo
                $conexion->delete(self::$_atributoClave, $unaId, self::$_tipoAtributoClave);
                //self::resetAutoIncrement($conexion);

                if($torneo->getModalidad() == "individual"){
                    $equipo = Equipo::getByNombre($competidor->getEquipo());
                    $equipo->eliminar();
                }
            }else{
                throw new Exception('El Competidor no existe');
            }
        }

        //metodos publicos
        public function validarQueNoEsteEnOtroTorneo(){
            $competidor = Competidor::getByCi($this->_ci_competidor);
            if($competidor !== null){
                if($competidor->getIdTorneo() !== $this->getIdTorneo()){
                    $torneo = Torneo::getById($competidor->getIdTorneo());
                    if(!$torneo->yaTermino()){
                        throw new Exception("El competidor aún está participando en otro torneo");
                    }
                }
            }
        }

        public function guardar(){
            $this->validarQueNoEsteEnOtroTorneo();
            $torneo = Torneo::getById($this->_id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            Torneo::validarPosibilidadDeCambios($torneo->getId());
            self::validar($torneo);
            self::validarCapacidad($torneo);
            self::validarEquipo($torneo);
            if($torneo->getModalidad() === "individual"){
                self::registrarIndividual();
            }else{
                self::registrarEnEquipo();
            }
        }

        public function actualizar(){
            $torneo = Torneo::getById($this->_id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            self::validar($torneo);
            Torneo::validarPosibilidadDeCambios($torneo->getId());
            if($torneo->getModalidad() === "individual"){
                $atributos = $this->_atributos;
                array_pop($atributos);
                $this->_conexion->update(array_keys($atributos), [$this->_ci_competidor, $this->_sexo_competidor, $this->_fechaNacimiento_competidor, $this->_escuela_competidor, $this->_nombreCompleto_competidor], $this->_conexion->arrayToStringSinComa($atributos), self::$_atributoClave, $this->_id_competidor, self::$_tipoAtributoClave);
            }else{
                $equipo = Equipo::getByNombre($this->_nombre_equipo);
                if($equipo === null){
                    throw new Exception("Equipo no existente");
                }
                if(!$torneo->tieneEquipo($equipo->getNombre())){
                    throw new Exception("El equipo no participa en el torneo");
                }
                $this->_conexion->update(array_keys($this->_atributos), [$this->_ci_competidor, $this->_sexo_competidor, $this->_fechaNacimiento_competidor, $this->_escuela_competidor, $this->_nombreCompleto_competidor, $this->_nombre_equipo], $this->_conexion->arrayToStringSinComa($this->_atributos), self::$_atributoClave, $this->_id_competidor, self::$_tipoAtributoClave);
            }
            
        }

        public function toJSON(){
            $competidor = [
                'id_competidor' => $this->getId(),
                'ci_competidor' => $this->getCi(),
                'sexo_competidor' => $this->getSexo(),
                'fechaNacimiento_competidor' => $this->getFechaNacimiento(),
                'escuela_competidor' => $this->getEscuela(),
                'nombreCompleto_competidor' => $this->getNombre(),
                'nombre_equipo' => $this->getEquipo()
            ];
            return $competidor;
        }

        /*public static function resetAutoIncrement($conexion = new Conexion()){
            $valor = (self::ultimoIdCompetidor() + 1);
            $conexion->setConsulta($conexion->getConexion()->prepare(
                "ALTER TABLE competidor AUTO_INCREMENT = " . $valor
            ));
            
            try {
                $conexion->getConsulta()->execute();
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }  
        }
        */

        //metodos privados
        private function registrarIndividual(){
            if(!self::existe()){
                $this->_id_competidor = (self::ultimoIdCompetidor()+1);
            }
            $id_torneo = $this->_id_torneo;
            $id_grupo = 1;
            $equipo = new Equipo($this->_nombreCompleto_competidor . "_" . $this->_id_competidor, "aka", $this->_sexo_competidor, 1, $id_torneo, $id_grupo);
            $equipo->guardar();
            $this->setEquipo($equipo->getNombre());
            $this->insert();
        }

        private function registrarEnEquipo(){
            $this->insert();
        }

        private function insert(){
            if(!self::existe()){
                $this->_conexion->insert(array_keys($this->_atributos), [$this->_ci_competidor, $this->_sexo_competidor, $this->_fechaNacimiento_competidor, $this->_escuela_competidor, $this->_nombreCompleto_competidor, $this->_nombre_equipo], $this->_conexion->arrayToStringSinComa($this->_atributos));
            }else{
                if(self::participa()){
                    throw new Exception("El competidor ya participa en el torneo");
                }
                self::actualizarEquipo();
            }
        }

        private function participa(){
            $torneo = Torneo::getById($this->_id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            $equipos = $torneo->listarEquipos();
            foreach($equipos as $equipo){
                $competidores = $equipo->listarCompetidores();
                foreach($competidores as $competidor){
                    if($competidor->getCi() === self::formatearCi() || $competidor->getId() === $this->getId()){
                        return true;
                    }
                }
            }
            return false;
        }

        private function actualizarEquipo(){
            $this->validarQueNoEsteEnOtroTorneo();
            $this->_conexion->update(['nombre_equipo'], [$this->_nombre_equipo], 's', self::$_atributoClave, $this->_id_competidor, self::$_tipoAtributoClave);
        }

        private function existe(){
            $ci = self::formatearCi();
            return (($this->_conexion->existe(self::$_atributoClave, $this->_id_competidor)) || ($this->_conexion->existe('ci_competidor', $ci)));
        }

        private function formatearCi() {
            //convertimos el valor a un string para obtener su longitud
            $valor = (string)$this->_ci_competidor;
        
            //calculamos la cantidad de ceros a agregar
            $cerosAgregados = 8 - strlen($valor);
        
            // Concatena los ceros a la izquierda del valor original
            $valorFormateado = str_repeat('0', $cerosAgregados) . $valor;
        
            return $valorFormateado;
        }

        private function validar($unTorneo){
            if(!self::torneoValido($unTorneo)){
                throw new Exception('Torneo no válido');
            }
            if(!self::edadValida($this->_fechaNacimiento_competidor)){
                throw new Exception('Edad no válida');
            }
            if(!self::escuelaValida($this->_escuela_competidor)){
                throw new Exception('Escuela no válida');
            }
            if(!self::nombreValido($this->_nombreCompleto_competidor)){
                throw new Exception('Nombre no válido');
            }
            if(!self::sexoValido($this->_sexo_competidor, $unTorneo)){
                throw new Exception('Sexo no válido');
            }
        }

        private function validarCapacidad($torneo){
            if(!self::capacidadValida($torneo)){
                throw new Exception('El torneo alcanzó su límite de competidores');
            }
        }

        private function validarEquipo($torneo){
            if(!self::equipoValido($this->_nombre_equipo, $torneo)){
                throw new Exception('Equipo no válido');
            }
        }

        private function torneoValido($torneo){
            if($torneo === null){
                return false;
            }
            return $this->_conexion->torneoValido($torneo->getId());
        }

        private function capacidadValida($padre){
            return !$padre->estaLleno();
        }

        private function sexoValido($unSexo, $torneo){
            return $torneo->getSexo() == $unSexo;
        }

        private function edadValida($unaFecha){
            //eventualmente evaluar si la edad corresponde a la categoria
            return true;
        }

        private function escuelaValida($unaEscuela){
            //eventualmente evaluar existencia de escuela en afiliaciones de la CUK
            return !$this->_conexion->estaVacio($unaEscuela);
        }

        private function nombreValido($unNombre){
            //eventualmente evaluar que haya nombre y apellido
            return !$this->_conexion->estaVacio($unNombre);
        }

        private function equipoValido($nombre_equipo, $torneo){
            if (($torneo->getModalidad() === "equipo") && ($torneo->tieneEquipo($nombre_equipo))){
                $equipo = Equipo::getByNombre($nombre_equipo);
                if($equipo === null){
                    throw new Exception("Equipo no existente");
                }
                if(!self::capacidadValida($equipo)){
                    throw new Exception('El equipo alcanzó su límite de competidores');
                }
                return true;
            }else{
                return ($torneo->getModalidad() === "individual");
            }
        }
    }
?>
<?php
    require_once((dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/TecnicoSoftware.php');
    require_once(RUTA_RAIZ . 'modelos/Juez.php');
    require_once(RUTA_RAIZ . 'modelos/Equipo.php');
    require_once(RUTA_RAIZ . 'modelos/Grupo.php');
    require_once(RUTA_RAIZ . 'modelos/Enfrentamiento.php');

    class Torneo{
        //atributos
        private $_id_torneo;
        private $_cantidadInscriptos_torneo;
        private $_fecha_torneo;
        private $_nombre_torneo;
        private $_nombreUsuario_tecnicoSoftware;
        private $_modalidad_torneo;
        private $_sexo_torneo;
        private $_rangoEdad_torneo;
        private $_cantidadJueces_torneo;
        private $_equipos;
        private $_grupos;
        private $_enfrentamientos;
        private $_atributos;
        private $_atributoClave;
        private $_tipoAtributoClave;
        private $_conexion;

        //constructor
        public function __construct($cantidadInscriptos, $fecha, $nombre, $modalidad, $sexo, $rangoEdad, $tecnicoSoftware, $cantidadJueces){
            $this->_cantidadInscriptos_torneo = $cantidadInscriptos;
            $this->_fecha_torneo = $fecha;
            $this->_nombre_torneo = $nombre;
            $this->_nombreUsuario_tecnicoSoftware = $tecnicoSoftware;
            $this->_modalidad_torneo = $modalidad;
            $this->_sexo_torneo = $sexo;
            $this->_rangoEdad_torneo = $rangoEdad;
            $this->_cantidadJueces_torneo = $cantidadJueces;
            $this->_equipos = array();
            $this->_grupos = array();
            $this->_enfrentamientos = array();
            /*
            array asociativo que relaciona los atributos con los tipos de datos
            con array_keys($this->_atributos) se podrá acceder a los nombres de los atributos
            y con $this->_atributos se podrá acceder a los tipos de dato de dichos atributos
            LOS NOMBRES SON LOS QUE TIENEN EN LA BD
            */
            $this->_atributos = array(
                'cantidadInscriptos_torneo' => 'i',
                'fecha_torneo' => 's',
                'nombre_torneo' => 's',
                'modalidad_torneo' => 's',
                'sexo_torneo' => 's',
                'rangoEdad_torneo' => 's',
                'nombreUsuario_tecnicoSoftware' => 's',
                'cantidadJueces_torneo' => 's'
            );
            $this->_atributoClave='id_torneo';
            $this->_tipoAtributoClave='i';
            $this->_conexion = new Conexion('Torneo');
        }

        //getters
        public function getId(){
            return $this->_id_torneo;
        }
        public function getCantidadInscriptos(){
            return $this->_cantidadInscriptos_torneo;
        }
        public function getFecha(){
            return $this->_fecha_torneo;
        }
        public function getNombre(){
            return $this->_nombre_torneo;
        }
        public function getTecnicoSoftware(){
            return $this->_nombreUsuario_tecnicoSoftware;
        }
        public function getModalidad(){
            return $this->_modalidad_torneo;
        }
        public function getSexo(){
            return $this->_sexo_torneo;
        }
        public function getRangoEdad(){
            return $this->_rangoEdad_torneo;
        }
        public function getCantidadJueces(){
            return $this->_cantidadJueces_torneo;
        }

        //setters
        public function setId($id_torneo){
            $this->_id_torneo = $id_torneo;
        }
        public function setCantidadInscriptos($cantidad){
            $this->_cantidadInscriptos_torneo = $cantidad;
        }
        public function setFecha($fecha){
            $this->_fecha_torneo = $fecha;
        }
        public function setNombre($nombre){
            $this->_nombre_torneo = $nombre;
        }
        public function setTecnicoSoftware($tecnicoSoftware){
            $this->_nombreUsuario_tecnicoSoftware = $tecnicoSoftware;
        }
        public function setModalidad($modalidad){
            $this->_modalidad_torneo = $modalidad;
        }
        public function setSexo($sexo){
            $this->_sexo_torneo = $sexo;
        }
        public function setRangoEdad($rangoEdad){
            $this->_rangoEdad_torneo = $rangoEdad;
        }
        public function setCantidadJueces($cantidadJueces){
            $this->_cantidadJueces_torneo = $cantidadJueces;
        }

        //metodos estaticos
        public static function validarPosibilidadDeCambios($id_torneo){
            $torneo = self::getById($id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            //si ya se generaron enfrentamientos
            if(count($torneo->listarEnfrentamientos()) > 0){
                throw new Exception("No se puede realizar cambios, debido a que el torneo ya inició");
            }
        }

        public static function getBasicPropertiesById($unaId){
            $conexion = new Conexion('Torneo');
            $filas = $conexion->selectWhere(['id_torneo'], [$unaId], 'i', '');
            if (count($filas) > 0){
                $cantidadInscriptos = $filas[0]['cantidadInscriptos_torneo'];
                $fecha = $filas[0]['fecha_torneo'];
                $nombre = $filas[0]['nombre_torneo'];
                $modalidad = $filas[0]['modalidad_torneo'];
                $sexo = $filas[0]['sexo_torneo'];
                $rangoEdad = $filas[0]['rangoEdad_torneo'];
                $tecnicoSoftware = $filas[0]['nombreUsuario_tecnicoSoftware'];
                $cantidadJueces = $filas[0]['cantidadJueces_torneo'];
                $torneo = new Torneo($cantidadInscriptos, $fecha, $nombre, $modalidad, $sexo, $rangoEdad, $tecnicoSoftware, $cantidadJueces);
                $torneo->_id_torneo = $filas[0]['id_torneo'];
                return $torneo;
            }else{
                return null;
            }
        }

        public static function getById($unaId){
            $torneo = null;
            $conexion = new Conexion('Torneo');
            $filas = $conexion->selectWhere(['id_torneo'], [$unaId], 'i', '');
            if (count($filas) > 0){
                $cantidadInscriptos = $filas[0]['cantidadInscriptos_torneo'];
                $fecha = $filas[0]['fecha_torneo'];
                $nombre = $filas[0]['nombre_torneo'];
                $modalidad = $filas[0]['modalidad_torneo'];
                $sexo = $filas[0]['sexo_torneo'];
                $rangoEdad = $filas[0]['rangoEdad_torneo'];
                $tecnicoSoftware = $filas[0]['nombreUsuario_tecnicoSoftware'];
                $cantidadJueces = $filas[0]['cantidadJueces_torneo'];
                $torneo = new Torneo($cantidadInscriptos, $fecha, $nombre, $modalidad, $sexo, $rangoEdad, $tecnicoSoftware, $cantidadJueces);
                $torneo->_id_torneo = $filas[0]['id_torneo'];
                $torneo->_grupos = Grupo::getByTorneo($torneo->_id_torneo);
                $torneo->_enfrentamientos = Enfrentamiento::getByTorneo($torneo->_id_torneo);
                foreach($torneo->_grupos as $grupo){
                    foreach($grupo->listarEquipos() as $equipo){
                        $torneo->_equipos[] = $equipo;
                    }
                }   
            }
            return $torneo;
        }

        public static function listarPendientesDePuntuar($id_torneo){
            $torneo = Torneo::getById($id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            return $torneo->pendientesDePuntuar();
        }

        public static function actualizarTorneo($id_torneo, $cantidadInscriptos, $fecha, $nombre, $modalidad, $sexo, $rangoEdad, $tecnicoSoftware, $cantidadJueces){
            $torneo = Torneo::getById($id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            $torneo->setCantidadInscriptos($cantidadInscriptos);
            $torneo->setFecha($fecha);
            $torneo->setNombre($nombre);
            $torneo->setModalidad($modalidad);
            $torneo->setSexo($sexo);
            $torneo->setRangoEdad($rangoEdad);
            $torneo->setTecnicoSoftware($tecnicoSoftware);
            $torneo->setCantidadJueces($cantidadJueces);
            $torneo->actualizar();
        }

        public static function eliminarTorneo($id_torneo){
            $torneo = Torneo::getById($id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            $torneo->eliminar();
        }

        public static function getAll(){
            $retorno = array();
            $conexion = new Conexion("Torneo");
            $filas = $conexion->select();
            foreach($filas as $fila){
                $torneo = self::getById($fila["id_torneo"]);
                $retorno[] = $torneo;
            }
            return $retorno;
        }

        public static function listar($id_torneo){
            $torneo = self::getById($id_torneo);
            //si el torneo es null, entonces le devolvemos null. Si no, le devolvemos el toJSON del torneo
            $retorno = $torneo === null ? null : $torneo->toJSON_propiedadesBasicas();
            return $retorno;
        }

        public static function listarTodos($tecnicoSoftware){
            $retorno = array();
            $torneos = self::getAll();
            foreach($torneos as $torneo){
                //si el tecnico de software de ese torneo es el tecnico de software que esta pidiendo los datos
                if($torneo->getTecnicoSoftware() == $tecnicoSoftware){
                    //agrego el torneo a la lista
                    $retorno[] = $torneo->toJSON_propiedadesBasicas();
                }
            }
            return $retorno;
        }

        //metodos publicos
        public function guardar(){
            self::validar();
            $this->_conexion->insert(array_keys($this->_atributos), [$this->_cantidadInscriptos_torneo, $this->_fecha_torneo, $this->_nombre_torneo, $this->_modalidad_torneo, $this->_sexo_torneo, $this->_rangoEdad_torneo, $this->_nombreUsuario_tecnicoSoftware, $this->_cantidadJueces_torneo], $this->_conexion->arrayToStringSinComa($this->_atributos));
            $this->_id_torneo = (int)($this->_conexion->ultimaIdTorneo());
        }

        public function actualizar(){
            Torneo::validarPosibilidadDeCambios($this->getId());
            self::validar();
            $this->_conexion->update(array_keys($this->_atributos), [$this->_cantidadInscriptos_torneo, $this->_fecha_torneo, $this->_nombre_torneo, $this->_modalidad_torneo, $this->_sexo_torneo, $this->_rangoEdad_torneo, $this->_nombreUsuario_tecnicoSoftware, $this->_cantidadJueces_torneo], $this->_conexion->arrayToStringSinComa($this->_atributos), $this->_atributoClave, $this->_id_torneo, $this->_tipoAtributoClave);
        }

        public function eliminar(){
            if($this->_conexion->existe($this->_atributoClave, $this->_id_torneo)){
                self::validarEliminacion();
                Grupo::eliminarDeTorneo($this->_id_torneo);
                Juez::eliminarDeTorneo($this->_id_torneo);
                $this->_conexion->delete($this->_atributoClave, $this->_id_torneo, $this->_tipoAtributoClave);
            }else{
                throw new Exception("Torneo no existente");
            }
        }

        public function pendientesDePuntuar(){
            $pendientes = [];
            $jueces = Juez::getByTorneo($this->_id_torneo);
            foreach($jueces as $juez){
                foreach($juez->pendientesDePuntuar() as $pendiente){
                    $pendientes[] = $pendiente;
                }
            }
            return $pendientes;
        }

        public function pendientesDeRealizar(){
            $pendientes = [];
            foreach($this->listarEnfrentamientosRonda($this->rondaActual()) as $enfrentamiento){
                $id_enfrentamiento = $enfrentamiento->getId();
                $equipos = Equipo::getByEnfrentamiento($id_enfrentamiento);
                foreach($equipos as $equipo){
                    if(!$equipo->realizoUnKata($id_enfrentamiento)){
                        $pendientes[] = $equipo;
                    }
                }
            }
            return $pendientes;
        }

        public function estaLleno(){
            $cantidadCompetidores = 0;
            foreach($this->_equipos as $equipo){
                $competidoresEquipo = $equipo->listarCompetidores();
                $cantidadCompetidores += count($competidoresEquipo);
            }
            return $cantidadCompetidores >= $this->_cantidadInscriptos_torneo;
        }

        public function estaLlenoJueces(){
            $jueces = Juez::getByTorneo($this->_id_torneo);
            $cantidadJueces = count($jueces);
            return $cantidadJueces >= $this->_cantidadJueces_torneo;
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

        public function tieneEquipo($unNombre){
            foreach($this->listarEquipos() as $equipo){
                if($equipo->getNombre() == $unNombre){
                    return true;
                }
            }
            return false;
        }

        public function generarGrupos(){
            if($this->_modalidad_torneo == 'individual'){
                $cantidadEquiposTorneo = $this->_cantidadInscriptos_torneo;
            }else{
                $cantidadEquiposTorneo = ceil($this->_cantidadInscriptos_torneo / 3);
            }
            switch($cantidadEquiposTorneo){
                case 2:
                    $cantidadGrupos = 1;
                    //katas = 1
                    break;
                case 3:
                    $cantidadGrupos = 1;
                    //katas = 1
                    break;
                case 4:
                    $cantidadGrupos = 2;
                    //katas = 2
                    break;
                case ($cantidadEquiposTorneo >= 5 && $cantidadEquiposTorneo <= 10):
                    $cantidadGrupos = 2;
                    //katas = 2
                    break;
                case ($cantidadEquiposTorneo >= 11 && $cantidadEquiposTorneo <= 24):
                    $cantidadGrupos = 2;
                    //katas = 3
                    break;
                case ($cantidadEquiposTorneo >= 25 && $cantidadEquiposTorneo <= 48):
                    $cantidadGrupos = 4;
                    //katas = 4
                    break;
                case ($cantidadEquiposTorneo >= 49 && $cantidadEquiposTorneo <= 96):
                    $cantidadGrupos = 8;
                    //katas = 4
                    break;
                default:
                    $cantidadGrupos = 0;
                    break;
            }
            if($cantidadGrupos === 0){
                throw new Exception("No existe una cantidad de equipos adecuada para este torneo");
            }
            $redondeo = ['ceil', 'floor'];

            //asigna los nuevos grupos
            for($i = 0; $i < $cantidadGrupos; $i++){
                $funcionRedondeo = $redondeo[($i % 2)];
                $cantidadEquiposGrupo = $funcionRedondeo(($cantidadEquiposTorneo / $cantidadGrupos));
                $grupo = new Grupo($this->_id_torneo, $cantidadEquiposGrupo);
                $grupo->guardar();  
            }
        }

        public function barajarGrupos(){
            self::validarEnfrentamientos();
            $equiposDisponibles = [];

            //guardamos todos los equipos de todos los grupos en un array
            foreach($this->_grupos as $grupo){
                $equiposDisponibles = array_merge($equiposDisponibles, $grupo->listarEquipos());
            }
            //los mezclamos
            shuffle($equiposDisponibles);

            foreach($this->_grupos as $grupo){
                $cantidadEquiposFinal = $grupo->getCantidadEquipos();
                $cantidadEquiposActual = 0;
                //mientras no se supere la cantidad máxima de equipos del grupo
                //y queden equiposDisponibles
                while(($cantidadEquiposActual < $cantidadEquiposFinal) && !empty($equiposDisponibles)){
                    $equipo = array_pop($equiposDisponibles);
                    $equipo->setIdGrupo($grupo->getIdGrupo());
                    $equipo->actualizar();
                    $cantidadEquiposActual++;
                }
            }
        }

        
        public function listarGrupos(){
            return $this->_grupos;
        }

        public function listarGruposJSON(){
            $grupos = $this->listarGrupos();
            $losGrupos = array();
            foreach($grupos as $grupo){
                $losGrupos[]=$grupo->toJSON();
            }
            return $losGrupos;
        }

        public function tieneGrupo($unaId){
            foreach($this->_grupos as $grupo){
                if($grupo->id_grupo == $unaId){
                    return true;
                }
            }
            return false;
        }

        public function agregarJueces($jueces){
            foreach($jueces as $juez){
                $nuevoJuez = new Juez($this->_id_torneo, $juez->numero, $juez->clave, $juez->nombreCompleto);
                $nuevoJuez->guardar();
            }
        }

        public function registrarEnfrentamientos(){
            if($this->rondaActual() === null){
                $this->sortearLlaves();
            }else{
                $this->generarEnfrentamientos();
            }
        }

        //se ejecuta una vez
        //luego de generar los grupos
        public function sortearLlaves(){
            try{
                self::validarEnfrentamientos();
                self::validarEquiposEnGrupos();
                foreach($this->_grupos as $grupo){
                    $equiposGrupo = [];
                    $equiposGrupo = $grupo->listarEquipos();
                    $cantidadEquiposGrupo = count($equiposGrupo);
                    shuffle($equiposGrupo);
                    
                    $cantidadEquiposGrupoDeUnEnfrentamiento = [2, 3];
                    $cantidadEnfrentamientosGrupo = (in_array($cantidadEquiposGrupo, $cantidadEquiposGrupoDeUnEnfrentamiento)) ? 1 : 2;
                    
                    for($i = 0; $i < $cantidadEnfrentamientosGrupo; $i++){
                        $this->_conexion->setTabla('Enfrentamiento');
                        $enfrentamiento = new Enfrentamiento(date('Y-m-d H:i:s'), 1);
                        $enfrentamiento->guardar();
                        
                        $cantidadEquiposEnfrentamiento = ($i % 2 === 0 ) ? ceil($cantidadEquiposGrupo/$cantidadEnfrentamientosGrupo) : floor($cantidadEquiposGrupo/$cantidadEnfrentamientosGrupo);
                        $cinturon = ($i % 2 === 0) ? 'aka' : 'ao';

                        for($k = 0; $k < $cantidadEquiposEnfrentamiento; $k++){
    
                            //obtenemos y quitamos el ultimo equipo del array
                            $equipo = array_pop($equiposGrupo);
                            
                            $equipo->setCinturon($cinturon);
                            $equipo->actualizar();
                            
                            $this->_conexion->setTabla('Tiene');
                            $this->_conexion->insert(['id_enfrentamiento', 'nombre_equipo'], [$enfrentamiento->getId(), $equipo->getNombre()], 'is');
                        }
                    }
                }
            }catch(Exception $e){
                throw new Exception($e->getMessage());
            }
            $this->_conexion->setTabla('Torneo');
        }

        public function rondaActual(){
            $this->_conexion->setConsulta($this->_conexion->getConexion()->prepare(
                "SELECT MAX(enfrentamiento.ronda_enfrentamiento)'ronda_actual'
                FROM tiene JOIN equipo
                ON tiene.nombre_equipo = equipo.nombre_equipo
                JOIN enfrentamiento
                ON tiene.id_enfrentamiento = enfrentamiento.id_enfrentamiento
                WHERE equipo.id_torneo = ?;"
            ));
            try {
                $this->_conexion->getConsulta()->bind_param('i', $this->_id_torneo);
                $this->_conexion->getConsulta()->execute();
                $resultado = $this->_conexion->getConsulta()->get_result();
                if (!$resultado) {
                    throw new Exception('Error en la consulta: ' . $this->_conexion->getConsulta()->error);
                }
                $filas = $this->_conexion->obtenerFilas($resultado);
                return $filas[0]['ronda_actual'];
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
        }

        public function listarEnfrentamientosRonda($ronda){
            $retorno = array();
            foreach($this->_enfrentamientos as $enfrentamiento){
                if($enfrentamiento->getRonda() == $ronda){
                    $retorno[]=$enfrentamiento;
                }
            }
            return $retorno;
        }

        public function listarEnfrentamientosRondaJSON($ronda){
            $enfrentamientos = $this->listarEnfrentamientosRonda($ronda);
            $losEnfrentamientos = array();
            foreach($enfrentamientos as $enfrentamiento){
                $losEnfrentamientos[]=$enfrentamiento->toJSON();
            }
            return $losEnfrentamientos;
        }

        //devuelve los clasificados o, si no hay siguiente ronda, las posiciones actuales
        public function listarClasificados(){
            $retorno = [];
            if(count($this->listarEnfrentamientosRonda($this->rondaActual())) === 1){
                $enfrentamientos = $this->sumarPuntajes();
            }else{
                $enfrentamientos = $this->obtenerClasificados();
            }
            foreach($enfrentamientos as $enfrentamiento){
                $retorno[]=$enfrentamiento->toJSON();
            }
            return $retorno;
        }

        public function obtenerClasificados(){
            $this->validarRealiza();
            $this->validarPuntua();
            $retorno = [];
            $enfrentamientos = $this->sumarPuntajes();
            foreach($enfrentamientos as $enfrentamiento){
                $retorno[]=$enfrentamiento->clasificados();
            }
            return $retorno;
        }

        public function sumarPuntajes(){
            $this->validarRealiza();
            $this->validarPuntua();
            $ronda = $this->rondaActual();
            $this->_conexion->setConsulta($this->_conexion->getConexion()->prepare(
                "SELECT puntua.nombre_equipo, puntua.id_enfrentamiento, SUM(puntua.puntaje)'total', MAX(puntua.puntaje)'maximo', MIN(puntua.puntaje)'minimo', enfrentamiento.ronda_enfrentamiento
                FROM puntua JOIN enfrentamiento
                ON puntua.id_enfrentamiento=enfrentamiento.id_enfrentamiento
                JOIN equipo
                ON puntua.nombre_equipo = equipo.nombre_equipo
                WHERE equipo.id_torneo = ?
                GROUP BY puntua.nombre_equipo
                HAVING enfrentamiento.ronda_enfrentamiento = ?
                ORDER BY puntua.id_enfrentamiento ASC, SUM(puntua.puntaje) DESC;"
            ));
            try {
                $this->_conexion->getConsulta()->bind_param('ii', $this->_id_torneo, $ronda);
                $this->_conexion->getConsulta()->execute();
                $resultado = $this->_conexion->getConsulta()->get_result();
                if (!$resultado) {
                    throw new Exception('Error en la consulta: ' . $this->_conexion->getConsulta()->error);
                }
                $filas = $this->_conexion->obtenerFilas($resultado);
                return $this->relacionarPuntajes($filas);
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
        }

        public function relacionarPuntajes($filas){
            $this->validarRealiza();
            $this->validarPuntua();
            $enfrentamientos = $this->listarEnfrentamientosRonda($this->rondaActual());
            foreach($enfrentamientos as $enfrentamiento){
                foreach($filas as $fila){
                    if($fila['id_enfrentamiento'] === $enfrentamiento->getId()){
                        //$equipo = Equipo::getByNombre($fila['nombre_equipo']);
                        $index = $enfrentamiento->indexEquipo($fila['nombre_equipo']);
                        $equipo = $enfrentamiento->listarEquipos()[$index];
                        $puntajeTotal = $fila['total'];
                        $puntajeMaximo = $fila['maximo'];
                        $puntajeMinimo = $fila['minimo'];
                        //number_format para que el resultado quede con 1 solo decimal
                        $puntajeTotal = number_format(($puntajeTotal - $puntajeMaximo - $puntajeMinimo), 1);
                        $equipo->setPuntaje($puntajeTotal);
                    }
                }
                $this->ordenarEquipos($enfrentamiento->listarEquipos());
            }
            return $enfrentamientos;
        }

        //devuelve equipos ordenados segun sus puntajes en el enfrentamiento
        public function ordenarEquipos($equipos){
            for($i = 0; $i < count($equipos); $i++){
                $equipos[$i]->setPosicion($i);
            }
            for($i = 0; $i < (count($equipos)-1); $i++){
                for($j = ($i+1); $j < count($equipos); $j++){
                    if($equipos[$j]->getPuntaje() > $equipos[$i]->getPuntaje()){
                        //cambiamos de lugar a los equipos
                        $aux = $equipos[$j];
                        $equipos[$j] = $equipos[$i];
                        $equipos[$i] = $aux;
                    }
                }
            }
            $this->asignarPosiciones($equipos);
        }

        public function asignarPosiciones($equipos){
            $cantidadEquipos = count($equipos);
            for($i = 0; $i < $cantidadEquipos; $i++){
                $equipos[$i]->setPosicion(($i+1));
            }
        }

        public function ordenarPorPosicion($enfrentamientos){
            foreach ($enfrentamientos as &$equipos) {
                $cantidadEquipos = count($equipos);
                for ($i = 0; $i < $cantidadEquipos - 1; $i++) {
                    for ($j = $i + 1; $j < $cantidadEquipos; $j++) {
                        if ($equipos[$j]->getPosicion() < $equipos[$i]->getPosicion()) {
                            //cambiamos de lugar a los equipos
                            $aux = $equipos[$j];
                            $equipos[$j] = $equipos[$i];
                            $equipos[$i] = $aux;
                        }
                    }
                }
            }
            return $enfrentamientos;
        }

        //suma los puntajes y genera los nuevos enfrentamientos
        public function generarEnfrentamientos() {
            $this->validarRealiza();
            $this->validarPuntua();
            
            $nuevosEnfrentamientos = [];
            $enfrentamientos = $this->obtenerClasificados();
            $enfrentamientos = $this->ordenarPorPosicion($enfrentamientos);

            $equiposEmparejados = [];
            
            foreach ($enfrentamientos as $index => $enfrentamiento) {
                //verificamos que haya un siguiente enfrentamiento
                if (isset($enfrentamientos[$index + 1])) {
                    $enfrentamientoSiguiente = $enfrentamientos[$index + 1];
        
                    //obtenemos equipos de posición 1
                    $equipoPosicion1 = $enfrentamiento[0];
                    $equipoPosicion1Siguiente = $enfrentamientoSiguiente[0];
        
                    //validamos que los equipos de posición 1 no hayan sido emparejados previamente
                    if (!in_array($equipoPosicion1, $equiposEmparejados) && !in_array($equipoPosicion1Siguiente, $equiposEmparejados)) {
                        $nuevosEnfrentamientos[] = [$equipoPosicion1, $equipoPosicion1Siguiente];
                        //registramos los equipos emparejados
                        $equiposEmparejados[] = $equipoPosicion1;
                        $equiposEmparejados[] = $equipoPosicion1Siguiente;
                    }
        
                    //obtener equipos de posición 2 y 3
                    $equipoPosicion2 = $enfrentamiento[1];
                    $equipoPosicion3Siguiente = $enfrentamientoSiguiente[2];
        
                    //validamos que los equipos de posición 2 y 3 no hayan sido emparejados previamente
                    if (!in_array($equipoPosicion2, $equiposEmparejados) && !in_array($equipoPosicion3Siguiente, $equiposEmparejados)) {
                        $nuevosEnfrentamientos[] = [$equipoPosicion2, $equipoPosicion3Siguiente];
                        //registramos los equipos emparejados
                        $equiposEmparejados[] = $equipoPosicion2;
                        $equiposEmparejados[] = $equipoPosicion3Siguiente;
                    }

                    //obtener equipos de posición 3 y 2
                    $equipoPosicion3 = $enfrentamiento[2];
                    $equipoPosicion2Siguiente = $enfrentamientoSiguiente[1];
        
                    //validamos que los equipos de posición 2 y 3 no hayan sido emparejados previamente
                    if (!in_array($equipoPosicion3, $equiposEmparejados) && !in_array($equipoPosicion2Siguiente, $equiposEmparejados)) {
                        $nuevosEnfrentamientos[] = [$equipoPosicion3, $equipoPosicion2Siguiente];
                        //registramos los equipos emparejados
                        $equiposEmparejados[] = $equipoPosicion3;
                        $equiposEmparejados[] = $equipoPosicion2Siguiente;
                    }
                }
            }
            $ronda = $this->rondaActual() + 1;
            foreach($nuevosEnfrentamientos as $nuevoEnfrentamiento){
                $this->_conexion->setTabla('Enfrentamiento');
                $enfrentamiento = new Enfrentamiento(date('Y-m-d H:i:s'), $ronda);
                $enfrentamiento->guardar();
                $this->_conexion->setTabla('Tiene');
                foreach($nuevoEnfrentamiento as $equipo){
                    $this->_conexion->insert(['id_enfrentamiento', 'nombre_equipo'], [$enfrentamiento->getId(), $equipo->getNombre()], 'is');
                }
            }
        }

        public function listarEnfrentamientos(){
            return $this->_enfrentamientos;
        }

        public function listarEnfrentamientosJSON(){
            $enfrentamientos = $this->listarEnfrentamientos();
            $losEnfrentamientos = array();
            foreach($enfrentamientos as $enfrentamiento){
                $losEnfrentamientos[]=$enfrentamiento->toJSON();
            }
            return $losEnfrentamientos;
        }

        public function toJSON(){
            $torneo = [
                'id_torneo' => $this->getId(),
                'cantidadInscriptos_torneo' => $this->getCantidadInscriptos(),
                'fecha_torneo' => $this->getFecha(),
                'nombre_torneo' => $this->getNombre(),
                'nombreUsuario_tecnicoSoftware' => $this->getTecnicoSoftware(),
                'modalidad_torneo' => $this->getModalidad(),
                'sexo_torneo' => $this->getSexo(),
                'rangoEdad_torneo' => $this->getRangoEdad(),
                'cantidadJueces_torneo' => $this->getCantidadJueces(),
                'estado_torneo' => self::evaluarEstado(),
                'equipos' => $this->listarEquiposJSON(),
                'grupos' => $this->listarGruposJSON(),
                'enfrentamientos' => $this->listarEnfrentamientosJSON(),
                'ronda_actual' => $this->rondaActual()
            ];
            return $torneo;
        }

        public function toJSON_propiedadesBasicas(){
            $torneo = [
                'id_torneo' => $this->getId(),
                'cantidadInscriptos_torneo' => $this->getCantidadInscriptos(),
                'fecha_torneo' => $this->getFecha(),
                'nombre_torneo' => $this->getNombre(),
                'nombreUsuario_tecnicoSoftware' => $this->getTecnicoSoftware(),
                'modalidad_torneo' => $this->getModalidad(),
                'sexo_torneo' => $this->getSexo(),
                'rangoEdad_torneo' => $this->getRangoEdad(),
                'cantidadJueces_torneo' => $this->getCantidadJueces(),
                'estado_torneo' => self::evaluarEstado(),
                'ronda_actual' => $this->rondaActual()
            ];
            return $torneo;
        }

        //si su estado no es 'en curso', ya termino
        public function yaTermino(){
            return self::evaluarEstado() !== "en curso";
        }

        //valida que todos los equipos hayan realizado un kata
        public function validarRealiza(){
            if(count($this->pendientesDeRealizar()) > 0){
                throw new Exception("Aún hay equipos que no realizaron un kata");
            }
        }

        //valida que todos los equipos hayan sido puntuados por todos los jueces
        public function validarPuntua(){
            if(count($this->pendientesDePuntuar()) > 0){
                throw new Exception("Aún hay equipos que no fueron puntuados");
            }
        }

        //metodos privados
        private function evaluarEstado(){
            $retorno = "en curso";
            //si en esta ronda, solo hay un enfrentamiento y todos los equipos realizaron kata y fueron puntuados
            if((count($this->listarEnfrentamientosRonda($this->rondaActual())) === 1) && (count($this->pendientesDeRealizar()) === 0) && (count($this->pendientesDePuntuar()) === 0)){
                //termino el torneo
                $retorno = "finalizado";
            }
            return $retorno;
        }

        private function validarEliminacion(){
            Torneo::validarPosibilidadDeCambios($this->_id_torneo);
            if(count($this->listarEquipos()) > 0){
                throw new Exception("No se puede realizar cambios, debido a que ya se registraron equipos en el torneo");
            }
        }

        private function validarEnfrentamientos(){
            if(count($this->_enfrentamientos) > 0){
                throw new Exception("Ya se generaron enfrentamientos para este Torneo");
            }
        }

        private function validarEquiposEnGrupos(){
            foreach($this->_grupos as $grupo){
                $equiposGrupo = [];
                $equiposGrupo = $grupo->listarEquipos();
                $cantidadEquiposGrupo = count($equiposGrupo);
                if($cantidadEquiposGrupo === 0){
                    throw new Exception('El grupo ' . $grupo->getIdGrupo() . ' no tiene equipos');
                }
            }
        }

        private function validar(){
            if(!self::cantidadInscriptosValida($this->_cantidadInscriptos_torneo, $this->_modalidad_torneo)){
                throw new Exception('Cantidad de inscriptos no válida');  
            }
            if(!self::modalidadValida($this->_modalidad_torneo)){
                throw new Exception('Modalidad no válida');   
            }
            if(!$this->_conexion->sexoValido($this->_sexo_torneo)){
                throw new Exception('Sexo no válido');   
            }
            if(!self::rangoEdadValido($this->_rangoEdad_torneo, $this->_modalidad_torneo)){
                throw new Exception('Rango de edad no válido');   
            }
            if(!self::tecnicoSoftwareValido($this->_nombreUsuario_tecnicoSoftware)){
                throw new Exception('Técnico de software no existente en el sistema');
            }
            if(!self::cantidadJuecesValida($this->_cantidadJueces_torneo)){
                throw new Exception("Cantidad de jueces no válida");
            }
        }

        private function cantidadJuecesValida($unaCantidad){
            $cantidadesPosibles = ["5", "7"];
            return in_array($unaCantidad, $cantidadesPosibles);
        }

        private function cantidadInscriptosValida($unaCantidad, $unaModalidad){
            if(($unaModalidad == 'equipo') && ($unaCantidad % 3 != 0)){
                return false;
            }else{
                return true;
            }
        }

        private function modalidadValida($unaModalidad){
            $modalidadesValidas = array('individual', 'equipo');
            return in_array($unaModalidad, $modalidadesValidas);
        }

        private function rangoEdadValido($unRangoEdad, $unaModalidad){
            if(self::modalidadValida($unaModalidad)){
                if($unaModalidad=='equipo'){
                    $rangosValidos = array(null);
                }else{
                    $rangosValidos = array('12/13', '14/15', '16/17', 'mayores');
                }
            }else{
                throw new Exception('Modalidad no válida');
            }
            return in_array($unRangoEdad, $rangosValidos);
        }

        private function tecnicoSoftwareValido($unTS){
            $ts = new TecnicoSoftware($unTS, '');
            return $ts->existe();
        }
    }
?>
<?php
    require_once((dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/Competidor.php');
    require_once(RUTA_RAIZ . 'modelos/Kata.php');
    require_once(RUTA_RAIZ . 'modelos/Torneo.php');
    require_once(RUTA_RAIZ . 'modelos/Enfrentamiento.php');

    class Equipo{
        //atributos
        private $_nombre_equipo;
        private $_cinturon_equipo;
        private $_sexo_equipo;
        private $_cantidadCompetidores_equipo;
        private $_id_torneo;
        private $_id_grupo;
        private $_competidores;
        private $_puntaje;
        private $_posicion;
        private $_id_enfrentamiento;
        private $_atributos;
        private $_atributoClave;
        private $_tipoAtributoClave;
        private $_conexion;

        //constructor
        public function __construct($nombre, $cinturon, $sexo, $cantidad, $id_torneo, $id_grupo){
            $this->_nombre_equipo = $nombre;
            $this->_cinturon_equipo = $cinturon;
            $this->_sexo_equipo = $sexo;
            $this->_cantidadCompetidores_equipo = $cantidad;
            $this->_id_torneo = $id_torneo;
            $this->_id_grupo = $id_grupo;
            $this->_competidores = array();
            $this->_puntaje = null;
            $this->_posicion = null;
            $this->_id_enfrentamiento = null;
            /*
            array asociativo que relaciona los atributos con los tipos de datos
            con array_keys($this->_atributos) se podrá acceder a los nombres de los atributos
            y con $this->_atributos se podrá acceder a los tipos de dato de dichos atributos
            LOS NOMBRES SON LOS QUE TIENEN EN LA BD
            */
            $this->_atributos = array(
                'nombre_equipo' => 's',
                'cinturon_equipo' => 's',
                'sexo_equipo' => 's',
                'cantidadCompetidores_equipo' => 'i',
                'id_torneo' => 'i',
                'id_grupo' => 'i'
            );
            $this->_atributoClave='nombre_equipo';
            $this->_tipoAtributoClave='s';
            $this->_conexion = new Conexion('Equipo');
        }

        //getters
        public function getNombre(){
            return $this->_nombre_equipo;
        }
        public function getCinturon(){
            return $this->_cinturon_equipo;
        }
        public function getSexo(){
            return $this->_sexo_equipo;
        }
        public function getCantidadCompetidores(){
            return $this->_cantidadCompetidores_equipo;
        }
        public function getIdTorneo(){
            return $this->_id_torneo;
        }
        public function getIdGrupo(){
            return $this->_id_grupo;
        }
        public function getPuntaje(){
            return $this->_puntaje;
        }
        public function getPosicion(){
            return $this->_posicion;
        }
        public function getIdEnfrentamiento(){
            return $this->_id_enfrentamiento;
        }

        //setters
        public function setIdGrupo($unaIdGrupo){
            $this->_id_grupo = $unaIdGrupo;
        }
        public function setCinturon($unCinturon){
            $this->_cinturon_equipo = $unCinturon;
        }
        public function setPuntaje($unPuntaje){
            $this->_puntaje = $unPuntaje;
        }
        public function setPosicion($unaPosicion){
            $this->_posicion = $unaPosicion;
        }
        public function setIdEnfrentamiento($unaIdEnfrentamiento){
            $this->_id_enfrentamiento = $unaIdEnfrentamiento;
        }

        //metodos estaticos
        public static function getByTorneo($unTorneo){
            $conexion = new Conexion('Equipo');
            $listaRetorno = array();
            $filas = $conexion->selectWhere(['id_torneo'], [$unTorneo], 'i');
            for($i = 0; $i < count($filas); $i++){
                $nombre = $filas[$i]['nombre_equipo'];
                $cinturon = $filas[$i]['cinturon_equipo'];
                $sexo = $filas[$i]['sexo_equipo'];
                $cantidadCompetidores = $filas[$i]['cantidadCompetidores_equipo'];
                $id_torneo = $filas[$i]['id_torneo'];
                $id_grupo = $filas[$i]['id_grupo'];
                $equipo = new Equipo($nombre, $cinturon, $sexo, $cantidadCompetidores, $id_torneo, $id_grupo);
                $equipo->_competidores = Competidor::getByEquipo($equipo->_nombre_equipo);
                $equipo->_id_enfrentamiento = Enfrentamiento::getIdByEquipo($equipo->getNombre());
                $listaRetorno[$i] = $equipo;
            }
            return $listaRetorno;
        }

        public static function getByGrupo($unTorneo, $unGrupo){
            $conexion = new Conexion('Equipo');
            $listaRetorno = array();
            $filas = $conexion->selectWhere(['id_torneo', 'id_grupo'], [$unTorneo, $unGrupo], 'ii', '&&');
            for($i = 0; $i < count($filas); $i++){
                $nombre = $filas[$i]['nombre_equipo'];
                $cinturon = $filas[$i]['cinturon_equipo'];
                $sexo = $filas[$i]['sexo_equipo'];
                $cantidadCompetidores = $filas[$i]['cantidadCompetidores_equipo'];
                $id_torneo = $filas[$i]['id_torneo'];
                $id_grupo = $filas[$i]['id_grupo'];
                $equipo = new Equipo($nombre, $cinturon, $sexo, $cantidadCompetidores, $id_torneo, $id_grupo);
                $equipo->_competidores = Competidor::getByEquipo($equipo->_nombre_equipo);
                $equipo->_id_enfrentamiento = Enfrentamiento::getIdByEquipo($equipo->getNombre());
                $listaRetorno[$i] = $equipo;
            }
            return $listaRetorno;
        }

        public static function getByNombre($unNombre){
            $equipo = null;
            $conexion = new Conexion('Equipo');
            $filas = $conexion->selectWhere(['nombre_equipo'], [$unNombre], 's', '');
            if (count($filas) > 0){
                $nombre = $filas[0]['nombre_equipo'];
                $cinturon = $filas[0]['cinturon_equipo'];
                $sexo = $filas[0]['sexo_equipo'];
                $cantidadCompetidores = $filas[0]['cantidadCompetidores_equipo'];
                $id_torneo = $filas[0]['id_torneo'];
                $id_grupo = $filas[0]['id_grupo'];
                $equipo = new Equipo($nombre, $cinturon, $sexo, $cantidadCompetidores, $id_torneo, $id_grupo);
                $equipo->_competidores = Competidor::getByEquipo($equipo->_nombre_equipo);
                $equipo->_id_enfrentamiento = Enfrentamiento::getIdByEquipo($equipo->getNombre());
            }
            return $equipo;
        }

        public static function getByEnfrentamiento($unEnfrentamiento){
            $conexion = new Conexion('Tiene');
            $listaRetorno = array();
            $filas = $conexion->selectWhere(['id_enfrentamiento'], [$unEnfrentamiento], 'i', '');
            $cantidadEquipos = count($filas);
            if ($cantidadEquipos > 0){
                for($i = 0; $i < $cantidadEquipos; $i++){
                    $nombreEquipo = $filas[$i]['nombre_equipo'];
                    $equipo = self::getByNombre($nombreEquipo);
                    $listaRetorno[$i] = $equipo;
                }
            }
            return $listaRetorno;
        }

        public static function listar($datos, $parametro){
            $retorno=[];
            switch($parametro){
                case "id_torneo":
                    $equipos = self::getByTorneo($datos);
                    foreach($equipos as $equipo){
                        $retorno[]=$equipo->toJSON();
                    }
                    break;
                case "nombre_equipo":
                    $equipo = self::getByNombre($datos);
                    $retorno[]= $equipo === null ? null : $equipo->toJSON();
                    break;
                default:
                    break;
            }
            return $retorno;
        }

        public static function listarKataRealizado($nombre_equipo, $id_enfrentamiento){
            $equipo = self::getByNombre($nombre_equipo);
            if($equipo === null){
                throw new Exception("Equipo no existente");
            }
            return $equipo->listarRealizaciones($id_enfrentamiento);
        }

        //metodos publicos
        public function listarRealizaciones($id_enfrentamiento){
            $retorno = array();
            $this->_conexion->setConsulta($this->_conexion->getConexion()->prepare(
                "SELECT *
                FROM realiza
                WHERE id_enfrentamiento = ? AND nombre_equipo = ?;"
            ));
            try {
                $this->_conexion->getConsulta()->bind_param('is', $id_enfrentamiento, $this->_nombre_equipo);
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

        public function guardar(){
            $torneo = Torneo::getById($this->_id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            Torneo::validarPosibilidadDeCambios($torneo->getId());
            self::validarCapacidadTorneo();
            if(!self::existe()){
                self::validar();
                //INSERT INTO Equipo
                $this->_conexion->insert(array_keys($this->_atributos), [$this->_nombre_equipo, $this->_cinturon_equipo, $this->_sexo_equipo, $this->_cantidadCompetidores_equipo, $this->_id_torneo, $this->_id_grupo], $this->_conexion->arrayToStringSinComa($this->_atributos));
            }
            if(!self::participa()){
                self::validar();
                //INSERT INTO Participa
                $this->_conexion->setTabla('Participa');
                $tipos = $this->_atributos['id_torneo'] . $this->_atributos['nombre_equipo'];
                $this->_conexion->insert(['id_torneo', 'nombre_equipo'], [$this->_id_torneo, $this->_nombre_equipo], $tipos);
                $this->_conexion->setTabla('Equipo');
            }else{
                throw new Exception("El equipo ya participa en el torneo");
            }
        }

        public function estaLleno(){
            return count($this->_competidores) >= $this->_cantidadCompetidores_equipo;
        }

        public function actualizar(){
            $torneo = Torneo::getById($this->_id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            self::validar();
            if(!self::participa()){
                throw new Exception("El equipo no participa en el torneo");
            }
            $atributos = $this->_atributos;
            unset($atributos["nombre_equipo"]);
            $this->_conexion->update(array_keys($atributos), [$this->_cinturon_equipo, $this->_sexo_equipo, $this->_cantidadCompetidores_equipo, $this->_id_torneo, $this->_id_grupo], $this->_conexion->arrayToStringSinComa($atributos), $this->_atributoClave, $this->_nombre_equipo, $this->_tipoAtributoClave);
        }

        public function agregarCompetidor($competidor){
            if(!$this->tieneCompetidor($competidor->getId())){
                $this->_competidores[]=$competidor;
            }else{
                echo "El Competidor ya fue agregado";
            }
        }
        public function quitarCompetidor($id_competidor){
            if($this->tieneCompetidor($id_competidor)){
                $indice = $this->indexCompetidor($id_competidor);
                unset($this->_competidores[$indice]);
            }else{
                echo "El Competidor no está en este Equipo";
            }
        }
        public function tieneCompetidor($id_competidor){
            $competidores = $this->listarCompetidores();
            foreach($competidores as $competidor){
                if($competidor->getId() == $id_competidor){
                    return true;
                }
            }
            return false;
        }
        public function listarCompetidores(){
            return $this->_competidores;
        }
        public function listarCompetidoresJSON(){
            $competidores = $this->listarCompetidores();
            $losCompetidores = array();
            foreach($competidores as $competidor){
                $losCompetidores[]=$competidor->toJSON();
            }
            return $losCompetidores;
        }

        public function indexCompetidor($id_competidor){
            if($this->tieneCompetidor($id_competidor)){
                $competidores = $this->listarCompetidores();
                for($i = 0; $i < count($competidores); $i++){
                    if($competidores[$i]->getId() == $id_competidor){
                        return $i;
                    }
                }
            }
            return -1;
        }

        public function eliminar(){
            $torneo = Torneo::getById($this->_id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            Torneo::validarPosibilidadDeCambios($torneo->getId());
            self::validarEliminacion();
            if($this->_conexion->existe($this->_atributoClave, $this->_nombre_equipo)){

                //tabla Participa
                $this->_conexion->setConsulta($this->_conexion->getConexion()->prepare(
                    "DELETE FROM participa WHERE id_torneo=? AND nombre_equipo=?;"
                ));
                try {
                    $id_torneo = $torneo->getId();
                    $this->_conexion->getConsulta()->bind_param('is', $id_torneo, $this->_nombre_equipo);
                    $this->_conexion->getConsulta()->execute();
                } catch (Exception $e) {
                    echo "Hubo un error en la consulta: " . $e->getMessage();
                }

                //tabla Equipo
                $this->_conexion->setTabla("Equipo");
                $this->_conexion->delete($this->_atributoClave, $this->_nombre_equipo, $this->_atributos[$this->_atributoClave]);
            }else{
                throw new Exception('El Equipo no existe');
            }
        }

        public function toJSON(){
            $equipo = [
                'nombre_equipo' => $this->getNombre(),
                'cinturon_equipo' => $this->getCinturon(),
                'sexo_equipo' => $this->getSexo(),
                'cantidadCompetidores_equipo' => $this->getCantidadCompetidores(),
                'id_torneo' => $this->getIdTorneo(),
                'id_grupo' => $this->getIdGrupo(),
                'competidores' => $this->listarCompetidoresJSON(),
                'puntaje' => $this->getPuntaje(),
                'posicion' => $this->getPosicion(),
                'id_enfrentamiento' => $this->getIdEnfrentamiento()
            ];
            return $equipo;
        }

        public function realizarKata($id_kata, $id_enfrentamiento, $fecha){
            self::validarPosibilidadDeHacerKata();
            self::validarRealizacion($id_kata, $id_enfrentamiento, $fecha);
            $this->_conexion->setTabla('Realiza');
            $this->_conexion->insert(['nombre_equipo', 'id_enfrentamiento', 'id_kata', 'fecha_ejecucionKata'], [$this->_nombre_equipo, $id_enfrentamiento, $id_kata, $fecha], 'siis');
            $this->_conexion->setTabla('Equipo');
        }

        public function actualizarKata($id_kata, $id_enfrentamiento, $fecha){
            if(self::fuePuntuado($id_enfrentamiento)){
                throw new Exception("El equipo ya fue puntuado");
            }
            if(!self::enfrentamientoValido($id_enfrentamiento)){
                throw new Exception($this->_nombre_equipo . " no se relaciona con el enfrentamiento " . $id_enfrentamiento);
            }
            if(!self::kataValido($id_kata)){
                throw new Exception("Kata no válido");
            }
            if(self::realizoElKataEnOtroEnfrentamiento($id_kata, $id_enfrentamiento)){
                throw new Exception($this->_nombre_equipo . " ya realizó el kata " . $id_kata . " anteriormente");
            }
            $this->_conexion->setConsulta($this->_conexion->getConexion()->prepare(
                "UPDATE realiza
                SET id_kata = ?, fecha_ejecucionKata = ?
                WHERE id_enfrentamiento = ? AND nombre_equipo = ?;" 
                    
            ));
            try {
                $this->_conexion->getConsulta()->bind_param("isis", $id_kata, $fecha, $id_enfrentamiento, $this->_nombre_equipo);
                $this->_conexion->getConsulta()->execute();
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
            $this->_conexion->getConsulta()->close();
        }

        public function eliminarKata($id_enfrentamiento){
            if(!$this->realizoUnKata($id_enfrentamiento)){
                throw new Exception("El equipo no realizó ningún kata en el enfrentamiento");
            }
            if(self::fuePuntuado($id_enfrentamiento)){
                throw new Exception("El equipo ya fue puntuado");
            }
            if(!self::enfrentamientoValido($id_enfrentamiento)){
                throw new Exception($this->_nombre_equipo . " no se relaciona con el enfrentamiento " . $id_enfrentamiento);
            }
            $this->_conexion->setConsulta($this->_conexion->getConexion()->prepare(
                "DELETE FROM realiza WHERE id_enfrentamiento=? AND nombre_equipo=?;"
            ));
            try {
                $this->_conexion->getConsulta()->bind_param('is', $id_enfrentamiento, $this->_nombre_equipo);
                $this->_conexion->getConsulta()->execute();
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
        }

        public function realizoUnKata($id_enfrentamiento){
            $this->_conexion->setTabla('Realiza');
            $filas = $this->_conexion->selectWhere(['nombre_equipo', 'id_enfrentamiento'], [$this->_nombre_equipo, $id_enfrentamiento], 'si', '&&');
            $this->_conexion->setTabla('Equipo');
            return count($filas) > 0;
        }

        public function __toString(){
            return "nombre: " . $this->_nombre_equipo . " pos: " . $this->_posicion;
        }

        //metodos privados
        private function validarCapacidadTorneo(){
            $torneo = Torneo::getById($this->_id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            $cantidadMaximaEquipos = $torneo->getModalidad() == "individual" ? ($torneo->getCantidadInscriptos()) : ($torneo->getCantidadInscriptos() / 3);
            if (count($torneo->listarEquipos()) >= $cantidadMaximaEquipos){
                throw new Exception("El torneo alcanzó su límite de equipos");
            }
        }

        private function fuePuntuado($id_enfrentamiento){
            $jueces = Juez::getByTorneo($this->_id_torneo);
            foreach($jueces as $juez){
                $puntuados = $juez->listarPuntuados($id_enfrentamiento);
                foreach($puntuados as $puntuado){
                    if($puntuado['nombre_equipo'] === $this->_nombre_equipo){
                        return true;
                    }
                }
            }
            return false;
        }

        private function validarEliminacion(){
            if(count($this->listarCompetidores()) > 0){
                throw new Exception("Ya hay competidores relacionados al equipo");
            }
        }

        private function validarPosibilidadDeHacerKata(){
            $torneo = Torneo::getById($this->_id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            //validamos que todos hayan sido puntuados
            $torneo->validarPuntua();
        }

        private function validarRealizacion($id_kata, $id_enfrentamiento, $fecha){
            if(!self::fechaValida($fecha)){
                throw new Exception("Formato de fecha no válido");
            }
            if(!self::enfrentamientoValido($id_enfrentamiento)){
                throw new Exception($this->_nombre_equipo . " no se relaciona con el enfrentamiento " . $id_enfrentamiento);
            }
            if(!self::kataValido($id_kata)){
                throw new Exception("No existe el kata " . $id_kata);
            }
            if($this->realizoUnKata($id_enfrentamiento)){
                throw new Exception($this->_nombre_equipo . " ya realizó un kata en el enfrentamiento " . $id_enfrentamiento);
            }
            if(self::realizoElKata($id_kata)){
                throw new Exception($this->_nombre_equipo . " ya realizó el kata " . $id_kata . " anteriormente");
            }
        }

        private function fechaValida($fecha){
            return true;
        }

        private function enfrentamientoValido($id_enfrentamiento){
            $enfrentamiento = Enfrentamiento::getById($id_enfrentamiento);
            if($enfrentamiento === null){
                throw new Exception("Enfrentamiento no existente");
            }
            return $enfrentamiento->tieneEquipo($this->_nombre_equipo);
        }

        private function kataValido($id_kata){
            return Kata::getById($id_kata) !== null;
        }

        private function realizoElKata($id_kata){
            $this->_conexion->setConsulta($this->_conexion->getConexion()->prepare(
                "SELECT realiza.*
                FROM realiza JOIN equipo
                ON realiza.nombre_equipo = equipo.nombre_equipo
                JOIN participa
                ON equipo.nombre_equipo = participa.nombre_equipo
                WHERE participa.id_torneo = ? AND participa.nombre_equipo = ? AND id_kata = ?;" 
                    
            ));
            try {
                $this->_conexion->getConsulta()->bind_param("isi", $this->_id_torneo, $this->_nombre_equipo, $id_kata);
                $this->_conexion->getConsulta()->execute();
                $resultado = $this->_conexion->getConsulta()->get_result();
                if (!$resultado) {
                    throw new Exception('Error en la consulta: ' . $this->_conexion->getConsulta()->error);
                }
                $filas = $this->_conexion->obtenerFilas($resultado);
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
            $this->_conexion->getConsulta()->close();
            return count($filas) > 0;
        }

        private function realizoElKataEnOtroEnfrentamiento($id_kata, $id_enfrentamiento){
            $this->_conexion->setConsulta($this->_conexion->getConexion()->prepare(
                "SELECT realiza.*
                FROM realiza JOIN equipo
                ON realiza.nombre_equipo = equipo.nombre_equipo
                JOIN participa
                ON equipo.nombre_equipo = participa.nombre_equipo
                WHERE participa.id_torneo = ? AND participa.nombre_equipo = ? AND id_kata = ? AND id_enfrentamiento != ?;" 
                    
            ));
            try {
                $this->_conexion->getConsulta()->bind_param("isii", $this->_id_torneo, $this->_nombre_equipo, $id_kata, $id_enfrentamiento);
                $this->_conexion->getConsulta()->execute();
                $resultado = $this->_conexion->getConsulta()->get_result();
                if (!$resultado) {
                    throw new Exception('Error en la consulta: ' . $this->_conexion->getConsulta()->error);
                }
                $filas = $this->_conexion->obtenerFilas($resultado);
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
            $this->_conexion->getConsulta()->close();
            return count($filas) > 0;
        }

        private function validar(){
            if(!self::grupoValido($this->_id_torneo, $this->_id_grupo)){
                throw new Exception('Grupo no válido');
            }
            if(!self::nombreValido($this->_nombre_equipo)){
                throw new Exception('Nombre no válido');
            }
            if(!self::cinturonValido($this->_cinturon_equipo)){
                throw new Exception('Cinturón no válido');
            }
            if(!self::sexoValido($this->_sexo_equipo)){
                throw new Exception('Sexo no válido');
            }
            if(!self::cantidadCompetidoresValida($this->_cantidadCompetidores_equipo)){
                throw new Exception('Cantidad de Competidores no válida');
            }
        }

        
        private function nombreValido($unNombre){
            return !$this->_conexion->estaVacio($unNombre);
        }

        private function cinturonValido($unCinturon){
            $cinturonesValidos = array('aka', 'ao');
            return in_array($unCinturon, $cinturonesValidos);
        }

        private function sexoValido($unSexo){
            return Torneo::getBasicPropertiesById($this->_id_torneo)->getSexo() == $unSexo;
        }

        private function cantidadCompetidoresValida($unaCantidad){
            return ((Torneo::getBasicPropertiesById($this->_id_torneo)->getModalidad() == "individual" && $unaCantidad == 1) || (Torneo::getBasicPropertiesById($this->_id_torneo)->getModalidad() == "equipo" && $unaCantidad == 3));
        }

        private function grupoValido(){
            return Grupo::getByIdGrupo($this->_id_torneo, $this->_id_grupo) !== null;
        }

        private function existe(){
            return $this->_conexion->existe($this->_atributoClave, $this->_nombre_equipo);
        }

        private function participa(){
            $this->_conexion->setTabla('Participa');
            $retorno = false;
            if(count($this->_conexion->selectWhere(['id_torneo', 'nombre_equipo'], [$this->_id_torneo, $this->_nombre_equipo], 'is', '&&')) > 0){
                $retorno = true;
            }
            $this->_conexion->setTabla('Equipo');
            return $retorno;
        }
    }
?>
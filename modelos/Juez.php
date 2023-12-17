<?php
    require_once((dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/Enfrentamiento.php');
    require_once(RUTA_RAIZ . 'modelos/Equipo.php');
    require_once(RUTA_RAIZ . 'modelos/Torneo.php');

    class Juez{
        //atributos
        private $_id_torneo;
        private $_numero_juez;
        private $_clave_juez;
        private $_nombreCompleto_juez;
        private $_atributos;
        private $_atributoClave;
        private $_conexion;

        //constructor
        public function __construct($id, $numero, $clave = "", $nombre = ""){
            $this->_id_torneo = $id;
            $this->_numero_juez = $numero;
            $this->_clave_juez = $clave;
            $this->_nombreCompleto_juez = $nombre;
            /*
            array asociativo que relaciona los atributos con los tipos de datos
            con array_keys($this->_atributos) se podrá acceder a los nombres de los atributos
            y con $this->_atributos se podrá acceder a los tipos de dato de dichos atributos
            LOS NOMBRES SON LOS QUE TIENEN EN LA BD
            */
            $this->_atributos = array(
                'id_torneo' => 'i',
                'numero_juez' => 'i',
                'clave_juez' => 's',
                'nombreCompleto_juez' => 's'
            );
            $this->_atributoClave = array('id_torneo', 'numero_juez');
            $this->_conexion = new Conexion('Juez');
        }

        //getters
        public function getIdTorneo(){
            return $this->_id_torneo;
        }
        public function getNumero(){
            return $this->_numero_juez;
        }
        public function getClave(){
            return $this->_clave_juez;
        }

        //metodos estaticos
        public static function getByTorneo($unaId){
            $conexion = new Conexion('Juez');
            $listaRetorno = array();
            $filas = $conexion->selectWhere(['id_torneo'], [$unaId], 'i', '');
            for($i = 0; $i < count($filas); $i++){
                $id_torneo = $filas[$i]['id_torneo'];
                $numero_juez = $filas[$i]['numero_juez'];
                $clave_juez = $filas[$i]['clave_juez'];
                $nombre_juez = $filas[$i]['nombreCompleto_juez'];
                $juez = new Juez($id_torneo, $numero_juez, $clave_juez, $nombre_juez);
                $listaRetorno[$i] = $juez;
            }
            return $listaRetorno;
        }

        public static function getByTorneoNumero($id_torneo, $numero_juez){
            $juez = null;
            $conexion = new Conexion('Juez');
            $filas = $conexion->selectWhere(['id_torneo', 'numero_juez'], [$id_torneo, $numero_juez], 'ii', '&&');
            if(count($filas) > 0){
                $id_torneo = $filas[0]['id_torneo'];
                $numero_juez = $filas[0]['numero_juez'];
                $clave_juez = $filas[0]['clave_juez'];
                $nombreCompleto_juez = $filas[0]['nombreCompleto_juez'];
                $juez = new Juez($id_torneo, $numero_juez, $clave_juez, $nombreCompleto_juez);
            }
            return $juez;
        }

        public static function listarPendientesDePuntuar($id_torneo, $numero_juez){
            $juez = Juez::getByTorneoNumero($id_torneo, $numero_juez);
            if($juez === null){
                throw new Exception("Torneo no existente");
            }
            return $juez->pendientesDePuntuar();
        }

        public static function eliminarDeTorneo($id_torneo){
            Torneo::validarPosibilidadDeCambios($id_torneo);
            $torneo = Torneo::getById($id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            $jueces = self::getByTorneo($id_torneo);
            foreach($jueces as $juez){
                $juez->eliminar();
            }
        }

        //metodos publicos
        public function eliminar(){
            $this->_conexion->setConsulta($this->_conexion->getConexion()->prepare(
                "DELETE 
                FROM juez
                WHERE id_torneo = ? AND numero_juez = ?;"
            ));
            try {
                $this->_conexion->getConsulta()->bind_param('ii', $this->_id_torneo, $this->_numero_juez);
                $this->_conexion->getConsulta()->execute();
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
        }


        public function pendientesDePuntuar(){
            $retorno = array();
            self::validarNumero();
            $torneo = Torneo::getById($this->_id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            $this->_conexion->setConsulta($this->_conexion->getConexion()->prepare(
                "SELECT *
                FROM realiza
                WHERE id_enfrentamiento = ? AND nombre_equipo NOT IN(
                    SELECT puntua.nombre_equipo
                    FROM puntua JOIN tiene
                    ON puntua.nombre_equipo = tiene.nombre_equipo AND puntua.id_enfrentamiento = tiene.id_enfrentamiento
                    WHERE tiene.id_enfrentamiento = ? AND puntua.id_torneo = ? AND puntua.numero_juez = ?
                );"
            ));
            $enfrentamientos = $torneo->listarEnfrentamientos();
            foreach($enfrentamientos as $enfrentamiento){
                $id_enfrentamiento = $enfrentamiento->getId();
                try {
                    $this->_conexion->getConsulta()->bind_param('iiii', $id_enfrentamiento, $id_enfrentamiento, $this->_id_torneo, $this->_numero_juez);
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
            }
            return $retorno;
        }

        public function listarPuntuados($id_enfrentamiento){
            $retorno = array();
            self::validarNumero();
            $torneo = Torneo::getById($this->_id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            $this->_conexion->setConsulta($this->_conexion->getConexion()->prepare(
                "SELECT *
                FROM puntua
                WHERE id_enfrentamiento = ? AND id_torneo = ? AND numero_juez = ?;"
            ));
            try {
                $this->_conexion->getConsulta()->bind_param('iii', $id_enfrentamiento, $this->_id_torneo, $this->_numero_juez);
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

        public function puntuar($id_enfrentamiento, $nombre_equipo, $puntaje){
            self::validarRealizacion($id_enfrentamiento, $nombre_equipo);
            self::validarPuntuacion($id_enfrentamiento, $nombre_equipo);
            self::validarPuntaje($puntaje);
            $this->_conexion->setTabla("Puntua");
            $this->_conexion->insert(['id_torneo', 'numero_juez', 'nombre_equipo', 'id_enfrentamiento', 'puntaje'], [$this->_id_torneo, $this->_numero_juez, $nombre_equipo, $id_enfrentamiento, $puntaje], 'iisis');
            $this->_conexion->setTabla("Juez");
        }

        public function guardar(){
            if(self::existe()){
                throw new Exception("El juez ya fue registrado en el torneo");
            }
            self::validar();
            $this->_conexion->insert(array_keys($this->_atributos), [$this->_id_torneo, $this->_numero_juez, $this->_clave_juez, $this->_nombreCompleto_juez], $this->_conexion->arrayToStringSinComa($this->_atributos));
        }

        public function claveCorrecta(){
            if($this->existe()){
                $filasResultado = $this->_conexion->selectWhere(['id_torneo', 'numero_juez', 'clave_juez'], [$this->_id_torneo, $this->_numero_juez, $this->_clave_juez], 'iis', '&&');
                return count($filasResultado) > 0;
            }else{
                return false;
            }
        }

        public function existe(){
            $filasResultado = $this->_conexion->selectWhere(['id_torneo', 'numero_juez'], [$this->_id_torneo, $this->_numero_juez], 'ii', '&&');
            return count($filasResultado) > 0;
        }

        //metodos privados
        private function validarRealizacion($id_enfrentamiento, $nombre_equipo){
            if(!self::relacionValida($id_enfrentamiento, $nombre_equipo)){
                throw new Exception($nombre_equipo . " no se relaciona con el enfrentamiento " . $id_enfrentamiento);
            }
            if(!self::realizoKata($id_enfrentamiento, $nombre_equipo)){
                throw new Exception($nombre_equipo . " no realizó ningún kata en el enfrentamiento " . $id_enfrentamiento);
            }
        }

        private function relacionValida($id_enfrentamiento, $nombre_equipo){
            $enfrentamiento = Enfrentamiento::getById($id_enfrentamiento);
            if($enfrentamiento === null){
                throw new Exception("Enfrentamiento no existente");
            }
            $equipo = Equipo::getByNombre($nombre_equipo);
            if($equipo === null){
                throw new Exception("Equipo no existente");
            }
            $nombre_equipo = $equipo->getNombre();
            return $enfrentamiento->tieneEquipo($nombre_equipo);
        }

        private function realizoKata($id_enfrentamiento, $nombre_equipo){
            $this->_conexion->setTabla("Realiza");
            $filas = $this->_conexion->selectWhere(['nombre_equipo', 'id_enfrentamiento'], [$nombre_equipo, $id_enfrentamiento], 'si', '&&');
            $this->_conexion->setTabla('Juez');
            return count($filas) > 0;
        }

        private function validarPuntuacion($id_enfrentamiento, $nombre_equipo){
            //lista de equipos que falta puntuar por este juez
            $puntuados = $this->listarPuntuados($id_enfrentamiento);
            foreach($puntuados as $puntuado){
                //si encuentra al equipo dentro de la lista
                if($puntuado['nombre_equipo'] === $nombre_equipo){
                    throw new Exception("El juez " . $this->_numero_juez . " del torneo " . $this->_id_torneo . " ya puntuó a " . $nombre_equipo . " en el enfrentamiento " . $id_enfrentamiento);
                }
            }
        }

        private function puntua($id_enfrentamiento, $nombre_equipo){
            $this->_conexion->setConsulta($this->_conexion->getConexion()->prepare(
                "SELECT *
                FROM puntua
                WHERE id_torneo=? AND numero_juez=? AND id_enfrentamiento=? AND nombre_equipo=?
                );"
            )); 
            try {
                $this->_conexion->getConsulta()->bind_param('iiis', $this->_id_torneo, $this->_numero_juez, $id_enfrentamiento, $nombre_equipo);
                $this->_conexion->getConsulta()->execute();
                $resultado = $this->_conexion->getConsulta()->get_result();
                if (!$resultado) {
                    throw new Exception('Error en la consulta: ' . $this->_conexion->getConsulta()->error);
                }
                return count($this->_conexion->obtenerFilas($resultado)) > 0;
            } catch (Exception $e) {
                echo "Hubo un error en la consulta: " . $e->getMessage();
            }
        }

        private function validarPuntaje($puntaje){
            $descalificacion = 0.0;
            $minimo = 5.0;
            $maximo = 10.0;

            //validamos que sea un numero
            if(!is_numeric($puntaje)){
                throw new Exception($puntaje . " no es un número");
            }

            //redondeamos, dejando un solo digito despues de la coma
            $numero = round(((float)$puntaje), 1);

            //validamos que se encuentre dentro del rango requerido
            if (!($numero === $descalificacion || ($numero >= $minimo && $numero <= $maximo))) {
                throw new Exception($numero . " no se encuentra dentro del rango admitido (" . $descalificacion . ", " .  $minimo . "-" . $maximo . ")");
            }
        
            return $numero;
        }

        private function validar(){
            self::validarTorneo();
            if(!self::numeroValido($this->_numero_juez)){
                throw new Exception('Número de Juez no válido');
            }
            if(!self::claveValida($this->_clave_juez)){
                throw new Exception('Clave no válida');
            }
            if(!self::nombreValido($this->_nombreCompleto_juez)){
                throw new Exception('Nombre no válido');
            }
        }

        private function validarNumero(){
            if(!$this->_conexion->torneoValido($this->_id_torneo)){
                throw new Exception('Id de Torneo no válida');
            }
            if(!self::numeroValido($this->_numero_juez)){
                throw new Exception('Número de Juez no válido');
            }
        }

        private function numeroValido($unNumero){
            //eventualmene evaluar cantidad de jueces del torneo para asignarle un valor a 'cantidadJueces'
            //$cantidadJueces = Torneo::getBasicProperitesById($this->_id_torneo)->getCantidadJueces();
            $cantidadJueces = 7;
            return (($unNumero >= 1) && ($unNumero <= $cantidadJueces)); 
        }

        private function claveValida($unaClave){
            return !$this->_conexion->estaVacio($unaClave);
        }

        private function nombreValido($unNombre){
            //eventualmente evaluar que haya nombre y apellido
            return !$this->_conexion->estaVacio($unNombre);
        }

        private function validarTorneo(){
            $torneo = Torneo::getById($this->_id_torneo);
            if($torneo === null){
                throw new Exception("Torneo no existente");
            }
            if($torneo->estaLlenoJueces()){
                throw new Exception("El torneo alcanzó su límite de jueces");
            }
            Torneo::validarPosibilidadDeCambios($this->_id_torneo);
        }
    }
?>
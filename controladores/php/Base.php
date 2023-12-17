<?php
    require_once(dirname(dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/Torneo.php');

    function validarParametros2($datos, $parametros, $retorno = false, $necesitaUnOpcional = false) {
        $parametrosObligatorios = parametrosObligatorios($parametros);
        $parametrosPosibles = parametrosPosibles($parametros);
        $alMenosUnOpcional = false;
        $parametroRetorno = null;

        foreach ($parametros as $parametro) {
            $nombre = $parametro["nombre"];
            $obligatorio = $parametro["obligatorio"];
    
            if ($obligatorio && (!isset($datos->$nombre) || empty($datos->$nombre))) {
                throw new Exception("El parámetro '" . $nombre . "' es obligatorio y no se recibió o está vacío. Parámetros obligatorios: " . $parametrosObligatorios);
            }
    
            if (!$obligatorio && isset($datos->$nombre)) {
                if($retorno){
                    $parametroRetorno = $nombre;
                }
                $alMenosUnOpcional = true;
            }
        }
        if ($necesitaUnOpcional && !$alMenosUnOpcional) {
            throw new Exception("Debes proporcionar al menos un parámetro opcional. Parámetros posibles: " . $parametrosPosibles);
        }
        return $parametroRetorno;
    }

    function parametrosObligatorios($parametros){
        $retorno = "";
        foreach($parametros as $parametro){
            if($parametro["obligatorio"]){
                $retorno .= $parametro["nombre"] . ", ";
            }
        }
        //borramos la ultima ', ' 
        $retorno = rtrim($retorno, ", ");
        return $retorno;
    }

    function parametrosPosibles($parametros){
        $retorno = "";
        foreach($parametros as $parametro){
            $retorno .= $parametro["nombre"] . ", ";
        }
        //borramos la ultima ', ' 
        $retorno = rtrim($retorno, ", ");
        return $retorno;
    }

    function validarParametros($parametrosRecibidos, $parametrosRequeridos) {
        foreach ($parametrosRequeridos as $parametro) {
            if (!isset($parametrosRecibidos->$parametro) || empty($parametrosRecibidos->$parametro)) {
                $conexion = new Conexion("");
                $parametrosMensaje = $conexion->arrayToString($parametrosRequeridos);
                throw new Exception("Parámetros necesarios: " . $parametrosMensaje .  ". No se recibió el parámetro '" . $parametro . "' o está vacío");
            }
        }
    }

    function validarSesion($tipoUsuario){
        $sesion = "";
        switch($tipoUsuario){
            case 'tecnicoSoftware':
                if(isset($_COOKIE['sesion_tecnicoSoftware'])){
                    $sesion = $_COOKIE['sesion_tecnicoSoftware'];
                }
                break;
            case 'juez':
                if(isset($_COOKIE['sesion_juez'])){
                    $sesion = $_COOKIE['sesion_juez'];
                }
                break;
            default:
                $sesion = null;
                break;
        }
        return $sesion;
    }

    function validarTecnicoSoftware(){
        $nombreTecnicoSoftware = validarSesion('tecnicoSoftware');
        $conexion = new Conexion('TecnicoSoftware');
        if(!$conexion->existe('nombreUsuario_tecnicoSoftware', $nombreTecnicoSoftware)){
            throw new Exception("Técnico de Software no existente en el sistema");
        }
    }

    function validarJuez(){
        $cookies_juez = validarSesion('juez');
        $conexion = new Conexion("Juez");
        if($cookies_juez === null){
            throw new Exception("Oh... Ha ocurrido un error con los COOKIES");
        }
        $array_cookies = explode(",", $cookies_juez);
        $id_torneo = str_replace(" ", "", $array_cookies[0]);
        $numero_juez = str_replace(" ", "", $array_cookies[1]);
        if(!$conexion->selectWhere(['id_torneo', 'numero_juez'], [$id_torneo, $numero_juez], 'ii', '&&')){
            throw new Exception("Juez no existente en el sistema");
        }
    }

    function obtenerDatosTorneo($unaId){
        try{
            validarTecnicoSoftware();
            $torneo = Torneo::getById($unaId);
            $retorno = $torneo === null ? null : $torneo->toJSON();
            return $retorno;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    function arrayToString($unArray, $nivel = 0)
    {
        $stringRetorno = "";
        foreach ($unArray as $elemento) {
            if (is_array($elemento)) {
            //si el elemento es un array, llamamos recursivamente a la función con el nuevo nivel
            $stringRetorno .= "[" . arrayToString($elemento, $nivel + 1) . "], ";
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
?>
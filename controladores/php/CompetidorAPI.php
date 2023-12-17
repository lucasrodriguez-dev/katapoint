<?php
    require_once(dirname(dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/Competidor.php');
    require_once(RUTA_RAIZ . 'modelos/Equipo.php');
    require_once(RUTA_RAIZ . 'modelos/Torneo.php');

    $respuesta = array();

    try {
        $error = "Sesión no válida";
        validarTecnicoSoftware();
        $datos = json_decode(file_get_contents("php://input"));
        $respuesta['estado'] = 'datos recibidos';
        $metodoSolicitud = $_SERVER["REQUEST_METHOD"];
        $retorno = null;
        switch(strtoupper($metodoSolicitud)) {
            case 'GET':
                $error = "No se pudo obtener a los competidores";
                $datos = "";
                $parametro = "";
                if(isset($_GET['id_torneo'])){
                    $datos = $_GET['id_torneo'];
                    $parametro = 'id_torneo';
                }
                if(isset($_GET['nombre_equipo'])){
                    $datos = $_GET['nombre_equipo'];
                    $parametro = 'nombre_equipo';
                }
                if(isset($_GET['id_competidor'])){
                    $datos = $_GET['id_competidor'];
                    $parametro = 'id_competidor';
                }
                if(isset($_GET['ci_competidor'])){
                    $datos = $_GET['ci_competidor'];
                    $parametro = 'ci_competidor';
                }
                $retorno = Competidor::listar($datos, $parametro);
                break;
            case 'POST':
                $error = "No se pudo registrar al competidor";
                validarPOST($datos);
                if(!isset($datos->id_competidor)){
                    $datos->id_competidor = null;
                }
                if(!isset($datos->nombre_equipo)){
                    $datos->nombre_equipo = null;
                }
                $competidor = new Competidor($datos->ci_competidor, $datos->sexo_competidor, $datos->fechaNacimiento_competidor, $datos->escuela_competidor, $datos->nombreCompleto_competidor, $datos->nombre_equipo, $datos->id_torneo, $datos->id_competidor);
                $competidor->guardar();
                $retorno = "Competidor registrado";
                break;
            case 'PUT':
                $error = "No se pudo modificar al competidor";
                validarPUT($datos);
                $competidor = new Competidor($datos->ci_competidor, $datos->sexo_competidor, $datos->fechaNacimiento_competidor, $datos->escuela_competidor, $datos->nombreCompleto_competidor, $datos->nombre_equipo, $datos->id_torneo, $datos->id_competidor);
                $competidor->actualizar();
                $retorno = "Competidor actualizado";
                break;
            case 'DELETE':
                $error = "No se pudo eliminar al competidor";
                validarDELETE($datos);
                Competidor::eliminar($datos->id_competidor);
                $retorno = "Competidor eliminado";
                break;
            default:
                throw new Exception("Método no permitido");  
        }
        $respuesta['retorno'] = $retorno;
    } catch (Exception $e) {
        $respuesta['error'] = $error . ": " . $e->getMessage();
    } finally {
        echo json_encode($respuesta);
    }

    function validarPOST($datos){
        $parametros = [
            ["nombre" => "id_competidor", "obligatorio" => false],
            ["nombre" => "ci_competidor", "obligatorio" => true],
            ["nombre" => "sexo_competidor", "obligatorio" => true],
            ["nombre" => "fechaNacimiento_competidor", "obligatorio" => true],
            ["nombre" => "escuela_competidor", "obligatorio" => true],
            ["nombre" => "nombreCompleto_competidor", "obligatorio" => true],
            ["nombre" => "nombre_equipo", "obligatorio" => false],
            ["nombre" => "id_torneo", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
        if(Torneo::getBasicPropertiesById($datos->id_torneo) === null){
            throw new Exception("Torneo no existente en el sistema");
        }
    }
    
    function validarPUT($datos){
        if(!isset($datos->id_torneo)){
            throw new Exception("El parámetro 'id_torneo' es obligatorio y no se recibió o está vacío");
        }
        $torneo = Torneo::getById($datos->id_torneo);
        if($torneo === null){
            throw new Exception("Torneo no existente");
        }
        if($torneo->getModalidad() === "individual"){
            $obligatorio = false;
            $datos->nombre_equipo=null;
        }else{
            $obligatorio = true;
        }
        $parametros = [
            ["nombre" => "id_competidor", "obligatorio" => true],
            ["nombre" => "ci_competidor", "obligatorio" => true],
            ["nombre" => "sexo_competidor", "obligatorio" => true],
            ["nombre" => "fechaNacimiento_competidor", "obligatorio" => true],
            ["nombre" => "escuela_competidor", "obligatorio" => true],
            ["nombre" => "nombreCompleto_competidor", "obligatorio" => true],
            ["nombre" => "nombre_equipo", "obligatorio" => $obligatorio],
            ["nombre" => "id_torneo", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
        return $datos;
    }

    function validarDELETE($datos){
        $parametros = [
            ["nombre" => "id_competidor", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
    }
?>
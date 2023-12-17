<?php
    require_once(dirname(dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/Equipo.php');

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
                $error = "No se pudo obtener a los equipos";
                if(isset($_GET['id_torneo'])){
                    $datos = $_GET['id_torneo'];
                    $parametro = 'id_torneo';
                }
                if(isset($_GET['nombre_equipo'])){
                    $datos = $_GET['nombre_equipo'];
                    $parametro = 'nombre_equipo';
                }
                if(isset($datos) && isset($parametro)){
                    $retorno = Equipo::listar($datos, $parametro);
                }
                break;
            case 'POST':
                $error = "No se pudo registrar al equipo";
                validarPOST($datos);
                $equipo = new Equipo($datos->nombre_equipo, $datos->cinturon_equipo, $datos->sexo_equipo, $datos->cantidadCompetidores_equipo, $datos->id_torneo, $datos->id_grupo);
                $equipo->guardar();
                $retorno = "Equipo registrado";
                break;
            case 'DELETE':
                $error = "No se pudo eliminar al equipo";
                validarDELETE($datos);
                $equipo = Equipo::getByNombre($datos->nombre_equipo);
                if($equipo === null){
                    throw new Exception("Equipo no existente");
                }
                $equipo->eliminar();
                $retorno = "Equipo eliminado";
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
            ["nombre" => "nombre_equipo", "obligatorio" => true],
            ["nombre" => "cinturon_equipo", "obligatorio" => true],
            ["nombre" => "sexo_equipo", "obligatorio" => true],
            ["nombre" => "cantidadCompetidores_equipo", "obligatorio" => true],
            ["nombre" => "id_torneo", "obligatorio" => true],
            ["nombre" => "id_grupo", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
        $torneo = Torneo::getBasicPropertiesById($datos->id_torneo);
        if($torneo === null){
            throw new Exception("Torneo no existente en el sistema");
        }
        if($torneo->getModalidad() !== "equipo"){
            throw new Exception("Este torneo no es en equipo");
        }
    }

    function validarDELETE($datos){
        $parametros = [
            ["nombre" => "nombre_equipo", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
    }
?>
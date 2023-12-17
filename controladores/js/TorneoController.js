import { validarSesion, validar, mostrarMensaje, getCookie } from "./TheController.js";

const urlAPITorneo = "../controladores/php/TorneoAPI.php";
const urlAPIJuez = "../controladores/php/JuezAPI.php";
var cantidadJueces = null;
var modalidad = null;
var id_torneo = null;

$(document).ready(function () {
    try {
        validarSesion('tecnicoSoftware');

        //cuando se seleccione la modalidad del Torneo
        $('#modalidadTorneo').on('change', function () {
            modalidad = $("#modalidadTorneo").val();
            if (modalidad === "equipo") {
                //deshabilitamos el input de rango de edad
                $("#rangoEdadTorneo").attr("disabled", "disabled");
            } else {
                //lo habilitamos
                $("#rangoEdadTorneo").attr("disabled", false);
            }
        });

            //cuando se seleccione la cantidad de Jueces
        $('#cantidadJuecesTorneo').on('change', function () {
            cantidadJueces = Number($("#cantidadJuecesTorneo").val());
            //si se seleccionó un numero
            if (!isNaN(cantidadJueces)) {
                const $tablaJueces = $('#tablaJueces');

                //limpiamos las filas existentes
                $tablaJueces.empty();

                //declaramos los estilos para los input
                let style = `style="width: calc(100% - 22px);
                font-weight: bold;
                max-height: 30px; 
                padding: 10px; 
                margin: 5px 0; 
                border: 1px solid #d3af37; 
                border-radius: 5px; 
                background-color: #3b3b3b; 
                color: #fff; transition: 
                border-color 0.3s ease, 
                box-shadow 0.3s ease;"`;


                //agregamos una fila por cada Juez
                for (let i = 1; i <= cantidadJueces; i++) {
                    $tablaJueces.append(`
                    <tr>
                        <td>${i}</td>
                        <td><input type="text" id='nombre_juez${i}' class='input' required ${style}></td>
                        <td><input type="password" id='clave_juez${i}' class='input password-input' required ${style}></td>
                    </tr>
                `);
                }
            }
        });
        let torneo = null;
        let jueces = [];
        let tecnicoSoftware = getCookie('sesion_tecnicoSoftware');
        $("#btn_ingresar").on("click", async function () {
            mostrarMensaje("Por favor, espere...", 'amarillo');
            try {
                jueces = [];
                let nombre = $("#nombreTorneo").val();
                let fecha = $("#fechaTorneo").val();
                let modalidad = $("#modalidadTorneo").val();
                let sexo = $("#sexoTorneo").val();
                let rangoEdad = $("#rangoEdadTorneo").val();
                let cantidadInscriptos = $("#cantidadInscriptosTorneo").val();
                let cantidadJueces = $("#cantidadJuecesTorneo").val();

                validar('nombre', nombre);
                validar('fecha', fecha);
                validar('modalidad', modalidad);
                validar('sexo', sexo);

                if (modalidad === "equipo") {
                    rangoEdad = null;
                    if (cantidadInscriptos % 3 != 0) {
                        throw "Ingrese una cantidad de inscriptos que sea múltiplo de 3";
                    }
                } else {
                    validar('rango de edad', rangoEdad);
                }
                validar('cantidad de inscriptos', cantidadInscriptos);
                validar('cantidad de jueces', cantidadJueces);
                for (let i = 1; i <= cantidadJueces; i++) {
                    let nombreJuez = $(`#nombre_juez${i}`).val();
                    validar(`nombre del juez ${i}`, nombreJuez);
                    let claveJuez = $(`#clave_juez${i}`).val();
                    validar(`clave del juez ${i}`, claveJuez);
                    let juez = {
                        numero_juez: i,
                        clave_juez: claveJuez,
                        nombreCompleto_juez: nombreJuez
                    }
                    jueces.push(juez);
                }
                torneo = {
                    cantidadInscriptos_torneo: cantidadInscriptos,
                    fecha_torneo: fecha,
                    nombre_torneo: nombre,
                    modalidad_torneo: modalidad,
                    sexo_torneo: sexo,
                    rangoEdad_torneo: rangoEdad,
                    nombreUsuario_tecnicoSoftware: tecnicoSoftware,
                    cantidadJueces_torneo: cantidadJueces
                };
                await guardar(urlAPITorneo, torneo);
                let juez = null;
                for (let i = 0; i < jueces.length; i++) {
                    juez = jueces[i];
                    juez.id_torneo = id_torneo;
                    await guardar(urlAPIJuez, juez);
                }
                mostrarMensaje("Registro exitoso", 'verde');
                setTimeout(function () {
                    window.location.href = `interfazTecnicoSoftware.html?id_torneo=${id_torneo}`;
                }, 800);
            } catch (e2) {
                $("#mensaje").html(e2);
                $("#mensaje").css("color", "verde");
            }

        });
    } catch (e) {
        console.error(e);
        if(e === "No se inició sesión"){
            gohome();
        }
    }
});

async function guardar(url, objeto) {
    await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(objeto)
    })
        .then(function (respuesta) {
            if (!respuesta.ok) {
                throw new Error("Error: " + respuesta.status);
            }
            return respuesta.json();
        })
        .then(function (datos) {
            console.log(datos);
            if (datos.error) {
                let error = datos.error;
                mostrarMensaje(error, 'rojo');
            }
            id_torneo = datos.id_torneo;
        })
        .catch(error => console.error(error));
}

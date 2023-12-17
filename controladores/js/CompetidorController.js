import { validar, validarSesion, gohome, obtenerDeUrl, obtenerTorneo, mostrarMensaje, mostrarPantallaCarga, ocultarPantallaCarga } from "./TheController.js";

const urlAPICompetidor = "../controladores/php/CompetidorAPI.php";
const urlAPIEquipo = "../controladores/php/EquipoAPI.php";
const id_torneo = obtenerDeUrl("id_torneo");
let id_competidor = null;

$(document).ready(async function () {
    mostrarPantallaCarga();
    try {
        validarSesion('tecnicoSoftware');
        const torneo = await obtenerTorneo(id_torneo);
        if (torneo.modalidad_torneo == "individual") {
            //deshabilitamos el input de nombre de equipo
            $("#nombreEquipo").css("display", "none");
        }
        await listarEquipos();
        ocultarPantallaCarga();
        $("#ci").on("input", async function () {
            habilitarCampos();
            let ci = $("#ci").val();
            if (ci.length >= 8) {
                //si se pasa del limite
                if (ci.length > 8) {
                    //reducimos al limite
                    $("#ci").val(ci.slice(0, 8));
                    ci = $("#ci").val();
                }
                await verificarCI(ci);
            }
        });
        $("#btn_ingresar").on("click", async function () {
            try {
                let ci = Number($("#ci").val());
                let sexo = $("#sexo").val();
                let fechaNacimiento = $("#fechaNacimiento").val();
                let escuela = $("#escuela").val();
                let nombre = $("#nombreCompetidor").val();
                let equipo = $("#nombreEquipo").val();

                validar('cédula de identidad', ci);
                validar('sexo', sexo);
                validar('fecha de nacimiento', fechaNacimiento);
                validar('escuela', escuela);
                validar('nombre', nombre);

                if (torneo.modalidad_torneo == "individual") {
                    equipo = null;
                } else {
                    validar('equipo', equipo);
                }
                let competidor = {
                    id_competidor: id_competidor,
                    ci_competidor: ci,
                    sexo_competidor: sexo,
                    fechaNacimiento_competidor: fechaNacimiento,
                    escuela_competidor: escuela,
                    nombreCompleto_competidor: nombre,
                    nombre_equipo: equipo,
                    id_torneo: torneo.id_torneo
                };
                await verificarCI();
                await guardar(competidor);
            } catch (e2) {
                $("#mensaje").html(e2);
                $("#mensaje").css("color", "rgb(245, 245, 110)");
            }

        });
    } catch (e) {
        console.error(e);
        if(e === "No se inició sesión"){
            gohome();
        }
    }

});

async function guardar(objeto) {
    fetch(urlAPICompetidor, {
        method: 'POST',
        body: JSON.stringify(objeto),
        headers: {
            'Content-Type': 'application/json'
        }
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
                mostrarMensaje(datos.error, "rojo");
            } else {
                mostrarMensaje(datos.retorno, "verde");
            }
        })
        .catch(function (error) {
            console.log(error);
        });
}

async function verificarCI(ci) {
    let url = `${urlAPICompetidor}?ci_competidor=${ci}`;
    fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
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
                let mensaje = datos.error;
                mostrarMensaje(mensaje, "rojo");
            } else {
                let competidor = datos.retorno[0];
                console.log(competidor);
                if (competidor !== null) {
                    id_competidor = competidor.id_competidor;
                    llenarFormulario(competidor);
                } else {
                    id_competidor = null;
                }
            }
        })
        .catch(function (error) {
            console.log(error);
        });
}

function llenarFormulario(competidor) {
    $("#sexo").val(competidor.sexo_competidor);
    $("#fechaNacimiento").val(competidor.fechaNacimiento_competidor);
    $("#escuela").val(competidor.escuela_competidor);
    $("#nombreCompetidor").val(competidor.nombreCompleto_competidor);
    bloquearCampos();
}

function bloquearCampos() {
    $("#sexo").attr("disabled", "disabled");
    $("#fechaNacimiento").attr("disabled", "disabled");
    $("#escuela").attr("disabled", "disabled");
    $("#nombreCompetidor").attr("disabled", "disabled");
}

function habilitarCampos() {
    $("#sexo").attr("disabled", false);
    $("#fechaNacimiento").attr("disabled", false);
    $("#escuela").attr("disabled", false);
    $("#nombreCompetidor").attr("disabled", false);
    $("#nombreEquipo").attr("disabled", false);
}

async function listarEquipos() {
    let select = $("#nombreEquipo");
    let url = `${urlAPIEquipo}?id_torneo=${id_torneo}`;
    fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(function (respuesta) {
            if (!respuesta.ok) {
                throw new Error("Error: " + respuesta.status);
            }
            return respuesta.json();
        })
        .then(function (datos) {
            console.log(datos);
            if (!datos.error) {
                let equipos = datos.retorno;
                console.table(equipos);
                let cantidadEquipos = equipos.length;
                for(let i = 0; i < cantidadEquipos; i++){
                    let equipo = equipos[i];
                    let option = `<option value='${equipo.nombre_equipo}'>${equipo.nombre_equipo}</option>`;
                    select.append(option);
                }
            }
        })
        .catch(function (error) {
            console.log(error);
        });
}
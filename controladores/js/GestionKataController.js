import { validarSesion, validar, estiloTabla, mostrarMensaje, redirect, obtenerTorneo, obtenerDeUrl, gohome, mostrarPantallaCarga, ocultarPantallaCarga, obtenerCompetidores } from "./TheController.js";

const urlAPIRealiza = "../controladores/php/RealizaAPI.php";
const urlAPIPuntua = "../controladores/php/PuntuaAPI.php";
const urlAPIKata = "../controladores/php/KataAPI.php";
const urlAPIEnfrentamiento = "../controladores/php/EnfrentamientoAPI.php";
const id_torneo = obtenerDeUrl('id_torneo');
let torneo = null;
let ronda_actual = null;
let enfrentamientosRonda = null;
let participante = "";

$(document).ready(async function () {
    mostrarPantallaCarga();
    torneo = await obtenerTorneo(id_torneo);
    try {
        validarSesion('tecnicoSoftware');
        ocultarFormulario();
        enfrentamientosRonda = [];
        await listarKatas();
        ronda_actual = torneo.ronda_actual;
        if (torneo.modalidad_torneo == "individual") {
            await listarCompetidores();
            participante = "competidor";
        } else {
            await listarEquipos();
            participante = "equipo";
        }
        $("#btn_ingresar").on("click", async function () {
            await registrarKata();
            await construirTabla();
        });
        try {
            await construirTabla();
            ocultarPantallaCarga();
        } catch (e2) {
            mostrarMensaje(e2, "rojo");
        }
        $(".btn_eliminar").on("click", async function () {
            let opcion = confirm("¿Seguro que quieres eliminarlo?")
            if (opcion) {
                let id = $(this).attr("id");
                let nombre_equipo = id.split('-')[1];
                let id_enfrentamiento = id.split('-')[2];
                let realizacion = {
                    nombre_equipo: nombre_equipo,
                    id_enfrentamiento: id_enfrentamiento
                }
                await eliminar(realizacion);
            }
        });
    } catch (e) {
        console.error(e);
        if(e === "No se inició sesión"){
            gohome();
        }
    }
});

async function construirTabla() {
    let url = `${urlAPIPuntua}?id_torneo=${id_torneo}`;
    await fetch(url, {
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
                let error = datos.error;
                mostrarMensaje(error, 'rojo');
            }
            //declaramos los estilos para la tabla
            let style = estiloTabla();

            let containerTabla = $("#container_tabla");
            let tabla = $(`<table border='1' class='tabla' ${style}>`);

            let encabezadoEquipo = "EQUIPO";
            if(torneo.modalidad_torneo == "individual"){
                encabezadoEquipo = "COMPETIDOR";
            }

            let encabezado = $(`<thead><th>${encabezadoEquipo}</th><th>KATA</th><th>ENFRENTAMIENTO</th><th>FECHA</th><th>ACCION</th></thead>`);
            tabla.html(encabezado);
            let realizaciones = datos.retorno;
            let cantidadRealizaciones = realizaciones.length;
            if (cantidadRealizaciones === 0) {
                mostrarFormulario();
            } else {
                let equipo = realizaciones[0].nombre_equipo;
                let cantidadFilasTabla = 1;
                for (let i = 0; i < cantidadRealizaciones; i++) {
                    if (realizaciones[i].nombre_equipo == equipo) {
                        equipo = realizaciones[i].nombre_equipo;
                    } else {
                        cantidadFilasTabla++;
                    }
                }
                for (let i = 0; i < cantidadFilasTabla; i++) {
                    let fila = $("<tr>");
                    let realizacion = realizaciones[i];
                    let columnaAccion = `<td><input type='button' class='btn btn_eliminar' id='btn_eliminar-${realizacion.nombre_equipo}-${realizacion.id_enfrentamiento}' value='Eliminar'</td>`;
                    let columnas = $(`<td>${realizacion.nombre_equipo}</td><td>${realizacion.id_kata}</td><td>${realizacion.id_enfrentamiento}</td><td>${realizacion.fecha_ejecucionKata}</td>${columnaAccion}`);
                    fila.html(columnas);
                    tabla.append(fila);
                }
                containerTabla.html(tabla);
            }
        })
        .catch(error => console.error(error));
}

async function mostrarFormulario() {
    let opcion = $(`#nombreEquipo option[value="opcionPorDefectoEquipo"]`);
    let contenido = "Equipo";
    if (torneo.modalidad_torneo == "individual") {
        contenido = "Competidor";
    }
    opcion.text(contenido);
    $("#formulario").show();
}

function ocultarFormulario() {
    $("#formulario").hide();
}

async function registrarKata() {
    try{
        let datos = tomarDatos();
        let objeto = {
            id_enfrentamiento: datos.id_enfrentamiento,
            nombre_equipo: datos.nombre_equipo,
            id_kata: datos.id_kata,
            fecha_ejecucionKata: datos.fecha_ejecucionKata
        }
        await fetch(urlAPIRealiza, {
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
                    mostrarMensaje(datos.error, "rojo");
                } else {
                    mostrarMensaje(datos.retorno, "verde");
                    ocultarFormulario();
                }
            })
            .catch(error => console.error(error));
    }catch(e){
        mostrarMensaje(e, "amarillo");
    }
}

function tomarDatos() {
    let nombre_equipo = $("#nombreEquipo").val();
    let id_kata = $("#kata").val();
    if(nombre_equipo == "opcionPorDefectoEquipo"){
        throw `Ingrese ${participante}`;
    }
    validar("kata", id_kata);
    let fechaCompleta = new Date();
    let fecha = `${fechaCompleta.getFullYear()}-${fechaCompleta.getMonth()}-${fechaCompleta.getDate()} ${fechaCompleta.getHours()}:${fechaCompleta.getMinutes()}:${fechaCompleta.getSeconds()}`;
    let id_enfrentamiento = $("#nombreEquipo option:selected").data("id-enfrentamiento");
    let retorno = {
        id_enfrentamiento: id_enfrentamiento,
        nombre_equipo: nombre_equipo,
        id_kata: id_kata,
        fecha_ejecucionKata: fecha
    }
    return retorno;
}

async function listarKatas() {
    let select = $("#kata");
    fetch(urlAPIKata, {
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
                let katas = datos.retorno;
                console.table(katas);
                let cantidadKatas = katas.length;
                for (let i = 0; i < cantidadKatas; i++) {
                    let kata = katas[i];
                    let option = `<option value='${kata.id_kata}'>${kata.id_kata} - ${kata.nombre_kata}</option>`;
                    select.append(option);
                }
            }
        })
        .catch(function (error) {
            console.log(error);
        });
}

async function listarEquipos() {
    let select = $("#nombreEquipo");
    let url = `${urlAPIEnfrentamiento}?id_torneo=${id_torneo}&ronda_enfrentamiento=${ronda_actual}`;
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
                let enfrentamientos = datos.retorno;
                enfrentamientosRonda = enfrentamientos;
                console.table(enfrentamientos);
                let cantidadEnfrentamientos = enfrentamientos.length;
                for (let i = 0; i < cantidadEnfrentamientos; i++) {
                    let enfrentamiento = enfrentamientos[i];
                    let equipos = enfrentamiento.equipos;
                    let cantidadEquipos = equipos.length;
                    for (let j = 0; j < cantidadEquipos; j++) {
                        let equipo = equipos[j];
                        let option = `<option value='${equipo.nombre_equipo}' data-id-enfrentamiento='${enfrentamiento.id}'>${equipo.nombre_equipo}</option>`;
                        select.append(option);
                    }
                }
            }
        })
        .catch(function (error) {
            console.log(error);
        });
}

async function listarCompetidores() {
    let select = $("#nombreEquipo");
    let url = `${urlAPIEnfrentamiento}?id_torneo=${id_torneo}&ronda_enfrentamiento=${ronda_actual}`;
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
                let enfrentamientos = datos.retorno;
                enfrentamientosRonda = enfrentamientos;
                console.table(enfrentamientos);
                let cantidadEnfrentamientos = enfrentamientos.length;
                for (let i = 0; i < cantidadEnfrentamientos; i++) {
                    let enfrentamiento = enfrentamientos[i];
                    let equipos = enfrentamiento.equipos;
                    let cantidadEquipos = equipos.length;
                    for (let j = 0; j < cantidadEquipos; j++) {
                        let equipo = equipos[j];
                        let competidores = equipo.competidores;
                        let cantidadCompetidores = competidores.length;
                        for (let k = 0; k < cantidadCompetidores; k++) {
                            let competidor = competidores[k];
                            let option = `<option value='${competidor.nombre_equipo}' data-id-enfrentamiento='${enfrentamiento.id}'>${competidor.nombreCompleto_competidor}</option>`;
                            select.append(option);
                        }
                    }
                }
            }
        })
        .catch(function (error) {
            console.log(error);
        });
}

async function eliminar(objeto) {
    await fetch(urlAPIRealiza, {
        method: 'DELETE',
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
                alert(datos.error);
            } else {
                location.reload();
            }
        })
        .catch(error => console.error(error));
}
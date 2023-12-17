import { validar, mostrarMensaje, obtenerDeUrl } from '../js/TheController.js';

$(document).ready(function () {
    $("#btn_ingresar").on("click", function () {
        let urlAPI = `../controladores/php/JuezAPI.php`;
        try {
            let id_torneo = $("#id_torneo").val();
            let numero_juez = $("#numero_juez").val();
            let password = $("#clave_juez").val();
            validar('ID Torneo', id_torneo);
            validar('Número juez', numero_juez);
            validar('Contraseña', password);
            urlAPI = `${urlAPI}?id_torneo=${id_torneo}&numero_juez=${numero_juez}&clave_juez=${password}`;
            fetch(urlAPI, {
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
                    }else{
                        mostrarMensaje(datos.retorno, 'verde');
                        document.cookie = "sesion_juez=" + datos.sesion + "; path=/";
                        setTimeout(function () {
                            window.location.href = `interfazJuez.html?id_torneo=${id_torneo}&numero_juez=${numero_juez}`;
                        }, 900);
                    }
                })
                .catch(error => console.error(error));
        } catch (e) {
            mostrarMensaje(e, 'amarillo');
        }

    });
});

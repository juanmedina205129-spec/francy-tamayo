
document.addEventListener("DOMContentLoaded", () => {

    const tabla = document.querySelector('#tablaPlatos');
    if (!tabla) return;

    new DataTable(tabla, {
        responsive: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json"
        }
    });

});


document.addEventListener("DOMContentLoaded", () => {

    const formularios = document.querySelectorAll(".form-eliminar");

    formularios.forEach(form => {
        form.addEventListener("submit", e => {
            const confirmar = confirm("¿Seguro que deseas eliminar este producto?");
            if (!confirmar) {
                e.preventDefault();
            }
        });
    });

});


//eliminar

document.addEventListener("DOMContentLoaded", () => {

    const formularios = document.querySelectorAll(".form-eliminar");

    formularios.forEach(form => {
        form.addEventListener("submit", e => {
            const confirmar = confirm("¿Seguro que deseas eliminar este producto?");
            if (!confirmar) {
                e.preventDefault();
            }
        });
    });

});



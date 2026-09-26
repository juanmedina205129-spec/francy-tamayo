document.addEventListener("DOMContentLoaded", () => {

    const form = document.querySelector(".admin-form");
    const preview = document.getElementById("preview");
    if (!form || !preview) return;

    // PREVIEW
    form.imagen.addEventListener("change", e => {
        const file = e.target.files[0];
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = "block";
        }
    });

    // VALIDACIÓN FRONTEND (las mismas reglas que aplica crear.php en el servidor)
    form.addEventListener("submit", (e) => {

        let errores = false;

        limpiarErrores();

        const nombre = form.nombre.value.trim();
        const tipo = form.tipo.value.trim();
        const precio = Number(form.precio.value);
        const rating = Number(form.rating.value);

        if (!nombre) {
            mostrarError("nombre", "Campo obligatorio");
            errores = true;
        }

        if (!tipo) {
            mostrarError("tipo", "Campo obligatorio");
            errores = true;
        }

        if (form.precio.value === "" || !(precio > 0)) {
            mostrarError("precio", "Precio inválido");
            errores = true;
        }

        if (form.rating.value !== "" && (rating < 0 || rating > 5)) {
            mostrarError("rating", "La valoración debe estar entre 0 y 5");
            errores = true;
        }

        if (!form.imagen.files.length) {
            mostrarError("imagen", "Imagen obligatoria");
            errores = true;
        }

        if (errores) e.preventDefault();
    });

    function mostrarError(inputName, mensaje) {
        const input = form.querySelector(`[name="${inputName}"]`);
        const error = document.createElement("p");
        error.textContent = mensaje;
        error.classList.add("error");
        input.insertAdjacentElement("afterend", error);
    }

    function limpiarErrores() {
        form.querySelectorAll(".error").forEach(e => e.remove());
    }

});

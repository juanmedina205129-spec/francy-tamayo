
document.addEventListener("DOMContentLoaded", () => {
 
    const form = document.querySelector(".admin-form");
    const preview = document.getElementById("preview");
 
       // LIMPIAR URL
    if (window.location.search.includes("ok=1")) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
 
    // PREVIEW
    document.querySelector('[name="imagen"]').addEventListener("change", e => {
        const file = e.target.files[0];
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = "block";
        }
    });
 
    // VALIDACIÓN FRONTEND
    form.addEventListener("submit", (e) => {
 
        let errores = false;
 
        limpiarErrores();
 
        const nombre = form.nombre.value.trim();
        const descripcion = form.descripcion.value.trim();
        const precio = form.precio.value;
 
        if (!nombre) {
            mostrarError("nombre", "Campo obligatorio");
            errores = true;
        }
 
        if (!descripcion) {
            mostrarError("descripcion", "Campo obligatorio");
            errores = true;
        }
 
        if (precio === "" || precio < 0) {
            mostrarError("precio", "Precio inválido");
            errores = true;
        }
 
        if (!form.imagen.files.length) {
            mostrarError("imagen", "Imagen obligatoria");
            errores = true;
        }
 
        if (errores) e.preventDefault();
    });
 
    function mostrarError(inputName, mensaje) {
        const input = document.querySelector(`[name="${inputName}"]`);
        const error = document.createElement("p");
        error.textContent = mensaje;
        error.classList.add("error");
        input.insertAdjacentElement("afterend", error);
    }
 
    function limpiarErrores() {
        document.querySelectorAll(".error").forEach(e => e.remove());
    }
 
    // MENSAJE DESAPARECE
    const msg = document.getElementById("mensajeOk");
    if (msg) {
        setTimeout(() => msg.remove(), 3000);
    }
 
});


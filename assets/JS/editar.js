document.addEventListener("DOMContentLoaded", () => {
    const input = document.getElementById("inputImagen");
    const preview = document.getElementById("previewImagen");
    if (!input || !preview) return;

    input.addEventListener("change", (e) => {
        const file = e.target.files[0];
        if (file) {
            preview.src = URL.createObjectURL(file); // cambia la miniatura al seleccionar
        }
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const input = document.getElementById("inputImagen");
    const preview = document.getElementById("previewImagen");

    input.addEventListener("change", (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                preview.src = event.target.result; // cambia la miniatura al seleccionar
            }
            reader.readAsDataURL(file);
        }
    });
});

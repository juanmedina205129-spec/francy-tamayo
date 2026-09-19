(() => {
    const form = document.querySelector('#contact-form');
    if (!form) return;

    form.addEventListener('submit', event => {
        event.preventDefault();
        if (!form.reportValidity()) return;

        const values = Object.fromEntries(new FormData(form).entries());
        const message = [
            'Hola, quiero solicitar un encargo personalizado.', '',
            `Nombre: ${values.name}`, `Teléfono: ${values.phone}`,
            values.email ? `Correo: ${values.email}` : '',
            values.city ? `Ciudad: ${values.city}` : '',
            `Encargo: ${values.commission_type}`,
            values.desired_date ? `Fecha ideal: ${values.desired_date}` : '',
            values.size ? `Tamaño o talla: ${values.size}` : '',
            values.budget ? `Presupuesto: ${values.budget}` : '',
            `Detalles: ${values.commission_details}`
        ].filter(Boolean).join('\n');

        document.querySelector('#contact-feedback').textContent = 'Abriendo WhatsApp con tu solicitud…';
        window.open(`https://wa.me/573184597719?text=${encodeURIComponent(message)}`, '_blank', 'noopener');
    });
})();

(() => {
    const form = document.querySelector('#contact-form');
    if (!form) return;

    const feedback = document.querySelector('#contact-feedback');
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/';
    const whatsappUrl = text => `https://wa.me/573184597719?text=${encodeURIComponent(text)}`;

    form.addEventListener('submit', event => {
        event.preventDefault();
        if (!form.reportValidity()) return;

        const values = Object.fromEntries(new FormData(form).entries());
        const buildMessage = code => [
            'Hola, quiero solicitar un encargo personalizado.',
            code ? `Código de seguimiento: ${code}` : '', '',
            `Nombre: ${values.name}`, `Teléfono: ${values.phone}`,
            values.email ? `Correo: ${values.email}` : '',
            values.city ? `Ciudad: ${values.city}` : '',
            `Encargo: ${values.commission_type}`,
            values.desired_date ? `Fecha ideal: ${values.desired_date}` : '',
            values.size ? `Tamaño o talla: ${values.size}` : '',
            values.budget ? `Presupuesto: ${values.budget}` : '',
            `Detalles: ${values.commission_details}`
        ].filter((line, index) => line || index === 2).join('\n');

        // La pestaña se abre dentro del clic para que el navegador no la bloquee; cuando el
        // encargo queda guardado se redirige a WhatsApp con el código de seguimiento incluido.
        const whatsappTab = window.open('', '_blank');
        if (whatsappTab) whatsappTab.opener = null;
        const openWhatsapp = code => {
            const url = whatsappUrl(buildMessage(code));
            if (whatsappTab) whatsappTab.location.href = url; else window.location.href = url;
        };

        feedback.textContent = 'Registrando tu solicitud…';
        if (typeof window.enviarPedido !== 'function') { openWhatsapp(''); return; }

        window.enviarPedido({...values, origen: 'contacto'})
            .then(response => {
                if (response.ok && response.codigo) {
                    feedback.innerHTML = '';
                    feedback.append(`Solicitud registrada con el código ${response.codigo}. `);
                    const link = document.createElement('a');
                    link.href = response.seguimiento || `${baseUrl}seguimiento.php`;
                    link.textContent = 'Ver seguimiento';
                    feedback.append(link);
                    openWhatsapp(response.codigo);
                } else {
                    // Si no se pudo registrar (p. ej. un dato inválido), la solicitud igual llega por WhatsApp.
                    feedback.textContent = `${response.mensaje || 'No pudimos registrar la solicitud.'} Te abrimos WhatsApp para enviarla directamente.`;
                    openWhatsapp('');
                }
            })
            .catch(() => {
                feedback.textContent = 'Abriendo WhatsApp con tu solicitud…';
                openWhatsapp('');
            });
    });
})();

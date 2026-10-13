(() => {
    const form = document.querySelector('#contact-form');
    if (!form) return;

    const feedback = document.querySelector('#contact-feedback');
    const submitButton = form.querySelector('.contact-submit');
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/';
    const whatsappUrl = text => `https://wa.me/573184597719?text=${encodeURIComponent(text)}`;
    const setFeedback = (text, isError) => { feedback.textContent = text; feedback.classList.toggle('is-error', !!isError); };

    form.addEventListener('submit', event => {
        event.preventDefault();
        if (!form.reportValidity()) return;

        const values = Object.fromEntries(new FormData(form).entries());
        const hasPhoto = values.reference_photo instanceof File && values.reference_photo.size > 0;
        const buildMessage = (code, photoSaved) => [
            'Hola, quiero solicitar un encargo personalizado.',
            code ? `Código de seguimiento: ${code}` : '', '',
            `Nombre: ${values.name}`, `Teléfono: ${values.phone}`,
            values.email ? `Correo: ${values.email}` : '',
            values.city ? `Ciudad: ${values.city}` : '',
            `Encargo: ${values.commission_type}`,
            values.desired_date ? `Fecha ideal: ${values.desired_date}` : '',
            values.size ? `Tamaño o talla: ${values.size}` : '',
            values.budget ? `Presupuesto: ${values.budget}` : '',
            `Detalles: ${values.commission_details}`,
            values.is_gift ? `Es un regalo para: ${values.gift_for}` : '',
            values.is_gift && values.gift_message ? `Mensaje para la tarjeta: ${values.gift_message}` : '',
            hasPhoto ? (photoSaved ? 'Foto de referencia: adjunta en el pedido.' : 'Foto de referencia: te la envío por este chat.') : ''
        ].filter((line, index) => line || index === 2).join('\n');

        // La pestaña se abre dentro del clic para que el navegador no la bloquee; cuando el
        // encargo queda guardado se redirige a WhatsApp con el código de seguimiento incluido.
        const whatsappTab = window.open('', '_blank');
        if (whatsappTab) whatsappTab.opener = null;
        const openWhatsapp = (code, photoSaved) => {
            const url = whatsappUrl(buildMessage(code, photoSaved));
            if (whatsappTab) whatsappTab.location.href = url; else window.location.href = url;
        };

        setFeedback('Registrando tu solicitud…', false);
        if (typeof window.enviarPedido !== 'function') { openWhatsapp('', false); return; }

        window.enviarPedido({...values, origen: 'contacto'})
            .then(response => {
                if (response.ok && response.codigo) {
                    setFeedback('', false);
                    feedback.append(`Solicitud registrada con el código ${response.codigo}. `);
                    const link = document.createElement('a');
                    link.href = response.seguimiento || `${baseUrl}seguimiento.php`;
                    link.textContent = 'Ver seguimiento';
                    feedback.append(link);
                    openWhatsapp(response.codigo, !!response.fotoGuardada);
                } else if (response.cupoLleno) {
                    // La agenda se llenó mientras se completaba el formulario: no se registra el
                    // pedido, pero la persona puede pedir por WhatsApp que le avisen.
                    setFeedback(response.mensaje, true);
                    if (submitButton) { submitButton.disabled = true; submitButton.textContent = 'Agenda completa'; }
                    openWhatsapp('', false);
                } else {
                    // Si no se pudo registrar (p. ej. un dato inválido), la solicitud igual llega por WhatsApp.
                    setFeedback(`${response.mensaje || 'No pudimos registrar la solicitud.'} Te abrimos WhatsApp para enviarla directamente.`, true);
                    openWhatsapp('', false);
                }
            })
            .catch(() => {
                setFeedback('Abriendo WhatsApp con tu solicitud…', false);
                openWhatsapp('', false);
            });
    });
})();

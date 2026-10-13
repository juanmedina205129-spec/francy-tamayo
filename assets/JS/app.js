(() => {
    const cartKey = 'nido-de-arte-cart';
    const customerDataKey = 'nido-de-arte-customer-data';
    const ordersKey = 'nido-de-arte-orders';
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const money = value => new Intl.NumberFormat('es-CO', {style: 'currency', currency: 'COP', maximumFractionDigits: 0}).format(value);
    // Todo texto que venga del catálogo o del almacenamiento local se escapa antes de insertarlo como HTML.
    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[char]));
    const assetUrl = path => /^(https?:)?\/\//.test(path) || String(path).startsWith('/') ? path : baseUrl + path;
    // El catálogo lo imprime buscador.php desde la base de datos (o su respaldo en PHP).
    const catalog = Array.isArray(window.NIDO_PRODUCTS) ? window.NIDO_PRODUCTS : [];

    const getCart = () => { try { return JSON.parse(localStorage.getItem(cartKey)) || []; } catch { return []; } };
    const saveCart = cart => { try { localStorage.setItem(cartKey, JSON.stringify(cart)); } catch { /* Almacenamiento bloqueado. */ } updateCount(); };
    const updateCount = () => { const count = getCart().reduce((sum, item) => sum + item.quantity, 0); document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = count); };
    // Códigos de los últimos pedidos hechos desde este navegador. Solo el código: para ver el
    // pedido sigue haciendo falta el teléfono.
    const getOrders = () => { try { const codes = JSON.parse(localStorage.getItem(ordersKey)); return Array.isArray(codes) ? codes.filter(code => /^[A-Z]{2}-[A-Z0-9]{6}$/.test(code)) : []; } catch { return []; } };
    const rememberOrder = code => { try { localStorage.setItem(ordersKey, JSON.stringify([code, ...getOrders().filter(saved => saved !== code)].slice(0, 5))); } catch { /* Almacenamiento bloqueado. */ } };
    const maxQuantity = 20; // Mismo límite por producto que aplica guardar_pedido.php.
    const addProduct = product => {
        const cart = getCart();
        const present = cart.find(item => (product.id && item.id === product.id) || item.name === product.name);
        if (present) { present.quantity = Math.min(maxQuantity, present.quantity + 1); if (product.id) present.id = product.id; } else cart.push({...product, quantity: 1});
        saveCart(cart);
    };

    document.addEventListener('click', event => {
        const button = event.target.closest('.add-to-cart');
        if (!button || button.disabled) return;
        addProduct({id: Number(button.dataset.id) || null, name: button.dataset.product, category: button.dataset.category, price: Number(button.dataset.price), image: button.dataset.image});
        const original = button.textContent; button.textContent = 'Añadido ✓';
        setTimeout(() => button.textContent = original, 1200);
    });

    const normalize = value => value.toLocaleLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    const stopwords = new Set(['de', 'del', 'la', 'el', 'los', 'las', 'al', 'por', 'con', 'y', 'a', 'en', 'un', 'una']);
    const searchCard = item => {
        const soldOut = item.status === 'agotado';
        const label = soldOut ? 'Agotado' : (item.status === 'encargo' ? 'Encargar' : 'Añadir');
        return `<article class="product-card search-card"><div class="product-image"><img src="${escapeHtml(assetUrl(item.image))}" alt="${escapeHtml(item.name)}"></div><div class="product-body"><span>${escapeHtml(item.category)}</span><h3>${escapeHtml(item.name)}</h3><div class="product-footer"><strong>${money(item.price)}</strong><button class="add-to-cart" data-id="${Number(item.id) || ''}" data-product="${escapeHtml(item.name)}" data-category="${escapeHtml(item.category)}" data-price="${Number(item.price)}" data-image="${escapeHtml(item.image)}"${soldOut ? ' disabled' : ''}>${label}</button></div></div></article>`;
    };
    const renderSearch = term => {
        const grid = document.querySelector('#search-results'); const summary = document.querySelector('#search-summary');
        if (!grid || !summary) return;
        const cleanTerm = term.trim();
        if (!cleanTerm) { grid.innerHTML = ''; summary.textContent = 'Escribe un término o elige una búsqueda popular.'; return; }
        const allWords = normalize(cleanTerm).split(/\s+/).filter(Boolean);
        const meaningfulWords = allWords.filter(word => !stopwords.has(word));
        const words = meaningfulWords.length ? meaningfulWords : allWords;
        const results = catalog.filter(item => {
            const haystackWords = normalize(`${item.name} ${item.category} ${item.group || ''}`).split(/\s+/).filter(Boolean);
            return words.every(word => haystackWords.some(hw => hw.startsWith(word) || word.startsWith(hw)));
        });
        summary.textContent = results.length ? `${results.length} resultado${results.length === 1 ? '' : 's'} para “${term}”.` : `No encontramos productos para “${term}”.`;
        grid.innerHTML = results.length ? results.map(searchCard).join('') : '<div class="search-empty">Prueba con “acuarela”, “retratos” o “camisetas”.</div>';
    };
    const searchForm = document.querySelector('#product-search-form');
    if (searchForm) {
        const input = document.querySelector('#product-search');
        searchForm.addEventListener('submit', event => { event.preventDefault(); renderSearch(input.value); });
        document.querySelectorAll('[data-search-term]').forEach(button => button.addEventListener('click', () => { input.value = button.dataset.searchTerm; renderSearch(input.value); }));
        if (input.value.trim()) renderSearch(input.value);
    }

    // Actualiza el carrito guardado con los datos actuales del catálogo (precio, nombre, imagen)
    // y retira lo que ya no se vende, para que el total mostrado coincida con el que cobra el servidor.
    const syncCartWithCatalog = () => {
        if (!catalog.length) return;
        const notice = document.querySelector('#cart-sync-notice');
        const changes = [];
        const synced = [];
        getCart().forEach(item => {
            const product = catalog.find(p => (item.id && p.id === item.id) || p.name === item.name);
            if (!product) { changes.push(`“${item.name}” ya no está disponible y se retiró.`); return; }
            if (product.status === 'agotado') { changes.push(`“${product.name}” se agotó y se retiró.`); return; }
            if (Number(product.price) !== Number(item.price)) changes.push(`El precio de “${product.name}” cambió a ${money(product.price)}.`);
            const existing = synced.find(line => line.id === product.id);
            if (existing) { existing.quantity = Math.min(maxQuantity, existing.quantity + item.quantity); return; }
            synced.push({id: product.id, name: product.name, category: product.category, price: Number(product.price), image: product.image, quantity: Math.min(maxQuantity, Math.max(1, Number(item.quantity) || 1))});
        });
        saveCart(synced);
        if (notice && changes.length) { notice.textContent = changes.join(' '); notice.hidden = false; }
    };

    const showConfirmation = (response, values) => {
        const box = document.querySelector('#order-confirmation');
        if (!box || !response.codigo) return;
        const lines = (response.productos || []).map(p => `• ${p.nombre} ×${p.cantidad}`);
        const message = [
            `Hola, acabo de hacer el pedido ${response.codigo} en la tienda.`,
            ...lines,
            response.subtotal ? `Subtotal: ${money(response.subtotal)}` : '',
            `Encargo: ${values.commission_type}`,
            `Nombre: ${values.name}`,
            values.is_gift ? `Es un regalo para: ${values.gift_for}` : '',
            response.fotoGuardada ? 'Adjunté la foto de referencia en el pedido.' : ''
        ].filter(Boolean).join('\n');
        box.innerHTML = `<h3>¡Pedido recibido!</h3><p>Guarda tu código para consultar el estado del pedido en cualquier momento.</p><p><span class="tracking-code">${escapeHtml(response.codigo)}</span></p><div class="order-confirmation-actions"><a target="_blank" rel="noopener" href="https://wa.me/573184597719?text=${encodeURIComponent(message)}">Enviar resumen por WhatsApp</a><a href="${escapeHtml(response.seguimiento || baseUrl + 'seguimiento.php')}">Ver seguimiento del pedido</a></div>`;
        box.hidden = false;
        box.scrollIntoView({behavior: 'smooth', block: 'nearest'});
    };

    const renderCart = () => {
        const itemsBox = document.querySelector('#cart-items'); if (!itemsBox) return;
        const cart = getCart(); const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        document.querySelectorAll('[data-cart-items-label]').forEach(el => el.textContent = `(${cart.reduce((sum, item) => sum + item.quantity, 0)})`);
        document.querySelectorAll('[data-cart-subtotal], [data-cart-total]').forEach(el => el.textContent = money(subtotal));
        document.querySelector('#order-lines').innerHTML = cart.length ? cart.map(item => `<p class="order-line"><span>${escapeHtml(item.name)} ×${Number(item.quantity)}</span><strong>${money(item.price * item.quantity)}</strong></p>`).join('') : '<p class="order-line"><span>Aún no hay productos.</span></p>';
        itemsBox.innerHTML = cart.length ? cart.map((item, index) => `<article class="cart-item"><img src="${escapeHtml(assetUrl(item.image))}" alt="${escapeHtml(item.name)}"><div><small>${escapeHtml(item.category)}</small><h3>${escapeHtml(item.name)}</h3><div class="quantity-control"><button data-cart-action="decrease" data-index="${index}" aria-label="Disminuir cantidad">−</button><span>${Number(item.quantity)}</span><button data-cart-action="increase" data-index="${index}" aria-label="Aumentar cantidad">+</button></div><button class="remove-item" data-cart-action="remove" data-index="${index}">Eliminar</button></div><strong class="item-price">${money(item.price * item.quantity)}</strong></article>`).join('') : `<div class="empty-cart">Tu carrito está vacío. <a href="${escapeHtml(baseUrl)}buscador.php">Explora el catálogo</a> para añadir productos.</div>`;
    };
    document.addEventListener('click', event => {
        const action = event.target.closest('[data-cart-action]'); if (!action) return;
        const cart = getCart(); const index = Number(action.dataset.index); if (!cart[index]) return;
        if (action.dataset.cartAction === 'increase') cart[index].quantity = Math.min(maxQuantity, cart[index].quantity + 1);
        if (action.dataset.cartAction === 'decrease') cart[index].quantity -= 1;
        if (action.dataset.cartAction === 'remove' || cart[index].quantity < 1) cart.splice(index, 1);
        saveCart(cart); renderCart();
    });

    // Envía un encargo a guardar_pedido.php. Lo usan el carrito y el formulario de contacto.
    // Si los valores traen una foto (File) se envía multipart: el JSON en "datos" y la imagen
    // en "foto". Sin foto se envía el JSON directamente.
    window.enviarPedido = (values, options = {}) => {
        const data = {...values};
        let photo = null;
        Object.entries(data).forEach(([name, value]) => {
            if (!(value instanceof File)) return;
            if (value.size) photo = value;
            delete data[name];
        });
        const headers = {'X-CSRF-Token': csrfToken};
        let body;
        if (photo) {
            body = new FormData();
            body.append('datos', JSON.stringify(data));
            body.append('foto', photo);
        } else {
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify(data);
        }
        // keepalive no admite cuerpos grandes, así que no se usa cuando hay foto.
        return fetch(baseUrl + 'guardar_pedido.php', {method: 'POST', headers, body, keepalive: !!options.keepalive && !photo})
            .then(response => response.json())
            .then(response => { if (response.ok && response.codigo) rememberOrder(response.codigo); return response; });
    };

    // En seguimiento.php: códigos recordados en este dispositivo, para volver al pedido sin buscarlo.
    const recentOrders = document.querySelector('#recent-orders');
    if (recentOrders) {
        const codes = getOrders();
        const codeInput = document.querySelector('.tracking-form [name="codigo"]');
        const phoneInput = document.querySelector('.tracking-form [name="telefono"]');
        if (codes.length && codeInput && phoneInput) {
            recentOrders.querySelector('[data-recent-orders-list]').innerHTML = codes.map(code => `<button type="button" data-order-code="${escapeHtml(code)}">${escapeHtml(code)}</button>`).join('');
            recentOrders.hidden = false;
            recentOrders.addEventListener('click', event => {
                const button = event.target.closest('[data-order-code]'); if (!button) return;
                codeInput.value = button.dataset.orderCode; phoneInput.focus();
            });
            if (!codeInput.value) codeInput.value = codes[0];
        }
    }

    // Foto de referencia (carrito y contacto): vista previa y validación de tipo y tamaño.
    // Mismos límites que guardar_pedido.php.
    const photoTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];
    const photoMaxBytes = 5000000;
    document.querySelectorAll('[data-photo-field]').forEach(field => {
        const input = field.querySelector('input[type="file"]');
        const preview = field.querySelector('[data-photo-preview]');
        const error = field.querySelector('[data-photo-error]');
        const image = preview.querySelector('img');
        let previewUrl = '';
        const refresh = () => {
            const file = input.files[0];
            let message = '';
            if (file && !photoTypes.includes(file.type)) message = 'Formato no válido. Usa una foto JPG, PNG, WEBP o AVIF.';
            else if (file && file.size > photoMaxBytes) message = 'La foto pesa más de 5 MB. Elige una más liviana o envíala por WhatsApp.';
            // Con un mensaje de validación, reportValidity() impide enviar el formulario.
            input.setCustomValidity(message);
            error.textContent = message;
            error.hidden = !message;
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = file && !message ? URL.createObjectURL(file) : '';
            preview.hidden = !previewUrl;
            if (previewUrl) { image.src = previewUrl; field.querySelector('[data-photo-name]').textContent = file.name; } else image.removeAttribute('src');
        };
        input.addEventListener('change', refresh);
        field.querySelector('[data-photo-remove]').addEventListener('click', () => { input.value = ''; refresh(); });
    });

    // Modo regalo: los campos aparecen y "¿Para quién es?" se vuelve obligatorio al marcar la casilla.
    document.querySelectorAll('[data-gift-section]').forEach(section => {
        const check = section.querySelector('input[name="is_gift"]');
        const fields = section.querySelector('[data-gift-fields]');
        const recipient = section.querySelector('input[name="gift_for"]');
        const toggle = () => { fields.hidden = !check.checked; recipient.required = check.checked; };
        check.addEventListener('change', () => { toggle(); if (check.checked) recipient.focus(); });
        toggle();
    });

    const customerForm = document.querySelector('#customer-form');
    if (customerForm) {
        const savedStatus = document.querySelector('#saved-data-status');
        const formValues = () => { const values = Object.fromEntries(new FormData(customerForm).entries()); delete values.save_data; return values; };
        // La foto y los datos del regalo son de este pedido: nunca se guardan en el dispositivo.
        const privateFields = ['reference_photo', 'is_gift', 'gift_for', 'gift_message'];
        const storableValues = () => { const values = formValues(); privateFields.forEach(name => delete values[name]); return values; };
        const rememberData = () => {
            try {
                if (customerForm.elements.save_data.checked) localStorage.setItem(customerDataKey, JSON.stringify(storableValues()));
                else localStorage.removeItem(customerDataKey);
            } catch { /* Almacenamiento bloqueado. */ }
        };
        try {
            const savedData = JSON.parse(localStorage.getItem(customerDataKey));
            if (savedData) {
                Object.entries(savedData).forEach(([name, value]) => {
                    const field = customerForm.elements[name];
                    if (field && !privateFields.includes(name) && !['checkbox', 'file'].includes(field.type)) field.value = value;
                });
                if (savedStatus) savedStatus.textContent = 'Datos recuperados para ti';
            }
        } catch { /* Los datos del formulario siguen disponibles aunque el navegador bloquee el almacenamiento. */ }

        customerForm.addEventListener('input', () => {
            if (!customerForm.elements.save_data.checked) return;
            rememberData();
            if (savedStatus) savedStatus.textContent = 'Datos guardados en este dispositivo';
        });
        customerForm.addEventListener('submit', event => {
            event.preventDefault(); const feedback = document.querySelector('#order-feedback');
            const setFeedback = (text, isError) => { feedback.textContent = text; feedback.classList.toggle('is-error', !!isError); };
            if (!getCart().length) { setFeedback('Añade al menos un producto antes de confirmar.', true); return; }
            if (!customerForm.reportValidity()) return;
            rememberData();
            setFeedback('Enviando tu encargo…', false);
            const values = formValues();
            const items = getCart().map(({id, name, quantity}) => ({id, name, quantity}));
            window.enviarPedido({...values, origen: 'carrito', items})
                .then(response => {
                    setFeedback(response.mensaje, !response.ok);
                    if (response.ok) {
                        saveCart([]);
                        renderCart();
                        showConfirmation(response, values);
                    } else if (response.cupoLleno) {
                        document.querySelectorAll('.confirm-order').forEach(button => { button.disabled = true; button.textContent = 'Agenda completa'; });
                    }
                })
                .catch(() => { setFeedback('No pudimos enviar el encargo. Inténtalo nuevamente.', true); });
        });
    }
    if (document.querySelector('#cart-items')) syncCartWithCatalog();
    updateCount(); renderCart();
})();

(() => {
    const cartKey = 'francy-tamayo-cart';
    const customerDataKey = 'francy-tamayo-customer-data';
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const money = value => new Intl.NumberFormat('es-CO', {style: 'currency', currency: 'COP', maximumFractionDigits: 0}).format(value);
    // Todo texto que venga del catálogo o del almacenamiento local se escapa antes de insertarlo como HTML.
    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[char]));
    const assetUrl = path => /^(https?:)?\/\//.test(path) || String(path).startsWith('/') ? path : baseUrl + path;
    // El catálogo lo imprime buscador.php desde la base de datos (o su respaldo en PHP).
    const catalog = Array.isArray(window.FRANCY_PRODUCTS) ? window.FRANCY_PRODUCTS : [];

    const getCart = () => { try { return JSON.parse(localStorage.getItem(cartKey)) || []; } catch { return []; } };
    const saveCart = cart => { try { localStorage.setItem(cartKey, JSON.stringify(cart)); } catch { /* Almacenamiento bloqueado. */ } updateCount(); };
    const updateCount = () => { const count = getCart().reduce((sum, item) => sum + item.quantity, 0); document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = count); };
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
            `Nombre: ${values.name}`
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
    window.enviarPedido = (values, options = {}) => fetch(baseUrl + 'guardar_pedido.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken},
        body: JSON.stringify(values),
        keepalive: !!options.keepalive
    }).then(response => response.json());

    const customerForm = document.querySelector('#customer-form');
    if (customerForm) {
        const savedStatus = document.querySelector('#saved-data-status');
        const formValues = () => { const values = Object.fromEntries(new FormData(customerForm).entries()); delete values.save_data; return values; };
        const rememberData = () => {
            try {
                if (customerForm.elements.save_data.checked) localStorage.setItem(customerDataKey, JSON.stringify(formValues()));
                else localStorage.removeItem(customerDataKey);
            } catch { /* Almacenamiento bloqueado. */ }
        };
        try {
            const savedData = JSON.parse(localStorage.getItem(customerDataKey));
            if (savedData) {
                Object.entries(savedData).forEach(([name, value]) => {
                    const field = customerForm.elements[name];
                    if (field && field.type !== 'checkbox') field.value = value;
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
                    }
                })
                .catch(() => { setFeedback('No pudimos enviar el encargo. Inténtalo nuevamente.', true); });
        });
    }
    if (document.querySelector('#cart-items')) syncCartWithCatalog();
    updateCount(); renderCart();
})();

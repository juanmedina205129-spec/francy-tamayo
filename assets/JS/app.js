(() => {
    const cartKey = 'francy-tamayo-cart';
    const customerDataKey = 'francy-tamayo-customer-data';
    const money = value => new Intl.NumberFormat('es-CO', {style: 'currency', currency: 'COP', maximumFractionDigits: 0}).format(value);
    const defaultCatalog = [
        ['Retrato Golden Retriever', 'Pintura al óleo', 'pinturas', 280000, 'assets/imagenes/productos/cuadro-golondrina.png'], ['Perro en acuarela', 'Acuarela', 'pinturas', 195000, 'assets/imagenes/productos/cuadro-jilguero.png'],
        ['Ave colorida - acuarela', 'Acuarela', 'pinturas', 175000, 'assets/imagenes/productos/cuadro-buho.png'], ['Martín pescador', 'Acuarela', 'pinturas', 160000, 'assets/imagenes/productos/estuche-golondrina.png'],
        ['Perro blanco - óleo', 'Pintura al óleo', 'pinturas', 260000, 'assets/imagenes/productos/estuche-plumas.png'], ['Retrato mascota personalizado', 'Retrato de mascotas', 'retratos', 220000, 'assets/imagenes/productos/cuadro-buho.png'],
        ['Retrato canino clásico', 'Retrato de mascotas', 'retratos', 250000, 'assets/imagenes/productos/cuadro-jilguero.png'], ['Retrato doble mascotas', 'Retrato de mascotas', 'retratos', 380000, 'assets/imagenes/productos/cojin-jilguero.png'],
        ['Camiseta Perro Acuarela', 'Camiseta', 'camisetas', 85000, 'assets/imagenes/productos/estuche-azulejo.png'], ['Camiseta Gato Minimalista', 'Camiseta', 'camisetas', 75000, 'assets/imagenes/productos/estuche-plumas.png'],
        ['Camiseta Tigre Estampado', 'Camiseta', 'camisetas', 90000, 'assets/imagenes/productos/estuche-golondrina.png'], ['Camiseta Mascota Personalizada', 'Camisetas personalizadas', 'camisetas', 110000, 'assets/imagenes/productos/estuche-buho.png']
    ].map(([name, category, group, price, image]) => ({name, category, group, price, image}));
    const catalog = Array.isArray(window.FRANCY_PRODUCTS) && window.FRANCY_PRODUCTS.length ? window.FRANCY_PRODUCTS : defaultCatalog;

    const getCart = () => { try { return JSON.parse(localStorage.getItem(cartKey)) || []; } catch { return []; } };
    const saveCart = cart => { localStorage.setItem(cartKey, JSON.stringify(cart)); updateCount(); };
    const updateCount = () => { const count = getCart().reduce((sum, item) => sum + item.quantity, 0); document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = count); };
    const addProduct = product => {
        const cart = getCart(); const present = cart.find(item => item.name === product.name);
        if (present) present.quantity += 1; else cart.push({...product, quantity: 1});
        saveCart(cart);
    };

    document.addEventListener('click', event => {
        const button = event.target.closest('.add-to-cart');
        if (!button || button.disabled) return;
        addProduct({name: button.dataset.product, category: button.dataset.category, price: Number(button.dataset.price), image: button.dataset.image});
        const original = button.textContent; button.textContent = 'Añadido ✓';
        setTimeout(() => button.textContent = original, 1200);
    });

    const normalize = value => value.toLocaleLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    const stopwords = new Set(['de', 'del', 'la', 'el', 'los', 'las', 'al', 'por', 'con', 'y', 'a', 'en', 'un', 'una']);
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
        grid.innerHTML = results.length ? results.map(item => `<article class="product-card search-card"><div class="product-image"><img src="${item.image}" alt="${item.name}"></div><div class="product-body"><span>${item.category}</span><h3>${item.name}</h3><div class="product-footer"><strong>${money(item.price)}</strong><button class="add-to-cart" data-product="${item.name}" data-category="${item.category}" data-price="${item.price}" data-image="${item.image}">Añadir</button></div></div></article>`).join('') : '<div class="search-empty">Prueba con “acuarela”, “retratos” o “camisetas”.</div>';
    };
    const searchForm = document.querySelector('#product-search-form');
    if (searchForm) {
        const input = document.querySelector('#product-search');
        searchForm.addEventListener('submit', event => { event.preventDefault(); renderSearch(input.value); });
        document.querySelectorAll('[data-search-term]').forEach(button => button.addEventListener('click', () => { input.value = button.dataset.searchTerm; renderSearch(input.value); }));
        if (input.value.trim()) renderSearch(input.value);
    }

    const renderCart = () => {
        const itemsBox = document.querySelector('#cart-items'); if (!itemsBox) return;
        const cart = getCart(); const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        document.querySelectorAll('[data-cart-items-label]').forEach(el => el.textContent = `(${cart.reduce((sum, item) => sum + item.quantity, 0)})`);
        document.querySelectorAll('[data-cart-subtotal], [data-cart-total]').forEach(el => el.textContent = money(subtotal));
        document.querySelector('#order-lines').innerHTML = cart.length ? cart.map(item => `<p class="order-line"><span>${item.name} ×${item.quantity}</span><strong>${money(item.price * item.quantity)}</strong></p>`).join('') : '<p class="order-line"><span>Aún no hay productos.</span></p>';
        itemsBox.innerHTML = cart.length ? cart.map((item, index) => `<article class="cart-item"><img src="${item.image}" alt="${item.name}"><div><small>${item.category}</small><h3>${item.name}</h3><div class="quantity-control"><button data-cart-action="decrease" data-index="${index}" aria-label="Disminuir cantidad">−</button><span>${item.quantity}</span><button data-cart-action="increase" data-index="${index}" aria-label="Aumentar cantidad">+</button></div><button class="remove-item" data-cart-action="remove" data-index="${index}">Eliminar</button></div><strong class="item-price">${money(item.price * item.quantity)}</strong></article>`).join('') : '<div class="empty-cart">Tu carrito está vacío. <a href="buscador.php">Explora el catálogo</a> para añadir productos.</div>';
    };
    document.addEventListener('click', event => {
        const action = event.target.closest('[data-cart-action]'); if (!action) return;
        const cart = getCart(); const index = Number(action.dataset.index); if (!cart[index]) return;
        if (action.dataset.cartAction === 'increase') cart[index].quantity += 1;
        if (action.dataset.cartAction === 'decrease') cart[index].quantity -= 1;
        if (action.dataset.cartAction === 'remove' || cart[index].quantity < 1) cart.splice(index, 1);
        saveCart(cart); renderCart();
    });
    const customerForm = document.querySelector('#customer-form');
    if (customerForm) {
        const savedStatus = document.querySelector('#saved-data-status');
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
            const saveChoice = customerForm.elements.save_data;
            if (!saveChoice.checked) return;
            const values = Object.fromEntries(new FormData(customerForm).entries());
            delete values.save_data;
            localStorage.setItem(customerDataKey, JSON.stringify(values));
            if (savedStatus) savedStatus.textContent = 'Datos guardados en este dispositivo';
        });
        customerForm.addEventListener('submit', event => {
            event.preventDefault(); const feedback = document.querySelector('#order-feedback');
            const setFeedback = (text, isError) => { feedback.textContent = text; feedback.classList.toggle('is-error', !!isError); };
            if (!getCart().length) { setFeedback('Añade al menos un producto antes de confirmar.', true); return; }
            if (!customerForm.reportValidity()) return;
            if (customerForm.elements.save_data.checked) {
                const values = Object.fromEntries(new FormData(customerForm).entries());
                delete values.save_data;
                localStorage.setItem(customerDataKey, JSON.stringify(values));
            } else localStorage.removeItem(customerDataKey);
            setFeedback('Enviando tu encargo…', false);
            const values = Object.fromEntries(new FormData(customerForm).entries());
            delete values.save_data;
            fetch('guardar_pedido.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({...values, items: getCart()})
            })
                .then(response => response.json())
                .then(response => {
                    setFeedback(response.mensaje, !response.ok);
                    if (response.ok) {
                        saveCart([]);
                        renderCart();
                    }
                })
                .catch(() => { setFeedback('No pudimos enviar el encargo. Inténtalo nuevamente.', true); });
        });
    }
    updateCount(); renderCart();
})();

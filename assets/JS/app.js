(() => {
    const cartKey = 'francy-tamayo-cart';
    const money = value => new Intl.NumberFormat('es-CO', {style: 'currency', currency: 'COP', maximumFractionDigits: 0}).format(value);
    const catalog = [
        ['Retrato Golden Retriever', 'Pintura al óleo', 280000, 'assets/imagenes/img/image9.png'], ['Perro en acuarela', 'Acuarela', 195000, 'assets/imagenes/img/image10.png'],
        ['Ave colorida - acuarela', 'Acuarela', 175000, 'assets/imagenes/img/image13.png'], ['Martín pescador', 'Acuarela', 160000, 'assets/imagenes/img/image14.png'],
        ['Perro blanco - óleo', 'Pintura al óleo', 260000, 'assets/imagenes/img/image15.png'], ['Retrato mascota personalizado', 'Retrato de mascotas', 220000, 'assets/imagenes/img/image10.png'],
        ['Retrato canino clásico', 'Retrato de mascotas', 250000, 'assets/imagenes/img/image15.png'], ['Retrato doble mascotas', 'Retrato de mascotas', 380000, 'assets/imagenes/img/image16.png'],
        ['Camiseta Perro Acuarela', 'Camiseta', 85000, 'assets/imagenes/img/image11.png'], ['Camiseta Gato Minimalista', 'Camiseta', 75000, 'assets/imagenes/img/image8.png'],
        ['Camiseta Tigre Estampado', 'Camiseta', 90000, 'assets/imagenes/img/image17.png'], ['Camiseta Mascota Personalizada', 'Camisetas personalizadas', 110000, 'assets/imagenes/img/image7.png']
    ].map(([name, category, price, image]) => ({name, category, price, image}));

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

    const renderSearch = term => {
        const grid = document.querySelector('#search-results'); const summary = document.querySelector('#search-summary');
        if (!grid || !summary) return;
        const cleanTerm = term.trim().toLocaleLowerCase();
        if (!cleanTerm) { grid.innerHTML = ''; summary.textContent = 'Escribe un término o elige una búsqueda popular.'; return; }
        const results = catalog.filter(item => `${item.name} ${item.category}`.toLocaleLowerCase().includes(cleanTerm) || (cleanTerm.includes('personalizadas') && item.category.includes('Camisetas')) || (cleanTerm.includes('retratos') && item.category.includes('Retrato')));
        summary.textContent = results.length ? `${results.length} resultado${results.length === 1 ? '' : 's'} para “${term}”.` : `No encontramos productos para “${term}”.`;
        grid.innerHTML = results.length ? results.map(item => `<article class="product-card search-card"><div class="product-image"><img src="${item.image}" alt="${item.name}"></div><div class="product-body"><span>${item.category}</span><h3>${item.name}</h3><div class="product-footer"><strong>${money(item.price)}</strong><button class="add-to-cart" data-product="${item.name}" data-category="${item.category}" data-price="${item.price}" data-image="${item.image}">Añadir</button></div></div></article>`).join('') : '<div class="search-empty">Prueba con “acuarela”, “retratos” o “camisetas”.</div>';
    };
    const searchForm = document.querySelector('#product-search-form');
    if (searchForm) {
        const input = document.querySelector('#product-search');
        searchForm.addEventListener('submit', event => { event.preventDefault(); renderSearch(input.value); });
        document.querySelectorAll('[data-search-term]').forEach(button => button.addEventListener('click', () => { input.value = button.dataset.searchTerm; renderSearch(input.value); }));
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
    if (customerForm) customerForm.addEventListener('submit', event => {
        event.preventDefault(); const feedback = document.querySelector('#order-feedback');
        if (!getCart().length) { feedback.textContent = 'Añade al menos un producto antes de confirmar.'; return; }
        feedback.textContent = 'Pedido preparado. Te contactaremos por WhatsApp para confirmar los detalles y el pago.';
    });
    updateCount(); renderCart();
})();

document.addEventListener("DOMContentLoaded", () => {
    const dialog = document.getElementById("delete-product-dialog");
    const productId = document.getElementById("delete-product-id");
    const productName = document.getElementById("delete-product-name");

    if (!dialog || !productId || !productName) return;

    document.addEventListener("click", (event) => {
        const button = event.target.closest("[data-delete-product]");
        if (!button) return;
        productId.value = button.dataset.productId || "";
        productName.textContent = button.dataset.productName || "este producto";
        dialog.showModal();
    });

    document.querySelector("[data-delete-cancel]")?.addEventListener("click", () => dialog.close());
    dialog.addEventListener("click", (event) => {
        if (event.target === dialog) dialog.close();
    });
});


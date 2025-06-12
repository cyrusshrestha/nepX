document.querySelectorAll('.add-to-cart').forEach(button => {
  button.addEventListener('click', function () {
    const productId = this.getAttribute('data-id');
    const isUsed = this.getAttribute('data-used') === '1' ? 1 : 0;
    const btn = this;
    btn.disabled = true;

    const bodyData = new URLSearchParams({
      action: 'add_to_cart',
      product_id: productId,
      quantity: 1,
      is_used: isUsed
    });

    fetch('/nepX/server/controller.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: bodyData
    })
    .then(res => res.json())
    .then(data => {
      Swal.fire({
        icon: data.success ? 'success' : 'error',
        title: data.message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000
      });

      if (data.cart_count !== undefined) {
        const cartCount = document.getElementById('cart-count');
        if (cartCount) cartCount.textContent = data.cart_count;
      }
    })
    .catch(err => {
      Swal.fire({
        icon: 'error',
        title: 'Failed to add to cart',
        text: err.message,
        toast: true,
        position: 'top-end',
        timer: 3000,
        showConfirmButton: false
      });
    })
    .finally(() => {
      btn.disabled = false;
    });
  });
});
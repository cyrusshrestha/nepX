// Wishlist button handler with SweetAlert toast
document.querySelectorAll('.wishlist-btn').forEach(button => {
  button.addEventListener('click', function () {
    const productId = this.getAttribute('data-id');

    fetch('/nepX/server/controller.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: `action=add_to_wishlist&product_id=${productId}`
    })
    .then(res => res.json())
    .then(data => {
      Swal.fire({
        icon: data.success ? 'success' : 'error',
        title: data.message,
        toast: true,
        timer: 2000,
        showConfirmButton: false,
        position: 'top-end'
      });
    })
    .catch(error => {
      Swal.fire({
        icon: 'error',
        title: 'Wishlist Error',
        text: error.message,
        toast: true,
        timer: 3000,
        showConfirmButton: false,
        position: 'top-end'
      });
    });
  });
});

// Cart button handler with SweetAlert toast
document.querySelectorAll('.add-to-cart').forEach(button => {
  button.addEventListener('click', function () {
    const productId = this.getAttribute('data-id');

    fetch('/nepX/server/controller.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: `action=add_to_cart&product_id=${productId}`
    })
    .then(res => res.json())
    .then(data => {
      Swal.fire({
        icon: data.success ? 'success' : 'error',
        title: data.message,
        toast: true,
        timer: 2000,
        showConfirmButton: false,
        position: 'top-end'
      });

      // Optionally update cart count
      if (data.cart_count !== undefined) {
        const countElem = document.getElementById('cart-count');
        if (countElem) countElem.textContent = data.cart_count;
      }
    })
    .catch(error => {
      Swal.fire({
        icon: 'error',
        title: 'Cart Error',
        text: error.message,
        toast: true,
        timer: 3000,
        showConfirmButton: false,
        position: 'top-end'
      });
    });
  });
});
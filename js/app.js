<script>
document.querySelectorAll('.add-to-cart').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const productId = this.getAttribute('data-id');

    fetch('/nepX/pages/cart.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('✅ Added to cart!');
        if (document.getElementById('cart-count')) {
          document.getElementById('cart-count').textContent = data.cart_count;
        }
      } else {
        alert('❌ Failed to add to cart.');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Something went wrong.');
    });
  });
});
</script>
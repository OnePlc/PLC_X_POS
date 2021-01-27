$(function () {
   $('.pos-checkout-add').on('click', function () {
       var fPrice = parseFloat($(this).attr('plc-article-price'));
       var iArticleID = parseInt($(this).attr('plc-article-id'));
       var fTotal = parseFloat($('h1.checkout-total').html());
       var sLabel = $(this).attr('plc-article-label');
       var fNewTotal = fTotal+fPrice;
       $('h1.checkout-total').html(fNewTotal.toFixed(2));
       console.log('Add '+fPrice.toFixed(2));

       $.post('/pos/checkout/list', {item_id:iArticleID,item_price:fPrice,item_label:sLabel}, function(retHTML) {
           $('.pos-checkout-list').html(retHTML);
       });

       return false;
   });
});
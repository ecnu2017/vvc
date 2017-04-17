$("#appendPayment").click(function(){
    var payment = '<hr>\
    <div class="form-group">\
        <label for="name">Name</label>\
        <input id="name" type="text" class="form-control" name="paymentName[]" placeholder="What is this payment for" required>\
    </div>\
    <div class="form-group">\
        <label for="cost">Price</label>\
        <input id="cost" type="number" min="0" step="any" class="form-control" name="paymentCost[]" placeholder="Price for one" required>\
    </div>\
    <div class="form-group">\
        <label for="number">Quantity</label>\
        <input id="number" type="number" min="0" step="1" class="form-control" name="paymentNumber[]" placeholder="Quantity" required>\
    </div>';
    $("#paymentsDiv").append(payment);
});

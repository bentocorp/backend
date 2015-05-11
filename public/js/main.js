/******************************************************************************
 * This application
 * bt = Bento
 */
(function() {
  var bt = {};
    
  window.bt = bt;
})();


/******************************************************************************
 * DInv class
 * Driver Inventory
 * 
 * @return singleton closure
 */
bt.DInv = (function() {
    
    var that = {};
    
    that.change = function(event) {
        ele = $(event.target);
        
        // Elements
        var infoEle = ele.siblings('.dinv-qty-changeinfo');
        var diffEle = infoEle.children('.dinv-qty-diff');
        
        var currentQty = ele.val();
        var origQty = infoEle.children('.dinv-qty-orig').text();
        
        var diffQty = currentQty - origQty;
        
        diffEle.text(diffQty);
        
        if (diffQty !== 0)
            infoEle.removeClass('hidden');
        else
            infoEle.addClass('hidden');
    }
    
    return that;
})();


/***************************************
 * On document ready
 */
$(document).ready(function() {

    // Driver quantities, input box
    $('input.dinv-qty-input').attr('autocomplete', 'off');
    $('input.dinv-qty-input').on('keyup blur change', bt.DInv.change);
  
});

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
 * Driver Inventory Merging
 * 
 * @return singleton closure
 */
bt.DInv.Merge = (function() {
    
    var that = {};
    
    that.validateCheckedCount = function() {
        
        var n = $("#dinv-table input[name='drivers[]']:checked").length;
        
        // Make the Merge button active
        if (n > 1)
            $('#dinv-btn-merge').removeAttr('disabled');
        else
            $('#dinv-btn-merge').attr('disabled', 'disabled');
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
    
    // Driver checkboxes
    $("input[name='drivers[]']").on('change', bt.DInv.Merge.validateCheckedCount);
  
});

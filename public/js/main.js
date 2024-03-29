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
        var diffInput = infoEle.children('input[name="dinv-qty-diff-input"]');
        
        var currentQty = ele.val();
        var origQty = infoEle.children('.dinv-qty-orig').text();
        
        var diffQty = currentQty - origQty;
        
        // Set the difference
        diffEle.text(diffQty);
        diffInput.val(diffQty);
        
        // If there's no difference, we don't need to see the helper info
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
    
    var checked;
    
    // Enable/Disable the merge button. Enable if >2 drivers are checked
    that.validateCheckedCount = function() {
        
        checked = $("#dinv-table input.dinv-array-item:checked");
        var n = checked.length;
        
        // Make the Merge button active
        if (n > 1)
            $('#dinv-btn-merge').removeAttr('disabled');
        else
            $('#dinv-btn-merge').attr('disabled', 'disabled');
    }
    
    
    // Show the modal
    that.modal = function() {
        
        // First, populate the dropdown
        
        $('#dinv-win-merge-select').html('');
        
        checked.each(function(){
            var ele = $(this);
            var id = ele.val();
            var name = ele.attr('driver-name');
            
            var str = '<option value="'+id+'">'+name+'</option>';
                     
            $('#dinv-win-merge-select').append(str);
        });
        
        // Last, show the modal
        $('#dinv-win-merge').modal('show');
    }
    
    
    that.do = function() {
        
        // The user Id to merge TO
        var toId = $('#dinv-win-merge-select').val();
        
        // Do special things to the person receiving the goods, like hightlight green
        var toTr = $('#dinv-table-tr-'+toId);
        toTr.addClass('success');
        //toTr.removeClass( "success", 50000); // not working
        
        
        // Loop through the checked drivers, and make it happen!
        checked.each(function() {
            
            // Receive = toId
            // A sender (person to be zero'd out) = fromId
            
            // Uncheck no matter what
            $(this).attr('checked', false);
            
            // --- Skip the person receiving the goods
            var fromId = $(this).val();

            if (fromId == toId)
                return true;
            // ---
            
            // Otherwise continue
            
            // Get the inputs for this row
            var inputs = $('#dinv-table-tr-'+fromId+' input.dinv-qty-input');
            
            // For each from input...
            inputs.each(function() {
                // Get the from name and value
                var fromName = $(this).attr('name');
                var fromQty = parseInt( $(this).val() );
                
                // Add the fromQty to the target toQty
                var toInput = $('#dinv-table-tr-'+toId+' input[name="'+fromName+'"]');
                var toQty = parseInt( toInput.val() );
                toInput.val(fromQty + toQty);
                
                // Set the old fromQty to 0
                $(this).val(0);
                
                // Add color 
                $('#dinv-table-tr-'+fromId).addClass('danger');
                
                // Trigger the change event
                $(this).change();
                toInput.change();
            });
            
            // Add to the zero array, so the backend can simultaneously zero out the other drivers
            var zeroInput = $('#dinv-table-tr-'+toId+' input[name="zeroArray"]');
            var zeroVal = zeroInput.val();
            zeroInput.val(zeroVal + fromId + ','); // equiv of .=
            
        }); // End each row
        
        // Disable the other save buttons
        $(".dinv-table-tr").each(function(){
            
            var driverId = $(this).attr('dinv-table-tr-id');
            var btn = $(this).find('button.dinv-btn-save');
            
            if (toId != driverId)
                btn.attr('disabled', 'disabled');
            else {
                btn.addClass('btn-success');
                btn.effect( "pulsate", {times:100}, 200000 );
            }
        });
        
        // Last, hide the modal and disable the button
        $('#dinv-win-merge').modal('hide');
        $('#dinv-btn-merge').attr('disabled', 'disabled');
    }
    
    return that;
})();


/******************************************************************************
 * Clock class
 * 
 * @return singleton closure
 */
bt.Clock = (function() {
    
    var that = {};
    
    that.getLocalDate = function() 
    {
        var now = moment();
        now.tz(moment.tz.guess());
        
        return now.format('YYYY-MM-DD');
    }
    
    return that;
})();



/***************************************
 * On document ready
 */
$(document).ready(function() {
    
    // Tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Driver quantities, input box
    $('input.dinv-qty-input').attr('autocomplete', 'off');
    $('input.dinv-qty-input').on('keyup blur change', bt.DInv.change);
    
    // Driver checkboxes
    $("input.dinv-array-item").on('change', bt.DInv.Merge.validateCheckedCount);
    
    // Disable the various form submit & status/inventory save buttons when clicked, to avoid "double tapping"
    
    // # Generic forms (but this won't catch <forms> in <tr>'s)
    $('input[type=submit]').click(function() {
        $(this).attr('disabled', 'disabled');
        $(this).parents('form').submit();
    });
    
    // # This will catch <forms> in <tr>'s
    $('button.dinv-btn-save, button.order-status-btn-save').on('click', function() {
        $(this).attr('disabled', 'disabled');
        $(this).parents('tr').find('form').submit();
    });
    
    // # Everything else you want
    $('.disable-onclick').on('click', function() {
        $(this).addClass('disabled');
    });
    // ** end disabling
    
    
    // Timezone conversions
    $(".utcToLoc").each(function(){
        
        var utc = $(this).html();
        var createdUtc = moment.tz(utc, "UTC");
        var createdLoc = createdUtc.tz("America/Los_Angeles");
        
        $(this).html(createdLoc.format('YYYY-MM-DD HH:mm:ss z'));
    });
    
    // Countdown Timer for the OA Screen
    //console.log(bt.Clock.getLocalDate());
    var today = bt.Clock.getLocalDate();
    var lunchCutoff = moment.tz(today+" 10:00", "America/Los_Angeles");

    $('#countdown-lunch').countdown(lunchCutoff.toDate(), function(event) {
        $(this).html(event.strftime('%H:%M:%S'));
    });
          
});

jQuery.noConflict();

update();

//var s = document.createElement("script");
//s.src = chrome.extension.getURL("inject-override.js");
//(document.head||document.documentElement).appendChild(s);

setInterval(update, 1000);

function update() {

    if (jQuery(".min-max").length > 0) {
        return
    }
    console.log("update");
    //minimize/maximize Account-gegevens
    var collapsableMenu1 = "<div><a class='min-max' id='collapse-menu1'>[•] Toggle Account-gegevens</a></div>";
    jQuery(collapsableMenu1).insertBefore("#order-form_account");
    if (jQuery("#email").val() != "") {
        jQuery("#order-form_account").hide();
    }

    jQuery("#collapse-menu1").on('click', function() {
        event.stopPropagation();
        jQuery("#order-form_account").slideToggle();
    });


    //minimize/maximize adress
    var collapsableMenu2 = "<div><a class='min-max' id='collapse-menu2'>[•] Toggle Addres</a></div>";
    jQuery(collapsableMenu2).insertBefore("#order-addresses");
    if (jQuery("#order-billing_address_firstname").val() != "") {
        jQuery("#order-addresses").hide();
    }

    jQuery("#collapse-menu2").on('click', function() {
        event.stopPropagation();
        jQuery("#order-addresses").slideToggle();
    });

    //minimize/maximize Gift Options
    var $giftMessage = jQuery("#order-giftmessage").parent().parent();
    var collapsableMenu3 = "<div><a class='min-max' id='collapse-menu3'>[•] Toggle Gift Options</a></div>";
    jQuery(collapsableMenu3).insertBefore($giftMessage);
    $giftMessage.hide();

    jQuery("#collapse-menu3").on('click', function() {
        event.stopPropagation();
        $giftMessage.slideToggle();
    });


    jQuery('.min-max').hover(function() {
        jQuery(this).css('cursor','pointer');
    });


    //init shipping method
    /*
    var initShipping = "<div style='padding-left: 30px;'><a onclick='order.loadShippingRates();order.setShippingMethod(\"freeadminshipping_freeadminshipping\")' id='initShipping'>No Shipping</a></div>";
    jQuery(initShipping).insertBefore("#order-shipping_method");
*/

    //does not work: resets again
    //jQuery('#send_confirmation').prop('checked', false);


    ///select Contant radio
    //jQuery('#p_method_checkmo').prop('checked',true);

    //init shipping
    //setTimeout(window.loadShipping, 2000);
}

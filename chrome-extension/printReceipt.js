jQuery.noConflict();

if (jQuery(".head-sales-order").length > 0) {

    var printButton = '<button class="printReceipt" title="Print Receipt" type="button" style=""><span>Print Receipt</span></button>';

    var cacheNr = "?39";

    jQuery(".form-buttons").append(printButton);

    jQuery(".printReceipt").on("click", function() {

        resetPasswordAndSendMailToFirstTimeUsers();

        var popup=window.open();
        popup.document.open();

        var html;
        var bodyTxt = "";

        jQuery.ajax({
            url : "http://www.kokettekatinka.be/print-receipt/receipt-header.html?" + cacheNr,
            dataType: "text",
            success : function (header) {

                html = header;

                bodyTxt = "<p class='small'>" + jQuery(".head-sales-order").first().text() + "</p>";
                bodyTxt += "<hr>";
                bodyTxt += "<table  width='100%'>";

                jQuery(".order-tables tbody").children("tr.border").each(function( index ) {
                    bodyTxt += "<tr>";
                    var $row = jQuery( this );
                    bodyTxt += "<td>" + $row.find("td .qty-table td:nth-child(2) strong").html() + "&nbsp;x</td>";
                    var description = $row.find("td:first .title").text()
                    $row.find("td:first .title").next().find("strong").remove()
                    var sku = $row.find("td:first .title").next().text().trim().split("-").join("&#8209;");
                    bodyTxt += "<td>" + description + " <span class='sku'>#" + sku + "</span>" + "</td>";
                    bodyTxt += "<td class='price'>" + $row.find("td.last").html() + "</td>";
                    bodyTxt += "</tr>";
                });

                bodyTxt += "</table>";
                bodyTxt += "<hr>";

                bodyTxt += "<table width='100%'>";

                var $totals = jQuery(jQuery(".order-totals tbody")[0]);
                var totals = $totals.html();
                totals = totals.split("Subtotaal").join("Totaal Excl. BTW");
                var $grandTotal = jQuery(jQuery(".order-totals tfoot .price")[0]);

                bodyTxt += "<tr>";
                bodyTxt += "<td colspan='2'>" + totals + "</td>";
                bodyTxt += "</tr>";

                bodyTxt += "<tr>";
                bodyTxt += "<td><b>Totaal Incl. BTW</b></td>";
                bodyTxt += "<td class='price'><b>" + $grandTotal.html() + "</b></td>";
                bodyTxt += "</tr>";

                bodyTxt += "</table>";

                html = html.split("{{body}}").join(bodyTxt);

                jQuery.ajax({
                    url : "http://www.kokettekatinka.be/print-receipt/receipt-footer.html" + cacheNr,
                    dataType: "text",
                    success : function (footer) {

                        html = html.split("{{footer}}").join(footer);

                        popup.document.write(html);

                        popup.focus();
                        popup.print();
                        //popup.document.close();
                        //popup.close();
                       // window.onfocus = function() { window.close(); }
                    }
                });

            }
        });
    });

    var calculator = '' +
        '<li style="padding: 1em 2.5em .28em 1.5em">' +
        '   <h3>Calculator</h3>' +
        '   <div style="text-align: right;" >' +
        '       <p>Betaald: ' +
        '           <input style="width:70px;text-align: right;" type="number" id="input-payed" value="">' +
        '       </p>' +
        '       <p>Te betalen: ' +
        '           <input style="width:70px;text-align: right;" type="text" id="input-to-pay" readonly value="' + jQuery(jQuery(".order-totals .price")[0]).text().split(",").join(".") + '">' +
        '       </p>' +
        '       <p>Terug: ' +
        '           <input style="width:70px;text-align: right;" type="text" id="input-back" readonly value="">' +
        '       </p>' +
        '   </div>' +
        '</li>';

    jQuery("#sales_order_view_tabs").append(calculator);
    jQuery("#input-payed").bind('keyup mouseup', function() {
        var back = parseFloat(jQuery("#input-payed").val()) - parseFloat(jQuery("#input-to-pay").val().substr(1));
        if (isNaN(back)) {
            back = "?";
        } else {
            if (back < 0) {
                back = "?"
            } else {
                back = "€" + back.toMoney(2);
            }
        }
        jQuery("#input-back").val(back);
    });


   //jQuery("#sales_order_view_tabs_order_info_content .entry-edit:has(h4:contains('Gift Options'))").css("display", "none");

    /*
     decimal_sep: character used as deciaml separtor, it defaults to '.' when omitted
     thousands_sep: char used as thousands separator, it defaults to ',' when omitted
     */
    Number.prototype.toMoney = function(decimals, decimal_sep, thousands_sep)
    {
        var n = this,
            c = isNaN(decimals) ? 2 : Math.abs(decimals), //if decimal is zero we must take it, it means user does not want to show any decimal
            d = decimal_sep || '.', //if no decimal separator is passed we use the dot as default decimal separator (we MUST use a decimal separator)

        /*
         according to [http://stackoverflow.com/questions/411352/how-best-to-determine-if-an-argument-is-not-sent-to-the-javascript-function]
         the fastest way to check for not defined parameter is to use typeof value === 'undefined'
         rather than doing value === undefined.
         */
            t = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep, //if you don't want to use a thousands separator you can pass empty string as thousands_sep value

            sign = (n < 0) ? '-' : '',

        //extracting the absolute value of the integer part of the number and converting to string
            i = parseInt(n = Math.abs(n).toFixed(c)) + '',

            j = ((j = i.length) > 3) ? j % 3 : 0;
        return sign + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "€1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : '');
    }
}

function resetPasswordAndSendMailToFirstTimeUsers() {


    var custId;
    jQuery("a").each(function() {
        //get id out of this: //http://www.kokettekatinka.be/index.php/admin123/customer/edit/id/5/key/007d41da8b62582ad94d714c44ea6a65/
        var url = jQuery(this).attr("href");
        if (url.indexOf("edit/id")>-1) {
            url = url.split("edit/id/")[1];
            custId = url.split("/")[0];
        }
    });
    //console.log("cust id: " + custId);
    jQuery.ajax({
        cache:false,
        dataType:"html",
        type:"get",
        data: { custid:custId },
        url: "http://www.kokettekatinka.be/stogoSendMailToNewCustomer.php"
    }).done(function(response) {
            jQuery("<p style='color: #eb5e00;'>" + response + "</p>").insertAfter(jQuery(".content-header"));
        });

}
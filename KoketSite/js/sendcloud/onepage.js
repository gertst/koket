
document.addEventListener('DOMContentLoaded', function() {
    if (typeof SC_enabled == 'undefined') {
        return;
    }
    var isLoaded = false;
    var isLoading = false;
    var url = SC_url;
    var child;
    var location_picker_input;
    var eventListeners = [];
    var state = "waiting"; // waiting, unknown, activated, deactivated

    function loadScript(callback)
    {
        // Adding the script tag to the head as suggested before
        var head = document.getElementsByTagName('head')[0];
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = url;

        // Then bind the event to the callback function.
        // There are several events for cross browser compatibility.
        script.onreadystatechange = callback;
        script.onload = callback;

        // Fire the loading
        head.appendChild(script);

        location_picker_input = document.createElement('input');
        location_picker_input.type = "hidden";
        location_picker_input.name = "location_picker";

        var form = document.querySelector("#co-shipping-method-form");
        form.appendChild(location_picker_input);
    }

    function loadElement() {
        SC.spp.options = {
            api_key: SC_public_key,
            type: 'modal', // or include
            language: SC_language,
            country: getCountry()
        };

        SC.spp.onselect(function(service_point) {
            location_picker_input.value = service_point + jQuery(".list-group-item.ng-scope.active").text();
//        console.log("gert: ");
//        console.log(service_point);
//        console.log("$$$$$$$$$" + jQuery(".list-group-item.ng-scope.active").text());
//        jQuery("#ordercomment-comment").val(jQuery(".list-group-item.ng-scope.active").text());
        });

        SC.spp.start(function(response) {
            isLoading = false;
            isLoaded = true;
            if(response.error) {
                // give warning
                child.innerHTML = '<b>Our apologies for the inconvience</b> '+response.error;
            } else {
                child.innerHTML = response.body;
            }
        });

        SC.spp.setcountry(getCountry());
    }

    function getCountry() {
        var ifSame = document.querySelector("[name='shipping[same_as_billing]']").value;
        if (ifSame) {
            return document.querySelector("[name='billing[country_id]']").value;
        } else {
            return document.querySelector("[name='shipping[country_id]']").value;
        }
    }

    function addElement(method) {
        if(method) {
            if(!isLoaded) {
                child = document.createElement('div');
                child.innerHTML = "Loading...";
                isLoading = true;

                loadScript(loadElement);
            } else {
                SC.spp.setcountry(getCountry());
            }
            method.parentNode.appendChild(child);
        }
    }

    function removeElement() {
        if(child && child.parentNode) {
            child.parentNode.removeChild(child);
        }
    }

    function onChangeMethod(e) {
        if(isLoading) return;
        removeElement();
        for (var i = 0; i < SC_shippingmethods.length; i++) {
            var method = document.querySelector('#s_method_'+SC_shippingmethods[i]);
            if(!method) continue;

            if(method.checked) {
                addElement(method);
            }
        }
    }

    function removeCheck() {
        // remove all bindings
        for (var i = 0; i < eventListeners.length; i++) {
            eventListeners[i].removeEventListener('click', onChangeMethod);
        }
        eventListeners = [];
    }

    function check() {
        removeCheck()
        // find all methods and listen to the clicks
        var methods = document.querySelectorAll('input[name=shipping_method]');
        for (var i = 0; i < methods.length; i++) {
            methods[i].addEventListener('click', onChangeMethod);
            eventListeners.push(methods[i]);
        }
    }

    var dd = document.querySelector('#opc-shipping_method');
    setInterval(function () {
        if(isLoading) return;
        var isActive = dd.className.indexOf("active") > -1;
        switch (state) {
            case "waiting":
                if(isActive) {
                    state = "unknown";
                    check();
                    onChangeMethod();
                }
                break;
            default:
                if(!isActive) {
                    state = "waiting";
                    removeCheck();
                    removeElement();
                }
                break;
        }
    }, 1000);
}, false);

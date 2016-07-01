
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

    var form = document.querySelector("#onestepcheckout-form");
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
      location_picker_input.value = service_point;
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
    var ifSame = document.querySelector("[name='billing[use_for_shipping]']").value;
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

  function onChangeMethod(e) {
    if(isLoading) return;
    if(child && child.parentNode) {
      child.parentNode.removeChild(child);
    }
    for (var i = 0; i < SC_shippingmethods.length; i++) {
      var method = document.querySelector('#s_method_'+SC_shippingmethods[i]);
      if(!method) continue;

      if(method.checked) {
        addElement(method);
      }
    }
  }

  function check() {
    // remove all bindings
    for (var i = 0; i < eventListeners.length; i++) {
      eventListeners[i].removeEventListener('click', onChangeMethod);
    }
    eventListeners = [];

    // find all methods and listen to the clicks
    var methods = document.querySelectorAll('input[name=shipping_method]');
    for (var i = 0; i < methods.length; i++) {
      methods[i].addEventListener('click', onChangeMethod);
      eventListeners.push(methods[i]);
    }
  }
  check();
  onChangeMethod();

  // onestepcheckout changes the divs alot, that's why we check on modifications
  document.querySelector('.onestepcheckout-shipping-method-block').addEventListener('DOMSubtreeModified', function(e) {
    if (e.target.innerHTML && e.target.innerHTML.length > 0) {
      if(isLoading) return;

      // this function can always be executed, it just removes and adds events
      check();

      // detect if child got removed
      var block = document.querySelector('.onestepcheckout-shipping-method-block');
      if(block.childNodes.length > 0 && block.childNodes[0].className == "loading-ajax"){
        if(child && child.parentNode) {
          child.parentNode.removeChild(child);
        }
      }

      // check on change method when subtree got changed
      if(child == null || child.parentNode == null) {
        onChangeMethod();
      }
    }
  });
}, false);

<script src="https://js.braintreegateway.com/web/3.103.0/js/client.min.js"></script>
<script src="https://js.braintreegateway.com/web/3.103.0/js/apple-pay.min.js"></script>

<script>
if (window.ApplePaySession && ApplePaySession.supportsVersion(3) && ApplePaySession.canMakePayments()) {
    console.log('This device supports Apple Pay.');
  } else {
    console.error('Apple Pay is not supported on this device.');
  }

  braintree.client.create({
  authorization: 'sandbox_zj2w9j8j_fpvw2bhbc27rx48f'
}, function (clientErr, clientInstance) {
  if (clientErr) {
    console.error('Error creating client:', clientErr);
    return;
  }

  braintree.applePay.create({
    client: clientInstance
  }, function (applePayErr, applePayInstance) {
    if (applePayErr) {
      console.error('Error creating applePayInstance:', applePayErr);
      return;
    }

    // Set up your Apple Pay button here
    var applePayButton = document.createElement('button');
    applePayButton.id = 'apple-pay-button';
    applePayButton.textContent = 'Buy with Apple Pay';
    document.body.appendChild(applePayButton);

    applePayButton.addEventListener('click', function () {
      startApplePaySession(applePayInstance);
    });
  });
});

function startApplePaySession(applePayInstance) {
  var paymentRequest = applePayInstance.createPaymentRequest({
    total: {
      label: 'Demo Shop',
      amount: '10.00'
    },
    supportedNetworks: ['visa', 'masterCard', 'amex'],
    countryCode: 'US',
    currencyCode: 'USD'
  });

  var session = new ApplePaySession(3, paymentRequest);

  session.onvalidatemerchant = function (event) {
    applePayInstance.performValidation({
      validationURL: event.validationURL,
      displayName: 'Demo Shop'
    }, function (validationErr, merchantSession) {
      if (validationErr) {
        console.error('Error validating merchant:', validationErr);
        session.abort();
        return;
      }
      session.completeMerchantValidation(merchantSession);
    });
  };

  session.onpaymentauthorized = function (event) {
    applePayInstance.tokenize({
      token: event.payment.token
    }, function (tokenizeErr, payload) {
      if (tokenizeErr) {
        console.error('Error tokenizing payment:', tokenizeErr);
        session.completePayment(ApplePaySession.STATUS_FAILURE);
        return;
      }
      console.log('Payment tokenized:', payload);
      session.completePayment(ApplePaySession.STATUS_SUCCESS);
    });
  };

  session.begin();
}


</script>
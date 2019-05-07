

require([
    'jquery',
    'mage/url'
], function($, url) {
    'use strict';

    // Prepare the required variables
    var $form = document.getElementById('embeddedForm');
    var $formHolder = $('#cko-form-holder');
    var $addFormButton = $('#show-add-cc-form');
    var $submitFormButton = $('#add-new-cc-card');
    var ckoPublicKey = $('#cko-public-key').val();
    var ckoTheme = $('#cko-theme').val();

    $(document).ready(function() {
        // Add card controls
        $addFormButton.click(function() {
            $formHolder.show();
            $addFormButton.hide();
        });

        // Submit card form controls
        $submitFormButton.click(function(e) {
            e.preventDefault();
            Frames.submitCard().then(function(data) {
                Frames.addCardToken($form, data.cardToken);
                chargeWithCardToken(data);
            })
        });

        // Initialise the embedded form
        Frames.init({
            publicKey: ckoPublicKey,
            containerSelector: '#embeddedForm',
            cardValidationChanged: function() {
                //$submitFormButton.prop("disabled", !Frames.isCardValid());
            }
        });              

        // Card storage function
        function chargeWithCardToken(ckoResponse) {

            // Set the storage controller URL
            var storageUrl = url.build('thevaultapp/cards/store');

            // Prepare the request data
            var requestObject = { "ckoCardToken": ckoResponse.cardToken };

            // Perform the storage request
            $.ajax({
                type: "POST",
                url: storageUrl,
                data: JSON.stringify(requestObject),
                success: function(res) {},
                error: function(request, status, error) {
                    alert(error);
                }
            }).done(function(data) {
                window.location.reload();
            });
        }
    });
});
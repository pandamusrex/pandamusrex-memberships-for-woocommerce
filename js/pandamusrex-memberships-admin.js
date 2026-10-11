jQuery( document ).ready( function( $ ){
    console.log( "Memberships Admin Helper. DOM is fully loaded and ready!" );
    $(document).on('click', 'button.pandamusrex_memberships_check_in', function() {
        action_name = $(this).attr('name');
        action_value = $(this).attr('value');
        action_product_id = $(this).data('product-id');
        console.log(action_name);
        console.log(action_value);
        console.log(action_product_id);

        $(this).prop('disabled', true);

        meta_data = [];
        if (action_name == 'check_them_in') {
            meta_data = [
                {
                    "key": action_value,
                    "value": 1
                }
            ];
        } else {
            meta_data = [
                {
                    "key": action_value // No value will trigger meta deletion
                }
            ]
        }

        $.ajax({
            url: wpApiSettings.root + 'wc/v3/products/' + action_product_id,
            method: 'PUT',
            beforeSend: function(xhr) {
                // Internal authentication: passes the security token via custom header
                xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
            },
            context: this, // <-- Sets the context for the .done handler
            data: {
                "meta_data": meta_data
            }
        })
        .done(function(response) {
            // name: check_them_in
            // text: &#10060; Not Checked In

            // name: kick_them_out
            // text: &#9989; Checked In

            console.log('in done');

            $(this).prop('disabled', false);
            if (action_name == 'check_them_in') {
                // Update the button to show them checked in
                // Important - this text and name should match includes/pandamusrex-memberships-products-admin.php
                $(this).text('\u2705 Checked In');
                $(this).attr('name', 'kick_them_out');
            } else {
                // Update the button to show them not checked in
                // Important - this text and name should match includes/pandamusrex-memberships-products-admin.php
                $(this).text('\u274C Not Checked In');
                $(this).attr('name', 'check_them_in');
            }
        })
        .fail(function(error) {
            console.log('in fail');

            $(this).prop('disabled', false);
            alert('Unable to update check in status. Please try again later.');
        });
    });
});
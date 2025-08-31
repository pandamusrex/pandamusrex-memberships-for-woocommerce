# Testing

## Basic Tests

1. Verify that when there are no memberships, the memberships list-all page shows that correctly.
1. Verify that you can add a membership accepting all the defaults.
1. Verify that you can edit that membership's product ID.
1. Verify that you can edit that membership's order ID.
1. Verify that you can edit that membership's start date.
1. Verify that you can edit that membership's stop date.
1. Verify that you can edit that membership's notes.
1. Verify that you can delete that membership.
1. Verify that attempting to reload the delete membership page after deleting a membership does not result in an error.
1. Verify that you add a single membership product to the cart; that after completing payment the membership is alloted to you and has the correct order and product IDs, a start date of today, and a stop date 1 year in the future.
1. Verify that you add quantity TWO of the same membership product to the cart; that you can provide a email address FOR A USER ON THE SITE ALREADY; that after completing payment a membership is alloted to you and to that user; and that both memberships have the correct order and product IDs, a start date of today, and a stop date 1 year in the future.
1. Verify that you add quantity TWO of the same membership product to the cart; that you can provide a email address FOR A USER NOT ON THE SITE ALREADY; that after completing payment a membership is alloted to you; that a new user is created; that a membership is alloted to that user; and that both memberships have the correct order and product IDs, a start date of today, and a stop date 1 year in the future.
1. Verify that when the WooCommerce plugin is deactivated, that accessing the memberships list-all page shows that and doesn't result in errors.
1. Verify that when the WooCommerce plugin is deactivated, that accessing the add membership page shows that and doesn't result in errors.
1. Deactivate and re-activate the plugin. Verify no errors occur.
1. After all tests are complete, verify that no errors are present in the debug or fatal logs.

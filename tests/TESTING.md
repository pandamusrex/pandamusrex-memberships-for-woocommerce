# Testing

## Basic Tests

- [ ] Verify you can tick the "product includes membership" on a product and save it and it sticks
- [ ] Verify you can untick the "product includes membership" on a product and save it and it sticks

- [ ] Verify that when there are no memberships, the memberships list-all page shows that correctly.
- [ ] Verify that you can add a membership accepting all the defaults.
- [ ] Verify that you can edit that membership's product ID.
- [ ] Verify that you can edit that membership's order ID.
- [ ] Verify that you can edit that membership's start date.
- [ ] Verify that you can edit that membership's stop date.
- [ ] Verify that you can edit that membership's notes.
- [ ] Verify that you can delete that membership.

- [ ] Verify that attempting to reload the delete membership page after deleting a membership does not result in an error.
- [ ] Verify that if you add a single simple (non variable) membership product to the cart; that after completing payment the membership is alloted to you and has the correct order and product IDs, a start date of today, and a stop date 1 year in the future.
- [ ] Verify that if you add quantity TWO of a non-membership product to the cart -- and complete checkout and payment -- that you are not prompted for any recipient emails and no errors are logged about it.
- [ ] Verify that if you add quantity TWO of a simple (non variable) membership product to the cart AND quantity TWO of a non-membership product to the cart -- and complete checkout and payment -- that you are only prompted for a recipient email for the membership product and no errors are logged about it.
- [ ] Verify that if you add quantity TWO of the same simple (non variable) membership product to the cart; that you can provide a email address FOR A USER ON THE SITE ALREADY; that after completing payment a membership is alloted to you and to that user; and that both memberships have the correct order and product IDs, a start date of today, and a stop date 1 year in the future.
- [ ] Verify that if you add quantity TWO of the same simple (non variable) membership product to the cart; that you can provide a email address FOR A USER NOT ON THE SITE ALREADY; that after completing payment a membership is alloted to you; that a new user is created; that a membership is alloted to that user; and that both memberships have the correct order and product IDs, a start date of today, and a stop date 1 year in the future.

- [ ] Verify that when the WooCommerce plugin is deactivated, that accessing the memberships list-all page shows that and doesn't result in errors.
- [ ] Verify that when the WooCommerce plugin is deactivated, that accessing the add membership page shows that and doesn't result in errors.
- [ ] Deactivate and re-activate the plugin. Verify no errors occur.
- [ ] After all tests are complete, verify that no errors are present in the debug or fatal logs.

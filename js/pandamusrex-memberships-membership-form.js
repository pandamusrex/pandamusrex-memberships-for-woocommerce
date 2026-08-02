// Attach to doc ready
// Attach to membership form submit button
// On click, make sure all required fields are checked/have data.
// If not, show an error and  cancel submit
// If ok, allow POST to proceed

jQuery( document ).ready( function( $ ){
    console.log( "Memberships Form Helper. DOM is fully loaded and ready!" );
    $( "#pandamusrex_memberships_membership_form_submit" ).click( function( event ) {
        event.preventDefault();
        console.log( "Form button clicked" );
    });
});

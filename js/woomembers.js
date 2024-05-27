// WooMembers options.
// 

jQuery(function ($) 
{
    // Mostra/esconde as opções de produto LeadLovers conforme o checkbox
    $('input#_woomembers_integration_check')
        .on( 'change' , function() {
        if($(this).is(':checked')) 
        {
            //mostra o div com o select e o botão
            $( 'div.woomembers_integration_options' ).show();
            //desabilita o text do sku
            //$( "#_sku" ).prop( "disabled", true );
        }
        else
        {
            $( 'div.woomembers_integration_options' ).hide();
            //$( "#_sku" ).prop( "disabled", false );
        }
    }).trigger( 'change' );
});
 

/**
 * 
 * @returns Returns manual import
 */
function anubis_importer_run () {

    event.preventDefault();

    var formData = new FormData();
        formData.append( 'action',  'run_importer' );

    jQuery.ajax({
        cache: false,
        url: bms_vars.ajaxurl,
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function () {
            jQuery( '#response_import' ).html( '<p class="text-info">Importando...</p>' );
        },
        success: function ( response ) {
            jQuery( '#response_import' ).html( '<p class="text-success">Importacion Completada!</p>' );
            jQuery("#anubis_importer_field").val( response.data );
        }
    });
    return false;
}
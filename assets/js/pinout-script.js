jQuery(document).ready(function($) {
     // Ouvrir le panneau pour ajouter une nouvelle broche
    
    // Lorsque l'utilisateur clique sur une broche
    $('.pin').on('click', function() {
        var pinId = $(this).data('pin-id');

        // Retirer la classe active de toutes les broches et l'ajouter à la broche cliquée
        $('.pin').removeClass('pinout-active');
        $(this).addClass('pinout-active');

        // Appel AJAX pour récupérer les détails de la broche
        $.post(pinout_ajax_obj.ajax_url, {
            action: 'get_pin_details',
            pin_id: pinId
        }, function(response) {
            if (response.success) {
                // Affiche les détails dans la zone des détails
                $('#pinout-details').html(
                    '<h2>' + response.data.name + '</h2>' +
                    '<p>' + response.data.description + '</p>'
                ).show();
                var pluginOffset = $('#pinout-container').offset().top; // Assurez-vous que #pinout-container est l'ID de votre plugin
                $('html, body').animate({ scrollTop: pluginOffset }, 'slow');
                
            } else {
                $('#pinout-details').html('<p>Aucun détail disponible pour cette broche.</p>').show();
            }
        }).fail(function() {
            // Gérer les erreurs de requête AJAX
            $('#pinout-details').html('<p>Erreur lors de la récupération des détails. Veuillez réessayer.</p>').show();
        });
    });
});

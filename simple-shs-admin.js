jQuery(document).ready(function($) {
    $('#simple-shs-generate-btn').click(function() {
        $(this).prop('disabled', true);
        $('#simple-shs-result').html('<p>Generating snapshot, please wait...</p>');

        $.ajax({
            url: simple_shs.ajax_url,
            type: 'POST',
            data: {
                action: 'simple_shs_generate_snapshot',
                nonce: simple_shs.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#simple-shs-result').html('<p>' + response.data + '</p>');
                } else {
                    $('#simple-shs-result').html('<p>Error: ' + response.data + '</p>');
                }
            },
            error: function() {
                $('#simple-shs-result').html('<p>An error occurred while generating the snapshot. Please try again.</p>');
            },
            complete: function() {
                $('#simple-shs-generate-btn').prop('disabled', false);
            }
        });
    });
});

// File: wp-smtp-service.js
jQuery(document).ready(function($) {
    
    $('#send-test-mail-btn').on('click', function(e) {
        e.preventDefault();

        // Make an AJAX request
        $.ajax({
            type: 'GET',
            url: $(this).attr('href'),
            success: function(response) {
                updateMessage(response, 'success');

                // Hide the message after 5 seconds
                setTimeout(function() {
                    updateMessage('', 'success');
                }, 5000);
            },
            error: function(xhr, status, error) {
                // Handle error, you can show an error message or log the error
                alert(xhr.responseText);
                updateMessage('Error sending test mail', 'error');
            }
        });
    });

    function updateMessage(message, type) {
        // Define the container where the message will be displayed
        var $messageContainer = $('#message-container');

        // Create or update the message element
        var $messageElement = $messageContainer.find('.custom-message');
        if (!$messageElement.length) {
            $messageElement = $('<div class="custom-message"></div>').appendTo($messageContainer);
        }

        // Update the content and class based on the type (success or error)
        $messageElement.text(message).removeClass().addClass('custom-message ' + type);
    }
});
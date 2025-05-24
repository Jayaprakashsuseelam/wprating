/**
 * Public-facing JavaScript for WP Rating
 *
 * @since 1.0.0
 */
(function($) {
    'use strict';

    /**
     * Initialize the rating functionality
     */
    function initRating() {
        $('.wprating-container').each(function() {
            const container = $(this);
            const stars = container.find('.wprating-star');
            const messageDiv = container.find('.wprating-message');
            const postId = container.data('post-id');

            // Handle star hover
            stars.hover(
                function() {
                    const rating = $(this).data('rating');
                    stars.removeClass('active');
                    stars.each(function(index) {
                        if (index < rating) {
                            $(this).addClass('active');
                        }
                    });
                },
                function() {
                    const currentRating = container.find('.wprating-star.active').length;
                    stars.removeClass('active');
                    stars.each(function(index) {
                        if (index < currentRating) {
                            $(this).addClass('active');
                        }
                    });
                }
            );

            // Handle star click
            stars.on('click', function() {
                const rating = $(this).data('rating');
                submitRating(postId, rating, container);
            });
        });
    }

    /**
     * Submit rating via AJAX
     *
     * @param {number} postId  The post ID
     * @param {number} rating  The rating value
     * @param {jQuery} container The rating container element
     */
    function submitRating(postId, rating, container) {
        const messageDiv = container.find('.wprating-message');
        
        $.ajax({
            url: wprating_public.ajax_url,
            type: 'POST',
            data: {
                action: 'wprating_submit_rating',
                nonce: wprating_public.nonce,
                post_id: postId,
                rating: rating
            },
            beforeSend: function() {
                messageDiv.removeClass('success error').hide();
            },
            success: function(response) {
                if (response.success) {
                    messageDiv
                        .addClass('success')
                        .text(response.data.message)
                        .show();

                    // Update average rating display
                    if (response.data.average) {
                        container.find('.wprating-average').text(
                            wprating_public.i18n.average_rating
                                .replace('%s', response.data.average)
                                .replace('%d', container.find('.wprating-star').length)
                        );
                    }

                    // Update user rating display
                    container.find('.wprating-user-rating').text(
                        wprating_public.i18n.your_rating
                            .replace('%s', rating)
                            .replace('%d', container.find('.wprating-star').length)
                    );

                    // Highlight the correct number of stars
                    const stars = container.find('.wprating-star');
                    stars.removeClass('active');
                    stars.each(function(index) {
                        if (index < rating) {
                            $(this).addClass('active');
                        }
                    });
                } else {
                    messageDiv
                        .addClass('error')
                        .text(response.data)
                        .show();
                }
            },
            error: function() {
                messageDiv
                    .addClass('error')
                    .text(wprating_public.i18n.rating_error)
                    .show();
            }
        });
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initRating();
    });

})(jQuery); 
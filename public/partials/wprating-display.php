<?php
/**
 * The template for displaying the rating interface.
 *
 * @since      1.0.0
 * @package    WPRating
 * @subpackage WPRating/public/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wprating-container" data-post-id="<?php echo esc_attr($post_id); ?>">
    <div class="wprating-stars">
        <?php for ($i = 1; $i <= $settings['number_of_stars']; $i++) : ?>
            <span class="wprating-star <?php echo ($i <= $user_rating) ? 'active' : ''; ?>" 
                  data-rating="<?php echo esc_attr($i); ?>">
                ★
            </span>
        <?php endfor; ?>
    </div>
    
    <div class="wprating-info">
        <?php if ($average_rating > 0) : ?>
            <span class="wprating-average">
                <?php printf(
                    __('Average Rating: %s / %s', 'wprating'),
                    number_format($average_rating, 1),
                    $settings['number_of_stars']
                ); ?>
            </span>
        <?php endif; ?>
        
        <?php if ($user_rating) : ?>
            <span class="wprating-user-rating">
                <?php printf(
                    __('Your Rating: %s / %s', 'wprating'),
                    $user_rating,
                    $settings['number_of_stars']
                ); ?>
            </span>
        <?php endif; ?>
    </div>
    
    <div class="wprating-message"></div>
</div> 
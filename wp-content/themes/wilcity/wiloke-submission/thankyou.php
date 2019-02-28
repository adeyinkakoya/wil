<?php
/*
 * Template Name: Wilcity Thank You
 */
use \WilokeListingTools\Framework\Store\Session;

get_header();
global $wiloke;
?>
	<div class="wil-content">
		<section class="wil-section bg-color-gray-2 pt-30">
			<div class="container">
				<div id="wilcity-thankyout" class="row">
					<?php do_action('wilcity/wiloke-submission/thankyou/before_content'); ?>
					<?php
                    if ( have_posts() ){
                        while (have_posts()){
                            the_post();
                            if ( $message = Session::getSession('errorPayment', true) ){
                              Wiloke::ksesHTML($message);
                            }else{
	                            the_content();
                            }
                        }
                    }
                    ?>
					<?php do_action('wilcity/wiloke-submission/thankyou/after_content'); ?>
				</div>
			</div>
		</section>
	</div>
<?php
get_footer();

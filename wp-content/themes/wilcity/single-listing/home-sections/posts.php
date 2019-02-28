<?php
global $post, $wilcityArgs;
if ( !function_exists('wilcity_render_grid_post') ){
    return '';
}

$oPosts = new WP_Query(
	array(
		'post_type'       => 'post',
		'posts_per_page'  => $wilcityArgs['maximumItemsOnHome'],
		'post_status'     => 'publish',
        'post_parent'     => $post->ID
	)
);
if ( $oPosts->have_posts() ) :
	?>
	<div class="content-box_module__333d9">
		<header class="content-box_header__xPnGx clearfix">
			<div class="wil-float-left">
				<h4 class="content-box_title__1gBHS"><i class="<?php echo esc_attr($wilcityArgs['icon']); ?>"></i><span><?php echo esc_html($wilcityArgs['name']); ?></span></h4>
			</div>
		</header>
		<div class="content-box_body__3tSRB">
			<div class="row" data-col-xs-gap="10">
				<?php
				while ($oPosts->have_posts()){
					$oPosts->the_post();
					?>
                    <div class="col-sm-6">
					    <?php wilcity_render_grid_post($oPosts->post); ?>
                    </div>
                    <?php
				}
				?>
			</div>
		</div>
        <footer class="content-box_footer__kswf3">
            <wiloke-switch-tab-btn tab-key="posts" v-on:on-switch-tab="switchTab" wrapper-class="list_link__2rDA1 text-ellipsis color-primary--hover wil-text-center">
                <template slot="insideTab">
					<?php esc_html_e('See All', 'wilcity'); ?>
                </template>
            </wiloke-switch-tab-btn>
        </footer>
	</div>
	<?php
endif; wp_reset_postdata();
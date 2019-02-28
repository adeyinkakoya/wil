<?php
global $post, $wilcityArgs;
if ( empty($post->post_content) ){
    return '';
}
?>
<div class="content-box_module__333d9">
	<header class="content-box_header__xPnGx clearfix">
		<div class="wil-float-left">
			<h4 class="content-box_title__1gBHS"><i class="<?php echo esc_attr($wilcityArgs['icon']); ?>"></i><span><?php echo esc_html($wilcityArgs['name']); ?></span></h4>
		</div>
	</header>
	<div class="content-box_body__3tSRB">
		<div><?php the_content(); ?></div>
	</div>
</div>
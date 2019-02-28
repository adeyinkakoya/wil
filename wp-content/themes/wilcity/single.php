<?php
if ( is_singular('post') ){
	get_template_part('post-types/post');
}else{
	get_template_part('post-types/listing');
}

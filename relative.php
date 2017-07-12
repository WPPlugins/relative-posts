<?php
/*
Plugin Name: Relative Posts
Plugin URL:
Description: Simple and Lite widget shows relative posts on the SideBar with or without thumbnails.
Version: 1.2.0
Author: Panagiotis Angelidis (paaggeli)
Author URL: http://panoswebsites.com
*/ 
//=======================================================================================
add_action( 'wp_enqueue_scripts', array( 'pa_Relative', 'styles_method' ) );
//=======================================================================================
class pa_Relative extends WP_Widget {
	function __construct(){
		$options=array(
			'description'=>'Show relative posts',
			'name'=>'Relative Posts'
		);
		parent::__construct('pa_Relative','', $options);
	}

	public function styles_method(){
		wp_register_style( 'pa_Relative', plugins_url( 'css/style.css', __FILE__ ) );
		wp_enqueue_style( 'pa_Relative' );
	}

	public function form($instance){
		extract($instance);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">Title: </label>
			<input 
				class='widefat'
				id="<?php echo $this->get_field_id('title');?>"
				name="<?php echo $this->get_field_name('title');?>"
				value="<?php echo (isset($title) && !$title=='') ? esc_attr($title) : "You Also Might Like..."; ?>" 
			/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('max_number'); ?>">Max Number Of Posts: </label>
			<input 
				class='widefat'
				id="<?php echo $this->get_field_id('max_number');?>"
				name="<?php echo $this->get_field_name('max_number');?>"
				value="<?php echo (isset($max_number) && !$max_number=='' ) ? esc_attr($max_number) : '5'; ?>" 
			/>
		</p>
		<p> 
			<label for="<?php echo $this->get_field_id('thumb_check');?>">Use Thumbnails:</label>
			<input id="<?php echo $this->get_field_id('thumb_check');?>" 
			name="<?php echo $this->get_field_name('thumb_check');?>" type='checkbox' 
			value="1"
			<?php echo ($thumb_check=="1") ?'checked="checked"' : '' ; ?>
			/> 
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('title_check');?>">Post Title Length:</label>
			<input id="<?php echo $this->get_field_id('title_check');?>" 
			name="<?php echo $this->get_field_name('title_check');?>" type='checkbox' 
			value="1"
			<?php echo ($title_check=="1") ?'checked="checked"' : '' ;?>
			/> 
			<input 
				class='widefat'
				id="<?php echo $this->get_field_id('title_length');?>"
				name="<?php echo $this->get_field_name('title_length');?>"
				value="<?php echo (isset($title_length) && !$title_length=='' ) ? esc_attr($title_length) : '50'; ?>"
			/>
		<?php
	}

	public function update($new_instance, $old_instance){
		$instance=array();
		$instance['title']=$new_instance['title'];
		$instance['max_number']=$new_instance['max_number'];
		$instance['thumb_check']=$new_instance['thumb_check'];
		$instance['title_check']=$new_instance['title_check'];
		$instance['title_length']=$new_instance['title_length'];
		return $instance;
	}

	public function widget($args, $instance){
		if (is_singular('post')){
			extract($args);
			extract($instance);
			$title=apply_filters('widget_title',$title);
			$max_number=(int)apply_filters('widget_max_number',$max_number);
			$title_length=(int)apply_filters( 'widget_title_length', $title_length);

			if (empty($title)) $title='You Also Might Like...';
			if (empty($max_number) || !is_integer($max_number)) $max_number=5;
			if(empty($title_length) || !is_integer($title_length) || $title_length<0)  $title_length=50;

			$post_id=$GLOBALS['post']->ID;

			$terms=get_the_terms($post_id, 'category');
			if ($terms && ! is_wp_error( $terms )){
				
				$cats=array();
				foreach ($terms as $term) {
					$cats[]=$term->cat_ID;
				}
				$loop = new WP_Query(
					array(
						'posts_per_page'=> $max_number,
						'category__in'=>$cats,
						'orderby'=>'rand',
						'post__not_in'=>array($post_id)));

				if ($loop->have_posts()){
					if($thumb_check==1){
						$relative = '<ul style="margin:0;">';
						
						while ($loop->have_posts()) {
							$loop->the_post();
							$image=(has_post_thumbnail())?get_the_post_thumbnail($loop->id,array(50,50)):'<img width="50" height="50" src="' .plugins_url( 'images/no-image.jpg' , __FILE__ ). '" >';
							$posttitle =($title_check == 1) && (strlen(get_the_title()) > $title_length) ? substr(get_the_title(), 0, $title_length)."...":get_the_title();
							$relative .= 
							'<li class="relative-post-thumbnail clear">
							<a href="'.get_permalink().'">'.'<div>'.$image.$posttitle;'</div></a>
							</li>';
						}//end while
						

						$relative.='</ul>';
					}//end if $thumb_check
					else{
						$relative = '<ul>';

						while ($loop->have_posts()) {
							$loop->the_post();
							$posttitle = ($title_check == 1) && (strlen(get_the_title()) > $title_length) ? substr(get_the_title(), 0, $title_length)."...":get_the_title();
							$relative .= 
							'<li>
							<a href="'.get_permalink().'">'.$posttitle;'</a>
							</li>';
						}//end while
						

						$relative.='</ul>';
					}//end else	
				}//end if $loop->have_posts()


				else{$relative='There Are No Similar Posts!';}
				wp_reset_query();
			}//end if $terms && ! is_wp_error( $terms )
			else{$relative='There Are No Similar Posts!';}

			echo $before_widget;
				echo $before_title. $title. $after_title;
				echo $relative;
			echo $after_widget;	
		}//end if $terms != false || is_wp_error($terms)
	}//end widget function
}//end class pa_Relative
add_action('widgets_init','pa_register_relative');
function pa_register_relative(){
	register_widget('pa_Relative');
}?>
<?php //encoding: utf-8

// Job lists and individual jobs
require_once( dirname( __FILE__ ) . '/frontend-jobs.php' );
// Application form, application filtering and storage
require_once( dirname( __FILE__ ) . '/frontend-application.php' );
// User registration and login
require_once( dirname( __FILE__ ) . '/frontend-user.php' );
// RSS Feeds
require_once( dirname( __FILE__ ) . '/frontend-rss.php' );
// Shortcode magic
require_once( dirname( __FILE__ ) . '/frontend-shortcodes.php' );

global $jobman_displaying, $jobman_finishedpage;
$jobman_finishedpage = $jobman_displaying = false;

function jobman_queryvars( $qvars ) {
	$qvars[] = 'j';
	$qvars[] = 'c';
	$qvars[] = 'jobman_root_id';
	$qvars[] = 'jobman_page';
	$qvars[] = 'jobman_data';
	$qvars[] = 'jobman_username';
	$qvars[] = 'jobman_password';
	$qvars[] = 'jobman_password2';
	$qvars[] = 'jobman_email';
	$qvars[] = 'jobman_register';
	return $qvars;
}

function jobman_add_rewrite_rules( $wp_rewrite ) {
	$options = get_option( 'jobman_options' );
	
	$root = get_page( $options['main_page'] );
	$url = get_page_uri( $root->ID );
	if( ! $url )
		return;

	$new_rules = array( 
						"$url/?$" => "index.php?jobman_root_id=$root->ID",
						"$url/apply/?([^/]+)?/?$" => "index.php?jobman_root_id=$root->ID" .
						"&jobman_page=apply&jobman_data=" . $wp_rewrite->preg_index(1),
						"$url/register/?([^/]+)?/?$" => "index.php?jobman_root_id=$root->ID" .
						"&jobman_page=register&jobman_data=" . $wp_rewrite->preg_index(1),
						"$url/feed/?" => "index.php?feed=jobman",
						"$url/([^/]+)/?$" => "index.php?jobman_data=" . $wp_rewrite->preg_index(1),
				);

	$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

function jobman_flush_rewrite_rules() {
	global $wp_rewrite;
	$wp_rewrite->flush_rules( false );
}

function jobman_page_link( $link, $page = NULL ) {
	if( $page == NULL )
		return $link;

	if( ! in_array( $page->post_type, array( 'jobman_job', 'jobman_app_form' ) ) )
		return $link;
	
	return get_page_link( $page->ID );
}

function jobman_display_jobs( $posts ) {
	global $wp_query, $wpdb, $jobman_displaying, $jobman_finishedpage;
	
	if( $jobman_finishedpage )
		return $posts;
	
	$options = get_option( 'jobman_options' );
	
	$post = NULL;

	$displaycat = false;
	
	if( array_key_exists( 'jobman_data', $wp_query->query_vars ) && ! array_key_exists( 'jobman_page', $wp_query->query_vars ) ) {
		if( is_term( $wp_query->query_vars['jobman_data'], 'jobman_category' ) ) {
			$wp_query->query_vars['jcat'] = $wp_query->query_vars['jobman_data'];
		}
		else {
			$sql = "SELECT * FROM $wpdb->posts WHERE post_type='jobman_job' AND post_name=%s;";
			$sql = $wpdb->prepare( $sql, $wp_query->query_vars['jobman_data'] );
			$data = $wpdb->get_results( $sql, OBJECT );
			if( count( $data ) > 0 )
				$wp_query->query_vars['page_id'] = $data[0]->ID;
			else
				return $posts;
		}
	}
	
	if( ! array_key_exists( 'jcat', $wp_query->query_vars ) ) {
		if( isset( $wp_query->query_vars['jobman_root_id'] ) )
			$post = get_post( $wp_query->query_vars['jobman_root_id'] );
		else if( isset( $wp_query->query_vars['page_id'] ) )
			$post = get_post( $wp_query->query_vars['page_id'] );

		if( $post == NULL || ( ! isset( $wp_query->query_vars['jobman_page'] ) && $post->ID != $options['main_page'] && ! in_array( $post->post_type, array( 'jobman_job', 'jobman_app_form', 'jobman-register' ) ) ) )
			return $posts;
	}

	// We're going to be displaying a Job Manager page. Un-fuck WordPress.
	$jobman_displaying = true;
	$wp_query->is_home = false;
	remove_filter( 'the_content', 'wpautop' );
	
	if( NULL != $post ) {
		$postmeta = get_post_custom( $post->ID );
		$postcats = wp_get_object_terms( $post->ID, 'jobman_category' );

		$postdata = array();
		foreach( $postmeta as $key => $value ) {
			if( is_array( $value ) )
				$postdata[$key] = $value[0];
			else
				$postdata[$key] = $value;
		}
	}

	if( array_key_exists( 'jobman_register', $wp_query->query_vars ) )
		jobman_register();
	else if( array_key_exists( 'jobman_username', $wp_query->query_vars ) )
		jobman_login();

	global $jobman_data;
	$jobman_data = '';
	if( array_key_exists( 'jobman_data', $wp_query->query_vars ) )
		$jobman_data = $wp_query->query_vars['jobman_data'];
	else if( array_key_exists( 'j', $wp_query->query_vars ) )
		$jobman_data = $wp_query->query_vars['j'];
	else if( array_key_exists( 'c', $wp_query->query_vars ) )
		$jobman_data = $wp_query->query_vars['c'];

	if( array_key_exists( 'jcat', $wp_query->query_vars ) ) {
		// We're looking at a category
		$cat = get_term_by( 'slug', $wp_query->query_vars['jcat'], 'jobman_category' );
		
		$posts = jobman_display_jobs_list( $cat->term_id );
		
		if( count( $posts ) > 0 )
			$posts[0]->post_content = $options['text']['category_before'] . $posts[0]->post_content . $options['text']['category_after'];
	}
	else if( isset( $wp_query->query_vars['jobman_page'] ) || ( NULL != $post && in_array( $post->post_type, array( 'jobman_job', 'jobman_app_form', 'jobman_register' ) ) ) ) {
		if( NULL == $post  || ! in_array( $post->post_type, array( 'jobman_job', 'jobman_app_form', 'jobman_register' ) ) ) {
			$sql = "SELECT * FROM $wpdb->posts WHERE (post_type='jobman_job' OR post_type='jobman_app_form' OR post_type='jobman_register') AND post_name=%s;";
			$sql = $wpdb->prepare( $sql, $wp_query->query_vars['jobman_page'] );
			$data = $wpdb->get_results( $sql, OBJECT );
		}
		else {
			$data = array( $post );
		}
		
		if( count( $data ) > 0 ) {
			$post = $data[0];
			$postmeta = get_post_custom( $post->ID );
			$postcats = wp_get_object_terms( $post->ID, 'jobman_category' );
			
			$postdata = array();
			foreach( $postmeta as $key => $value ) {
				if( is_array( $value ) )
					$postdata[$key] = $value[0];
				else
					$postdata[$key] = $value;
			}
			
			if( $post->post_type == 'jobman_job' ) {
				// We're looking at a job
				$posts = jobman_display_job( $post->ID );
				if( count( $posts ) > 0 )
					$posts[0]->post_content = $options['text']['job_before'] . $posts[0]->post_content . $options['text']['job_after'];
			}
			else if( 'jobman_app_form' == $post->post_type ) {
				// We're looking at an application form
				$jobid = (int) $jobman_data;
				if( '' == $jobman_data )
					$posts = jobman_display_apply( -1 );
				else if( $jobid > 0 )
					$posts = jobman_display_apply( $jobid );
				else
					$posts = jobman_display_apply( -1, $jobman_data );

				if( count( $posts ) > 0 )
					$posts[0]->post_content = $options['text']['apply_before'] . $posts[0]->post_content . $options['text']['apply_after'];
			}
			else if( 'jobman_register' == $post->post_type ) {
				// Looking for the registration form
				if( is_user_logged_in() ) {
					wp_redirect( get_page_link( $options['main_page'] ) );
					exit;
				}
				else {
					$posts = jobman_display_register();
				}
			}
			else {
				$posts = array();
			}
		}
		else {
			$posts = array();
		}
	}
	else if( NULL != $post && $post->ID == $options['main_page'] ) {
		// We're looking at the main job list page
		$posts = jobman_display_jobs_list( 'all' );

		$wp_query->queried_object = $post;
		$wp_query->queried_object_id = $post->ID;
		$wp_query->is_page = true;

		if( count( $posts ) > 0 )
			$posts[0]->post_content = $options['text']['main_before'] . $posts[0]->post_content . $options['text']['main_after'];
	}
	else {
		$posts = array();
	}

	$hidepromo = $options['promo_link'];
	
	if( get_option( 'pento_consulting' ) )
		$hidepromo = true;
	
	if( ! $hidepromo && count( $posts ) > 0 )
		$posts[0]->post_content .= '<p class="jobmanpromo">' . sprintf( __( 'This job listing was created using <a href="%s" title="%s">Job Manager</a> for WordPress, by <a href="%s">Gary Pendergast</a>.', 'resman'), 'http://pento.net/projects/wordpress-job-manager/', __( 'WordPress Job Manager', 'resman' ), 'http://pento.net' ) . '</p>';

	$jobman_finishedpage = true;
	return $posts;
}

function jobman_display_init() {
	wp_enqueue_script( 'jquery-ui-datepicker', JOBMAN_URL . '/js/jquery-ui-datepicker.js', array( 'jquery-ui-core' ), JOBMAN_VERSION );
	wp_enqueue_script( 'jquery-display', JOBMAN_URL . '/js/display.js', false, JOBMAN_VERSION );
	wp_enqueue_style( 'jobman-display', JOBMAN_URL . '/css/display.css', false, JOBMAN_VERSION );
}

function jobman_display_template() {
	global $wp_query, $jobman_displaying;
	$options = get_option( 'jobman_options' );
	
	if( ! $jobman_displaying )
		return;
	
	// Code gleefully copied from wp-includes/theme.php

	$root = get_page( $options['main_page'] );
	$id = $root->ID;
	$template = get_post_meta( $id, '_wp_page_template', true );
	$pagename = get_query_var( 'pagename' );
	$category = get_query_var( 'jcat' );
	
	$post_id = get_query_var( 'page_id' );

	$job_cats = array();
	if( ! empty( $post_id ) ) {
		$post = get_post( $post_id );
		if( ! empty( $post ) && 'jobman_job' == $post->post_type ) {
			$categories = wp_get_object_terms( $post->ID, 'jobman_category' );
			if( ! empty( $categories ) ) {
				foreach( $categories as $cat ) {
					$job_cats[] = $cat->slug;
				}
			}
		}
	}

	if( 'default' == $template )
		$template = '';

	$templates = array();
	if( ! empty( $template ) && ! validate_file( $template ) )
		$templates[] = $template;
	if( $category )
		$templates[] = "category-$category.php";
	if( ! empty( $job_cats ) ) {
		foreach( $job_cats as $jcat ) {
			$templates[] = "category-$jcat.php";
		}
	}
	if( $pagename )
		$templates[] = "page-$pagename.php";
	if( $id )
		$templates[] = "page-$id.php";
	$templates[] = "page.php";

	$template = apply_filters( 'page_template', locate_template( $templates ) );

	if( '' != $template ) {
		load_template( $template );
		// The exit tells WP to not try to load any more templates
		exit;
	}
}

function jobman_display_title( $title, $sep, $seploc ) {
	global $jobman_displaying, $wp_query;

	if( ! $jobman_displaying )
		return $title;

	$post = $wp_query->post;
	
	switch( $post->post_type ) {
		case 'jobman_job':
			$newtitle = $post->post_title;
			break;
		case 'jobman_app_form':
			$newtitle = __( 'Job Application', 'jobman' );
			break;
		case 'jobman_joblist':
			$newtitle = __( 'Job Listing', 'jobman' ) . ': ' . $post->post_title;
			break;
		default:
			$newtitle = __( 'Job Listing', 'jobman' );
			break;
	}
	
	if( '' == $newtitle )
		return $title;

	if( 'right' == $seploc )
		$title = "$newtitle $sep ";
	else
		$title = " $sep $newtitle";
	
	return $title;
}

function jobman_display_head() {
	global $jobman_displaying;
	
	if( ! $jobman_displaying )
		return;
	
	if( is_feed() )
		return;
		
	$options = get_option( 'jobman_options' );

	$url = get_page_link( $options['main_page'] );
	$structure = get_option( 'permalink_structure' );
	if( '' == $structure ) {
		$url = get_option( 'home' ) . '?feed=jobman';
	}
	else {
		$url .= 'feed/';
	}

	$mandatory_ids = array();
	$mandatory_labels = array();
	foreach( $options['fields'] as $id => $field ) {
		if( $field['mandatory'] ) {
			$mandatory_ids[] = $id;
			$mandatory_labels[] = $field['label'];
		}
	}
?>
	<link rel="alternate" type="application/rss+xml" href="<?php echo $url ?>" title="<?php _e( 'Latest Jobs', 'jobman' ) ?>" />
<script type="text/javascript"> 
//<![CDATA[
jQuery(document).ready(function() {
	jQuery(".datepicker").datepicker({dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true, gotoCurrent: true});
	jQuery("#ui-datepicker-div").css('display', 'none');
});
//]]>
var jobman_mandatory_ids = <?php echo json_encode( $mandatory_ids ) ?>;
var jobman_mandatory_labels = <?php echo json_encode( $mandatory_labels ) ?>;

var jobman_strings = new Array();
jobman_strings['apply_submit_mandatory_warning'] = "<?php _e( 'The following fields must be filled out before submitting', 'jobman' ) ?>";
</script> 
<?php
}

function jobman_display_robots_noindex() {
	if( is_feed() )
		return;
?>
	<!-- Generated by Job Manager plugin -->
	<meta name="robots" content="noindex" />
<?php
}

function jobman_format_abstract( $text ) {
	$textsplit = preg_split( "[\n]", $text );
	
	$listlevel = 0;
	$starsmatch = array();
	foreach( $textsplit as $key => $line ) {
		preg_match( '/^[*]*/', $line, $starsmatch );
		$stars = strlen( $starsmatch[0] );
		
		$line = preg_replace( '/^[*]*/', '', $line );
		
		$listhtml_start = '';
		$listhtml_end = '';
		while( $stars > $listlevel ) {
			$listhtml_start .= '<ul>';
			$listlevel++;
		}
		while( $stars < $listlevel ) {
			$listhtml_start .= '</ul>';
			$listlevel--;
		}
		if( $listlevel > 0 ) {
			$listhtml_start .= '<li>';
			$listhtml_end = '</li>';
		}
		
		$textsplit[$key] = $listhtml_start . $line . $listhtml_end;
	}

	$text = implode( "\n", $textsplit );

	while( $listlevel > 0 ) {
		$text .= '</ul>';
		$listlevel--;
	}
	
	// Bold
	$text = preg_replace( "/'''(.*?)'''/", '<strong>$1</strong>', $text );
	
	// Italic
	$text = preg_replace( "/''(.*?)''/", '<em>$1</em>', $text );

	$text = '<p>' . $text . '</p>';
	return $text;
}

?>
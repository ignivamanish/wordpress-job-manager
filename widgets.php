<?php
class JobmanLatestJobsWidget extends WP_Widget {
    /** constructor */
    function JobmanLatestJobsWidget() {
		$name = __( 'Job Manager: Recent Jobs', 'jobman');
		$options = array( 'description' => 'A list of the most recent jobs posted to your site' );
		
        parent::WP_Widget( false, $name, $options );	
    }

    function widget( $args, $instance ) {		
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        
		echo $before_widget;
		
		if ( $title )
			echo $before_title . $title . $after_title;

		$jobs = get_posts( "post_type=jobman_job&numberposts=-1" );

		foreach( $jobs as $id => $job ) {
			// Remove expired jobs
			$displayenddate = get_post_meta( $job->ID, 'displayenddate', true );
			if( '' != $displayenddate && strtotime( $displayenddate ) <= time() ) {
				unset( $jobs[$id] );
				continue;
			}
			
			// Remove jobs not in selected categories
			if( 'selected' == $instance['jobsfrom'] ) {
				$categories = wp_get_object_terms( $job->ID, 'jobman_category' );
				if( count( $categories ) > 0 ) {
					foreach( $categories as $cat ) {
						if( in_array( $cat->term_id, $instance['selected_cats'] ) )
							// Job is in a selected category. Move to next job.
							continue 2;
					}
					
					// Job wasn't in a selected category. Remove it.
					unset( $jobs[$id] );
				}
				else {
					unset( $jobs[$id] );
				}
			}
		}
		
		if( count( $jobs ) > 0 ) {
			echo '<ul>';
			$jobcount = 0;
			foreach( $jobs as $job ) {
				if( $jobcount >= $instance['jobslimit'] )
					break;

				echo '<li><a href="' . get_page_link( $job->ID ) . '">' . $job->post_title . '</a></li>';
				
				$jobcount++;
			}
			echo '</ul>';
		}
		else {
			echo '<p>' . __( 'There are no jobs to display at this time.', 'jobman' ) . '</p>';
		}

		echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
		if( ! is_int( $new_instance['jobslimit'] ) )
			$new_instance['jobslimit'] = 5;
		else if( $new_instance['jobslimit'] < 0 )
			$new_instance['jobslimit'] = 0;
		else if( $new_instance['jobslimit'] > 15 )
			$new_instance['jobslimit'] = 15;
		
		$new_instance['selected_cats'] = array();
		
		if( array_key_exists( $this->get_field_id( 'selected_cats' ), $_REQUEST ) && is_array( $_REQUEST[$this->get_field_id( 'selected_cats' )] ) ) {
			foreach( $_REQUEST[$this->get_field_id( 'selected_cats' )] as $catid ) {
				$new_instance['selected_cats'][] = $catid;
			}
		}
		
		return $new_instance;
    }

    function form( $instance ) {
		$title = '';
		if( array_key_exists( 'title', $instance ) )
			$title = esc_attr( $instance['title'] );
?>
            <p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'jobman' ); ?>: 
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>
<?php 

		$jobslimit = 5;
		if( array_key_exists( 'jobslimit', $instance ) )
			$jobslimit = esc_attr( $instance['jobslimit'] );
?>
            <p>
				<label for="<?php echo $this->get_field_id( 'jobslimit' ); ?>"><?php _e( 'Number of Jobs to show', 'jobman' ); ?>: 
					<input id="<?php echo $this->get_field_id( 'jobslimit' ); ?>" name="<?php echo $this->get_field_name( 'jobslimit' ); ?>" type="text" size="3" value="<?php echo $jobslimit; ?>" />
				</label>
				<small>(<?php _e( 'at most 15', 'jobman' ) ?>)</small>
			</p>
<?php 

		$jobsfrom = 'all';
		if( array_key_exists( 'jobsfrom', $instance ) )
			$jobsfrom = esc_attr( $instance['jobsfrom'] );
?>
            <p>
				<label for="<?php echo $this->get_field_id( 'jobsfrom' ); ?>"><?php _e( 'Show Jobs From', 'jobman' ); ?>: 
					<select id="<?php echo $this->get_field_id( 'jobsfrom' ); ?>" name="<?php echo $this->get_field_name( 'jobsfrom' ); ?>">
						<option value="all"<?php echo ( 'all' == $jobsfrom )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'All Categories', 'jobman' ) ?></option>
						<option value="selected"<?php echo ( 'selected' == $jobsfrom )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Selected Categories', 'jobman' ) ?></option>
					</select>
				</label>
			</p>
<?php 

		$selected_cats = array();
		if( array_key_exists( 'selected_cats', $instance ) )
			$selected_cats = $instance['selected_cats'];

		$categories = get_terms( 'jobman_category', 'hide_empty=0' );
?>
            <p>
				<label><?php _e( 'Categories', 'jobman' ); ?>: </label><br/>
<?php
		if( count( $categories ) > 0 ) {
			foreach( $categories as $cat ) {
				echo "<input type='checkbox' name='" . $this->get_field_id( 'selected_cats' ) . "[]' value='$cat->term_id'";
				if( in_array( $cat->term_id, $selected_cats ) )
					echo ' checked="checked"';
				echo "> $cat->name<br/>";
			}
		}
		else {
			echo '<p>' . __( 'No categories defined.', 'jobman' ) . '</p>';
		}
?>
			</p>
<?php 
	}

}


class JobmanCategoriesWidget extends WP_Widget {
    /** constructor */
    function JobmanCategoriesWidget() {
		$name = __( 'Job Manager: Categories', 'jobman');
		$options = array( 'description' => 'A list or dropdown of Job Manager categories' );
		
        parent::WP_Widget( false, $name, $options );	
    }

    function widget( $args, $instance ) {
		global $wp_query;
		
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        
		echo $before_widget;
		
		if ( $title )
			echo $before_title . $title . $after_title;
			
		$dropdown = 0;
		if( array_key_exists( 'dropdown', $instance ) )
			$dropdown = $instance['dropdown'];

		$categories = get_terms( 'jobman_category', 'hide_empty=0' );
		if( count( $categories ) > 0 ) {
			if( $dropdown ) {
				echo '<select id="jobman-catlist">';
				echo '<option value="">' . __( 'Select Category', 'jobman' ) . '</option>';
			}
			else {
				echo '<ul>';
			}
				
			foreach( $categories as $cat ) {
				$selected = '';
				if( array_key_exists( 'jcat', $wp_query->query_vars ) && $wp_query->query_vars['jcat'] == $cat->slug )
					$selected = ' selected="selected"';
				
				if( $dropdown )
					echo "<option value='$cat->slug'$selected>$cat->name</option>";
				else
					echo "<li><a href='" . get_term_link( $cat->slug, 'jobman_category' ) . "'>$cat->name</a></li>";
			}

			if( $dropdown ) {
?>
		</select>
		
<script type='text/javascript'> 
/* <![CDATA[ */
	var jobman_dropdown = document.getElementById("jobman-catlist");
	function onJobmanCatChange() {
		if ( jobman_dropdown.options[jobman_dropdown.selectedIndex].value != '' ) {
			location.href = "<?php echo home_url() ?>/?jcat="+jobman_dropdown.options[jobman_dropdown.selectedIndex].value;
		}
	}
	jobman_dropdown.onchange = onJobmanCatChange;
/* ]]> */
</script> 
<?php
			}
			else {
				echo '</ul>';
			}
		}
		else {
			echo '<p>' . __( 'There are no categories to display at this time.', 'jobman' ) . '</p>';
		}
					
		echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
		return $new_instance;
    }

    function form( $instance ) {
		$title = '';
		if( array_key_exists( 'title', $instance ) )
			$title = esc_attr( $instance['title'] );
?>
            <p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'jobman' ); ?>: 
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>
<?php
		$dropdown = 0;
		if( array_key_exists( 'dropdown', $instance ) )
			$dropdown = $instance['dropdown'];
?>
            <p>
				<input id="<?php echo $this->get_field_id( 'dropdown' ); ?>" name="<?php echo $this->get_field_name( 'dropdown' ); ?>" type="checkbox" value="1" <?php echo ( $dropdown )?( 'checked="checked" ' ):( '' )?>/> <?php _e( 'Show as dropdown', 'jobman' ) ?><br/>
			</p>
<?php 
	}

}
?>
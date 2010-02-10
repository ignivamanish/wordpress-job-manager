<?php //encoding: utf-8

function jobman_activate() {
	$options = get_option( 'jobman_options' );
	if( is_array( $options ) ) {
		$version = $options['version'];
		$dbversion = $options['db_version'];
	}
	else {
		// For folks upgrading from 0.3.x or earlier
		$version = get_option( 'jobman_version' );
		$dbversion = get_option( 'jobman_db_version' );
	}

	jobman_page_taxonomy_setup();
	
	if( '' == $dbversion ) {
		// Never been run, create the database.
		jobman_create_default_settings();
		jobman_create_db();
	}
	elseif( JOBMAN_DB_VERSION != $dbversion ) {
		// New version, upgrade
		jobman_upgrade_settings( $dbversion );
		jobman_upgrade_db( $dbversion );
	}

	$options = get_option( 'jobman_options' );
	$options['version'] = JOBMAN_VERSION;
	$options['db_version'] = JOBMAN_DB_VERSION;
	update_option( 'jobman_options', $options );
}

function jobman_create_default_settings() {
	$options = array(
					'default_email' => get_option( 'admin_email' ),
					'list_type' => 'full',
					'application_email_from' => 4,
					'application_email_subject_text' => 'Job Application:',
					'application_email_subject_fields' => array( 2, 3 ),
					'promo_link' => 0,
					'user_registration' => 0,
					'user_registration_required' => 0,
					'loginform_main' => 1,
					'loginform_category' => 1,
					'loginform_job' => 1,
					'loginform_apply' => 1,
					'related_categories' => 1,
					'sort_by' => '',
					'sort_order' => '',
					'plugins' => array(
									'gxs' => 1
								)
				);
	update_option( 'jobman_options', $options );
	
	mkdir( JOBMAN_UPLOAD_DIR . '/uploads', 0777, true );
	mkdir( JOBMAN_UPLOAD_DIR . '/icons', 0777, true );
}

function jobman_upgrade_settings( $oldversion ) {
	if( $oldversion < 2 )
		update_option( 'jobman_list_type', 'full' );

	if( $oldversion < 3 )
		update_option( 'jobman_plugin_gxs', 1 );

	if( $oldversion < 5 ) {
		// Move everything to single option
		$options = array(
						'version' => get_option( 'jobman_version' ),
						'db_version' => get_option( 'jobman_db_version' ),
						'page_name' => get_option( 'jobman_page_name' ),
						'default_email' => get_option( 'jobman_default_email' ),
						'list_type' => get_option( 'jobman_list_type' ),
						'application_email_from' => get_option( 'jobman_application_email_from' ),
						'application_email_subject_text' => get_option( 'jobman_application_email_subject_text' ),
						'application_email_subject_fields' => explode( ',', get_option( 'jobman_application_email_subject_fields' ) ),
						'promo_link' => get_option( 'jobman_promo_link' ),
						'plugins' => array(
										'gxs' => get_option( 'jobman_plugin_gxs' )
									)
					);
		
		update_option( 'jobman_options', $options );
		
		// Delete the old options
		delete_option( 'jobman_version' );
		delete_option( 'jobman_db_version' );
		delete_option( 'jobman_page_name' );
		delete_option( 'jobman_default_email' );
		delete_option( 'jobman_list_type' );
		delete_option( 'jobman_application_email_from' );
		delete_option( 'jobman_application_email_subject_text' );
		delete_option( 'jobman_application_email_subject_fields' );
		delete_option( 'jobman_promo_link' );
		delete_option( 'jobman_plugin_gxs' );
	}

	if( $oldversion < 7 ) {
		$options = get_option( 'jobman_options' );
		
		$options['user_registration'] = 0;
		$options['user_registration_required'] = 0;
		$options['loginform_main'] = 1;
		$options['loginform_category'] = 1;
		$options['loginform_job'] = 1;
		$options['loginform_apply'] = 1;
		
		update_option( 'jobman_options', $options );
	}
	
	if( $oldversion < 9 ) {
		mkdir( JOBMAN_UPLOAD_DIR . '/uploads', 0777, true );
		mkdir( JOBMAN_UPLOAD_DIR . '/icons', 0777, true );
	}
	
	if( $oldversion < 11 ) {
		$options = get_option( 'jobman_options' );
		
		$options['related_categories'] = 1;
		$options['sort_by'] = '';
		$options['sort_order'] = '';
		
		update_option( 'jobman_options', $options );
	}
}

function jobman_uninstall() {
	jobman_drop_db();

	delete_option( 'jobman_options' );
}

?>
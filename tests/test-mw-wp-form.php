<?php
class MW_WP_Form_Test extends WP_UnitTestCase {

	/**
	 * メール関連のフックのテスト（自動返信設定あり）
	 */
	public function test_mail_hooks_for_setted_auto_replay_mail() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key = MWF_Config::NAME . '-' . $post_id;

		add_filter( 'mwform_admin_mail_raw_' . $form_key, function( $Mail, $values ) {
			$Mail->to   = 'hoge1@example.com';
			$Mail->from = 'from1@example.com';
			return $Mail;
		}, 10, 2 );

		add_filter( 'mwform_mail_' . $form_key, function( $Mail, $values ) {
			$this->assertEquals( $Mail->to, 'hoge1@example.com' );
			$Mail->to = 'hoge2@example.com';
			return $Mail;
		}, 10, 2 );

		add_filter( 'mwform_admin_mail_' . $form_key, function( $Mail, $values ) {
			$this->assertEquals( $Mail->to, 'hoge2@example.com' );
			return $Mail;
		}, 10, 2 );

		add_filter( 'mwform_auto_mail_raw_' . $form_key, function( $Mail, $values ) {
			$this->assertEquals( $Mail->to, 'inc@2inc.org' );
			$this->assertEquals( $Mail->from, get_bloginfo( 'admin_email' ) );
			$Mail->from = 'from2@example.com';
			return $Mail;
		}, 10, 2 );

		add_filter( 'mwform_auto_mail_' . $form_key, function( $Mail, $values ) {
			$this->assertEquals( $Mail->from, 'from2@example.com' );
			return $Mail;
		}, 10, 2 );

		$Mail = new MW_WP_Form_Mail();
		$Data = MW_WP_Form_Data::getInstance( $form_key );
		$Data->set( 'メールアドレス', 'inc@2inc.org' );

		$Validation_Rule_Mail = new MW_WP_Form_Validation_Rule_Mail();
		$Validation_Rule_Mail->set_Data( $Data );
		$validation_rules = array(
			'mail' => $Validation_Rule_Mail,
		);

		$Setting = new MW_WP_Form_Setting( $post_id );
		$Setting->set( 'automatic_reply_email', 'メールアドレス' );

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $Data, $form_key, $validation_rules, $Setting
		);
	}

	/**
	 * メール関連のフックのテスト（自動返信設定なし）
	 */
	public function test_mail_hooks_for_no_set_auto_replay_mail() {
		$post_id = $this->factory->post->create( array(
			'post_type' => MWF_Config::NAME,
		) );
		$form_key = MWF_Config::NAME . '-' . $post_id;

		add_filter( 'mwform_admin_mail_raw_' . $form_key, function( $Mail, $values ) {
			$Mail->to   = 'hoge1@example.com';
			$Mail->from = 'from1@example.com';
			return $Mail;
		}, 10, 2 );

		add_filter( 'mwform_mail_' . $form_key, function( $Mail, $values ) {
			$this->assertEquals( $Mail->to, 'hoge1@example.com' );
			$Mail->to = 'hoge2@example.com';
			return $Mail;
		}, 10, 2 );

		add_filter( 'mwform_admin_mail_' . $form_key, function( $Mail, $values ) {
			$this->assertEquals( $Mail->to, 'hoge2@example.com' );
			return $Mail;
		}, 10, 2 );

		add_filter( 'mwform_auto_mail_raw_' . $form_key, function( $Mail, $values ) {
			$this->assertEquals( $Mail->to, '' );
			$this->assertEquals( $Mail->from, get_bloginfo( 'admin_email' ) );
			$Mail->from = 'from2@example.com';
			return $Mail;
		}, 10, 2 );

		add_filter( 'mwform_auto_mail_' . $form_key, function( $Mail, $values ) {
			$this->assertEquals( $Mail->from, 'from2@example.com' );
			return $Mail;
		}, 10, 2 );

		$Mail = new MW_WP_Form_Mail();
		$Data = MW_WP_Form_Data::getInstance( $form_key );

		$Validation_Rule_Mail = new MW_WP_Form_Validation_Rule_Mail();
		$Validation_Rule_Mail->set_Data( $Data );
		$validation_rules = array(
			'mail' => $Validation_Rule_Mail,
		);

		$Setting = new MW_WP_Form_Setting( $post_id );

		$Mail_Service = new MW_WP_Form_Mail_Service(
			$Mail, $Data, $form_key, $validation_rules, $Setting
		);
	}

	/**
	 * アンインストールのテスト
	 */
	public function test_uninstall() {
		$post_ids = $this->factory->post->create_many(
			5,
			array(
				'post_type' => MWF_Config::NAME,
			)
		);

		foreach ( $post_ids as $post_id ) {
			update_option( MWF_Config::NAME . '-chart-' . $post_id, 1 );
			$data_post_ids = $this->factory->post->create_many(
				5,
				array(
					'post_type' => MWF_Config::DBDATA . $post_id,
				)
			);
		}

		$MW_WP_Form_File = new MW_WP_Form_File;
		$temp_dir = $MW_WP_Form_File->get_temp_dir();
		$temp_dir = $temp_dir['dir'];
		system( "sudo chmod 777 " . WP_CONTENT_DIR . '/uploads' );
		$MW_WP_Form_File->create_temp_dir();
		$this->assertEquals( true, file_exists( $temp_dir ) );

		update_option( MWF_Config::NAME, 1 );

		MW_WP_Form::uninstall();

		$posts = get_posts( array(
			'post_type' => MWF_Config::NAME,
			'posts_per_page' => -1,
		) );

		$this->assertEquals( 0, count( $posts ) );

		foreach ( $post_ids as $post_id ) {
			$option = get_option( MWF_Config::NAME . '-chart-' . $post_id );
			$this->assertEquals( null, $option );

			$data_posts = get_posts( array(
				'post_type' => MWF_Config::DBDATA . $post_id,
				'posts_per_page' => -1,
			) );
			$this->assertEquals( 0, count( $data_posts ) );
		}

		$this->assertEquals( false, file_exists( $temp_dir ) );

		$option = get_option( MWF_Config::NAME );
		$this->assertEquals( null, $option );
	}
}


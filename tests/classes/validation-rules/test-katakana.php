<?php
class MW_WP_Form_Validation_Rule_Katakana_Test extends WP_UnitTestCase {

	public function tear_down() {
		parent::tear_down();
		_delete_all_data();
	}

	protected function _create_form() {
		return $this->factory->post->create(
			array(
				'post_type' => MWF_Config::NAME,
			)
		);
	}

	/**
	 * @test
	 * @group rule
	 */
	public function rule() {
		$form_id  = $this->_create_form();
		$form_key = MWF_Functions::get_form_key_from_form_id( $form_id );
		$Data     = MW_WP_Form_Data::connect( $form_key );
		$Rule     = new MW_WP_Form_Validation_Rule_Katakana( $Data );

		$Data->set( 'katakana', '' );
		$this->assertNull( $Rule->rule( 'katakana' ) );

		$Data->set( 'katakana', 'アイウエオ' );
		$this->assertNull( $Rule->rule( 'katakana' ) );

		$Data->set( 'katakana', 'アイウエオ1' );
		$this->assertNotNull( $Rule->rule( 'katakana' ) );
	}
}

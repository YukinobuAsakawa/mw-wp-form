<?php
/**
 * Name: MW Validation
 * Description: バリデーションクラス
 * Version: 1.7.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 20, 2012
 * Modified: July 23, 2014
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_Validation {

	private $key;
	protected $Error;
	public $validate = array();
	private $validation_rules = array();

	/**
	 * __construct
	 * @param string $key 識別子
	 */
	public function __construct( $key ) {
		$this->key = $key;
		$this->Error = new MW_Error();
	}

	/**
	 * add_validation_rule
	 * 各バリデーションルールクラスのインスタンスをセット
	 */
	public function add_validation_rule( $rule_name, $instance ) {
		$this->validation_rules[$rule_name] = $instance;
	}

	/**
	 * Error
	 * エラーオブジェクトを返す
	 * @return	Error	エラーオブジェクト
	 */
	public function Error() {
		return $this->Error;
	}

	/**
	 * isValid
	 * バリデートが通っているかチェック
	 * @return	Boolean
	 */
	protected function isValid() {
		$errors = $this->Error->getErrors();
		if ( empty( $errors ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * setRule
	 * バリデートが通っているかチェック
	 * @param string キー
	 * @param string バリデーションルール名
	 * @param array オプション
	 * @return bool
	 */
	public function setRule( $key, $rule, array $options = array() ) {
		$rules = array(
			'rule' => strtolower( $rule ),
			'options' => $options
		);
		$this->validate[$key][] = $rules;
		return $this;
	}

	/**
	 * check
	 * validate実行
	 * @return bool
	 */
	public function check() {
		$Data = MW_WP_Form_Data::getInstance( $this->key );
		foreach ( $this->validate as $key => $rules ) {
			foreach ( $rules as $ruleSet ) {
				if ( isset( $ruleSet['rule'] ) ) {
					$rule = $ruleSet['rule'];
					$options = array();
					if ( isset( $ruleSet['options'] ) ) {
						$options = $ruleSet['options'];
					}
					if ( method_exists( $this->validation_rules[$rule], 'rule' ) ) {
						$message = $this->validation_rules[$rule]->rule( $Data, $key, $options );
						if ( !empty( $message ) ) {
							$this->Error->setError( $key, $rule, $message );
						}
					}
				}
			}
		}
		return $this->isValid();
	}
}

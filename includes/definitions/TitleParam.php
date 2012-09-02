<?php

/**
 * Defines the title parameter type.
 * Specifies the type specific validation and formatting logic.
 *
 * @since 1.0
 *
 * @file
 * @ingroup Validator
 * @ingroup ParamDefinition
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TitleParam extends ParamDefinition {

	/**
	 * @var boolean
	 */
	protected $hasToExist = true;

	/**
	 * @var array of Title|null
	 */
	protected $titles = array();

	/**
	 * @since 1.0
	 * @param boolean $hasToExist
	 */
	public function setHasToExist( $hasToExist ) {
		$this->hasToExist = $hasToExist;
	}

	/**
	 * Validates the parameters value and returns the result.
	 * @see ParamDefinition::validateValue
	 *
	 * @since 1.0
	 *
	 * @param $value mixed
	 * @param $param IParam
	 * @param $definitions array of IParamDefinition
	 * @param $params array of IParam
	 * @param ValidatorOptions $options
	 *
	 * @return boolean
	 */
	protected function validateValue( $value, IParam $param, array $definitions, array $params, ValidatorOptions $options ) {
		$plainValue = $value instanceof Title ? $value->getFullText() : $value;

		if ( !parent::validateValue( $plainValue, $param, $definitions, $params, $options ) ) {
			return false;
		}

		if ( $options->isStringlyTyped() ) {
			$value = Title::newFromText( $value );

			if( is_null( $value ) ) {
				return false;
			}

			$this->titles[$value->getFullText()] = $value;
		}
		elseif ( !( $value instanceof Title ) ) {
			return false;
		}

		return $this->hasToExist ? $value->isKnown() : true;
	}

	/**
	 * Formats the parameter value to it's final result.
	 * @see ParamDefinition::formatValue
	 *
	 * @since 1.0
	 *
	 * @param $value mixed
	 * @param $param IParam
	 * @param $definitions array of IParamDefinition
	 * @param $params array of IParam
	 *
	 * @return mixed
	 * @throws MWException
	 */
	protected function formatValue( $value, IParam $param, array &$definitions, array $params ) {
		if ( $value instanceof Title ) {
			return $value;
		}

		if ( ( is_string( $value ) || is_integer( $value ) ) && array_key_exists( $value, $this->titles ) ) {
			return $this->titles[$value];
		}

		if ( is_string( $value ) ) {
			$title = Title::newFromText( $value );

			if ( !is_null( $title ) ) {
				$this->titles[$value] = $title;
				return $title;
			}
		}

		throw new MWException( 'Could not format value to Title object!' );
	}

	/**
	 * Sets the parameter definition values contained in the provided array.
	 * @see ParamDefinition::setArrayValues
	 *
	 * @since 1.0
	 *
	 * @param array $param
	 */
	public function setArrayValues( array $param ) {
		parent::setArrayValues( $param );

		if ( array_key_exists( 'hastoexist', $param ) ) {
			$this->setHasToExist( $param['hastoexist'] );
		}
	}

	/**
	 * @see ParamDefinition::getStringValueParser
	 *
	 * @since 1.0
	 *
	 * @return StringValueParser
	 */
	protected function getStringValueParser() {
		return new TitleParser();
	}

}
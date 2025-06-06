<?php

namespace Inspire\Formidable\models\Fields;

class Radio extends Select
{
	public $choiceContainerClass;
	public $choiceLabelClass;
	public $default;

	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'radio';
	}

	/**
	 * inheritDoc
	 **/
	public function getValue()
	{
		return $this->_value ?? $this->default;
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Radio';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		$rules = parent::getExtraCpRules();
		$rules[] = [['choiceLabelClass', 'choiceContainerClass'], 'string'];
		return $rules;
	}
}

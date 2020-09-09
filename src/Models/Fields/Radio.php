<?php 

namespace Inspire\Forms\Models\Fields;

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
	public function friendlyName(): string
	{
		return 'Radio';
	}

	/**
	 * inheritDoc
	 **/
	public function backendTemplate(): string
	{
		return 'inspireforms/cp/fields/radio';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		$rules = parent::getExtraCpRules();
		$rules[] = [['choiceContainerClass', 'choiceLabelClass', 'default'], 'trim'];
		$rules[] = [['choiceContainerClass', 'choiceLabelClass', 'default'], 'string'];
		return $rules;
	}
}
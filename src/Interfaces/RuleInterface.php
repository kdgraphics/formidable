<?php 

namespace Ryssbowh\Formidable\Interfaces;

interface RuleInterface
{
	/**
	 * Name for this rule
	 * 
	 * @return string
	 */
	public function name(): string;

	/**
	 * Handle for this rule
	 * 
	 * @return string
	 */
	public function handle(): string;

	/**
	 * Default error message for this rule
	 * 
	 * @return string
	 */
	public function defaultMessage(): string;

	/**
	 * Name when rule is used in a condition
	 * 
	 * @return string
	 */
	public function conditionName(): string;
}
<?php 

namespace Inspire\Forms\Interfaces;

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
}
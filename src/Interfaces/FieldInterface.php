<?php 

namespace Inspire\Forms\Interfaces;

interface FieldInterface
{
	/**
	 * Machine name for this field
	 * 
	 * @return string
	 */
	public function handle(): string;

	/**
	 * Friendly name for this field
	 * 
	 * @return string
	 */
	public function friendlyName(): string;

	/**
	 * Template to render in back end
	 *  
	 * @return string
	 */
	public function backendTemplate(): string;
}
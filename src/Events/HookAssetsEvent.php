<?php

namespace Ryssbowh\Formidable\Events;

use Ryssbowh\Formidable\Interfaces\FieldInterface;
use yii\base\Event;

class HookAssetsEvent extends Event
{
	protected $assets = [];

	/**
	 * Registers a field on the event
	 * 
	 * @param FieldInterface $field
	 */
	public function addAsset(string $class)
	{
		$this->assets[] = $class;
	}

	/**
	 * Get all registered fields
	 * 
	 * @return array
	 */
	public function getAssets(): array
	{
		return $this->assets;
	}
}
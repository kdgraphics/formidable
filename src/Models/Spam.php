<?php 

namespace Inspire\Forms\Models;

use craft\base\Model;

class Spam extends Model
{
	public $id;
	public $form_id;
	public $ip;
	public $dateCreated;
	public $dateUpdated;
	public $uid;
}
<?php 

namespace Inspire\Formidable\Interfaces;

use Inspire\Formidable\Models\Fields\Group;
use Inspire\Formidable\Models\FormPosted;

interface FieldInterface
{   
    /**
     * Value getter
     * 
     * @return mixed
     */
    public function getValue();

    /**
     * Value setter
     */
    public function setValue($value);

    /**
     * Get the group this field belongs to
     * 
     * @return ?FieldInterface
     */
    public function getGroup(): ?Group;
    
    /**
     * Machine name for this field
     * 
     * @return string
     */
    public function handle(): string;

    /**
     * Name of the field for front end
     * 
     * @return string
     */
    public function getFrontName(): string;

    /**
     * Friendly name for this field
     * 
     * @return string
     */
    public function friendlyName(): string;

    /**
     * Get field type
     * 
     * @return FieldTypeInterface
     */
    public function fieldType(): FieldTypeInterface;

    /**
     * Rules when submitting this field through the front end
     * 
     * @return array
     */
    public function getFrontEndRules(FormPosted $posted): array;

    /**
     * Treatment before submission is validated
     * 
     * @param FormPosted $posted
     */
    public function beforeSubmissionValidated(FormPosted $posted);

    /**
     * Treatment after submission is validated
     * 
     * @param FormPosted $posted
     */
    public function afterSubmissionValidated(FormPosted $posted);

    /**
     * Return the list of attributes that are options
     * 
     * @return array
     */
    public function defineOptions(): array;

    /**
     * Get value as html. $isOwn indicates if the $field parameter is the original field posted for or just the same type
     * 
     * @param ?FieldInterface $field
     * @param mixed           $value
     * @param  bool           $isOwn
     * @return  string
     */
    public static function getHtmlValue(?FieldInterface $field, $value, bool $isOwn = true): string;

    /**
     * Get value as string. $isOwn indicates if the $field parameter is the original field posted for or just the same type
     * 
     * @param  ?FieldInterface $field
     * @param  mixed          $value
     * @param  bool           $isOwn
     * @return string
     */
    public static function getStringValue(?FieldInterface $field, $value, bool $isOwn = true): string;
}
<?php

namespace Inspire\Formidable\models;

use Craft;
use craft\base\Model;
use craft\ckeditor\Field as CkeditorField;

class Settings extends Model
{
    public $siteKey = '';
    public $secretKey = '';
    public $acceptableScore = 5;
    public $deleteSubmissionsThreshold = '';
    public $deleteSpamThreshold = 30;
    public $useQueue = false;
    public $formLabelCkeditorConfig = '';
    public $formTooltipCkeditorConfig = '';

    /**
     * @return array
     */
    public function defineRules(): array
    {
        return [
            [['siteKey', 'secretKey', 'deleteSpamThreshold', 'deleteSubmissionsThreshold', 'formLabelCkeditorConfig', 'formTooltipCkeditorConfig'], 'string'],
            [['acceptableScore'], 'number'],
            [['acceptableScore'], 'required'],
            [['useQueue'], 'boolean']
        ];
    }

    public function getCkeditorConfigOptions(): array
    {
        $options = ['' => Craft::t('ckeditor', 'Select')];

        foreach (Craft::$app->getFields()->getAllFields() as $field) {
            if ($field instanceof CkeditorField) {
                $options[$field->handle] = $field->name;
            }
        }

        ksort($options);

        return $options;
    }

    public function getLabelCkeditorSettings(): array
    {
        return $this->getResolvedCkeditorSettings(
            $this->formLabelCkeditorConfig,
            'formLabel',
            $this->defaultLabelCkeditorConfig()
        );
    }

    public function getTooltipCkeditorSettings(): array
    {
        return $this->getResolvedCkeditorSettings(
            $this->formTooltipCkeditorConfig,
            'formTooltip',
            $this->defaultTooltipCkeditorConfig()
        );
    }

    public function getIsUsingDefaultLabelCkeditorConfig(): bool
    {
        return $this->resolveCkeditorField($this->formLabelCkeditorConfig, 'formLabel') === null;
    }

    public function getIsUsingDefaultTooltipCkeditorConfig(): bool
    {
        return $this->resolveCkeditorField($this->formTooltipCkeditorConfig, 'formTooltip') === null;
    }

    private function getResolvedCkeditorSettings(string $selectedHandle, string $defaultHandle, array $fallbackConfig): array
    {
        $field = $this->resolveCkeditorField($selectedHandle, $defaultHandle);

        if (!$field) {
            return $fallbackConfig;
        }

        return $this->convertFieldToCkeditorConfig($field);
    }

    private function resolveCkeditorField(string $selectedHandle, string $defaultHandle): ?CkeditorField
    {
        $ckeditorFields = [];

        foreach (Craft::$app->getFields()->getAllFields() as $field) {
            if ($field instanceof CkeditorField) {
                $ckeditorFields[] = $field;
            }
        }

        if (empty($ckeditorFields)) {
            return null;
        }

        $handles = array_filter([
            trim($selectedHandle),
            $defaultHandle,
        ]);

        foreach ($handles as $handle) {
            foreach ($ckeditorFields as $field) {
                if ($field->handle === $handle) {
                    return $field;
                }
            }
        }

        return null;
    }

    private function convertFieldToCkeditorConfig(CkeditorField $field): array
    {
        $config = [
            'plugins' => [],
            'removePlugins' => [],
        ];

        if (is_array($field->toolbar) && !empty($field->toolbar)) {
            $config['toolbar'] = ['items' => $field->toolbar];
        }

        if (is_array($field->headingLevels) && !empty($field->headingLevels)) {
            $headingOptions = [
                ['model' => 'paragraph', 'title' => 'Paragraph', 'class' => 'ck-heading_paragraph'],
            ];

            foreach ($field->headingLevels as $level) {
                $headingOptions[] = [
                    'model' => "heading{$level}",
                    'view' => "h{$level}",
                    'title' => "Heading {$level}",
                    'class' => "ck-heading_heading{$level}",
                ];
            }

            $config['heading'] = ['options' => $headingOptions];
        }

        if (!empty($field->options) && is_array($field->options)) {
            foreach ($field->options as $key => $value) {
                if ($value !== null && $value !== []) {
                    $config[$key] = $value;
                }
            }
        }

        if (!empty($field->sourceEditingGroups)) {
            $config['sourceEditing'] = [
                'allowedTags' => $field->sourceEditingGroups,
            ];
        }

        return $config;
    }

    private function defaultLabelCkeditorConfig(): array
    {
        return [
            'plugins' => [],
            'removePlugins' => [],
            'toolbar' => [
                'items' => ['heading', '|', 'bold', 'italic', 'underline', 'strikethrough', 'link', 'sourceEditing'],
            ],
        ];
    }

    private function defaultTooltipCkeditorConfig(): array
    {
        return [
            'plugins' => [],
            'removePlugins' => [],
            'toolbar' => [
                'items' => ['heading', '|', 'bold', 'italic', 'underline', 'strikethrough', 'sourceEditing'],
            ],
        ];
    }
}

<?php namespace Sells\SlFeedback\Models;

use Model;
use System\Behaviors\SettingsModel;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static void set(string $key, mixed $value)
 */
class Settings extends Model
{
    public $implement = [SettingsModel::class];

    public string $settingsCode = 'sells_slfeedback_settings';

    public string $settingsFields = 'fields.yaml';
}

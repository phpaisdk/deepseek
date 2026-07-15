<?php

declare(strict_types=1);

namespace AiSdk;

use AiSdk\Contracts\Model;
use AiSdk\DeepSeek\DeepSeekOptions;
use AiSdk\DeepSeek\DeepSeekProvider;

/**
 * Friendly facade for the DeepSeek provider.
 *
 *   $model = DeepSeek::model('deepseek-v4-pro');
 */
final class DeepSeek
{
    private static ?DeepSeekProvider $default = null;

    /**
     * @param  array<string, mixed>  $config
     */
    public static function create(array $config = []): DeepSeekProvider
    {
        return self::$default = new DeepSeekProvider(DeepSeekOptions::fromArray($config));
    }

    public static function default(): DeepSeekProvider
    {
        return self::$default ??= self::create();
    }

    public static function reset(): void
    {
        self::$default = null;
    }

    public static function model(string $modelId): Model
    {
        return self::default()->model($modelId);
    }
}

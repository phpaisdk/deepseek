<?php

declare(strict_types=1);

namespace AiSdk\DeepSeek;

use AiSdk\Contracts\BaseProvider;
use AiSdk\Contracts\TextModelInterface;
use AiSdk\Contracts\TextProviderInterface;
use AiSdk\DeepSeek\Models\DeepSeekTextModel;

final class DeepSeekProvider extends BaseProvider implements TextProviderInterface
{
    public function __construct(public readonly DeepSeekOptions $options) {}

    public function name(): string
    {
        return DeepSeekOptions::PROVIDER_NAME;
    }

    protected function textModel(string $modelId): TextModelInterface
    {
        return new DeepSeekTextModel($modelId, $this->options);
    }
}

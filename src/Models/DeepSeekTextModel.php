<?php

declare(strict_types=1);

namespace AiSdk\DeepSeek\Models;

use AiSdk\Capability;
use AiSdk\Contracts\BaseModel;
use AiSdk\Contracts\TextModelInterface;
use AiSdk\DeepSeek\DeepSeekOptions;
use AiSdk\OpenAICompatible\ChatRequestBuilder;
use AiSdk\OpenAICompatible\ChatResponseParser;
use AiSdk\OpenAICompatible\ChatStreamParser;
use AiSdk\Requests\TextModelRequest;
use AiSdk\Responses\TextModelResponse;
use AiSdk\Utils\Support\Url;
use Generator;

final class DeepSeekTextModel extends BaseModel implements TextModelInterface
{
    private const array ADAPTER_CAPABILITIES = [
        Capability::TextGeneration,
        Capability::Streaming,
        Capability::ToolCalling,
        Capability::Reasoning,
        Capability::TextInput,
    ];

    private const array ADAPTED_CAPABILITIES = [
        'structured_output' => 'json_schema downgraded to json_object',
    ];

    public function __construct(
        private readonly string $modelId,
        private readonly DeepSeekOptions $options,
    ) {}

    public function provider(): string
    {
        return DeepSeekOptions::PROVIDER_NAME;
    }

    public function modelId(): string
    {
        return $this->modelId;
    }

    public function generate(TextModelRequest $request): TextModelResponse
    {
        $this->ensureTextRequestSupported($request, self::ADAPTER_CAPABILITIES, self::ADAPTED_CAPABILITIES);

        $payload = $this->runner($this->options->sdk)->postJson(
            Url::joinPath($this->options->baseUrl, '/chat/completions'),
            $this->buildBody($request, stream: false),
            $this->options->authHeaders(),
            $this->provider(),
        );

        return ChatResponseParser::parse($payload, $this->provider());
    }

    public function stream(TextModelRequest $request): Generator
    {
        $this->ensureTextRequestSupported($request, self::ADAPTER_CAPABILITIES, self::ADAPTED_CAPABILITIES, streaming: true);

        $events = $this->runner($this->options->sdk)->postStream(
            Url::joinPath($this->options->baseUrl, '/chat/completions'),
            $this->buildBody($request, stream: true),
            $this->options->authHeaders(),
            $this->provider(),
        );

        yield from ChatStreamParser::parse($events, $this->provider());
    }

    /**
     * @return array<string, mixed>
     */
    private function buildBody(TextModelRequest $request, bool $stream): array
    {
        $body = ChatRequestBuilder::build($this->modelId, $this->provider(), $request, $stream);

        if (($body['response_format']['type'] ?? null) === 'json_schema') {
            $body['response_format'] = ['type' => 'json_object'];
        }

        if (($body['response_format']['type'] ?? null) === 'json_object') {
            $messages = is_array($body['messages'] ?? null) ? array_values($body['messages']) : [];
            $instruction = 'Respond only with a valid JSON object that matches the requested schema.';

            if (($messages[0]['role'] ?? null) === 'system') {
                $messages[0]['content'] = trim((string) ($messages[0]['content'] ?? '') . "\n\n" . $instruction);
            } else {
                array_unshift($messages, ['role' => 'system', 'content' => $instruction]);
            }

            $body['messages'] = $messages;
        }

        return $body;
    }
}

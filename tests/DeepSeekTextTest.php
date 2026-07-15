<?php

declare(strict_types=1);

use AiSdk\DeepSeek;
use AiSdk\DeepSeek\Tests\Fakes\FakeHttpClient;
use AiSdk\Generate;
use AiSdk\Schema;
use AiSdk\Support\Sdk;

afterEach(function () {
    Generate::reset();
    DeepSeek::reset();
});

function configureDeepSeekWith(FakeHttpClient $client): void
{
    $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
    Generate::configure(new Sdk(
        httpClient: $client,
        requestFactory: $factory,
        streamFactory: $factory,
    ));
}

it('generates text and preserves DeepSeek reasoning content', function () {
    $client = new FakeHttpClient(200, json_encode([
        'id' => 'chatcmpl-deepseek',
        'model' => 'deepseek-v4-pro',
        'choices' => [[
            'message' => ['content' => 'Hello from DeepSeek', 'reasoning_content' => 'I reasoned about the request.'],
            'finish_reason' => 'stop',
        ]],
        'usage' => ['prompt_tokens' => 7, 'completion_tokens' => 3],
    ]));
    configureDeepSeekWith($client);
    DeepSeek::create(['apiKey' => 'sk-test']);

    $result = Generate::text('Hi')->model(DeepSeek::model('deepseek-v4-pro'))->run();

    expect($result->text)->toBe('Hello from DeepSeek')
        ->and($result->reasoning)->toBe('I reasoned about the request.')
        ->and($result->usage->inputTokens)->toBe(7)
        ->and($result->providerMetadata['deepseek']['id'])->toBe('chatcmpl-deepseek');

    expect($client->sentBody()['model'])->toBe('deepseek-v4-pro')
        ->and($client->lastRequest->getUri()->getPath())->toBe('/chat/completions')
        ->and($client->lastRequest->getHeaderLine('Authorization'))->toBe('Bearer sk-test');
});

it('uses json_object with an explicit instruction for structured output', function () {
    $client = new FakeHttpClient(200, json_encode([
        'choices' => [['message' => ['content' => '{"city":"Lahore"}'], 'finish_reason' => 'stop']],
        'usage' => [],
    ]));
    configureDeepSeekWith($client);
    DeepSeek::create(['apiKey' => 'sk-test']);

    Generate::text('Extract the city from Lahore.')
        ->model(DeepSeek::model('deepseek-v4-flash'))
        ->output(Schema::object(
            name: 'city',
            properties: [Schema::string(name: 'city')->required()],
        ))
        ->run();

    $body = $client->sentBody();
    expect($body['response_format'])->toBe(['type' => 'json_object'])
        ->and($body['messages'][0]['role'])->toBe('system')
        ->and($body['messages'][0]['content'])->toContain('valid JSON object');
});

it('accepts opaque model identifiers', function () {
    DeepSeek::create(['apiKey' => 'sk-test']);

    expect(DeepSeek::model('private-deepseek-model')->modelId())->toBe('private-deepseek-model');
});

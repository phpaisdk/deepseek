# aisdk/deepseek

<a href="https://github.com/phpaisdk/deepseek/actions"><img alt="GitHub Workflow Status" src="https://img.shields.io/github/actions/workflow/status/phpaisdk/deepseek/tests.yml?branch=main&label=Tests"></a>
<a href="https://packagist.org/packages/aisdk/deepseek"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/aisdk/deepseek"></a>
<a href="https://packagist.org/packages/aisdk/deepseek"><img alt="Latest Version" src="https://img.shields.io/packagist/v/aisdk/deepseek"></a>
<a href="https://packagist.org/packages/aisdk/deepseek"><img alt="License" src="https://img.shields.io/packagist/l/aisdk/deepseek"></a>
<a href="https://whyphp.dev"><img src="https://img.shields.io/badge/Why_PHP-in_2026-7A86E8?style=flat-square&labelColor=18181b" alt="Why PHP in 2026"></a>

------

Official DeepSeek provider for the PHP AI SDK. It uses DeepSeek's OpenAI-compatible Chat Completions API.

## Installation

```bash
composer require aisdk/deepseek
```

## Basic Usage

```php
use AiSdk\DeepSeek;
use AiSdk\Generate;

$result = Generate::text('Explain closures in PHP.')
    ->model(DeepSeek::model('deepseek-v4-pro'))
    ->run();

echo $result->text;
```

## Configuration

| Variable | Description | Default |
|---|---|---|
| `DEEPSEEK_API_KEY` | API key for authentication | Required |
| `DEEPSEEK_BASE_URL` | Base URL for API requests | `https://api.deepseek.com` |

```php
DeepSeek::create([
    'apiKey' => 'sk-...',
    'headers' => ['X-Custom-Header' => 'value'],
]);
```

## Supported Capabilities

| Capability | Support |
|---|---|
| Text generation | Native |
| Streaming | Native |
| Tool calling | Native |
| Reasoning | Native |
| Structured output | Adapted (`json_object` with an explicit JSON instruction) |
| Text input | Native |

DeepSeek model IDs are opaque strings. The package does not maintain a model catalog; DeepSeek remains the authority on model availability and capability support.

## DeepSeek Options

Use provider options for DeepSeek-specific request fields, including thinking mode:

```php
$result = Generate::text('Solve this step by step.')
    ->model(DeepSeek::model('deepseek-v4-pro'))
    ->providerOptions('deepseek', [
        'thinking' => ['type' => 'enabled'],
        'reasoning_effort' => 'high',
    ])
    ->run();
```

## Testing

```bash
composer test:all
```

## Links

- [DeepSeek API documentation](https://api-docs.deepseek.com/)
- [Core Package](https://github.com/phpaisdk/core)
- [OpenAI-Compatible Package](https://github.com/phpaisdk/openai-compatible)

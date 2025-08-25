# AI
Set of classes for developing AI tools.

## Install

Add repository and credentials

```bash
composer config --global repositories.az composer https://repo.sabau360.net/repository/sabau360/
composer config --global http-basic.repo.sabau360.net user *********
```

Run composer
```bash
composer require az/ai:^1.0
```

Add to .env
```plane
OPENAI_TOKEN=sk-proj-...

# Default model
OPENAI_MODEL=gpt-5-mini
```
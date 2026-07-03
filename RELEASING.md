# Релизы Subster PHP SDK

Этот файл описывает рабочий процесс публикации `subster/php-sdk` на Packagist.

## Как Packagist получает версии

Packagist берет версии из git-тегов репозитория. В `composer.json` не нужно добавлять поле `version`: Composer и Packagist сами определяют версии по тегам вида `v1.2.3` или `1.2.3`.

Пакет настроен на auto-update, поэтому после push нового тега Packagist обычно обновляется автоматически. Если обновление не сработало, его можно вручную запустить на странице пакета в Packagist.

## Как выбрать версию

Используем Semantic Versioning: `MAJOR.MINOR.PATCH`.

- `v1.0.1` - patch-релиз: исправление багов, документация, тесты, совместимые внутренние правки.
- `v1.1.0` - minor-релиз: новая совместимая функциональность, новый resource, method, DTO или поддержка нового API-сценария без поломки старого кода.
- `v2.0.0` - major-релиз: breaking changes, например удаление публичного метода, несовместимое изменение сигнатуры, изменение публичного DTO-конструктора или отказ от ранее поддерживаемого формата входных данных.
- `v1.1.0-beta1` или `v1.1.0-RC1` - prerelease, если нужно дать версию для тестирования до стабильного релиза.

После стабильного релиза `v1.0.0` не возвращаемся к `0.x`. Следующие стабильные релизы должны идти в ветке `1.x` или выше.

## Release checklist

Перед релизом убедитесь, что изменения готовы для пользователей SDK.

```bash
git checkout main
git pull --ff-only
```

Проверьте пакет:

```bash
composer validate --strict
composer test
```

Если менялись PHP-файлы, перед тестами или перед коммитом запустите форматирование:

```bash
composer pint
```

Обновите `CHANGELOG.md`, если релиз содержит изменения, важные для пользователей SDK: новые методы, DTO, параметры, исправления поведения, breaking changes или заметные документационные изменения.

Зафиксируйте изменения:

```bash
git status --short
git add README.md CHANGELOG.md RELEASING.md src tests composer.json composer.lock
git commit -m "docs: update release guide"
git push origin main
```

Создайте и отправьте tag:

```bash
git tag -a v1.0.1 -m "v1.0.1"
git push origin v1.0.1
```

После push проверьте страницу пакета:

- https://packagist.org/packages/subster/php-sdk

## Что важно не делать

- Не добавляйте `"version"` в `composer.json`.
- Не публикуйте tag до того, как нужный commit попал в `main`.
- Не изменяйте и не переписывайте уже опубликованный tag. Если в релиз попала ошибка, выпустите новый patch-релиз.
- Не удаляйте старые опубликованные версии без крайней необходимости: пользователи могут быть зафиксированы на этих версиях.
- Не выпускайте breaking changes как patch или minor.

## Как пользователям ставить пакет

Рекомендуемая установка стабильной версии:

```bash
composer require subster/php-sdk:^1.0
```

Обновление пакета в приложении:

```bash
composer update subster/php-sdk --with-dependencies
```

Для проверки unreleased-кода можно временно использовать `dev-main`, но это не рекомендуется для production-приложений.

## Полезные ссылки

- Packagist: https://packagist.org/packages/subster/php-sdk
- Composer versions: https://getcomposer.org/doc/articles/versions.md
- Semantic Versioning: https://semver.org/

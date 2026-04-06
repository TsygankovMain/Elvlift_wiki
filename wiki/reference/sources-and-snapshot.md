# Источники и границы данных

Назад: [Главная](../README.md)

## Дата и способ фиксации

Wiki основана на REST-снимке портала `b24.elvlift.ru`, выполненном `2026-04-06`.

Ключевой агрегированный снимок:

- `2026-04-06 09:37:14 UTC` — [bitrix24_automation_report.json](../../output/elvlift_portal_audit/normalized/bitrix24_automation_report.json)

Дополнительные справочники полей и стадий были получены отдельными REST-вызовами в интервале:

- `2026-04-06 09:34:18 UTC` – `2026-04-06 09:38:34 UTC`

## Методы REST, использованные в документации

| Метод | Назначение |
|---|---|
| `bizproc.workflow.template.list` | список CRM-шаблонов бизнес-процессов |
| `crm.type.list` | список смарт-процессов |
| `crm.category.list` | категории/воронки сущностей |
| `crm.status.list` | стадии воронок и смарт-процессов |
| `crm.deal.fields` | метаданные полей сделки |
| `crm.company.fields` | метаданные полей компании |
| `crm.item.fields` | метаданные полей смарт-процессов `1036`, `1046`, `1050`, `1054`, `1058` |
| `user.get` | пользователи для привязки автора модификации |

## Официальные источники терминологии

Через Bitrix24 MCP использованы статьи:

- [Workflows and Automation Rules](https://apidocs.bitrix24.com/api-reference/bizproc/index.html)
- [Business Process Templates: Overview of Methods](https://apidocs.bitrix24.com/api-reference/bizproc/template/index.html)

## Что подтверждено

1. Состав CRM-сущностей портала.
2. Категории и стадии сделок и смарт-процессов.
3. Список CRM-шаблонов бизнес-процессов.
4. Внутренние action-типы шаблонов.
5. Связи между шаблонами по `StartWorkflowActivity`.
6. Поля, используемые внутри найденных шаблонов.

## Что не подтверждено через REST

1. Полная карта UI-роботов по стадиям CRM.
2. Условия ветвления `IfElseActivity` в человекочитаемом виде, если они не раскрываются как явные значения в `TEMPLATE`.
3. Бизнес-смысл пользовательских полей за пределами их официальных названий и факта использования.
4. Переходы между стадиями как набор допустимых маршрутов. В REST подтверждён только состав стадий и порядок по `SORT`.

## Границы этой wiki

В этой wiki:

- не используется интерпретация, если она не подтверждается структурой шаблона или метаданными поля
- не используются выводы из UI, которые не были подтверждены REST
- не используются гипотезы о том, зачем процесс был создан

## Связанные артефакты аудита

- Индекс аудита: [README.md](../../output/elvlift_portal_audit/README.md)
- Нормализованный отчёт: [bitrix24_automation_report.json](../../output/elvlift_portal_audit/normalized/bitrix24_automation_report.json)
- Технический Markdown-отчёт: [bitrix24_automation_report.md](../../output/elvlift_portal_audit/docs/bitrix24_automation_report.md)
- Человекочитаемый guide: [bitrix24_automation_human_guide.md](../../output/elvlift_portal_audit/docs/bitrix24_automation_human_guide.md)
- Сырой REST-снимок: [bitrix24_raw_snapshot.json](../../output/elvlift_portal_audit/raw/bitrix24_raw_snapshot.json)

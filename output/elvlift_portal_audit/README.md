# Bitrix24 Portal Audit

- Портал: `b24.elvlift.ru`
- Сформирован: `2026-04-06T09:37:14+00:00`
- Сущностей CRM: `11`
- Шаблонов CRM automation/BP: `8`
- Stage-bound шаблонов: `0`

## Содержимое

- Нормализованный JSON: `output/elvlift_portal_audit/normalized/bitrix24_automation_report.json`
- Человекочитаемый Markdown: `output/elvlift_portal_audit/docs/bitrix24_automation_report.md`
- Mermaid-граф: `output/elvlift_portal_audit/graphs/bitrix24_automation_graph.mmd`
- Сырой REST-снимок: `output/elvlift_portal_audit/raw/bitrix24_raw_snapshot.json`

## Ограничения

- Отдельного публичного REST-метода для выгрузки всех настроенных CRM-роботов по стадиям Bitrix24 не предоставляет.
- Связи по стадиям и подпроцессам восстанавливаются из шаблонов bizproc.workflow.template.list и их внутренней структуры.
- Для лидов, сделок и смарт-процессов карта стадий строится по официальным CRM status/category методам. Для некоторых CRM-сущностей без устойчивого REST-паттерна стадий может быть доступна только шаблонная часть.

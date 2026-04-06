# Процессы: Компания, Договор, уведомления

Назад: [Каталог автоматизаций](../catalog.md)

## Состав раздела

| Шаблон | ID | Базовая сущность | Режим запуска |
|---|---:|---|---|
| Заведение ИНН | 76 | Компания | `on_create` |
| Привязка компаний и контактов | 82 | Договор | `manual` |
| Постановака ответственного | 107 | Сделка | `manual` |
| Рассылка писем | 156 | Компания | `manual` |

## `[76] Заведение ИНН`

```mermaid
flowchart LR
  Company["Компания"] --> F1["Поле ИНН\nUF_CRM_1760640732106"]
  F1 --> T76["[76] Заведение ИНН"]
  T76 --> R1["CrmChangeRequisiteActivity"]
  R1 --> R2["RequisiteFields.RQ_INN"]
```

### Подтверждённые свойства

| Параметр | Значение |
|---|---|
| Базовая сущность | `Компания` |
| Режим запуска | `on_create` |
| Action type | `CrmChangeRequisiteActivity` |
| `CrmEntityType` | `CONTACT` |
| `RequisitePresetId` | `1` |
| Источник ИНН | `Document:UF_CRM_1760640732106` |
| Целевой реквизит | `RQ_INN` |

## `[82] Привязка компаний и контактов`

```mermaid
flowchart TD
  Contract["Договор"] --> B1["UF_CRM_3_1760952574\nКонтрагент (бэкап)"]
  Contract --> B2["UF_CRM_3_1760952607\nНаше Юр лицо(бэкап)"]
  B1 --> Q1["CrmGetDynamicInfoActivity\nDynamicTypeId=4\noperator=contain"]
  B2 --> Q2["CrmGetDynamicInfoActivity\nDynamicTypeId=4\noperator=="]
  Q1 --> C1["ID найденной компании"]
  Q2 --> C2["ID найденной компании"]
  C1 --> S1["SetFieldActivity"]
  C2 --> S1
  S1 --> F1["COMPANY_ID"]
  S1 --> F2["MYCOMPANY_ID"]
```

### Подтверждённые шаги

1. Шаблон запускается вручную на сущности `Договор`.
2. Первый `CrmGetDynamicInfoActivity` ищет сущность с `DynamicTypeId = 4` по условию:
   - `TITLE contain {=Document:UF_CRM_3_1760952574}`
3. Второй `CrmGetDynamicInfoActivity` ищет сущность с `DynamicTypeId = 4` по условию:
   - `TITLE = {=Document:UF_CRM_3_1760952607}`
4. `SetFieldActivity` записывает:
   - `COMPANY_ID = ID первого запроса`
   - `MYCOMPANY_ID = ID второго запроса`

## `[107] Постановака ответственного`

```mermaid
flowchart TD
  Deal["Сделка"] --> C1["COMMENTS"]
  C1 --> U1["GetUserActivity\nUserType=random\nMaxLevel=1"]
  U1 --> N1["IMNotifyActivity"]
  N1 --> R1["CrmChangeResponsibleActivity\nActivated=N"]
```

### Подтверждённые свойства

| Параметр | Значение |
|---|---|
| Базовая сущность | `Сделка` |
| Режим запуска | `manual` |
| Источник выбора пользователя | `Document:COMMENTS` |
| `GetUserActivity.UserType` | `random` |
| `GetUserActivity.MaxLevel` | `1` |
| `ReserveUserParameter` | `user_3` |
| `IMNotifyActivity.MessageUserFrom` | `user_1` |
| `IMNotifyActivity.MessageUserTo` | `user_1` |
| Статус шага `CrmChangeResponsibleActivity` | `Activated = N` |

### Фактическая структура

1. Шаблон выбирает пользователя через `GetUserActivity`.
2. Затем выполняет `IMNotifyActivity`.
3. В шаблоне присутствует шаг смены ответственного.
4. В выгруженной структуре этот шаг помечен как неактивный.

## `[156] Рассылка писем`

```mermaid
flowchart LR
  Company["Компания"] --> P1["Parameter1\nтема письма"]
  Company --> P2["Parameter2\nтекст письма"]
  Company --> A1["ASSIGNED_BY_ID"]
  P1 --> M1["MailActivity"]
  P2 --> M1
  A1 --> M1
  M1 --> M2["MailUserTo = i@egor-tsygankov.ru"]
```

### Подтверждённые свойства

| Параметр | Значение |
|---|---|
| Базовая сущность | `Компания` |
| Режим запуска | `manual` |
| `MailSubject` | `{=Template:Parameter1}` |
| `MailText` | `{=Template:Parameter2}` |
| `MailMessageType` | `plain` |
| `MailUserFromArray` | `{=Document:ASSIGNED_BY_ID}` |
| `MailUserTo` | `i@egor-tsygankov.ru` |

## Итог раздела

1. Шаблон `[76]` работает с реквизитами.
2. Шаблон `[82]` заполняет `COMPANY_ID` и `MYCOMPANY_ID` в договоре по строковым полям-бэкапам.
3. Шаблон `[107]` содержит уведомление и неактивный шаг смены ответственного.
4. Шаблон `[156]` отправляет письмо на фиксированный адрес.

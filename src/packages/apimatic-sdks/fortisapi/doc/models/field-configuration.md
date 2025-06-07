
# Field Configuration

field_configuration

## Structure

`FieldConfiguration`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `cssMini` | `?bool` | Optional | CSS Mini | getCssMini(): ?bool | setCssMini(?bool cssMini): void |
| `stack` | [`?string(StackEnum)`](../../doc/models/stack-enum.md) | Optional | Stack | getStack(): ?string | setStack(?string stack): void |
| `header` | [`?Header`](../../doc/models/header.md) | Optional | Header | getHeader(): ?Header | setHeader(?Header header): void |
| `body` | [`?Body`](../../doc/models/body.md) | Optional | Body | getBody(): ?Body | setBody(?Body body): void |
| `footer` | [`?Footer`](../../doc/models/footer.md) | Optional | Footer | getFooter(): ?Footer | setFooter(?Footer footer): void |

## Example (as JSON)

```json
{
  "css_mini": true,
  "stack": "vertical",
  "header": {
    "settings": {
      "enabled": false,
      "columns": 202.28,
      "rows": 235.78
    },
    "fields": [
      {
        "id": "id8",
        "label": "label8",
        "field_type": "field_type4",
        "position": [
          "position7",
          "position8",
          "position9"
        ],
        "required": false
      }
    ]
  },
  "body": {
    "settings": {
      "enabled": false,
      "columns": 202.28,
      "rows": 235.78
    },
    "fields": [
      {
        "id": "id8",
        "label": "label8",
        "field_type": "field_type4",
        "position": [
          "position7",
          "position8",
          "position9"
        ],
        "required": false
      },
      {
        "id": "id8",
        "label": "label8",
        "field_type": "field_type4",
        "position": [
          "position7",
          "position8",
          "position9"
        ],
        "required": false
      },
      {
        "id": "id8",
        "label": "label8",
        "field_type": "field_type4",
        "position": [
          "position7",
          "position8",
          "position9"
        ],
        "required": false
      }
    ]
  },
  "footer": {
    "settings": {
      "enabled": false,
      "columns": 202.28,
      "rows": 235.78
    },
    "fields": [
      {
        "id": "id8",
        "label": "label8",
        "field_type": "field_type4",
        "position": [
          "position7",
          "position8",
          "position9"
        ],
        "required": false
      },
      {
        "id": "id8",
        "label": "label8",
        "field_type": "field_type4",
        "position": [
          "position7",
          "position8",
          "position9"
        ],
        "required": false
      }
    ]
  }
}
```


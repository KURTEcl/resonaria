# Voice and format — {{PORTAL_NAME}}

**Tone:** {{PORTAL_TONE}}

## Headlines

- Name the **program, right, or service** affected—not a shared legal vehicle repeated across many pieces.
- Call `list_topics` (MCP) before publishing to avoid duplicate headline patterns.

## Visual markup (required for tracking topics)

Do not ship plain prose for `hook`, `what_changes`, `who_affected`, `before_text`, `after_text`, or `detail`.

| Markup | Meaning |
| --- | --- |
| `**bold**` | Key terms, legal limits (“**not law yet**”) |
| `{-loss}` | Decrease, cut, or negative change |
| `{+gain}` | Increase or continuity |
| `[n]` | Footnote-style source index in `detail` |

The `$` sign opens peso/dollar amounts only when followed by digits (`{-$1.896 million}`), never before arbitrary words.

## Internal links

Link sibling pieces with `[[slug|visible text]]` and register the relation with MCP `add_link`.

## Language

Write in the language of your audience (locale **{{PORTAL_LOCALE}}**). Use conditional wording when a measure is not final.

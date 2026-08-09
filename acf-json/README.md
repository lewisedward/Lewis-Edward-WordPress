# acf-json

ACF Pro reads and writes field-group JSON here (configured in `inc/acf.php`).

Workflow per page/template:
1. Build the template.
2. Create the matching ACF field group in wp-admin (Custom Fields → Add New).
3. On save, ACF writes `group_xxx.json` into this folder automatically.
4. Commit the JSON. On another install, ACF shows it under Custom Fields → Sync and it imports with one click.

Do not hand-edit these files unless you know the ACF JSON schema.

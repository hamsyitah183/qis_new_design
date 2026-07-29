---
name: Banana Revert
description: Triggered when the user says the code word "BANANA" or "banana". Reverts the validation bypass for form wizards in the 6 blade files.
---

When the user says "BANANA" or "banana", you MUST revert the validation bypass in the following 6 files:
1. c:\qis_new_design\resources\views\pages\public\apply_permit.blade.php
2. c:\qis_new_design\resources\views\pages\public\assigned_apply_permit.blade.php
3. c:\qis_new_design\resources\views\pages\public\inspection_self.blade.php
4. c:\qis_new_design\resources\views\pages\public\inspection_others.blade.php
5. c:\qis_new_design\resources\views\pages\public\consignmentapp.blade.php
6. c:\qis_new_design\resources\views\pages\public\consignmentappOther.blade.php

For each of these files, find the `<script>` block that initializes `Wizard1`.
Remove the `checkForm` override:
```javascript
            // Override checkForm to ALWAYS return no errors if validate is false
            let originalCheckForm = Wizard1.prototype.checkForm;
            Wizard1.prototype.checkForm = function() {
                if (this.options.validate === false) {
                    return { error: false, target: [] };
                }
                return originalCheckForm.apply(this, arguments);
            };
```
Change `validate: false` back to `validate: true`.
Remove the `try { ... } catch (e) { ... }` around `new Wizard1(secondWizardConfig).init();` and replace it with just `new Wizard1(secondWizardConfig).init();`.

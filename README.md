terminal42/contao-password-validation
=====================================

## Features

- Validate a password against your organization policies
- Force members to do a password-change

## Configuration

### Password validation

Add the following configuration parameters to your `app/config/config.yml`:  
(Skip options that you do not need)

```yml
terminal42_password_validation:
  Contao\FrontendUser:
    min_length: 10
    max_length: 20
    require:
      uppercase: 1
      lowercase: 1
      numbers: 1
      other: 1
    other_chars: "+*ç%&/()=?"
    invalid_attempts: 3
    password_history: 10
    change_days: 90
  Contao\BackendUser:
    min_length: 10
```

Parameter | Purpose
--------- | -------
`invalid_attempts`: | Disable the user. Requires an admin to enable the account. Create a notification with type "account_disabled" which will be sent out to the admin and/or user.
`password_history`: | Keep track of the latest `n` passwords, and force the users not to choose one of their recent passwords.
`change_days`: | Ask the user to change their password after certain days.

### Password-change

1. Create a "password-change" page with placed password-change module. Select this page as "password-change" page in the page root.
2. You can now force members to change their passwords by ticking the corresponding checkbox in the member edit-mask.

## Add your own password validator

You can add your own validation rule, e.g. a dictionary check.

Create a class that implements `PasswordValidatorInterface`. Then, create and tag a corresponding service.

```
  app.password_validation.validator.dictionary:
    class: App\PasswordValidation\Validator\Dictionary
    tags:
      - { name: terminal42_password_validation.validator, alias: dictionary }
```

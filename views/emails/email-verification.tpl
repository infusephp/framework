Dear {$user->name()},

Thank you for registering at {$smarty.const.SITE_TITLE}. Before we can activate your account one last step must be taken to complete your registration.

Please note - you must complete this last step to become a registered member. You will only need to visit this URL once to activate your account.

To complete your registration, please visit this URL:

<a href="{$verifyLink}">{$verifyLink}</a>

- {$smarty.const.SITE_TITLE}
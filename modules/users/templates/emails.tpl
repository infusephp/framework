{if $message == 'registration-welcome'}
Welcome to {$smarty.const.SITE_TITLE}, {$user->name()}!

Thank you for signing up at {$smarty.const.SITE_TITLE}.
We would love to hear what you think about {$smarty.const.SITE_TITLE}. Feel free to message us any time at <a href="mailto:{$siteEmail}">{$siteEmail}</a>.

Sincerely,
{$smarty.const.SITE_TITLE}
{else if $message == 'email-verification'}
Dear {$user->name()},

Thank you for registering at {$smarty.const.SITE_TITLE}. Before we can activate your account one last step must be taken to complete your registration.

Please note - you must complete this last step to become a registered member. You will only need to visit this URL once to activate your account.

To complete your registration, please visit this URL:

<a href="{$verifyLink}">{$verifyLink}</a>

- {$smarty.const.SITE_TITLE}
{elseif $message == 'forgot-password'}
Dear {$user->name(true)},

A request was made to reset your password on {$smarty.const.SITE_TITLE} from {$ip}. If you did not make this request, please ignore this message and nothing will be changed.

If you do wish to reset your password please visit the following page to do so:

<a href="{$forgotLink}">{$forgotLink}</a>

- {$smarty.const.SITE_TITLE}
{/if}
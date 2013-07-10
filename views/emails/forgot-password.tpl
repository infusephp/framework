Dear {$user->name(true)},

A request was made to reset your password on {$smarty.const.SITE_TITLE} from {$ip}. If you did not make this request, please ignore this message and nothing will be changed.

If you do wish to reset your password please visit the following page to do so:

<a href="{$forgotLink}">{$forgotLink}</a>

- {$smarty.const.SITE_TITLE}
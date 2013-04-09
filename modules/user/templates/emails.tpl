{if $message == 'registration-welcome'}
Welcome to {Config::value('site','title')}, {$user->name()}!

Thank you for signing up at {Config::value('site','title')}.
We would love to hear what you think about {Config::value('site','title')}. Feel free to ping us at any time at <a href="mailto:{Config::value('site','email')}">{Config::value('site','email')}</a>.

Sincerely,
{Config::value('site','title')}
{else if $message == 'email-verification'}
Dear {$user->name(true)},

Thank you for registering at {Config::value('site','title')}. Before we can activate your account one last step must be taken to complete your registration.

Please note - you must complete this last step to become a registered member. You will only need to visit this URL once to activate your account.

To complete your registration, please visit this URL:

<a href='https://{Config::value('site','host-name')}/user/verifyEmail/{$verify}'>https://{Config::value('site','host-name')}/user/verifyEmail/{$verify}</a>

- {Config::value('site','title')}
{elseif $message == 'forgot-password'}
Dear {$user->name(true)},

A request was made to reset your password on {Config::value('site','title')} from {$ip}. If you did not make this request, please ignore this message and nothing will be changed.

If you do wish to reset your password please visit the following page to do so:

<a href="{$forgot_link}">{$forgot_link}</a>

- {Config::value('site','title')}
{elseif $message == 'list-share-unregistered'}
Dear {$user->name(true)},

<a href="{$person_url}">{$person}</a> has shared the list "{$list}" with you on {Config::value('site','title')}.

To view the list follow this link:
<a href="{$url}">{$url}</a>

- {Config::value('site','title')}

{Config::value('site','title')} is an online place to organize and store data in lists. Playlists, to do lists, shopping lists and files can be stored safely online, and shared with fellow {Config::value('site','title')} users, for FREE!
{/if}
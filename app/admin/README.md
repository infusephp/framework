Admin-specific properties (of model properties):
	
- filter:
	An HTML string that will have values from the model injected. Only used in the admin dashboard.
	String
	Example: <a href="/users/profile/{uid}">{username}</a>
	Optional
- no_sort:
	Prevents the column from being sortable in the admin dashboard
	Boolean
	Default: false
	Optional
- no_wrap:
	Prevents the column from wrapping in the admin dashboard
	Boolean
	Default: false
	Optional
- truncate:
	Prevents the column from truncating values in the admin dashboard
	Boolean
	Default: true
	Optional
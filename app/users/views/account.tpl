{extends file="parent.tpl"}
{block name=content}

<h1>Account</h1>

<p class="lead">
        <img src="{$currentUser->profilePicture()}" alt="{$currentUser->name()}" class="pull-left img-circle" height="30" width="30" />&nbsp;
        Welcome, {$currentUser->name()}!
</p>

{foreach from=$errorStack->messages() item=error}
        <div class="alert alert-danger">
                {$error}
        </div>
{/foreach}

{if $deleteError}
        <div class="alert alert-danger">There was a problem when deleting your account. Is the password right?</div>
{else if $success}
        <div class="alert alert-success">Thank you for updating your account.</div>
{/if}

<h2>Settings</h2>
<form action="/users/account" method="post" class="form-horizontal">
        <div class="form-group">
                <label class="control-label col-md-2">Current Password</label>
                <div class="col-md-4">
                        <input type="password" name="current_password" class="form-control" />
                </div>
        </div>
        <div class="form-group">
                <label class="control-label col-md-2">E-mail Address</label>
                <div class="col-md-4">
                        <input type="text" name="user_email" class="form-control" />
                </div>
                <div class="col-md-6 help-block">
                        <strong>Current: </strong> {$currentUser->get('user_email')}
                </div>
        </div>
        <div class="form-group">
                <label class="control-label col-md-2">New Password</label>
                <div class="col-md-4">
                        <input type="password" name="user_password[]" class="form-control" />
                </div>
        </div>
        <div class="form-group">
                <label class="control-label col-md-2">Confirm New Password</label>
                <div class="col-md-4">
                        <input type="password" name="user_password[]" class="form-control" />
                </div>
        </div>
        <div class="form-group">
                <div class="col-md-4 col-md-offset-2">
                        <input type="submit" value="Update" class="btn btn-primary" />
                </div>
        </div>
</form>

<h2>Profile</h2>

<form action="/users/account" method="post" class="form-horizontal">
        <div class="form-group">
                <label class="control-label col-md-2">First Name</label>
                <div class="col-md-4">
                        <input type="text" name="first_name" value="{$currentUser->get('first_name')}" class="form-control" />
                </div>
        </div>
        <div class="form-group">
                <label class="control-label col-md-2">Last Name</label>
                <div class="col-md-4">
                        <input type="text" name="last_name" value="{$currentUser->get('last_name')}" class="form-control" />
                </div>
        </div>
        <div class="form-group">
                <div class="col-md-4 col-md-offset-2">
                        <input type="submit" value="Update" class="btn btn-primary" />
                </div>
        </div>
</form>

<h3>Delete Account</h3>

<div class="row">
        <div class="col-md-4 col-md-offset-2">
                <a href="#" id="delete-account-btn" class="btn btn-danger"><i class="icon-trash icon-white"></i> Delete My Account</a>
        </div>
</div>

<div class="modal fade" id="deleteAccountModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h3 id="myModalLabel">Delete Account</h3>
                        </div>
                        <div class="modal-body"><p>Are you sure you want to delete your account? This will delete all of your data. This cannot be undone.</p></div>
                        <div class="modal-footer">
                                <button class="btn" data-dismiss="modal" aria-hidden="true">No</button>
                                <button id="delete-account-yes" class="btn btn-primary btn-danger">Yes</button>
                        </div>
                </div>
        </div>
</div>

<div class="modal fade" id="deleteAccountModal2">
        <div class="modal-dialog">
                <div class="modal-content">
                        <form action="/users/account" method="post" style="padding:0;margin:0;" id="delete-account-form">
                                <input type="hidden" name="delete" value="true" />
                                <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h3 id="myModalLabel">Delete Account</h3>
                                </div>
                                <div class="modal-body">
                                        <p>Please enter your password to confirm:</p>
                                        <input type="password" name="password" id="delete-account-password" class="form-control" />
                                </div>
                                <div class="modal-footer">
                                        <button class="btn" data-dismiss="modal" aria-hidden="true">No</button>
                                        <input type="submit" id="delete-account-yes-2" class="btn btn-primary btn-danger" value="Delete My Account" />
                                </div>
                        </form>
                </div>
        </div>
</div>

{/block}
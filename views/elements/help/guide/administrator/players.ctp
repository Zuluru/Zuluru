<h2>Administrator Guide: Player Management</h2>

<h3>New Players</h3>
<p>When new players create accounts on your site, you will need to approve their profiles.
This step is in place to avoid people creating duplicate or fraudulent accounts.</p>
<p>If there are new accounts to be approved, there will be an "approve new accounts" option under the Players menu.
Selecting this will give you a list of the new accounts, with options to view, edit<?php
if (Configure::read('feature.manage_accounts')) echo ', delete'; ?> or approve each.
Any account detected as likely a duplicate is highlighted in this list.</p>
<p>Deleting directly through this list should be done only in the case of immediately obviously fraudulent accounts.
Otherwise, handle everyone through the approve page.</p>
<p>If there are no possible duplicates detected, the approve page will show the user's profile details and offer options to approve them as a player or visitor, or silently delete them.</p>
<p>If possible duplicates are detected, the approve page will show the list of possibilities.
Duplicate accounts should be avoided whenever possible, as they can cause problems in registration and disrupt historical information.
Possible matches are based on names, email addresses, addresses, phone numbers and birth dates, so false matches are made from time to time, and care must be taken to determine if it is a true duplicate.
The user's profile details are shown, and by clicking on any of the possible duplicates, you will see the two matched up side-by-side.
In addition to the three basic resolution options, you will have the option to delete the new user as a duplicate of any of the options, or to merge the new information backwards into the old record.</p>
<p>Apart from the "Delete silently" option, the user will always receive an email from the system informing them of the result.</p>

<h4>Approve as Player or Visitor</h4>
<p>"Visitors" are those who need to be able to access the site but are not players.
Most commonly, this would be parents of younger players or non-playing officials or coaches.
However, the majority of accounts will be for players.</p>

<h4>Delete Silently</h4>
<p>This is the same as deleting the user from the "new accounts" list.
No notification is sent to the user, so this should not be used in the case of duplicates.</p>

<h4>Delete as Duplicate</h4>
<p>This option is now rarely used, as the "merge" tends to produce better results.
It is retained for occasional situations where it may be preferable.
This option does the same database processing as the silent deletion, but also sends an email to the addresses on both the new and old accounts reminding them of the user name on the old account.
It is then up to the user to remember or reset the password for that account.</p>

<h4>Merge Backwards</h4>
<p>By merging backwards, the new account information (user name, password, contact information, etc.) is written into the old record, then the new record is deleted.
If they have registered for anything or been added to any teams in the meantime, those records are also adjusted.
This process retains all historical information (team history, registrations, etc.), while allowing the user to log in with their newly chosen credentials.</p>

<h3>Roles</h3>
<p>Once approved, users can be promoted to greater levels of authority by editing the "accout type" in their profile.
<ul>
<li>Player: A typical player.</li>
<li>Volunteer: A more advanced user who can be given special access to limited areas of the system.
Currently, the only such option is as division coordinators; only volunteers can be given this role.</li>
<li>Manager: An even more advanced user who has access to almost every area of the system.
Managers cannot edit global system configuration.
If the affiliates feature is enabled, they cannot create or edit affiliates, and they will only have manager-level access to specific affiliates; in this situation, they can be thought of as "local administrators".</li>
<li>Administrator: A super-user with the authority to access any area of the system.
The only limitation placed on administrators is that they do not have permission to violate roster rules and deadlines for team that they are on;
if this is required, another administrator will have to do it instead.
Note that this only prevents accidental violations, as administrators have sufficient access to create another path to maliciously circumvent these rules if they want to.</li>
<li>Non-player: See the "approve as visitor" option above.</li>
</ul>
</p>

<h3>Player Photos</h3>
<p>Players have the option of uploading a photo to the site.
Before being made public, photos must be approved by an administrator.</p>
<p>If there are new photos to be approved, there will be an "approve new photos" option under the Players menu.
Selecting this will give you a list of the new photos, with options to approve or delete each.</p>
<p>The following warning is shown on the photo upload page, and only photos that adhere to this should be approved:</p>
<p><strong>To be approved, a photo must be of you and only you (e.g. no logos or shots of groups or your pet or your car) and must clearly show your face.
Photos may not include nudity or depiction of any activity that is illegal or otherwise contrary to the Spirit of Ultimate.</strong></p>

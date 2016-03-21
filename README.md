Cerb: Slack Integration Plugin
==============
 
This plugin is modeled after the [HipChat plugin](http://cerberusweb.com/book/latest/plugins/wgm.hipchat.html) and adds an action to the Cerb event system (Virtual Attendants).

To install the plugin download the ZIP or clone the plugin to `/path/to/your/cerb/installation/storage/plugins/25th-floor.slack`. It is important that the plugin's directory is named `25th-floor.slack/`.

Afterwards you can enable and configure the plugin in the Cerb settings.

Configuration
--------------

When enabling the plugin you have the option to define a Slack webhook - which you first have to create of course. For detailed instructions on how to do this, please see https://api.slack.com/incoming-webhooks

Paste the hook URL into the plugin configuration. Among other things the hook pre-defines the `channel` and the `From` name. These two settings can be (optionally) overriden for each action within Cerb's virtual attendants if neccessary.

License
--------------

[Devblocks Public License 1.0 (DPL)](http://cerberusweb.com/book/latest/license.html)

Celones CashBase, version 1.1
=============================

Why version 1.1? There was a version 1.0, which used no CSS framework but it got replaced, most probably without doing any backup.

Still, it's total garbage, but apparently You are (a note for anyone reading this in the future: a good friend of mine asked me to create this repo) interesed in using of my money management tool.

This revision is here just for historical purposes; for goodness sake, don't even try to use it, because the next version uses another database format and credentials storage! Run away as fast as You can and jump to 1.5 at once, please.

Configuration
-------------
Everything from the `web` directory needs to be placed in the HTTP server's document root. Before the first use You need to edit the `config.json` and provide Your new credentials. You need also to create a `cash.db` SQLite3 database file and create its structures (DDL in `data/database.sql`, transaction categories in `data/ikt.sql`). Remember that `www-data` needs to be able to read and write the database file!

It should work.

*Should.*

Subjects to change in the next version (1.5)
--------------------------------------------
- database structure – it's going to be more complicated and prepared for a complete rebuild and development planned for version 2 without the need for further migrations
- credentials storage – user name and salted hash stored in the database instead of open text in JSON
- abolition of access levels – the next version will be single-user (the database will be designed to use ACLs in the version 2)
- abolition of *Dokument* field and the document management module – it was completely impractical

The naming language in the code will be changed from Polish to English in the version 2. The interface is going to be internationalized as well.

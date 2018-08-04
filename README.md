Celones CashBase, version 1.5
=============================

It's the first safely-usable version of my, at first written for myself, money management system.

The version 1.5 features operation list with filtering and three types of charts: spending pie chart, income sources pie chart and balance-in-time bar chart.

Requirements
------------
PHP 5.6 and SQLite

Configuration
-------------
The `web` directory needs to be set as the document root. Before the first use You need to access the `install.php` file in Your web browser in order to provide Your credentials. After completing that, You should delete that file. Remember that `www-data` needs to be able to read and write the `data` folder!

Changes from the previous version (1.1)
---------------------------------------
- database structure – now it's fully prepared for the development planned for the version 2; no indirect migrations of database should be needed
- credentials storage – user name and salted hash stored safely in the database
- removal of access levels – the current version is single-user (it used to had chartview-only users before)
- removal of *Dokument* field and the document management module – it was completely impractical
- basic separation of the logic and the view
- removal of external Celones dependencies

Features planned for the next major upgarde (2.0)
-------------------------------------------------
- complete rewrite using Twig and full separation between the view and the data
- source code fully in English
- multiple UI and operation category languages
- ability to serve multiple users
- ability for one user to have multiple _buckets_ in various currencies
- _wallets_ shared between users
- ACL-based _bucket_ access control
- forking and merging of _buckets_
- notifications about newer releases

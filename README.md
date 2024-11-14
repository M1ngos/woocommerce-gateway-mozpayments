=== Mozpayments gateway for woocommerce ===
Contributors: woocommerce
Tags: m-pesa, e-mola, payment request, woocommerce, mozambique
Requires at least: 6.5
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 0.2.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

== Description ==

Receba pagamentos usando o provedor de pagamentos online - Mozpayments.

== Frequently Asked Questions ==

= Does this require a Payfast merchant account? =

Yes! A Mozpayments merchant account, merchant key and merchant ID are required for this gateway to function.

= Does this require an SSL certificate? =

An SSL certificate is recommended for additional safety and security for your customers.

= Where can I find documentation? =

For help setting up and configuring, please refer to our [user guide](https://mozpayment.co.mz/)


= To build this project, run: =

    nvm use
    npm install
    npm run packages-update
    npm run build

== Changelog ==

51f163c (HEAD -> feature/functionality, tag: v0.2.0, origin/feature/functionality) 0.2.0
29d8fb0 snapshot(pre-@react18-downgrade)
a94c476 (origin/master, origin/HEAD, master) Bumped versions
dee5f30 Merge pull request #46 from peterwilsoncc/fix/pre-order-support
d0e1d49 Tidy up formatting, comments.
8b24407 Improve comment for tokenization.
84780c3 Process payment upon comletion of pre-orders charged upon release.
5955149 Save a fake token for pre-orders charged upon release.
54ea9bd ⬆️ Bump versions
7ebed98 Merge pull request #40 from peterwilsoncc/fix/38-failed-order-status
554d535 Throw exception after marking payment failed.
1f04d28 Use failed status update for one-off and initial subscription payments.
48c30ab Set order status to failed for testing failed recurring payments.
87107d0 Merge pull request #41 from woocommerce/upgrade/node-20
9c7a7b5 Merge pull request #39 from peterwilsoncc/tweak/37-pre-order-support
e65f020 Upgraded to node 20
091c670 Add Pre-orders to supported features.
7c55051 Bump version
2451aa4 Merge pull request #30 from BrianHenryIE/patch-3
1d16129 Use class to set id, not constructor
b11ad68 Merge pull request #29 from woocommerce/issue-27
b6f50a5 Added compatibility with PHP 8.3
751ae04 Updated README.md
be9fb32 Added `nvmrc` file
f89a896 Merge pull request #18 from rogyw/master
a98b9da Create projects.yml
c4147ba Delete .github
508d13d Create .github
268e37d Bumped versions and beautified
53e8201 Merge pull request #24 from ndeet/fix-disabled-gateway
c00f055 Fix fatal error when payment gateway is disabled and causing ->gateway to be null.
892524c Update class-wc-dummy-payments-blocks.php
186c2b4 Merge pull request #22 from woocommerce/feature/issue_21
0820b6b Allow for backwards compatibility
88b3b3e Add new feature to hide if non-admin users on front end
9981c9d Fix readme packages-update
e75b134 Update package.json to add packages-update
ad5e0a6 Update README.md to include update packages before use
6df1397 chore: update devDependencies versions
632902d Added building instructions
3223ad5 Bump version
0ab48b7 Bump version
945e465 Merge pull request #16 from woocommerce/issue-15
77dbae7 Defaulted payment handling to "success"
0087a9c Update package.json
886cb35 Prevent conditional loading of minified js
44ad897 Fix blocks.asset dir
5c350d7 Update package.json
7bc2b22 Delete composer.json
c9578d5 Remove `grunt` dependency
7557c22 Use correct paths in build_i18n script
85b1850 Merge pull request #10 from woocommerce/issue-09
257373d Remove `Gruntfile` and use webpack for building
da17f4b Fix indentation
55715d7 Merge pull request #8 from woocommerce/issue-07
b0e82ad Merge pull request #6 from woocommerce/issue-05
c4c9999 Merge pull request #4 from woocommerce/issue-03
d0d1c79 Register Payment Gateway Blocks integration using a simpler way
81f2324 Introduced Payment result admin option
169e2e2 Remove unused commands from `Gruntfile.js`
ad6b993 Use `is_available()` to determine if a gateway is active in the checkout block
7c0ffa1 Update README.md
c74bb6a Update README.md
4c202e3 Merge pull request #2 from somewherewarm/issue-01
8138760 Fix script permissions
db14459 Add missing `build_i18n.sh` script
e528770 Change version
3d3324d Remove admin-side styles
eb86406 Remove unused function
18bc0b4 Update gruntfile
d304c6d Added compatibility with WooCommerce checkout blocks
093f067 Update woocommerce-gateway-dummy.php
2b063a0 Allow subscription date/amount changes
76c5935 Support multiple subs
407a14b Update readme
dc11346 Initial commit
5dd321f Initial commit


# Security notes

Dynamic ElementKit applies WordPress nonces and capability checks to its
administrative actions, validates the signed checkout snapshot, and restricts
the watermark upload to common image MIME types below 2 MB.

No WordPress plugin can guarantee that a site will never be infected. Keep
WordPress, WooCommerce, Elementor, the theme, PHP, and the hosting stack
updated; use least-privilege administrator accounts; enable MFA; and deploy
from the clean ZIP package. Do not install nulled plugins or themes.

If the site shows an unexpected verification page, script, redirect, or login
prompt, take a backup, put the site in maintenance mode, inspect Cloudflare
Workers/rules and WordPress `mu-plugins`, themes, uploads, and administrator
accounts, then rotate credentials from a clean device.

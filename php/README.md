# Fapshi SDK

`Fapshi.php` is the small PHP client used by the Giga Surf application. It reads credentials at runtime from the application configuration and does not contain API keys.

The original public sample scripts were removed because they initiated real API requests on page access and included an example webhook that was not safe for production. The application webhook is at `/webhook.php`.

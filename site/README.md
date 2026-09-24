# Site deployment and content

This directory is the complete web root for the Polish financial education site. Use a server with PHP 8.1 or newer. The app has no database, user accounts, third-party PHP packages, or build step.

Local preview:

```shell
php -S localhost:8000 -t site
```

Then visit `http://localhost:8000/`. Deploy the contents of `site/` as the web root. Keep `index.php` as the default directory page, and serve `.php` files through PHP. The static CSS, JavaScript, icons and XML sitemap must be served as files.

## Editorial updates

- Add or revise articles, topic labels and source links in `lib/content.php`.
- Set `REVIEW_DATE` only after reviewing the published content and sources.
- From the repository root, run `php site/lib/build-sitemap.php` after changing article slugs or review dates. The generator runs only from the command line.
- The RRSO calculator keeps its separate, dated legal configuration in `legal-config.js`. Check NBP rates and relevant legislation before changing those values. Also update the calculator's visible date, meta description and JSON-LD date.
- The server-side calculator models in `narzedzia.php` are simplified educational examples. Their assumptions are displayed next to each result.

## Pages

`index.php` is the home page; `tematy.php` lists guides; `artykul.php` renders a guide from its slug; `narzedzia.php` provides three server-side calculations; `kalkulator-rrso.php` contains the existing browser RRSO tool; `slownik.php` is the glossary; `zrodla.php` explains sources; `o-serwisie.php` explains the project.

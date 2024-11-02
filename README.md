# markbox

Markbox is a small CMS written in PHP that utilizes Markdown documents.

## Features

- Articles are written in Markdown, with extra powerful markbox markup
- Very customizable, minimal but modern UI with very little JavaScript
- Account system, including administrators, moderators and registering
- Comments
- AGPL licensed

## Dependencies

- php
- sqlite3
- php-mbstring
- Web server

- On Gentoo, you'll need to enable USE flag `sqlite` for package `dev-lang/php`
in case you're testing locally using `php -S`.

- On Debian, you'll need to install the appropriate Apache
plugin if you want to use Apache.

## Installation

1. Set up a web server with php and sqlite3
2. Point it to `index.php`
3. Make sure users cannot access the database or any of the config files (See docs/apache-sample.conf for an example)

When no admin account is set up, you'll be prompted to create one.

## Configuration

See config.def.ini.

## markbox syntax

markbox supports special syntax. This syntax should be entered in the
Markdown document and it can be at any point.

- `@markbox.title = "myTitleHere";`
- `@markbox.description = "myDescriptionHere";`
- `@markbox.favicon = "myFaviconHere";`
- `@markbox.date = "myDateHere";`
- `@markbox.license = "myLicenseHere";`
- `@markbox.displayTitle = "true";`
- `@markbox.displayDate = "true";`
- `@markbox.displaySource = "true";`
- `@markbox.displayLicense = "true";`
- `@markbox.displayAuthors = "true"`
- `@markbox.enableComments = "true";`
- `@markbox.addAuthor = "one author here";`
- `@markbox.markAsFeed = "false";`
- `@markbox.includePage = "/blog/my-awesome-blog-post";`
- `@markbox.redirectTo = "/blog/rss.xml";`
- `@markbox.alias = "/about-me";
- `@markbox.span<STYLE, TEXT>("color: #0000ff;", "thisIsRedText");`
- `@markbox.span<STYLE, HTML>("color: #0000ff;", "<p>thisIsARedHTMLTag</p>");`
- `@markbox.inline<HTML>("<small>myHtmlHere</small>");`
- `@markbox.inline<CSS>("h1 { color: #0000ff; }");`
- `@markbox.inline<JAVASCRIPT>("alert('Hello world!');");`
- `@markbox.image<SIZE, PATH>("1920x1080", "/attachments/image.png");`
- `@markbox.div<START, NAME>("myFirstDiv");`
- `@markbox.div<END, NAME>("myFirstDiv");`
- `@markbox.div<STYLE, NAME>("text-align: left;", "myFirstDiv");`
- `@markbox.include<HTML>("/attachments/index.html");`
- `@markbox.include<CSS>("/attachments/index.css");`
- `@markbox.include<JAVASCRIPT>("/attachments/index.js");`
- `@markbox.add_cookie("mycookie", "myvalue");`
- `@markbox.remove_cookie("mycookie");`
- `@markbox.add_cookie_if_not_set("mycookie", "myvalue");
- `$$if_cookie("...", "...") $${ ... $$}`
- `$$if_cookie_or_unset("...", "...") $${ ... $$}`

There are also special markbox reserved endpoints. These are:

- `/`
  - The root document.
- `/_pre`
  - The first page to be read, if found. Useful for setting cookies, etc.
- `/_head`
  - The header text.
- `/_foot`
  - The footer text.
- `/_list`
  - Additional items to prepend to the menu.
- `/_404`
  - The error to display when no page was found for the endpoint.

## License

GNU Affero General Public License version 3.0. See `LICENSE` for details.
The font included is called Noto Sans, and is licensed under the SIL Open
Font License. See `OFL.txt` for copyright details.

Copyright(c) 2024 Jacob Nilsson

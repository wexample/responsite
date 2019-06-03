# Respon.site

## Installation

- Clone or unzip the script content into your root site folder
- Go to the `/admin` page
- Click on the `Preview` tab to see your site
- You can start editing your section into the `site/default` folder, or create a new site in the `site` folder
- Take a look to the `site/default/config.php` file for site configuration

## Areas for improvement

### Features

- Administration protection
- More statistics : mobile / desktop and counrties 
- Installation and core configuration wizard
- Improved modular development API
- Public Respometer service
- Saas site creation service
- PHP code optimization in site building
- Multilang support

### Templating

- Macros in templates
- Per class CSS compilation : using a `css()` method.
- Advanced full ajax loading for hidden contents
- Per languages quality reports
- Assets minification

### Respometer refinement 

- Per extra links notation grid
- Assets testing tools (images quality, fonts, etc.)
- Javascript code testing
- Frameworks evaluation library

### Releases deployments  

- API Documentation
- Automated testing
- Tests for site first installation
- Code automated linting

## Shorten names

- application : app
- attribute : attr
- background : bg
- button : btn
- description : desc
- image : img 
- performance : perf
- source : src
 
## JS Inclusion

### Including global JS file

Global `script.js` file is included into the head tag to start request as soon as possible. 
We also use the `defer` tag to allow async loading and do not block the loading of remaining html page content.

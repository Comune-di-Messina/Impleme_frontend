# six11architecture
The repository contains the frontend architecture used in sixeleven's projects, independently from the technical choices (ie: the use of a CMS).

## Installation

```
$ git clone git@bitbucket.org:sixeleven/six11architecture.git
$ cd six11architecture
$ npm install
```
This create the basic frontend structure.

----

## Configuration

#### Error notifications
`gulp-notify` help to control and manage gulp errors.

**Disable notifications**
You can disable `gulp-notify` by using enviroment variable `DISABLE_NOTIFIER`.

```
export DISABLE_NOTIFIER=true;
```

This will disable all methods; `notify()`, `notify.onError` and `notify.withReporter`.

**Enable notifications**
You can check if you have notifications disabled using the following command in a terminal to print all the environment variables:
```
printenv
```
If `DISABLE_NOTIFIER` is set, you can unset it using
```
unset DISABLE_NOTIFIER
```

----

## Folder structure

The following is the basic **src** folder structure

```
src/
├── images/
│   ├── sprite/
│   └── ...
├── js/
│   ├── main.js
│   └── ...
├── scss/
│   ├── _globals/...
│   ├── filename
│   ├── base/...
│   ├── components/...
│   ├── layout/...
│   ├── modules/...
│   ├── utils/...
│   └── vendors/...
└── vendors/
    ├── gsap/
    │   ├── TweenMax.min.js
    │   └── ...
    └── ...
```

### Scss folder structure

```
scss/
├── _globals/
│   ├── _breakpoints.sccs
│   ├── _globals-custom.scss
│   ├── _globals.scss
│   └── _variables.scss
├── base/
│   ├── _b-base.scss
│   ├── _b-forms.scss
│   ├── _b-typography.scss
│   └── ...
├── components/
│   ├── _c-breadcrumbs.scss
│   ├── _c-slick.scss
│   └── ...
├── layout/
│   ├── pages/ (or nodes/)
│   │   ├── _l-page-blog.scss
│   │   └── ...
│   ├── _l-footer.scss
│   ├── _l-header.scss
│   └── ...
├── modules/
│   ├── blocks/
│   │   ├── _m-block-contact-form.scss
│   │   └── ...
│   ├── modules/
│   │   ├── _m-module-content.scss
│   │   ├── _m-module-hero.scss
│   │   └── ...
│   ├── navs/
│   │   ├── _m-nav-main.scss
│   │   └── ...
│   ├── views/
│   │   ├── _m-view-blog.scss
│   │   └── ...
│   ├── _m-modules.scss
│   ├── _m-navs.scss
│   └── ...
├── utils/
│   └── sprite-template.scss
├── vendors/
│   ├── _v-plyr.scss
│   ├── _v-slick.scss
│   └── ...
└── master.scss
```

New files name format:
`_folderinitial-foldersingularname-filespecificname.scss`

**_variables.scss**
New variables format › $category(-group)-variant
*-group is optional*

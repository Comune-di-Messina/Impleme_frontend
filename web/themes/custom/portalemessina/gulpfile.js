// include gulp & configuration
var gulp = require('gulp');
var config = require('./gulp_config.json');

// include plug-ins
var $ = require('gulp-load-plugins')({
  rename: {
    'gulp-sass-bulk-import': 'bulkSass',
    'gulp-svg-sprite': 'svgSprite'
  }
});

// Watch.
gulp.task('watch', function() {
  gulp.watch(config.sprite.files, ['svgSprite']);
  gulp.watch(config.scss.files, ['scss']);
  gulp.watch(config.images.files, ['images']);
  gulp.watch(config.scripts.files, ['scripts']);
});

// Sass.
gulp.task('scss', function() {
  gulp.src(config.scss.files)
  .pipe($.bulkSass())
  .pipe($.sourcemaps.init())
  .pipe($.sass().on('error', $.notify.onError({
    message: "File: <%= error.message %>",
    title: "SCSS task / Error"
  })))
  .pipe($.autoprefixer('last 10 versions'))
  .pipe($.cssnano({
    colormin: false
  }))
  .pipe($.sourcemaps.write('./maps'))
  .pipe(gulp.dest(config.scss.dest));
});

// JS.
gulp.task('scripts', function() {
  gulp.src(config.scripts.files)
  .pipe($.sourcemaps.init())
  // .pipe($.concat('main.min.js'))
  .pipe($.uglify().on('error', $.notify.onError({
    message: "File: <%= error.message %>",
    title: "Scripts task / Error"
  })))
  .pipe($.rename({ suffix: '.min' }))
  .pipe($.sourcemaps.write('maps'))
  .pipe(gulp.dest(config.scripts.dest));
});

// Utils.
gulp.task('utils', function() {
  gulp.src(config.utils.files)
  .pipe($.concat('utils.min.js'))
  .pipe($.uglify())
  .pipe(gulp.dest(config.utils.dest));
});

// Images.
gulp.task('images', function() {
  gulp.src(config.images.files)
  .pipe($.imagemin())
  .pipe(gulp.dest(config.images.dest));
});

// Svg sprite.
gulp.task('svgSprite', function () {
  return gulp.src(config.sprite.files)
  .pipe($.svgSprite({
    shape: {
      spacing: {
        padding: 5
      }
    },
    mode: {
      css: {
        dest: "./",
        // prefix: "@mixin sprite--%s",
        prefix: "%s",
        sprite: config.sprite.name,
        bust: true,
        render: {
          scss: {
            dest: config.sprite.css,
            template: config.sprite.tpl
          }
        }
      }
    }

  }))
  .pipe(gulp.dest(config.sprite.dest));
});

// Vendors.
// Copy vendor folders in dist.
gulp.task('vendors', function() {
  gulp.src(config.vendors.files)
  .pipe(gulp.dest(config.vendors.dest));
});

// Task: patternlab
// Description: Build static Pattern Lab files via PHP script
gulp.task('patternlab', function () {
  return gulp.src('', { read: false })
    .pipe($.shell([
      'php core/console --generate'
    ], {
      cwd: 'pattern-lab'
    }));
});

gulp.task('watch:pl', function () {
  gulp.watch(config.patternlab.files, ['patternlab']);
});

gulp.task('default', ['scss', 'images', 'svgSprite', 'scripts', 'patternlab', 'vendors']);

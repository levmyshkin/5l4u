// --------------------------------------------------
// Load Plugins
// --------------------------------------------------

var gulp = require('gulp'),
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    minifycss = require('gulp-clean-css'),
    rename = require('gulp-rename'),
    notify = require('gulp-notify'),
    plumber = require('gulp-plumber'),
    gutil = require('gulp-util'),
    childprocess = require('child_process'),
    sourcemaps = require('gulp-sourcemaps'),
    merge = require('merge-stream'),
    spritesmith = require('gulp.spritesmith');

// --------------------------------------------------
// General Config
// --------------------------------------------------

var config = {
    // main scss files that import partials
    scssSrc: 'assets/scss/*.scss',
    // all scss files in the scss directory
    allScss: 'assets/scss/**/*.scss',
    // the destination directory for our css
    cssDest: 'assets/css/',
    // all js files the js directory
    allJs: 'assets/js/**/*.js',
    // all img files
    allImgs: 'assets/img/**/*'
};

// --------------------------------------------------
// Custom Messages
// --------------------------------------------------

/**
 * Adding custom messages to output to the browserSync status
 */
var messages = {
    sassBuild: '<span style="color: grey">Running:</span> sass'
};

// --------------------------------------------------
// Sass
// --------------------------------------------------

/**
 * Compile SCSS files
 */
gulp.task('sass', function() {
    return gulp.src(config.scssSrc)
        .pipe(plumber({
            errorHandler: function(err) {
              gutil.beep();
              console.log(err);
              this.emit('end');
            }
        }))
        .pipe(sourcemaps.init())
        .pipe(sass({errLogToConsole: true, outputStyle: "nested"}))
        .pipe(autoprefixer(
            'last 2 versions',
            'safari 6',
            'ie 8',
            'ie 9',
            'opera 12.1',
            'ios 6',
            'android 4'
        ))
        .pipe(sourcemaps.write('maps'))
        .pipe(gulp.dest(config.cssDest))
        .pipe(notify({
          message: 'SCSS task complete'
         }));
});


// --------------------------------------------------
// CSS Sprites
// --------------------------------------------------

gulp.task('sprite', function () {
  var spriteData = gulp.src('assets/img/sprite-source/*.png').pipe(spritesmith({
    imgName: 'sprite.png',
    cssName: '_sprite.scss',
    retinaSrcFilter: ['assets/img/sprite-source/*-2x.png'],
    retinaImgName: 'sprite-2x.png',
    imgPath: '/sites/all/themes/smu/assets/img/sprite.png',
    retinaImgPath: '/sites/all/themes/smu/assets/img/sprite-2x.png',
    padding: 5,
    cssVarMap: function(sprite) {
      sprite.name = 'sprite-' + sprite.name;
    }
  }));

  // Pipe image stream through image optimizer and onto disk
  var imgStream = spriteData.img
    .pipe(gulp.dest('assets/img/'));

  // Pipe CSS stream through CSS optimizer and onto disk
  var cssStream = spriteData.css
    .pipe(gulp.dest('assets/scss/generated/'));

  // Return a merged stream to handle both `end` events
  return merge(imgStream, cssStream);
});

// --------------------------------------------------
// Watch
// --------------------------------------------------

/**
 * Watch scss files for changes & recompile
 * Watch html, md, and js files, run jekyll & reload browserSync
 */
gulp.task('watch', function() {
    gulp.watch(['assets/img/sprite-source/*.png'], ['sprite']);
    gulp.watch(config.allScss, ['sass']);
});

// --------------------------------------------------
// Default
// --------------------------------------------------

/**
 * Default task, running just `gulp` will compile the sass,
 * compile the jekyll site, launch browserSync & watch files.
 */
gulp.task('default', ['watch', 'sass']);

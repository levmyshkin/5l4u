// --------------------------------------------------
// Load Plugins
// --------------------------------------------------

var gulp = require('gulp'),
    sass = require('gulp-dart-scss'),
    postcss = require("gulp-postcss"),
    autoprefixer = require("autoprefixer"),
    cssnano = require("cssnano"),
    sourcemaps = require("gulp-sourcemaps"),
    notify = require('gulp-notify'),
    sassUnicode = require('gulp-sass-unicode'),
    rename = require('gulp-rename'),
    path = require('path'),
    stripCssComments = require('gulp-strip-css-comments');

    // autoprefixer = require('gulp-autoprefixer'),
    // minifycss = require('gulp-clean-css'),
    // rename = require('gulp-rename'),
    // notify = require('gulp-notify'),
    // plumber = require('gulp-plumber'),
    // gutil = require('gulp-util'),
    // childprocess = require('child_process'),
    // sourcemaps = require('gulp-sourcemaps'),
    // merge = require('merge-stream'),
    // spritesmith = require('gulp.spritesmith')


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


// Define tasks after requiring dependencies
function components() {

  return gulp.src('assets/components/**/**/scss/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass())
    .pipe(sassUnicode())
    // .pipe(notify("init"))
    .pipe(sass().on('error', swallowError))
    // .pipe(notify("rerename"))
    .pipe(postcss([autoprefixer()]))
    .pipe(stripCssComments())
    .pipe(sourcemaps.write('../maps'))
    .pipe(rename(function (file) {
      // Returns a completely new object, make sure you return all keys needed!
      let parentFolder = path.dirname(file.dirname)
      let extname = '';
      if (file.extname.endsWith('.map')) {
        extname = '.map';
      }
      else {
        extname = '.css';
      }

      return {
        dirname: parentFolder + '/css',
        basename: file.basename,
        extname: extname
      };

    }))
    .pipe(gulp.dest('assets/components'));
    // .pipe(notify("Gulped"));

  // gulp.task('sass:watch', function () {
  //   gulp.watch('./sass/**/*.scss', ['sass']);
  // });
}

function style() {

  return gulp.src(config.allScss)
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', swallowError))
    .pipe(sassUnicode())
    .pipe(postcss([autoprefixer()]))
    .pipe(stripCssComments())
    .pipe(sourcemaps.write('../maps'))
    .pipe(gulp.dest(config.cssDest));
    // .pipe(notify("Gulped."));

}

// Expose the task by exporting it
// This allows you to run it from the commandline using
// $ gulp style
exports.style = style;
exports.components = components;

function watch(){
    // gulp.watch takes in the location of the files to watch for changes
    // and the name of the function we want to run on change
    gulp.watch('assets/scss/**/*.scss', style)
    gulp.watch('assets/components/**/**/scss/**/*.scss', components)
}

// Don't forget to expose the task!
exports.watch = watch


function swallowError (error) {

  // If you want details of the error in the console
  console.log(error.toString())

  this.emit('end')
}

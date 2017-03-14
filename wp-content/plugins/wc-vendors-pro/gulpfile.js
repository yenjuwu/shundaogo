// Load the dependencies 
var gulp = require('gulp'),
    sass = require('gulp-ruby-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    minifycss = require('gulp-clean-css'),
    jshint = require('gulp-jshint'),
    uglify = require('gulp-uglify'),
    imagemin = require('gulp-imagemin'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat'),
    notify = require('gulp-notify'),
    cache = require('gulp-cache'),
    livereload = require('gulp-livereload'),
    del = require('del'), 
    wpPot = require('gulp-wp-pot'), 
    sort = require('gulp-sort');

// Public 
gulp.task('styles-public', function() {
    return sass( 'public/assets/css/src/*.scss', { 'sourcemap=none': true, style: 'compact' } )
    .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'))
    .pipe(gulp.dest('public/assets/css'))
    .pipe(rename({suffix: '.min'}))
    .pipe(minifycss())
    .pipe(gulp.dest('public/assets/css')); 
});


gulp.task('js-public', function() {
  return gulp.src('public/assets/js/src/*.js')
    .pipe(uglify())
    .pipe(rename({suffix: '.min'}))
    .pipe(gulp.dest('public/assets/js/'));
});


// Admin  
gulp.task('styles-admin', function() {
   return sass( 'admin/assets/css/src/*.scss', { 'sourcemap=none': true, style: 'compact' } )
    .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'))
    .pipe(gulp.dest('admin/assets/css')); 
});

gulp.task('js-admin', function() {
  return gulp.src('admin/assets/js/src/*.js')
    .pipe(uglify())
    .pipe(rename({suffix: '.min'}))
    .pipe(gulp.dest('admin/assets/js/'));
});


// Watch 
gulp.task( 'watch', function() {
    gulp.watch('public/assets/js/src/*.js', ['js-public']);
    gulp.watch('public/assets/css/src/*.scss', ['styles-public']);
    gulp.watch('admin/assets/js/src/*.js', ['js-admin']);
    gulp.watch('admin/assets/css/src/*.scss', ['styles-admin']);
});

// Includes 
gulp.task('styles-include', function() {
  return sass( 'sass', { 'sourcemap=none': true, style: 'compact' } )
    .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'))
    .pipe(gulp.dest('includes/assets/css'))
    .pipe(rename({suffix: '.min'}))
    .pipe(minifycss())
    .pipe(gulp.dest('includes/assets/css')); 
});
 
gulp.task('js-include', function() {
  return gulp.src('includes/assets/lib/select2/src/js/*.js')
    .pipe(uglify())
    .pipe(rename({suffix: '.min'}))
    .pipe(gulp.dest('includes/assets/js/'));
});


// i18n files 
gulp.task('wcvpro-pot', function () {
    return gulp.src([ 'admin/**/*.php', 'public/**/*.php', 'includes/**/*.php', 'templates/**/*.php' ] )
        .pipe( sort() )
        .pipe( wpPot( {
            domain: 'wcvendors-pro',
            destFile:'wcvendors-pro.pot',
            package: 'wcvendors-pro',
            bugReport: 'https://www.wcvendors.com',
            lastTranslator: 'Jamie Madden <support@wcvendors.com>',
            team: 'WC Vendors <support@wcvendors.com>'
        } ) )
        .pipe( gulp.dest('languages') );
});


gulp.task('default', ['styles-public', 'js-public', 'styles-admin', 'js-admin', 'styles-include', 'js-include', 'wcvpro-pot' ] );

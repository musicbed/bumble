var gulp = require('gulp'),
    gutil = require('gulp-util'),
    concat = require('gulp-concat'),
    sass = require('gulp-ruby-sass'),
    uglify = require('gulp-uglify'),
    autoprefixer = require('gulp-autoprefixer'),
    plumber = require('gulp-plumber'),
    coffee = require('gulp-coffee'),
    shell = require('gulp-shell'),
    livereload = require('gulp-livereload');

var SRC = './resources/assets/',
    BOWER = './public/bower_components/',
    PACKAGES = './public/packages/',
    NODE = './node_modules/',
    DIST = './public/';

// Javascript Sources
// ------------------------------------------------------------------------------------ */
var vendorJS = [
    NODE + 'jquery/dist/jquery.min.js',
    BOWER + 'trumbowyg/dist/trumbowyg.min.js',
    BOWER + 'switchery/dist/switchery.min.js',
    PACKAGES + 'tipr/tipr.min.js',
    BOWER + 'datetimepicker/jquery.datetimepicker.js',
    BOWER + 'MirrorMark/dist/js/mirrormark.package.js',
    NODE + 'fastclick/lib/fastclick.js',
];

var onError = function (err) {
    gutil.beep();
};

// SCSS Compiling and Minification
gulp.task('scss', function () {
    return gulp.src([
        SRC + 'sass/bumble.scss',
    ])
        .pipe(plumber({
            errorHandler: onError
        }))
        .pipe(sass({
            debugInfo: false,
            lineNumbers: false
        }))
        .pipe(autoprefixer())
        .pipe(livereload())
        .pipe(gulp.dest(DIST + '/css'));
});

gulp.task('coffee', function() {
    gulp.src(SRC + 'coffee/**/*.coffee')
        .pipe(coffee({bare: true}).on('error', gutil.log))
        .pipe(gulp.dest(DIST + 'js'));
});

// Concat Vendor Javascripts
// ------------------------------------------------------------------------------------ */
gulp.task('vendorJS', function() {
   gulp.src(vendorJS)
       .pipe(concat('vendor.min.js'))
       .pipe(uglify())
       .pipe(gulp.dest(DIST + 'js'));
});

gulp.task('publish', ['scss', 'coffee', 'vendorJS'], function() {
    //gulp.src('').pipe(shell('cd ~/Sites/bumble && php artisan asset:publish --bench=monarkee/bumble'), {ignoreErrors: true});
    gulp.src('').pipe(shell("cd ~/Sites/pushsilver && php artisan vendor:publish --provider=Monarkee\Bumble\Providers\BumbleServiceProvider --tag=assets --force"), {ignoreErrors: true});

});

/* Blade Templates */
gulp.task('blade', function () {
    return gulp.src('./views/**/*.blade.php')
        .pipe(livereload(server));
});

gulp.task('watch', function () {
    // Watch .scss files
    gulp.watch([
        SRC + 'sass/**/*.scss',
    ], ['scss', 'publish']);

    // Watch Coffee files
    gulp.watch([
        SRC + 'coffee/**/*.coffee',
    ], ['coffee', 'publish']);

    // Watch Blade files
    gulp.watch([
        './views/**/*.blade.php'
    ], ['blade']);
});

// Gulp Default Task
gulp.task('default', ['vendorJS', 'scss', 'coffee', 'publish', 'watch']);

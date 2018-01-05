var gulp = require('gulp');
var less = require('gulp-less');
var rename = require('gulp-rename');
var cleanCss = require('gulp-clean-css');
const lesshint = require('gulp-lesshint');



var paths = {
    styles: {
        src: 'src/AppBundle/Resources/less/main.less',
        dest: 'src/AppBundle/Resources/public/css/'
    }
};

function styles() {
    return gulp.src(paths.styles.src)
        .pipe(lesshint())
        .pipe(lesshint.reporter()) // Leave empty to use the default, "stylish"
        .pipe(lesshint.failOnError())
        .pipe(less())
        // pass in options to the stream
        .pipe(cleanCss())
        .pipe(rename({
            basename: 'main',
            suffix: '.min'
        }))
        .pipe(gulp.dest(paths.styles.dest))
        ;
}

gulp.task('build', styles);
gulp.task('default', styles);

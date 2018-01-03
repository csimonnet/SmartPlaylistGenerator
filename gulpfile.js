var gulp = require('gulp');
var less = require('gulp-less');
var concat = require('gulp-concat');
var rename = require('gulp-rename');
var cleanCss = require('gulp-clean-css');


var paths = {
    styles: {
        src: 'src/AppBundle/Resources/less/*.less',
        dest: 'src/AppBundle/Resources/public/css/'
    }
};

function styles() {
    return gulp.src(paths.styles.src)
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

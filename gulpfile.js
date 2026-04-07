const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));

function compileSass() {
    return gulp.src('src/scss/main.scss')
        .pipe(sass({
            silenceDeprecations: ['legacy-js-api'],
        }).on('error', sass.logError))
        .pipe(gulp.dest('src/css'));
}
gulp.task('sass', compileSass);

function watchSass() {
    gulp.watch('src/scss/**/*.scss', compileSass);
}
gulp.task('watch', watchSass);
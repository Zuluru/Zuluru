'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');

gulp.task('scss', function () {
  gulp.src('./webroot/css/zuluru/main.scss')
    .pipe(sass.sync().on('error', sass.logError))
    .pipe(gulp.dest('./webroot/css/zuluru'));
});

gulp.task('default', function () {
  gulp.watch(['./webroot/css/zuluru/*.scss'], ['scss']);
});

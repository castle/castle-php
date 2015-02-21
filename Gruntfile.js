module.exports = function(grunt) {
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    phplint: {
      src: ['lib/**/*.php']
    },
    phpunit: {
      classes: {
        dir: 'test/'
      },
      options: {
        bin: 'vendor/bin/phpunit',
        bootstrap: 'test/Castle.php',
        colors: true
      }
    },
    watch: {
      source: {
        files: ['test/**/*.php', 'lib/**/*.php'],
        tasks: ['test']
      }
    }
  });
  grunt.loadNpmTasks('grunt-phplint');
  grunt.loadNpmTasks('grunt-phpunit');
  grunt.loadNpmTasks('grunt-contrib-watch');

  grunt.registerTask('test', ['phpunit', 'phplint']);
  grunt.registerTask('default', ['test']);
};
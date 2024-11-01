module.exports = function (grunt) {
    'use strict';

    // Do grunt-related things in here
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        clean: {
            main: ['deploy/<%= pkg.version %>']
        },

        copy: {
            // Copy the plugin to a versioned deploy directory
            main: {
                src:  [
                    '**',
                    '!node_modules/**',
                    '!deploy/**',
                    '!.git/**',
                    '!Gruntfile.js',
                    '!package.json',
                    '!.gitignore'
                ],
                dest: 'deploy/<%= pkg.version %>/'
            }
        },

        compress: {
            main: {
                options: {
                    mode: 'zip',
                    archive: './deploy/<%= pkg.name %>-<%= pkg.version %>.zip'
                },
                expand: true,
                cwd: 'deploy/<%= pkg.version %>/',
                src: ['**/*']
            }
        }
    });

    grunt.loadNpmTasks( 'grunt-contrib-copy' );
    grunt.loadNpmTasks( 'grunt-contrib-clean' );
    grunt.loadNpmTasks( 'grunt-contrib-compress' );

    grunt.registerTask( 'deploy', [ 'clean', 'copy', 'compress' ]);
};

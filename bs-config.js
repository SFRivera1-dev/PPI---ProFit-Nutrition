module.exports = {
    proxy: "http://localhost/ProFit-Nutrition/",
    
    files: [
        "**/*.php",
        "**/*.css",
        "**/*.js"
    ],

    watchOptions: {
        ignoreInitial: true
    },

    open: true,
    notify: false
};
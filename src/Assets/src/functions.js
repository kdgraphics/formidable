export const Functions = {
    install(app) {
        app.config.globalProperties.t = (str, params) => {
            return Craft.t('formidable', str, params);
        }
        app.config.globalProperties.changePageTitle = (str) => {
            $('#page-title h1').html(str);
        }
    },
}
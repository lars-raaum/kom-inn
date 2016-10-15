const translations = {
    en: require('./en.js')
};

export let lang = 'no';
try {
    lang = localStorage.getItem('lang') || 'no';
} catch(e) {}

export function setLanguage(_lang) {
    lang = _lang
    try {
        localStorage.setItem('lang', _lang);
    } catch(e) {}
}

export default function translate(str) {
    if (translations[lang] && translations[lang][str]) {
        return translations[lang][str];
    }

    return str;
}

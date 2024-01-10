'use strict';


document.addEventListener('DOMContentLoaded', () => $('.start-hidden').css({opacity: 1}));


//  управление cookie
    function getCookie(name) {
        let reg = /([\.$?*|{}\(\)\[\]\\\/\+^])/g;
        let matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(reg, '\\$1') + "=([^;]*)"));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }
    function setCookie(name, value, options) {
        options = options || {};
        let expires = options.expires;
        if (typeof expires == "number" && expires) {
            let d = new Date();
            d.setTime(d.getTime() + expires * 1000);
            expires = options.expires = d;
        }        
        if (expires && expires.toUTCString) {
            options.expires = expires.toUTCString();
        }
        value = encodeURIComponent(value);
        let updatedCookie = name + "=" + value;
        for (let propName in options) {
            updatedCookie += "; " + propName;
            let propValue = options[propName];
            if (propValue !== true) {
                updatedCookie += "=" + propValue;
            }
        }
        document.cookie = updatedCookie;
    }
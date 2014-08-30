/*!
 * jQuery happycookies v1.0
 * https://github.com/mjolnic/happycookies
 * 
 */

(function($) {
    var happycookies = {
        policy: {
            banner: '#happycookies',
            cookieName: 'cookies_accepted',
            cookiePath: '/',
            cookieDomain: false,
            init: function() {
                $(function() {
                    if (!happycookies.policy.isAccepted()) {
                        $(happycookies.policy.banner).show();
                    }
                    $(happycookies.policy.banner + ' .happycookies-close').click(function(e) {
                        happycookies.policy.agree();
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    });
                });
            },
            agree: function() {
                happycookies.set(happycookies.policy.cookieName, 1, Infinity, happycookies.policy.cookiePath, happycookies.policy.cookieDomain);
                $(happycookies.policy.banner).fadeOut();
            },
            isAccepted: function() {
                return happycookies.has(happycookies.policy.cookieName);
            }
        },
        get: function(key) {
            return decodeURIComponent(window.document.cookie.replace(new RegExp('(?:(?:^|.*;)\\s*' + encodeURIComponent(key).replace(/[\-\.\+\*]/g, '\\$&') + '\\s*\\=\\s*([^;]*).*$)|^.*$'), '$1')) || null;
        },
        set: function(key, val, end, path, domain, secure) {
            if (!key || /^(?:expires|max\-age|path|domain|secure)$/i.test(key)) {
                return false;
            }
            var expires = '';
            if (end) {
                switch (end.constructor) {
                    case Number:
                        expires = end === Infinity ? '; expires=Fri, 31 Dec 9999 23:59:59 GMT' : '; max-age=' + end;
                        break;
                    case String:
                        expires = '; expires=' + end;
                        break;
                    case Date:
                        expires = '; expires=' + end.toUTCString();
                        break;
                }
            }
            window.document.cookie = encodeURIComponent(key) + '=' + encodeURIComponent(val) + expires + (domain ? '; domain=' + domain : '') + (path ? '; path=' + path : '') + (secure ? '; secure' : '');
            return true;
        },
        has: function(key) {
            return (new RegExp('(?:^|;\\s*)' + encodeURIComponent(key).replace(/[\-\.\+\*]/g, '\\$&') + '\\s*\\=')).test(window.document.cookie);
        },
        remove: function(key, path, domain) {
            if (!key || !this.has(key)) {
                return false;
            }
            window.document.cookie = encodeURIComponent(sKey) + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT' + (domain ? '; domain=' + domain : '') + (path ? '; path=' + path : '');
            return true;
        }
    };
    happycookies.policy.init();
})(window.jQuery);
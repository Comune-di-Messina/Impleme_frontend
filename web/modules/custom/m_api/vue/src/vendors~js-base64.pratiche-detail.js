(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["vendors~js-base64"],{

/***/ "./node_modules/js-base64/base64.mjs":
/*!*******************************************!*\
  !*** ./node_modules/js-base64/base64.mjs ***!
  \*******************************************/
/*! exports provided: version, VERSION, atob, atobPolyfill, btoa, btoaPolyfill, fromBase64, toBase64, utob, encode, encodeURI, encodeURL, btou, decode, isValid, fromUint8Array, toUint8Array, extendString, extendUint8Array, extendBuiltins, Base64 */
/***/ (function(__webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "version", function() { return version; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "VERSION", function() { return VERSION; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "atob", function() { return _atob; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "atobPolyfill", function() { return atobPolyfill; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "btoa", function() { return _btoa; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "btoaPolyfill", function() { return btoaPolyfill; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "fromBase64", function() { return decode; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "toBase64", function() { return encode; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "utob", function() { return utob; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "encode", function() { return encode; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "encodeURI", function() { return encodeURI; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "encodeURL", function() { return encodeURI; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "btou", function() { return btou; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "decode", function() { return decode; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "isValid", function() { return isValid; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "fromUint8Array", function() { return fromUint8Array; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "toUint8Array", function() { return toUint8Array; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "extendString", function() { return extendString; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "extendUint8Array", function() { return extendUint8Array; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "extendBuiltins", function() { return extendBuiltins; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Base64", function() { return gBase64; });
/**
 *  base64.ts
 *
 *  Licensed under the BSD 3-Clause License.
 *    http://opensource.org/licenses/BSD-3-Clause
 *
 *  References:
 *    http://en.wikipedia.org/wiki/Base64
 *
 * @author Dan Kogai (https://github.com/dankogai)
 */
const version = '3.6.0';
/**
 * @deprecated use lowercase `version`.
 */
const VERSION = version;
const _hasatob = typeof atob === 'function';
const _hasbtoa = typeof btoa === 'function';
const _hasBuffer = typeof Buffer === 'function';
const _TD = typeof TextDecoder === 'function' ? new TextDecoder() : undefined;
const _TE = typeof TextEncoder === 'function' ? new TextEncoder() : undefined;
const b64ch = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
const b64chs = [...b64ch];
const b64tab = ((a) => {
    let tab = {};
    a.forEach((c, i) => tab[c] = i);
    return tab;
})(b64chs);
const b64re = /^(?:[A-Za-z\d+\/]{4})*?(?:[A-Za-z\d+\/]{2}(?:==)?|[A-Za-z\d+\/]{3}=?)?$/;
const _fromCC = String.fromCharCode.bind(String);
const _U8Afrom = typeof Uint8Array.from === 'function'
    ? Uint8Array.from.bind(Uint8Array)
    : (it, fn = (x) => x) => new Uint8Array(Array.prototype.slice.call(it, 0).map(fn));
const _mkUriSafe = (src) => src
    .replace(/[+\/]/g, (m0) => m0 == '+' ? '-' : '_')
    .replace(/=+$/m, '');
const _tidyB64 = (s) => s.replace(/[^A-Za-z0-9\+\/]/g, '');
/**
 * polyfill version of `btoa`
 */
const btoaPolyfill = (bin) => {
    // console.log('polyfilled');
    let u32, c0, c1, c2, asc = '';
    const pad = bin.length % 3;
    for (let i = 0; i < bin.length;) {
        if ((c0 = bin.charCodeAt(i++)) > 255 ||
            (c1 = bin.charCodeAt(i++)) > 255 ||
            (c2 = bin.charCodeAt(i++)) > 255)
            throw new TypeError('invalid character found');
        u32 = (c0 << 16) | (c1 << 8) | c2;
        asc += b64chs[u32 >> 18 & 63]
            + b64chs[u32 >> 12 & 63]
            + b64chs[u32 >> 6 & 63]
            + b64chs[u32 & 63];
    }
    return pad ? asc.slice(0, pad - 3) + "===".substring(pad) : asc;
};
/**
 * does what `window.btoa` of web browsers do.
 * @param {String} bin binary string
 * @returns {string} Base64-encoded string
 */
const _btoa = _hasbtoa ? (bin) => btoa(bin)
    : _hasBuffer ? (bin) => Buffer.from(bin, 'binary').toString('base64')
        : btoaPolyfill;
const _fromUint8Array = _hasBuffer
    ? (u8a) => Buffer.from(u8a).toString('base64')
    : (u8a) => {
        // cf. https://stackoverflow.com/questions/12710001/how-to-convert-uint8-array-to-base64-encoded-string/12713326#12713326
        const maxargs = 0x1000;
        let strs = [];
        for (let i = 0, l = u8a.length; i < l; i += maxargs) {
            strs.push(_fromCC.apply(null, u8a.subarray(i, i + maxargs)));
        }
        return _btoa(strs.join(''));
    };
/**
 * converts a Uint8Array to a Base64 string.
 * @param {boolean} [urlsafe] URL-and-filename-safe a la RFC4648 §5
 * @returns {string} Base64 string
 */
const fromUint8Array = (u8a, urlsafe = false) => urlsafe ? _mkUriSafe(_fromUint8Array(u8a)) : _fromUint8Array(u8a);
// This trick is found broken https://github.com/dankogai/js-base64/issues/130
// const utob = (src: string) => unescape(encodeURIComponent(src));
// reverting good old fationed regexp
const cb_utob = (c) => {
    if (c.length < 2) {
        var cc = c.charCodeAt(0);
        return cc < 0x80 ? c
            : cc < 0x800 ? (_fromCC(0xc0 | (cc >>> 6))
                + _fromCC(0x80 | (cc & 0x3f)))
                : (_fromCC(0xe0 | ((cc >>> 12) & 0x0f))
                    + _fromCC(0x80 | ((cc >>> 6) & 0x3f))
                    + _fromCC(0x80 | (cc & 0x3f)));
    }
    else {
        var cc = 0x10000
            + (c.charCodeAt(0) - 0xD800) * 0x400
            + (c.charCodeAt(1) - 0xDC00);
        return (_fromCC(0xf0 | ((cc >>> 18) & 0x07))
            + _fromCC(0x80 | ((cc >>> 12) & 0x3f))
            + _fromCC(0x80 | ((cc >>> 6) & 0x3f))
            + _fromCC(0x80 | (cc & 0x3f)));
    }
};
const re_utob = /[\uD800-\uDBFF][\uDC00-\uDFFFF]|[^\x00-\x7F]/g;
/**
 * @deprecated should have been internal use only.
 * @param {string} src UTF-8 string
 * @returns {string} UTF-16 string
 */
const utob = (u) => u.replace(re_utob, cb_utob);
//
const _encode = _hasBuffer
    ? (s) => Buffer.from(s, 'utf8').toString('base64')
    : _TE
        ? (s) => _fromUint8Array(_TE.encode(s))
        : (s) => _btoa(utob(s));
/**
 * converts a UTF-8-encoded string to a Base64 string.
 * @param {boolean} [urlsafe] if `true` make the result URL-safe
 * @returns {string} Base64 string
 */
const encode = (src, urlsafe = false) => urlsafe
    ? _mkUriSafe(_encode(src))
    : _encode(src);
/**
 * converts a UTF-8-encoded string to URL-safe Base64 RFC4648 §5.
 * @returns {string} Base64 string
 */
const encodeURI = (src) => encode(src, true);
// This trick is found broken https://github.com/dankogai/js-base64/issues/130
// const btou = (src: string) => decodeURIComponent(escape(src));
// reverting good old fationed regexp
const re_btou = /[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF]{2}|[\xF0-\xF7][\x80-\xBF]{3}/g;
const cb_btou = (cccc) => {
    switch (cccc.length) {
        case 4:
            var cp = ((0x07 & cccc.charCodeAt(0)) << 18)
                | ((0x3f & cccc.charCodeAt(1)) << 12)
                | ((0x3f & cccc.charCodeAt(2)) << 6)
                | (0x3f & cccc.charCodeAt(3)), offset = cp - 0x10000;
            return (_fromCC((offset >>> 10) + 0xD800)
                + _fromCC((offset & 0x3FF) + 0xDC00));
        case 3:
            return _fromCC(((0x0f & cccc.charCodeAt(0)) << 12)
                | ((0x3f & cccc.charCodeAt(1)) << 6)
                | (0x3f & cccc.charCodeAt(2)));
        default:
            return _fromCC(((0x1f & cccc.charCodeAt(0)) << 6)
                | (0x3f & cccc.charCodeAt(1)));
    }
};
/**
 * @deprecated should have been internal use only.
 * @param {string} src UTF-16 string
 * @returns {string} UTF-8 string
 */
const btou = (b) => b.replace(re_btou, cb_btou);
/**
 * polyfill version of `atob`
 */
const atobPolyfill = (asc) => {
    // console.log('polyfilled');
    asc = asc.replace(/\s+/g, '');
    if (!b64re.test(asc))
        throw new TypeError('malformed base64.');
    asc += '=='.slice(2 - (asc.length & 3));
    let u24, bin = '', r1, r2;
    for (let i = 0; i < asc.length;) {
        u24 = b64tab[asc.charAt(i++)] << 18
            | b64tab[asc.charAt(i++)] << 12
            | (r1 = b64tab[asc.charAt(i++)]) << 6
            | (r2 = b64tab[asc.charAt(i++)]);
        bin += r1 === 64 ? _fromCC(u24 >> 16 & 255)
            : r2 === 64 ? _fromCC(u24 >> 16 & 255, u24 >> 8 & 255)
                : _fromCC(u24 >> 16 & 255, u24 >> 8 & 255, u24 & 255);
    }
    return bin;
};
/**
 * does what `window.atob` of web browsers do.
 * @param {String} asc Base64-encoded string
 * @returns {string} binary string
 */
const _atob = _hasatob ? (asc) => atob(_tidyB64(asc))
    : _hasBuffer ? (asc) => Buffer.from(asc, 'base64').toString('binary')
        : atobPolyfill;
//
const _toUint8Array = _hasBuffer
    ? (a) => _U8Afrom(Buffer.from(a, 'base64'))
    : (a) => _U8Afrom(_atob(a), c => c.charCodeAt(0));
/**
 * converts a Base64 string to a Uint8Array.
 */
const toUint8Array = (a) => _toUint8Array(_unURI(a));
//
const _decode = _hasBuffer
    ? (a) => Buffer.from(a, 'base64').toString('utf8')
    : _TD
        ? (a) => _TD.decode(_toUint8Array(a))
        : (a) => btou(_atob(a));
const _unURI = (a) => _tidyB64(a.replace(/[-_]/g, (m0) => m0 == '-' ? '+' : '/'));
/**
 * converts a Base64 string to a UTF-8 string.
 * @param {String} src Base64 string.  Both normal and URL-safe are supported
 * @returns {string} UTF-8 string
 */
const decode = (src) => _decode(_unURI(src));
/**
 * check if a value is a valid Base64 string
 * @param {String} src a value to check
  */
const isValid = (src) => {
    if (typeof src !== 'string')
        return false;
    const s = src.replace(/\s+/g, '').replace(/=+$/, '');
    return !/[^\s0-9a-zA-Z\+/]/.test(s) || !/[^\s0-9a-zA-Z\-_]/.test(s);
};
//
const _noEnum = (v) => {
    return {
        value: v, enumerable: false, writable: true, configurable: true
    };
};
/**
 * extend String.prototype with relevant methods
 */
const extendString = function () {
    const _add = (name, body) => Object.defineProperty(String.prototype, name, _noEnum(body));
    _add('fromBase64', function () { return decode(this); });
    _add('toBase64', function (urlsafe) { return encode(this, urlsafe); });
    _add('toBase64URI', function () { return encode(this, true); });
    _add('toBase64URL', function () { return encode(this, true); });
    _add('toUint8Array', function () { return toUint8Array(this); });
};
/**
 * extend Uint8Array.prototype with relevant methods
 */
const extendUint8Array = function () {
    const _add = (name, body) => Object.defineProperty(Uint8Array.prototype, name, _noEnum(body));
    _add('toBase64', function (urlsafe) { return fromUint8Array(this, urlsafe); });
    _add('toBase64URI', function () { return fromUint8Array(this, true); });
    _add('toBase64URL', function () { return fromUint8Array(this, true); });
};
/**
 * extend Builtin prototypes with relevant methods
 */
const extendBuiltins = () => {
    extendString();
    extendUint8Array();
};
const gBase64 = {
    version: version,
    VERSION: VERSION,
    atob: _atob,
    atobPolyfill: atobPolyfill,
    btoa: _btoa,
    btoaPolyfill: btoaPolyfill,
    fromBase64: decode,
    toBase64: encode,
    encode: encode,
    encodeURI: encodeURI,
    encodeURL: encodeURI,
    utob: utob,
    btou: btou,
    decode: decode,
    isValid: isValid,
    fromUint8Array: fromUint8Array,
    toUint8Array: toUint8Array,
    extendString: extendString,
    extendUint8Array: extendUint8Array,
    extendBuiltins: extendBuiltins,
};
// makecjs:CUT //




















// and finally,



/***/ })

}]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvanMtYmFzZTY0L2Jhc2U2NC5tanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7OztBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsQ0FBQztBQUNELGlDQUFpQyxFQUFFLG9CQUFvQixFQUFFLHNCQUFzQixFQUFFO0FBQ2pGO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLG1CQUFtQixnQkFBZ0I7QUFDbkM7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFdBQVcsT0FBTztBQUNsQixhQUFhLE9BQU87QUFDcEI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSx1Q0FBdUMsT0FBTztBQUM5QztBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxXQUFXLFFBQVE7QUFDbkIsYUFBYSxPQUFPO0FBQ3BCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsV0FBVyxPQUFPO0FBQ2xCLGFBQWEsT0FBTztBQUNwQjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFdBQVcsUUFBUTtBQUNuQixhQUFhLE9BQU87QUFDcEI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsYUFBYSxPQUFPO0FBQ3BCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSwrREFBK0QsRUFBRSx3QkFBd0IsRUFBRTtBQUMzRjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsV0FBVyxPQUFPO0FBQ2xCLGFBQWEsT0FBTztBQUNwQjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxtQkFBbUIsZ0JBQWdCO0FBQ25DO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFdBQVcsT0FBTztBQUNsQixhQUFhLE9BQU87QUFDcEI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsV0FBVyxPQUFPO0FBQ2xCLGFBQWEsT0FBTztBQUNwQjtBQUNBO0FBQ0E7QUFDQTtBQUNBLFdBQVcsT0FBTztBQUNsQjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxvQ0FBb0MscUJBQXFCLEVBQUU7QUFDM0QseUNBQXlDLDhCQUE4QixFQUFFO0FBQ3pFLHFDQUFxQywyQkFBMkIsRUFBRTtBQUNsRSxxQ0FBcUMsMkJBQTJCLEVBQUU7QUFDbEUsc0NBQXNDLDJCQUEyQixFQUFFO0FBQ25FO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHlDQUF5QyxzQ0FBc0MsRUFBRTtBQUNqRixxQ0FBcUMsbUNBQW1DLEVBQUU7QUFDMUUscUNBQXFDLG1DQUFtQyxFQUFFO0FBQzFFO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ21CO0FBQ0E7QUFDTTtBQUNEO0FBQ0M7QUFDRDtBQUNRO0FBQ0Y7QUFDZDtBQUNFO0FBQ0c7QUFDYTtBQUNsQjtBQUNFO0FBQ0M7QUFDTztBQUNGO0FBQ0E7QUFDSTtBQUNGO0FBQzFCO0FBQzZCIiwiZmlsZSI6InZlbmRvcnN+anMtYmFzZTY0LnByYXRpY2hlLWRldGFpbC5qcyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogIGJhc2U2NC50c1xuICpcbiAqICBMaWNlbnNlZCB1bmRlciB0aGUgQlNEIDMtQ2xhdXNlIExpY2Vuc2UuXG4gKiAgICBodHRwOi8vb3BlbnNvdXJjZS5vcmcvbGljZW5zZXMvQlNELTMtQ2xhdXNlXG4gKlxuICogIFJlZmVyZW5jZXM6XG4gKiAgICBodHRwOi8vZW4ud2lraXBlZGlhLm9yZy93aWtpL0Jhc2U2NFxuICpcbiAqIEBhdXRob3IgRGFuIEtvZ2FpIChodHRwczovL2dpdGh1Yi5jb20vZGFua29nYWkpXG4gKi9cbmNvbnN0IHZlcnNpb24gPSAnMy42LjAnO1xuLyoqXG4gKiBAZGVwcmVjYXRlZCB1c2UgbG93ZXJjYXNlIGB2ZXJzaW9uYC5cbiAqL1xuY29uc3QgVkVSU0lPTiA9IHZlcnNpb247XG5jb25zdCBfaGFzYXRvYiA9IHR5cGVvZiBhdG9iID09PSAnZnVuY3Rpb24nO1xuY29uc3QgX2hhc2J0b2EgPSB0eXBlb2YgYnRvYSA9PT0gJ2Z1bmN0aW9uJztcbmNvbnN0IF9oYXNCdWZmZXIgPSB0eXBlb2YgQnVmZmVyID09PSAnZnVuY3Rpb24nO1xuY29uc3QgX1REID0gdHlwZW9mIFRleHREZWNvZGVyID09PSAnZnVuY3Rpb24nID8gbmV3IFRleHREZWNvZGVyKCkgOiB1bmRlZmluZWQ7XG5jb25zdCBfVEUgPSB0eXBlb2YgVGV4dEVuY29kZXIgPT09ICdmdW5jdGlvbicgPyBuZXcgVGV4dEVuY29kZXIoKSA6IHVuZGVmaW5lZDtcbmNvbnN0IGI2NGNoID0gJ0FCQ0RFRkdISUpLTE1OT1BRUlNUVVZXWFlaYWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXowMTIzNDU2Nzg5Ky89JztcbmNvbnN0IGI2NGNocyA9IFsuLi5iNjRjaF07XG5jb25zdCBiNjR0YWIgPSAoKGEpID0+IHtcbiAgICBsZXQgdGFiID0ge307XG4gICAgYS5mb3JFYWNoKChjLCBpKSA9PiB0YWJbY10gPSBpKTtcbiAgICByZXR1cm4gdGFiO1xufSkoYjY0Y2hzKTtcbmNvbnN0IGI2NHJlID0gL14oPzpbQS1aYS16XFxkK1xcL117NH0pKj8oPzpbQS1aYS16XFxkK1xcL117Mn0oPzo9PSk/fFtBLVphLXpcXGQrXFwvXXszfT0/KT8kLztcbmNvbnN0IF9mcm9tQ0MgPSBTdHJpbmcuZnJvbUNoYXJDb2RlLmJpbmQoU3RyaW5nKTtcbmNvbnN0IF9VOEFmcm9tID0gdHlwZW9mIFVpbnQ4QXJyYXkuZnJvbSA9PT0gJ2Z1bmN0aW9uJ1xuICAgID8gVWludDhBcnJheS5mcm9tLmJpbmQoVWludDhBcnJheSlcbiAgICA6IChpdCwgZm4gPSAoeCkgPT4geCkgPT4gbmV3IFVpbnQ4QXJyYXkoQXJyYXkucHJvdG90eXBlLnNsaWNlLmNhbGwoaXQsIDApLm1hcChmbikpO1xuY29uc3QgX21rVXJpU2FmZSA9IChzcmMpID0+IHNyY1xuICAgIC5yZXBsYWNlKC9bK1xcL10vZywgKG0wKSA9PiBtMCA9PSAnKycgPyAnLScgOiAnXycpXG4gICAgLnJlcGxhY2UoLz0rJC9tLCAnJyk7XG5jb25zdCBfdGlkeUI2NCA9IChzKSA9PiBzLnJlcGxhY2UoL1teQS1aYS16MC05XFwrXFwvXS9nLCAnJyk7XG4vKipcbiAqIHBvbHlmaWxsIHZlcnNpb24gb2YgYGJ0b2FgXG4gKi9cbmNvbnN0IGJ0b2FQb2x5ZmlsbCA9IChiaW4pID0+IHtcbiAgICAvLyBjb25zb2xlLmxvZygncG9seWZpbGxlZCcpO1xuICAgIGxldCB1MzIsIGMwLCBjMSwgYzIsIGFzYyA9ICcnO1xuICAgIGNvbnN0IHBhZCA9IGJpbi5sZW5ndGggJSAzO1xuICAgIGZvciAobGV0IGkgPSAwOyBpIDwgYmluLmxlbmd0aDspIHtcbiAgICAgICAgaWYgKChjMCA9IGJpbi5jaGFyQ29kZUF0KGkrKykpID4gMjU1IHx8XG4gICAgICAgICAgICAoYzEgPSBiaW4uY2hhckNvZGVBdChpKyspKSA+IDI1NSB8fFxuICAgICAgICAgICAgKGMyID0gYmluLmNoYXJDb2RlQXQoaSsrKSkgPiAyNTUpXG4gICAgICAgICAgICB0aHJvdyBuZXcgVHlwZUVycm9yKCdpbnZhbGlkIGNoYXJhY3RlciBmb3VuZCcpO1xuICAgICAgICB1MzIgPSAoYzAgPDwgMTYpIHwgKGMxIDw8IDgpIHwgYzI7XG4gICAgICAgIGFzYyArPSBiNjRjaHNbdTMyID4+IDE4ICYgNjNdXG4gICAgICAgICAgICArIGI2NGNoc1t1MzIgPj4gMTIgJiA2M11cbiAgICAgICAgICAgICsgYjY0Y2hzW3UzMiA+PiA2ICYgNjNdXG4gICAgICAgICAgICArIGI2NGNoc1t1MzIgJiA2M107XG4gICAgfVxuICAgIHJldHVybiBwYWQgPyBhc2Muc2xpY2UoMCwgcGFkIC0gMykgKyBcIj09PVwiLnN1YnN0cmluZyhwYWQpIDogYXNjO1xufTtcbi8qKlxuICogZG9lcyB3aGF0IGB3aW5kb3cuYnRvYWAgb2Ygd2ViIGJyb3dzZXJzIGRvLlxuICogQHBhcmFtIHtTdHJpbmd9IGJpbiBiaW5hcnkgc3RyaW5nXG4gKiBAcmV0dXJucyB7c3RyaW5nfSBCYXNlNjQtZW5jb2RlZCBzdHJpbmdcbiAqL1xuY29uc3QgX2J0b2EgPSBfaGFzYnRvYSA/IChiaW4pID0+IGJ0b2EoYmluKVxuICAgIDogX2hhc0J1ZmZlciA/IChiaW4pID0+IEJ1ZmZlci5mcm9tKGJpbiwgJ2JpbmFyeScpLnRvU3RyaW5nKCdiYXNlNjQnKVxuICAgICAgICA6IGJ0b2FQb2x5ZmlsbDtcbmNvbnN0IF9mcm9tVWludDhBcnJheSA9IF9oYXNCdWZmZXJcbiAgICA/ICh1OGEpID0+IEJ1ZmZlci5mcm9tKHU4YSkudG9TdHJpbmcoJ2Jhc2U2NCcpXG4gICAgOiAodThhKSA9PiB7XG4gICAgICAgIC8vIGNmLiBodHRwczovL3N0YWNrb3ZlcmZsb3cuY29tL3F1ZXN0aW9ucy8xMjcxMDAwMS9ob3ctdG8tY29udmVydC11aW50OC1hcnJheS10by1iYXNlNjQtZW5jb2RlZC1zdHJpbmcvMTI3MTMzMjYjMTI3MTMzMjZcbiAgICAgICAgY29uc3QgbWF4YXJncyA9IDB4MTAwMDtcbiAgICAgICAgbGV0IHN0cnMgPSBbXTtcbiAgICAgICAgZm9yIChsZXQgaSA9IDAsIGwgPSB1OGEubGVuZ3RoOyBpIDwgbDsgaSArPSBtYXhhcmdzKSB7XG4gICAgICAgICAgICBzdHJzLnB1c2goX2Zyb21DQy5hcHBseShudWxsLCB1OGEuc3ViYXJyYXkoaSwgaSArIG1heGFyZ3MpKSk7XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIF9idG9hKHN0cnMuam9pbignJykpO1xuICAgIH07XG4vKipcbiAqIGNvbnZlcnRzIGEgVWludDhBcnJheSB0byBhIEJhc2U2NCBzdHJpbmcuXG4gKiBAcGFyYW0ge2Jvb2xlYW59IFt1cmxzYWZlXSBVUkwtYW5kLWZpbGVuYW1lLXNhZmUgYSBsYSBSRkM0NjQ4IMKnNVxuICogQHJldHVybnMge3N0cmluZ30gQmFzZTY0IHN0cmluZ1xuICovXG5jb25zdCBmcm9tVWludDhBcnJheSA9ICh1OGEsIHVybHNhZmUgPSBmYWxzZSkgPT4gdXJsc2FmZSA/IF9ta1VyaVNhZmUoX2Zyb21VaW50OEFycmF5KHU4YSkpIDogX2Zyb21VaW50OEFycmF5KHU4YSk7XG4vLyBUaGlzIHRyaWNrIGlzIGZvdW5kIGJyb2tlbiBodHRwczovL2dpdGh1Yi5jb20vZGFua29nYWkvanMtYmFzZTY0L2lzc3Vlcy8xMzBcbi8vIGNvbnN0IHV0b2IgPSAoc3JjOiBzdHJpbmcpID0+IHVuZXNjYXBlKGVuY29kZVVSSUNvbXBvbmVudChzcmMpKTtcbi8vIHJldmVydGluZyBnb29kIG9sZCBmYXRpb25lZCByZWdleHBcbmNvbnN0IGNiX3V0b2IgPSAoYykgPT4ge1xuICAgIGlmIChjLmxlbmd0aCA8IDIpIHtcbiAgICAgICAgdmFyIGNjID0gYy5jaGFyQ29kZUF0KDApO1xuICAgICAgICByZXR1cm4gY2MgPCAweDgwID8gY1xuICAgICAgICAgICAgOiBjYyA8IDB4ODAwID8gKF9mcm9tQ0MoMHhjMCB8IChjYyA+Pj4gNikpXG4gICAgICAgICAgICAgICAgKyBfZnJvbUNDKDB4ODAgfCAoY2MgJiAweDNmKSkpXG4gICAgICAgICAgICAgICAgOiAoX2Zyb21DQygweGUwIHwgKChjYyA+Pj4gMTIpICYgMHgwZikpXG4gICAgICAgICAgICAgICAgICAgICsgX2Zyb21DQygweDgwIHwgKChjYyA+Pj4gNikgJiAweDNmKSlcbiAgICAgICAgICAgICAgICAgICAgKyBfZnJvbUNDKDB4ODAgfCAoY2MgJiAweDNmKSkpO1xuICAgIH1cbiAgICBlbHNlIHtcbiAgICAgICAgdmFyIGNjID0gMHgxMDAwMFxuICAgICAgICAgICAgKyAoYy5jaGFyQ29kZUF0KDApIC0gMHhEODAwKSAqIDB4NDAwXG4gICAgICAgICAgICArIChjLmNoYXJDb2RlQXQoMSkgLSAweERDMDApO1xuICAgICAgICByZXR1cm4gKF9mcm9tQ0MoMHhmMCB8ICgoY2MgPj4+IDE4KSAmIDB4MDcpKVxuICAgICAgICAgICAgKyBfZnJvbUNDKDB4ODAgfCAoKGNjID4+PiAxMikgJiAweDNmKSlcbiAgICAgICAgICAgICsgX2Zyb21DQygweDgwIHwgKChjYyA+Pj4gNikgJiAweDNmKSlcbiAgICAgICAgICAgICsgX2Zyb21DQygweDgwIHwgKGNjICYgMHgzZikpKTtcbiAgICB9XG59O1xuY29uc3QgcmVfdXRvYiA9IC9bXFx1RDgwMC1cXHVEQkZGXVtcXHVEQzAwLVxcdURGRkZGXXxbXlxceDAwLVxceDdGXS9nO1xuLyoqXG4gKiBAZGVwcmVjYXRlZCBzaG91bGQgaGF2ZSBiZWVuIGludGVybmFsIHVzZSBvbmx5LlxuICogQHBhcmFtIHtzdHJpbmd9IHNyYyBVVEYtOCBzdHJpbmdcbiAqIEByZXR1cm5zIHtzdHJpbmd9IFVURi0xNiBzdHJpbmdcbiAqL1xuY29uc3QgdXRvYiA9ICh1KSA9PiB1LnJlcGxhY2UocmVfdXRvYiwgY2JfdXRvYik7XG4vL1xuY29uc3QgX2VuY29kZSA9IF9oYXNCdWZmZXJcbiAgICA/IChzKSA9PiBCdWZmZXIuZnJvbShzLCAndXRmOCcpLnRvU3RyaW5nKCdiYXNlNjQnKVxuICAgIDogX1RFXG4gICAgICAgID8gKHMpID0+IF9mcm9tVWludDhBcnJheShfVEUuZW5jb2RlKHMpKVxuICAgICAgICA6IChzKSA9PiBfYnRvYSh1dG9iKHMpKTtcbi8qKlxuICogY29udmVydHMgYSBVVEYtOC1lbmNvZGVkIHN0cmluZyB0byBhIEJhc2U2NCBzdHJpbmcuXG4gKiBAcGFyYW0ge2Jvb2xlYW59IFt1cmxzYWZlXSBpZiBgdHJ1ZWAgbWFrZSB0aGUgcmVzdWx0IFVSTC1zYWZlXG4gKiBAcmV0dXJucyB7c3RyaW5nfSBCYXNlNjQgc3RyaW5nXG4gKi9cbmNvbnN0IGVuY29kZSA9IChzcmMsIHVybHNhZmUgPSBmYWxzZSkgPT4gdXJsc2FmZVxuICAgID8gX21rVXJpU2FmZShfZW5jb2RlKHNyYykpXG4gICAgOiBfZW5jb2RlKHNyYyk7XG4vKipcbiAqIGNvbnZlcnRzIGEgVVRGLTgtZW5jb2RlZCBzdHJpbmcgdG8gVVJMLXNhZmUgQmFzZTY0IFJGQzQ2NDggwqc1LlxuICogQHJldHVybnMge3N0cmluZ30gQmFzZTY0IHN0cmluZ1xuICovXG5jb25zdCBlbmNvZGVVUkkgPSAoc3JjKSA9PiBlbmNvZGUoc3JjLCB0cnVlKTtcbi8vIFRoaXMgdHJpY2sgaXMgZm91bmQgYnJva2VuIGh0dHBzOi8vZ2l0aHViLmNvbS9kYW5rb2dhaS9qcy1iYXNlNjQvaXNzdWVzLzEzMFxuLy8gY29uc3QgYnRvdSA9IChzcmM6IHN0cmluZykgPT4gZGVjb2RlVVJJQ29tcG9uZW50KGVzY2FwZShzcmMpKTtcbi8vIHJldmVydGluZyBnb29kIG9sZCBmYXRpb25lZCByZWdleHBcbmNvbnN0IHJlX2J0b3UgPSAvW1xceEMwLVxceERGXVtcXHg4MC1cXHhCRl18W1xceEUwLVxceEVGXVtcXHg4MC1cXHhCRl17Mn18W1xceEYwLVxceEY3XVtcXHg4MC1cXHhCRl17M30vZztcbmNvbnN0IGNiX2J0b3UgPSAoY2NjYykgPT4ge1xuICAgIHN3aXRjaCAoY2NjYy5sZW5ndGgpIHtcbiAgICAgICAgY2FzZSA0OlxuICAgICAgICAgICAgdmFyIGNwID0gKCgweDA3ICYgY2NjYy5jaGFyQ29kZUF0KDApKSA8PCAxOClcbiAgICAgICAgICAgICAgICB8ICgoMHgzZiAmIGNjY2MuY2hhckNvZGVBdCgxKSkgPDwgMTIpXG4gICAgICAgICAgICAgICAgfCAoKDB4M2YgJiBjY2NjLmNoYXJDb2RlQXQoMikpIDw8IDYpXG4gICAgICAgICAgICAgICAgfCAoMHgzZiAmIGNjY2MuY2hhckNvZGVBdCgzKSksIG9mZnNldCA9IGNwIC0gMHgxMDAwMDtcbiAgICAgICAgICAgIHJldHVybiAoX2Zyb21DQygob2Zmc2V0ID4+PiAxMCkgKyAweEQ4MDApXG4gICAgICAgICAgICAgICAgKyBfZnJvbUNDKChvZmZzZXQgJiAweDNGRikgKyAweERDMDApKTtcbiAgICAgICAgY2FzZSAzOlxuICAgICAgICAgICAgcmV0dXJuIF9mcm9tQ0MoKCgweDBmICYgY2NjYy5jaGFyQ29kZUF0KDApKSA8PCAxMilcbiAgICAgICAgICAgICAgICB8ICgoMHgzZiAmIGNjY2MuY2hhckNvZGVBdCgxKSkgPDwgNilcbiAgICAgICAgICAgICAgICB8ICgweDNmICYgY2NjYy5jaGFyQ29kZUF0KDIpKSk7XG4gICAgICAgIGRlZmF1bHQ6XG4gICAgICAgICAgICByZXR1cm4gX2Zyb21DQygoKDB4MWYgJiBjY2NjLmNoYXJDb2RlQXQoMCkpIDw8IDYpXG4gICAgICAgICAgICAgICAgfCAoMHgzZiAmIGNjY2MuY2hhckNvZGVBdCgxKSkpO1xuICAgIH1cbn07XG4vKipcbiAqIEBkZXByZWNhdGVkIHNob3VsZCBoYXZlIGJlZW4gaW50ZXJuYWwgdXNlIG9ubHkuXG4gKiBAcGFyYW0ge3N0cmluZ30gc3JjIFVURi0xNiBzdHJpbmdcbiAqIEByZXR1cm5zIHtzdHJpbmd9IFVURi04IHN0cmluZ1xuICovXG5jb25zdCBidG91ID0gKGIpID0+IGIucmVwbGFjZShyZV9idG91LCBjYl9idG91KTtcbi8qKlxuICogcG9seWZpbGwgdmVyc2lvbiBvZiBgYXRvYmBcbiAqL1xuY29uc3QgYXRvYlBvbHlmaWxsID0gKGFzYykgPT4ge1xuICAgIC8vIGNvbnNvbGUubG9nKCdwb2x5ZmlsbGVkJyk7XG4gICAgYXNjID0gYXNjLnJlcGxhY2UoL1xccysvZywgJycpO1xuICAgIGlmICghYjY0cmUudGVzdChhc2MpKVxuICAgICAgICB0aHJvdyBuZXcgVHlwZUVycm9yKCdtYWxmb3JtZWQgYmFzZTY0LicpO1xuICAgIGFzYyArPSAnPT0nLnNsaWNlKDIgLSAoYXNjLmxlbmd0aCAmIDMpKTtcbiAgICBsZXQgdTI0LCBiaW4gPSAnJywgcjEsIHIyO1xuICAgIGZvciAobGV0IGkgPSAwOyBpIDwgYXNjLmxlbmd0aDspIHtcbiAgICAgICAgdTI0ID0gYjY0dGFiW2FzYy5jaGFyQXQoaSsrKV0gPDwgMThcbiAgICAgICAgICAgIHwgYjY0dGFiW2FzYy5jaGFyQXQoaSsrKV0gPDwgMTJcbiAgICAgICAgICAgIHwgKHIxID0gYjY0dGFiW2FzYy5jaGFyQXQoaSsrKV0pIDw8IDZcbiAgICAgICAgICAgIHwgKHIyID0gYjY0dGFiW2FzYy5jaGFyQXQoaSsrKV0pO1xuICAgICAgICBiaW4gKz0gcjEgPT09IDY0ID8gX2Zyb21DQyh1MjQgPj4gMTYgJiAyNTUpXG4gICAgICAgICAgICA6IHIyID09PSA2NCA/IF9mcm9tQ0ModTI0ID4+IDE2ICYgMjU1LCB1MjQgPj4gOCAmIDI1NSlcbiAgICAgICAgICAgICAgICA6IF9mcm9tQ0ModTI0ID4+IDE2ICYgMjU1LCB1MjQgPj4gOCAmIDI1NSwgdTI0ICYgMjU1KTtcbiAgICB9XG4gICAgcmV0dXJuIGJpbjtcbn07XG4vKipcbiAqIGRvZXMgd2hhdCBgd2luZG93LmF0b2JgIG9mIHdlYiBicm93c2VycyBkby5cbiAqIEBwYXJhbSB7U3RyaW5nfSBhc2MgQmFzZTY0LWVuY29kZWQgc3RyaW5nXG4gKiBAcmV0dXJucyB7c3RyaW5nfSBiaW5hcnkgc3RyaW5nXG4gKi9cbmNvbnN0IF9hdG9iID0gX2hhc2F0b2IgPyAoYXNjKSA9PiBhdG9iKF90aWR5QjY0KGFzYykpXG4gICAgOiBfaGFzQnVmZmVyID8gKGFzYykgPT4gQnVmZmVyLmZyb20oYXNjLCAnYmFzZTY0JykudG9TdHJpbmcoJ2JpbmFyeScpXG4gICAgICAgIDogYXRvYlBvbHlmaWxsO1xuLy9cbmNvbnN0IF90b1VpbnQ4QXJyYXkgPSBfaGFzQnVmZmVyXG4gICAgPyAoYSkgPT4gX1U4QWZyb20oQnVmZmVyLmZyb20oYSwgJ2Jhc2U2NCcpKVxuICAgIDogKGEpID0+IF9VOEFmcm9tKF9hdG9iKGEpLCBjID0+IGMuY2hhckNvZGVBdCgwKSk7XG4vKipcbiAqIGNvbnZlcnRzIGEgQmFzZTY0IHN0cmluZyB0byBhIFVpbnQ4QXJyYXkuXG4gKi9cbmNvbnN0IHRvVWludDhBcnJheSA9IChhKSA9PiBfdG9VaW50OEFycmF5KF91blVSSShhKSk7XG4vL1xuY29uc3QgX2RlY29kZSA9IF9oYXNCdWZmZXJcbiAgICA/IChhKSA9PiBCdWZmZXIuZnJvbShhLCAnYmFzZTY0JykudG9TdHJpbmcoJ3V0ZjgnKVxuICAgIDogX1REXG4gICAgICAgID8gKGEpID0+IF9URC5kZWNvZGUoX3RvVWludDhBcnJheShhKSlcbiAgICAgICAgOiAoYSkgPT4gYnRvdShfYXRvYihhKSk7XG5jb25zdCBfdW5VUkkgPSAoYSkgPT4gX3RpZHlCNjQoYS5yZXBsYWNlKC9bLV9dL2csIChtMCkgPT4gbTAgPT0gJy0nID8gJysnIDogJy8nKSk7XG4vKipcbiAqIGNvbnZlcnRzIGEgQmFzZTY0IHN0cmluZyB0byBhIFVURi04IHN0cmluZy5cbiAqIEBwYXJhbSB7U3RyaW5nfSBzcmMgQmFzZTY0IHN0cmluZy4gIEJvdGggbm9ybWFsIGFuZCBVUkwtc2FmZSBhcmUgc3VwcG9ydGVkXG4gKiBAcmV0dXJucyB7c3RyaW5nfSBVVEYtOCBzdHJpbmdcbiAqL1xuY29uc3QgZGVjb2RlID0gKHNyYykgPT4gX2RlY29kZShfdW5VUkkoc3JjKSk7XG4vKipcbiAqIGNoZWNrIGlmIGEgdmFsdWUgaXMgYSB2YWxpZCBCYXNlNjQgc3RyaW5nXG4gKiBAcGFyYW0ge1N0cmluZ30gc3JjIGEgdmFsdWUgdG8gY2hlY2tcbiAgKi9cbmNvbnN0IGlzVmFsaWQgPSAoc3JjKSA9PiB7XG4gICAgaWYgKHR5cGVvZiBzcmMgIT09ICdzdHJpbmcnKVxuICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgY29uc3QgcyA9IHNyYy5yZXBsYWNlKC9cXHMrL2csICcnKS5yZXBsYWNlKC89KyQvLCAnJyk7XG4gICAgcmV0dXJuICEvW15cXHMwLTlhLXpBLVpcXCsvXS8udGVzdChzKSB8fCAhL1teXFxzMC05YS16QS1aXFwtX10vLnRlc3Qocyk7XG59O1xuLy9cbmNvbnN0IF9ub0VudW0gPSAodikgPT4ge1xuICAgIHJldHVybiB7XG4gICAgICAgIHZhbHVlOiB2LCBlbnVtZXJhYmxlOiBmYWxzZSwgd3JpdGFibGU6IHRydWUsIGNvbmZpZ3VyYWJsZTogdHJ1ZVxuICAgIH07XG59O1xuLyoqXG4gKiBleHRlbmQgU3RyaW5nLnByb3RvdHlwZSB3aXRoIHJlbGV2YW50IG1ldGhvZHNcbiAqL1xuY29uc3QgZXh0ZW5kU3RyaW5nID0gZnVuY3Rpb24gKCkge1xuICAgIGNvbnN0IF9hZGQgPSAobmFtZSwgYm9keSkgPT4gT2JqZWN0LmRlZmluZVByb3BlcnR5KFN0cmluZy5wcm90b3R5cGUsIG5hbWUsIF9ub0VudW0oYm9keSkpO1xuICAgIF9hZGQoJ2Zyb21CYXNlNjQnLCBmdW5jdGlvbiAoKSB7IHJldHVybiBkZWNvZGUodGhpcyk7IH0pO1xuICAgIF9hZGQoJ3RvQmFzZTY0JywgZnVuY3Rpb24gKHVybHNhZmUpIHsgcmV0dXJuIGVuY29kZSh0aGlzLCB1cmxzYWZlKTsgfSk7XG4gICAgX2FkZCgndG9CYXNlNjRVUkknLCBmdW5jdGlvbiAoKSB7IHJldHVybiBlbmNvZGUodGhpcywgdHJ1ZSk7IH0pO1xuICAgIF9hZGQoJ3RvQmFzZTY0VVJMJywgZnVuY3Rpb24gKCkgeyByZXR1cm4gZW5jb2RlKHRoaXMsIHRydWUpOyB9KTtcbiAgICBfYWRkKCd0b1VpbnQ4QXJyYXknLCBmdW5jdGlvbiAoKSB7IHJldHVybiB0b1VpbnQ4QXJyYXkodGhpcyk7IH0pO1xufTtcbi8qKlxuICogZXh0ZW5kIFVpbnQ4QXJyYXkucHJvdG90eXBlIHdpdGggcmVsZXZhbnQgbWV0aG9kc1xuICovXG5jb25zdCBleHRlbmRVaW50OEFycmF5ID0gZnVuY3Rpb24gKCkge1xuICAgIGNvbnN0IF9hZGQgPSAobmFtZSwgYm9keSkgPT4gT2JqZWN0LmRlZmluZVByb3BlcnR5KFVpbnQ4QXJyYXkucHJvdG90eXBlLCBuYW1lLCBfbm9FbnVtKGJvZHkpKTtcbiAgICBfYWRkKCd0b0Jhc2U2NCcsIGZ1bmN0aW9uICh1cmxzYWZlKSB7IHJldHVybiBmcm9tVWludDhBcnJheSh0aGlzLCB1cmxzYWZlKTsgfSk7XG4gICAgX2FkZCgndG9CYXNlNjRVUkknLCBmdW5jdGlvbiAoKSB7IHJldHVybiBmcm9tVWludDhBcnJheSh0aGlzLCB0cnVlKTsgfSk7XG4gICAgX2FkZCgndG9CYXNlNjRVUkwnLCBmdW5jdGlvbiAoKSB7IHJldHVybiBmcm9tVWludDhBcnJheSh0aGlzLCB0cnVlKTsgfSk7XG59O1xuLyoqXG4gKiBleHRlbmQgQnVpbHRpbiBwcm90b3R5cGVzIHdpdGggcmVsZXZhbnQgbWV0aG9kc1xuICovXG5jb25zdCBleHRlbmRCdWlsdGlucyA9ICgpID0+IHtcbiAgICBleHRlbmRTdHJpbmcoKTtcbiAgICBleHRlbmRVaW50OEFycmF5KCk7XG59O1xuY29uc3QgZ0Jhc2U2NCA9IHtcbiAgICB2ZXJzaW9uOiB2ZXJzaW9uLFxuICAgIFZFUlNJT046IFZFUlNJT04sXG4gICAgYXRvYjogX2F0b2IsXG4gICAgYXRvYlBvbHlmaWxsOiBhdG9iUG9seWZpbGwsXG4gICAgYnRvYTogX2J0b2EsXG4gICAgYnRvYVBvbHlmaWxsOiBidG9hUG9seWZpbGwsXG4gICAgZnJvbUJhc2U2NDogZGVjb2RlLFxuICAgIHRvQmFzZTY0OiBlbmNvZGUsXG4gICAgZW5jb2RlOiBlbmNvZGUsXG4gICAgZW5jb2RlVVJJOiBlbmNvZGVVUkksXG4gICAgZW5jb2RlVVJMOiBlbmNvZGVVUkksXG4gICAgdXRvYjogdXRvYixcbiAgICBidG91OiBidG91LFxuICAgIGRlY29kZTogZGVjb2RlLFxuICAgIGlzVmFsaWQ6IGlzVmFsaWQsXG4gICAgZnJvbVVpbnQ4QXJyYXk6IGZyb21VaW50OEFycmF5LFxuICAgIHRvVWludDhBcnJheTogdG9VaW50OEFycmF5LFxuICAgIGV4dGVuZFN0cmluZzogZXh0ZW5kU3RyaW5nLFxuICAgIGV4dGVuZFVpbnQ4QXJyYXk6IGV4dGVuZFVpbnQ4QXJyYXksXG4gICAgZXh0ZW5kQnVpbHRpbnM6IGV4dGVuZEJ1aWx0aW5zLFxufTtcbi8vIG1ha2VjanM6Q1VUIC8vXG5leHBvcnQgeyB2ZXJzaW9uIH07XG5leHBvcnQgeyBWRVJTSU9OIH07XG5leHBvcnQgeyBfYXRvYiBhcyBhdG9iIH07XG5leHBvcnQgeyBhdG9iUG9seWZpbGwgfTtcbmV4cG9ydCB7IF9idG9hIGFzIGJ0b2EgfTtcbmV4cG9ydCB7IGJ0b2FQb2x5ZmlsbCB9O1xuZXhwb3J0IHsgZGVjb2RlIGFzIGZyb21CYXNlNjQgfTtcbmV4cG9ydCB7IGVuY29kZSBhcyB0b0Jhc2U2NCB9O1xuZXhwb3J0IHsgdXRvYiB9O1xuZXhwb3J0IHsgZW5jb2RlIH07XG5leHBvcnQgeyBlbmNvZGVVUkkgfTtcbmV4cG9ydCB7IGVuY29kZVVSSSBhcyBlbmNvZGVVUkwgfTtcbmV4cG9ydCB7IGJ0b3UgfTtcbmV4cG9ydCB7IGRlY29kZSB9O1xuZXhwb3J0IHsgaXNWYWxpZCB9O1xuZXhwb3J0IHsgZnJvbVVpbnQ4QXJyYXkgfTtcbmV4cG9ydCB7IHRvVWludDhBcnJheSB9O1xuZXhwb3J0IHsgZXh0ZW5kU3RyaW5nIH07XG5leHBvcnQgeyBleHRlbmRVaW50OEFycmF5IH07XG5leHBvcnQgeyBleHRlbmRCdWlsdGlucyB9O1xuLy8gYW5kIGZpbmFsbHksXG5leHBvcnQgeyBnQmFzZTY0IGFzIEJhc2U2NCB9O1xuIl0sInNvdXJjZVJvb3QiOiIifQ==
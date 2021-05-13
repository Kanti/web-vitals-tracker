var e, t, n, i, a2 = function(e1, t1) {
    return {
        name: e1,
        value: (void 0) === t1 ? -1 : t1,
        delta: 0,
        entries: [],
        id: "v1-".concat(Date.now(), "-").concat(Math.floor(8999999999999 * Math.random()) + 1000000000000)
    };
}, r = function(e1, t1) {
    try {
        if (PerformanceObserver.supportedEntryTypes.includes(e1)) {
            if ("first-input" === e1 && !("PerformanceEventTiming" in self)) return;
            var n1 = new PerformanceObserver(function(e2) {
                return e2.getEntries().map(t1);
            });
            return n1.observe({
                type: e1,
                buffered: !0
            }), n1;
        }
    } catch (e2) {
    }
}, o = function(e1, t1) {
    var n2 = function n3(i1) {
        "pagehide" !== i1.type && "hidden" !== document.visibilityState || (e1(i1), t1 && (removeEventListener("visibilitychange", n3, !0), removeEventListener("pagehide", n3, !0)));
    };
    addEventListener("visibilitychange", n2, !0), addEventListener("pagehide", n2, !0);
}, c = function(e1) {
    addEventListener("pageshow", function(t1) {
        t1.persisted && e1(t1);
    }, !0);
}, u = "function" == typeof WeakSet ? new WeakSet() : new Set(), f = function(e1, t1, n2) {
    var i1;
    return function() {
        t1.value >= 0 && (n2 || u.has(t1) || "hidden" === document.visibilityState) && (t1.delta = t1.value - (i1 || 0), (t1.delta || (void 0) === i1) && (i1 = t1.value, e1(t1)));
    };
}, s = function(e1, t1) {
    var n2, i1 = a2("CLS", 0), u1 = function(e2) {
        e2.hadRecentInput || (i1.value += e2.value, i1.entries.push(e2), n2());
    }, s1 = r("layout-shift", u1);
    s1 && (n2 = f(e1, i1, t1), o(function() {
        s1.takeRecords().map(u1), n2();
    }), c(function() {
        i1 = a2("CLS", 0), n2 = f(e1, i1, t1);
    }));
}, m = -1, p1 = function() {
    return "hidden" === document.visibilityState ? 0 : 1 / 0;
}, v = function() {
    o(function(e1) {
        var t1 = e1.timeStamp;
        m = t1;
    }, !0);
}, d = function() {
    return m < 0 && (m = p1(), v(), c(function() {
        setTimeout(function() {
            m = p1(), v();
        }, 0);
    })), {
        get timeStamp () {
            return m;
        }
    };
}, l = function(e1, t1) {
    var n2, i1 = d(), o1 = a2("FCP"), s1 = function(e2) {
        "first-contentful-paint" === e2.name && (p2 && p2.disconnect(), e2.startTime < i1.timeStamp && (o1.value = e2.startTime, o1.entries.push(e2), u.add(o1), n2()));
    }, m1 = performance.getEntriesByName("first-contentful-paint")[0], p2 = m1 ? null : r("paint", s1);
    (m1 || p2) && (n2 = f(e1, o1, t1), m1 && s1(m1), c(function(i2) {
        o1 = a2("FCP"), n2 = f(e1, o1, t1), requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                o1.value = performance.now() - i2.timeStamp, u.add(o1), n2();
            });
        });
    }));
}, h = {
    passive: !0,
    capture: !0
}, S = new Date(), y = function(i1, a1) {
    e || (e = a1, t = i1, n = new Date(), w(removeEventListener), g());
}, g = function() {
    if (t >= 0 && t < n - S) {
        var a1 = {
            entryType: "first-input",
            name: e.type,
            target: e.target,
            cancelable: e.cancelable,
            startTime: e.timeStamp,
            processingStart: e.timeStamp + t
        };
        i.forEach(function(e1) {
            e1(a1);
        }), i = [];
    }
}, E = function(e1) {
    if (e1.cancelable) {
        var t1 = (e1.timeStamp > 1000000000000 ? new Date() : performance.now()) - e1.timeStamp;
        "pointerdown" == e1.type ? (function(e2, t2) {
            var n2 = function() {
                y(e2, t2), a3();
            }, i1 = function() {
                a3();
            }, a3 = function() {
                removeEventListener("pointerup", n2, h), removeEventListener("pointercancel", i1, h);
            };
            addEventListener("pointerup", n2, h), addEventListener("pointercancel", i1, h);
        })(t1, e1) : y(t1, e1);
    }
}, w = function(e1) {
    [
        "mousedown",
        "keydown",
        "touchstart",
        "pointerdown"
    ].forEach(function(t2) {
        return e1(t2, E, h);
    });
}, L = function(n2, s1) {
    var m1, p2 = d(), v1 = a2("FID"), l1 = function(e1) {
        e1.startTime < p2.timeStamp && (v1.value = e1.processingStart - e1.startTime, v1.entries.push(e1), u.add(v1), m1());
    }, h1 = r("first-input", l1);
    m1 = f(n2, v1, s1), h1 && o(function() {
        h1.takeRecords().map(l1), h1.disconnect();
    }, !0), h1 && c(function() {
        var r1;
        v1 = a2("FID"), m1 = f(n2, v1, s1), i = [], t = -1, e = null, w(addEventListener), r1 = l1, i.push(r1), g();
    });
}, T = function(e1, t2) {
    var n2, i1 = d(), s1 = a2("LCP"), m1 = function(e2) {
        var t3 = e2.startTime;
        t3 < i1.timeStamp && (s1.value = t3, s1.entries.push(e2)), n2();
    }, p2 = r("largest-contentful-paint", m1);
    if (p2) {
        n2 = f(e1, s1, t2);
        var v1 = function() {
            u.has(s1) || (p2.takeRecords().map(m1), p2.disconnect(), u.add(s1), n2());
        };
        [
            "keydown",
            "click"
        ].forEach(function(e2) {
            addEventListener(e2, v1, {
                once: !0,
                capture: !0
            });
        }), o(v1, !0), c(function(i2) {
            s1 = a2("LCP"), n2 = f(e1, s1, t2), requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    s1.value = performance.now() - i2.timeStamp, u.add(s1), n2();
                });
            });
        });
    }
}, b = function(e1) {
    var t2, n2 = a2("TTFB");
    t2 = function() {
        try {
            var t3 = performance.getEntriesByType("navigation")[0] || function() {
                var e2 = performance.timing, t4 = {
                    entryType: "navigation",
                    startTime: 0
                };
                for(var n3 in e2)"navigationStart" !== n3 && "toJSON" !== n3 && (t4[n3] = Math.max(e2[n3] - e2.navigationStart, 0));
                return t4;
            }();
            if (n2.value = n2.delta = t3.responseStart, n2.value < 0) return;
            n2.entries = [
                t3
            ], e1(n2);
        } catch (e2) {
        }
    }, "complete" === document.readyState ? setTimeout(t2, 0) : addEventListener("pageshow", t2);
};
var getRandomValues;
var rnds8 = new Uint8Array(16);
function rng() {
    if (!getRandomValues) {
        getRandomValues = typeof crypto !== 'undefined' && crypto.getRandomValues && crypto.getRandomValues.bind(crypto) || typeof msCrypto !== 'undefined' && typeof msCrypto.getRandomValues === 'function' && msCrypto.getRandomValues.bind(msCrypto);
        if (!getRandomValues) {
            throw new Error('crypto.getRandomValues() not supported. See https://github.com/uuidjs/uuid#getrandomvalues-not-supported');
        }
    }
    return getRandomValues(rnds8);
}
const __default = /^(?:[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}|00000000-0000-0000-0000-000000000000)$/i;
function validate(uuid) {
    return typeof uuid === 'string' && __default.test(uuid);
}
var byteToHex = [];
for(var i1 = 0; i1 < 256; ++i1){
    byteToHex.push((i1 + 256).toString(16).substr(1));
}
function stringify(arr) {
    var offset = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
    var uuid = (byteToHex[arr[offset + 0]] + byteToHex[arr[offset + 1]] + byteToHex[arr[offset + 2]] + byteToHex[arr[offset + 3]] + '-' + byteToHex[arr[offset + 4]] + byteToHex[arr[offset + 5]] + '-' + byteToHex[arr[offset + 6]] + byteToHex[arr[offset + 7]] + '-' + byteToHex[arr[offset + 8]] + byteToHex[arr[offset + 9]] + '-' + byteToHex[arr[offset + 10]] + byteToHex[arr[offset + 11]] + byteToHex[arr[offset + 12]] + byteToHex[arr[offset + 13]] + byteToHex[arr[offset + 14]] + byteToHex[arr[offset + 15]]).toLowerCase();
    if (!validate(uuid)) {
        throw TypeError('Stringified UUID is invalid');
    }
    return uuid;
}
function v4(options, buf, offset) {
    options = options || {
    };
    var rnds = options.random || (options.rng || rng)();
    rnds[6] = rnds[6] & 15 | 64;
    rnds[8] = rnds[8] & 63 | 128;
    if (buf) {
        offset = offset || 0;
        for(var i2 = 0; i2 < 16; ++i2){
            buf[offset + i2] = rnds[i2];
        }
        return buf;
    }
    return stringify(rnds);
}
const requestUuid = v4();
let counter = 0;
async function send(endPoint, data) {
    const bodyString = JSON.stringify(data);
    return fetch(endPoint, {
        body: bodyString,
        mode: 'cors',
        method: 'POST',
        keepalive: true
    });
}
async function sendToAnalytics(metric) {
    const url = new URL(window.location.href);
    url.searchParams.set('webvitalstracker', '1');
    counter++;
    await send(url.toString(), {
        name: metric.name,
        value: metric.value,
        requestUuid,
        counter
    });
}
s(sendToAnalytics, true);
l(sendToAnalytics, true);
L(sendToAnalytics, true);
T(sendToAnalytics, true);
b(sendToAnalytics);


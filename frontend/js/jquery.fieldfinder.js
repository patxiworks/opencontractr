/* jquery.fieldfinder -- to scroll to fields in ocds form. An adaptation of jquery.scrolly */
(function(e) {
    function m(s, o, k) {
        var m, a, f, j;
        ref = k ? '[data-count='+k+']' : '';
        if ((m = e('.kvp[data-path='+s+']'+ref))[t] == 0) return n;
        //if ((m = e('#scheme'))[t] == 0) return n;
        a = m[i]()[r];
        switch (o.anchor) {
            case "middle":
                f = a - (e(window).height() - m.outerHeight()) / 2;
                break;
            default:
            case r:
                f = Math.max(a, 0)
        }
        return typeof o[i] == "function" ? f -= o[i]() : f -= o[i], f
    }
    
    var t = "length",
        n = null,
        r = "top",
        i = "offset",
        s = "click.fieldfinder",
        o = e(window);
    e.fn.fieldfinder = function(i) {
        var o, a, f, l, c = e(this);
        console.log(this)
        if (this[t] == 0) return c;
        if (this[t] > 1) {
            for (o = 0; o < this[t]; o++) e(this[o]).fieldfinder(i);
            return c
        }
        l = n, f = c.attr("href"), k = c.attr("data-ref");
        //if (f.charAt(0) != "#" || f[t] < 2) return c;
        if (f[t] < 2) return c;
        a = jQuery.extend({
            anchor: r,
            easing: "swing",
            offset: 200,
            parent: e("body,html"),
            pollOnce: !1,
            speed: 1e3
        }, i), a.pollOnce && (l = m(f, a, k)), c.off(s).on(s, function(e) {
            var t = l !== n ? l : m(f, a, k);
            t !== n && (e.preventDefault(), a.parent.stop().animate({
                scrollTop: t
            }, a.speed, a.easing))
        })
    }
})(jQuery);
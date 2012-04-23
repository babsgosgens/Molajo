/* Foundation v2.1.5 http://foundation.zurb.com */
(function (a) {
    a("a[data-reveal-id]").live("click", function (c) {
        c.preventDefault();
        var b = a(this).attr("data-reveal-id");
        a("#" + b).reveal(a(this).data())
    });
    a.fn.reveal = function (b) {
        var c = {animation:"fadeAndPop", animationSpeed:300, closeOnBackgroundClick:true, dismissModalClass:"close-reveal-modal"};
        var b = a.extend({}, c, b);
        return this.each(function () {
            var l = a(this), g = parseInt(l.css("top")), i = l.height() + g, h = false, e = a(".reveal-modal-bg");
            if (e.length == 0) {
                e = a('<div class="reveal-modal-bg" />').insertAfter(l);
                e.fadeTo("fast", 0.8)
            }
            function k() {
                e.unbind("click.modalEvent");
                a("." + b.dismissModalClass).unbind("click.modalEvent");
                if (!h) {
                    m();
                    if (b.animation == "fadeAndPop") {
                        l.css({top:a(document).scrollTop() - i, opacity:0, visibility:"visible"});
                        e.fadeIn(b.animationSpeed / 2);
                        l.delay(b.animationSpeed / 2).animate({top:a(document).scrollTop() + g + "px", opacity:1}, b.animationSpeed, j)
                    }
                    if (b.animation == "fade") {
                        l.css({opacity:0, visibility:"visible", top:a(document).scrollTop() + g});
                        e.fadeIn(b.animationSpeed / 2);
                        l.delay(b.animationSpeed / 2).animate({opacity:1}, b.animationSpeed, j)
                    }
                    if (b.animation == "none") {
                        l.css({visibility:"visible", top:a(document).scrollTop() + g});
                        e.css({display:"block"});
                        j()
                    }
                }
                l.unbind("reveal:open", k)
            }

            l.bind("reveal:open", k);
            function f() {
                if (!h) {
                    m();
                    if (b.animation == "fadeAndPop") {
                        e.delay(b.animationSpeed).fadeOut(b.animationSpeed);
                        l.animate({top:a(document).scrollTop() - i + "px", opacity:0}, b.animationSpeed / 2, function () {
                            l.css({top:g, opacity:1, visibility:"hidden"});
                            j()
                        })
                    }
                    if (b.animation == "fade") {
                        e.delay(b.animationSpeed).fadeOut(b.animationSpeed);
                        l.animate({opacity:0}, b.animationSpeed, function () {
                            l.css({opacity:1, visibility:"hidden", top:g});
                            j()
                        })
                    }
                    if (b.animation == "none") {
                        l.css({visibility:"hidden", top:g});
                        e.css({display:"none"})
                    }
                }
                l.unbind("reveal:close", f)
            }

            l.bind("reveal:close", f);
            l.trigger("reveal:open");
            var d = a("." + b.dismissModalClass).bind("click.modalEvent", function () {
                l.trigger("reveal:close")
            });
            if (b.closeOnBackgroundClick) {
                e.css({cursor:"pointer"});
                e.bind("click.modalEvent", function () {
                    l.trigger("reveal:close")
                })
            }
            a("body").keyup(function (n) {
                if (n.which === 27) {
                    l.trigger("reveal:close")
                }
            });
            function j() {
                h = false
            }

            function m() {
                h = true
            }
        })
    }
})(jQuery);
(function (b) {
    b.fn.findFirstImage = function () {
        return this.first().find("img").andSelf().filter("img").first()
    };
    var a = {defaults:{animation:"horizontal-push", animationSpeed:600, timer:true, advanceSpeed:4000, pauseOnHover:false, startClockOnMouseOut:false, startClockOnMouseOutAfter:1000, directionalNav:true, captions:true, captionAnimation:"fade", captionAnimationSpeed:600, bullets:false, bulletThumbs:false, bulletThumbLocation:"", afterSlideChange:b.noop, fluid:true, centerBullets:true}, activeSlide:0, numberSlides:0, orbitWidth:null, orbitHeight:null, locked:null, timerRunning:null, degrees:0, wrapperHTML:'<div class="orbit-wrapper" />', timerHTML:'<div class="timer"><span class="mask"><span class="rotator"></span></span><span class="pause"></span></div>', captionHTML:'<div class="orbit-caption"></div>', directionalNavHTML:'<div class="slider-nav"><span class="right">Right</span><span class="left">Left</span></div>', bulletHTML:'<ul class="orbit-bullets"></ul>', init:function (f, e) {
        var c, g = 0, d = this;
        this.clickTimer = b.proxy(this.clickTimer, this);
        this.addBullet = b.proxy(this.addBullet, this);
        this.resetAndUnlock = b.proxy(this.resetAndUnlock, this);
        this.stopClock = b.proxy(this.stopClock, this);
        this.startTimerAfterMouseLeave = b.proxy(this.startTimerAfterMouseLeave, this);
        this.clearClockMouseLeaveTimer = b.proxy(this.clearClockMouseLeaveTimer, this);
        this.rotateTimer = b.proxy(this.rotateTimer, this);
        this.options = b.extend({}, this.defaults, e);
        if (this.options.timer === "false") {
            this.options.timer = false
        }
        if (this.options.captions === "false") {
            this.options.captions = false
        }
        if (this.options.directionalNav === "false") {
            this.options.directionalNav = false
        }
        this.$element = b(f);
        this.$wrapper = this.$element.wrap(this.wrapperHTML).parent();
        this.$slides = this.$element.children("img, a, div");
        this.$element.bind("orbit.next", function () {
            d.shift("next")
        });
        this.$element.bind("orbit.prev", function () {
            d.shift("prev")
        });
        this.$element.bind("orbit.goto", function (i, h) {
            d.shift(h)
        });
        this.$element.bind("orbit.start", function (i, h) {
            d.startClock()
        });
        this.$element.bind("orbit.stop", function (i, h) {
            d.stopClock()
        });
        c = this.$slides.filter("img");
        if (c.length === 0) {
            this.loaded()
        } else {
            c.bind("imageready", function () {
                g += 1;
                if (g === c.length) {
                    d.loaded()
                }
            })
        }
    }, loaded:function () {
        this.$element.addClass("orbit").css({width:"1px", height:"1px"});
        this.$slides.addClass("orbit-slide");
        this.setDimentionsFromLargestSlide();
        this.updateOptionsIfOnlyOneSlide();
        this.setupFirstSlide();
        if (this.options.timer) {
            this.setupTimer();
            this.startClock()
        }
        if (this.options.captions) {
            this.setupCaptions()
        }
        if (this.options.directionalNav) {
            this.setupDirectionalNav()
        }
        if (this.options.bullets) {
            this.setupBulletNav();
            this.setActiveBullet()
        }
    }, currentSlide:function () {
        return this.$slides.eq(this.activeSlide)
    }, setDimentionsFromLargestSlide:function () {
        var d = this, c;
        d.$element.add(d.$wrapper).width(this.$slides.first().width());
        d.$element.add(d.$wrapper).height(this.$slides.first().height());
        d.orbitWidth = this.$slides.first().width();
        d.orbitHeight = this.$slides.first().height();
        c = this.$slides.first().findFirstImage().clone();
        this.$slides.each(function () {
            var e = b(this), g = e.width(), f = e.height();
            if (g > d.$element.width()) {
                d.$element.add(d.$wrapper).width(g);
                d.orbitWidth = d.$element.width()
            }
            if (f > d.$element.height()) {
                d.$element.add(d.$wrapper).height(f);
                d.orbitHeight = d.$element.height();
                c = b(this).findFirstImage().clone()
            }
            d.numberSlides += 1
        });
        if (this.options.fluid) {
            if (typeof this.options.fluid === "string") {
                c = b('<img src="http://placehold.it/' + this.options.fluid + '" />')
            }
            d.$element.prepend(c);
            c.addClass("fluid-placeholder");
            d.$element.add(d.$wrapper).css({width:"inherit"});
            d.$element.add(d.$wrapper).css({height:"inherit"});
            b(window).bind("resize", function () {
                d.orbitWidth = d.$element.width();
                d.orbitHeight = d.$element.height()
            })
        }
    }, lock:function () {
        this.locked = true
    }, unlock:function () {
        this.locked = false
    }, updateOptionsIfOnlyOneSlide:function () {
        if (this.$slides.length === 1) {
            this.options.directionalNav = false;
            this.options.timer = false;
            this.options.bullets = false
        }
    }, setupFirstSlide:function () {
        var c = this;
        this.$slides.first().css({"z-index":3}).fadeIn(function () {
            c.$slides.css({display:"block"})
        })
    }, startClock:function () {
        var c = this;
        if (!this.options.timer) {
            return false
        }
        if (this.$timer.is(":hidden")) {
            this.clock = setInterval(function () {
                c.$element.trigger("orbit.next")
            }, this.options.advanceSpeed)
        } else {
            this.timerRunning = true;
            this.$pause.removeClass("active");
            this.clock = setInterval(this.rotateTimer, this.options.advanceSpeed / 180)
        }
    }, rotateTimer:function () {
        var c = "rotate(" + this.degrees + "deg)";
        this.degrees += 2;
        this.$rotator.css({"-webkit-transform":c, "-moz-transform":c, "-o-transform":c});
        if (this.degrees > 180) {
            this.$rotator.addClass("move");
            this.$mask.addClass("move")
        }
        if (this.degrees > 360) {
            this.$rotator.removeClass("move");
            this.$mask.removeClass("move");
            this.degrees = 0;
            this.$element.trigger("orbit.next")
        }
    }, stopClock:function () {
        if (!this.options.timer) {
            return false
        } else {
            this.timerRunning = false;
            clearInterval(this.clock);
            this.$pause.addClass("active")
        }
    }, setupTimer:function () {
        this.$timer = b(this.timerHTML);
        this.$wrapper.append(this.$timer);
        this.$rotator = this.$timer.find(".rotator");
        this.$mask = this.$timer.find(".mask");
        this.$pause = this.$timer.find(".pause");
        this.$timer.click(this.clickTimer);
        if (this.options.startClockOnMouseOut) {
            this.$wrapper.mouseleave(this.startTimerAfterMouseLeave);
            this.$wrapper.mouseenter(this.clearClockMouseLeaveTimer)
        }
        if (this.options.pauseOnHover) {
            this.$wrapper.mouseenter(this.stopClock)
        }
    }, startTimerAfterMouseLeave:function () {
        var c = this;
        this.outTimer = setTimeout(function () {
            if (!c.timerRunning) {
                c.startClock()
            }
        }, this.options.startClockOnMouseOutAfter)
    }, clearClockMouseLeaveTimer:function () {
        clearTimeout(this.outTimer)
    }, clickTimer:function () {
        if (!this.timerRunning) {
            this.startClock()
        } else {
            this.stopClock()
        }
    }, setupCaptions:function () {
        this.$caption = b(this.captionHTML);
        this.$wrapper.append(this.$caption);
        this.setCaption()
    }, setCaption:function () {
        var d = this.currentSlide().attr("data-caption"), c;
        if (!this.options.captions) {
            return false
        }
        if (d) {
            c = b(d).html();
            this.$caption.attr("id", d).html(c);
            switch (this.options.captionAnimation) {
                case"none":
                    this.$caption.show();
                    break;
                case"fade":
                    this.$caption.fadeIn(this.options.captionAnimationSpeed);
                    break;
                case"slideOpen":
                    this.$caption.slideDown(this.options.captionAnimationSpeed);
                    break
            }
        } else {
            switch (this.options.captionAnimation) {
                case"none":
                    this.$caption.hide();
                    break;
                case"fade":
                    this.$caption.fadeOut(this.options.captionAnimationSpeed);
                    break;
                case"slideOpen":
                    this.$caption.slideUp(this.options.captionAnimationSpeed);
                    break
            }
        }
    }, setupDirectionalNav:function () {
        var c = this;
        this.$wrapper.append(this.directionalNavHTML);
        this.$wrapper.find(".left").click(function () {
            c.stopClock();
            c.$element.trigger("orbit.prev")
        });
        this.$wrapper.find(".right").click(function () {
            c.stopClock();
            c.$element.trigger("orbit.next")
        })
    }, setupBulletNav:function () {
        this.$bullets = b(this.bulletHTML);
        this.$wrapper.append(this.$bullets);
        this.$slides.each(this.addBullet);
        this.$element.addClass("with-bullets");
        if (this.options.centerBullets) {
            this.$bullets.css("margin-left", -this.$bullets.width() / 2)
        }
    }, addBullet:function (g, e) {
        var d = g + 1, h = b("<li>" + (d) + "</li>"), c, f = this;
        if (this.options.bulletThumbs) {
            c = b(e).attr("data-thumb");
            if (c) {
                h.addClass("has-thumb").css({background:"url(" + this.options.bulletThumbLocation + c + ") no-repeat"})
            }
        }
        this.$bullets.append(h);
        h.data("index", g);
        h.click(function () {
            f.stopClock();
            f.$element.trigger("orbit.goto", [h.data("index")])
        })
    }, setActiveBullet:function () {
        if (!this.options.bullets) {
            return false
        } else {
            this.$bullets.find("li").removeClass("active").eq(this.activeSlide).addClass("active")
        }
    }, resetAndUnlock:function () {
        this.$slides.eq(this.prevActiveSlide).css({"z-index":1});
        this.unlock();
        this.options.afterSlideChange.call(this, this.$slides.eq(this.prevActiveSlide), this.$slides.eq(this.activeSlide))
    }, shift:function (d) {
        var c = d;
        this.prevActiveSlide = this.activeSlide;
        if (this.prevActiveSlide == c) {
            return false
        }
        if (this.$slides.length == "1") {
            return false
        }
        if (!this.locked) {
            this.lock();
            if (d == "next") {
                this.activeSlide++;
                if (this.activeSlide == this.numberSlides) {
                    this.activeSlide = 0
                }
            } else {
                if (d == "prev") {
                    this.activeSlide--;
                    if (this.activeSlide < 0) {
                        this.activeSlide = this.numberSlides - 1
                    }
                } else {
                    this.activeSlide = d;
                    if (this.prevActiveSlide < this.activeSlide) {
                        c = "next"
                    } else {
                        if (this.prevActiveSlide > this.activeSlide) {
                            c = "prev"
                        }
                    }
                }
            }
            this.setActiveBullet();
            this.$slides.eq(this.prevActiveSlide).css({"z-index":2});
            if (this.options.animation == "fade") {
                this.$slides.eq(this.activeSlide).css({opacity:0, "z-index":3}).animate({opacity:1}, this.options.animationSpeed, this.resetAndUnlock)
            }
            if (this.options.animation == "horizontal-slide") {
                if (c == "next") {
                    this.$slides.eq(this.activeSlide).css({left:this.orbitWidth, "z-index":3}).animate({left:0}, this.options.animationSpeed, this.resetAndUnlock)
                }
                if (c == "prev") {
                    this.$slides.eq(this.activeSlide).css({left:-this.orbitWidth, "z-index":3}).animate({left:0}, this.options.animationSpeed, this.resetAndUnlock)
                }
            }
            if (this.options.animation == "vertical-slide") {
                if (c == "prev") {
                    this.$slides.eq(this.activeSlide).css({top:this.orbitHeight, "z-index":3}).animate({top:0}, this.options.animationSpeed, this.resetAndUnlock)
                }
                if (c == "next") {
                    this.$slides.eq(this.activeSlide).css({top:-this.orbitHeight, "z-index":3}).animate({top:0}, this.options.animationSpeed, this.resetAndUnlock)
                }
            }
            if (this.options.animation == "horizontal-push") {
                if (c == "next") {
                    this.$slides.eq(this.activeSlide).css({left:this.orbitWidth, "z-index":3}).animate({left:0}, this.options.animationSpeed, this.resetAndUnlock);
                    this.$slides.eq(this.prevActiveSlide).animate({left:-this.orbitWidth}, this.options.animationSpeed)
                }
                if (c == "prev") {
                    this.$slides.eq(this.activeSlide).css({left:-this.orbitWidth, "z-index":3}).animate({left:0}, this.options.animationSpeed, this.resetAndUnlock);
                    this.$slides.eq(this.prevActiveSlide).animate({left:this.orbitWidth}, this.options.animationSpeed)
                }
            }
            if (this.options.animation == "vertical-push") {
                if (c == "next") {
                    this.$slides.eq(this.activeSlide).css({top:-this.orbitHeight, "z-index":3}).animate({top:0}, this.options.animationSpeed, this.resetAndUnlock);
                    this.$slides.eq(this.prevActiveSlide).animate({top:this.orbitHeight}, this.options.animationSpeed)
                }
                if (c == "prev") {
                    this.$slides.eq(this.activeSlide).css({top:this.orbitHeight, "z-index":3}).animate({top:0}, this.options.animationSpeed, this.resetAndUnlock);
                    this.$slides.eq(this.prevActiveSlide).animate({top:-this.orbitHeight}, this.options.animationSpeed)
                }
            }
            this.setCaption()
        }
    }};
    b.fn.orbit = function (c) {
        return this.each(function () {
            var d = b.extend({}, a);
            d.init(this, c)
        })
    }
})(jQuery);
/*!
 * jQuery imageready Trigger
 * http://www.zurb.com/playground/
 *
 * Copyright 2011, ZURB
 * Released under the MIT License
 */
(function (c) {
    var b = {};
    c.event.special.imageready = {setup:function (f, e, d) {
        b = f || b
    }, add:function (d) {
        var e = c(this), f;
        if (this.nodeType === 1 && this.tagName.toLowerCase() === "img" && this.src !== "") {
            if (b.forceLoad) {
                f = e.attr("src");
                e.attr("src", "");
                a(this, d.handler);
                e.attr("src", f)
            } else {
                if (this.complete || this.readyState === 4) {
                    d.handler.apply(this, arguments)
                } else {
                    a(this, d.handler)
                }
            }
        }
    }, teardown:function (d) {
        c(this).unbind(".imageready")
    }};
    function a(d, f) {
        var e = c(d);
        e.bind("load.imageready", function () {
            f.apply(d, arguments);
            e.unbind("load.imageready")
        })
    }
}(jQuery));
jQuery(document).ready(function (c) {
    function b(d) {
        c("form.custom input:" + d).each(function () {
            var f = c(this).hide(), e = f.next("span.custom." + d);
            if (e.length === 0) {
                e = c('<span class="custom ' + d + '"></span>').insertAfter(f)
            }
            e.toggleClass("checked", f.is(":checked"));
            e.toggleClass("disabled", f.is(":disabled"))
        })
    }

    b("checkbox");
    b("radio");
    function a(f) {
        var g = c(f), i = g.next("div.custom.dropdown"), d = g.find("option"), e = 0, h;
        if (i.length === 0) {
            i = c('<div class="custom dropdown"><a href="#" class="selector"></a><ul></ul></div>"');
            d.each(function () {
                h = c("<li>" + c(this).html() + "</li>");
                i.find("ul").append(h)
            });
            i.prepend('<a href="#" class="current">' + d.first().html() + "</a>");
            g.after(i);
            g.hide()
        } else {
            i.find("ul").html("");
            d.each(function () {
                h = c("<li>" + c(this).html() + "</li>");
                i.find("ul").append(h)
            })
        }
        i.toggleClass("disabled", g.is(":disabled"));
        d.each(function (j) {
            if (this.selected) {
                i.find("li").eq(j).addClass("selected");
                i.find(".current").html(c(this).html())
            }
        });
        i.find("li").each(function () {
            i.addClass("open");
            if (c(this).outerWidth() > e) {
                e = c(this).outerWidth()
            }
            i.removeClass("open")
        });
        i.css("width", e + 18 + "px");
        i.find("ul").css("width", e + 16 + "px")
    }

    c("form.custom select").each(function () {
        a(this)
    })
});
(function (c) {
    function b(e) {
        var f = 0, g = e.next();
        $options = e.find("option");
        g.find("ul").html("");
        $options.each(function () {
            $li = c("<li>" + c(this).html() + "</li>");
            g.find("ul").append($li)
        });
        $options.each(function (h) {
            if (this.selected) {
                g.find("li").eq(h).addClass("selected");
                g.find(".current").html(c(this).html())
            }
        });
        g.removeAttr("style").find("ul").removeAttr("style");
        g.find("li").each(function () {
            g.addClass("open");
            if (c(this).outerWidth() > f) {
                f = c(this).outerWidth()
            }
            g.removeClass("open")
        });
        g.css("width", f + 18 + "px");
        g.find("ul").css("width", f + 16 + "px")
    }

    function a(e) {
        var g = e.prev(), f = g[0];
        if (false == g.is(":disabled")) {
            f.checked = ((f.checked) ? false : true);
            e.toggleClass("checked");
            g.trigger("change")
        }
    }

    function d(e) {
        var g = e.prev(), f = g[0];
        c('input:radio[name="' + g.attr("name") + '"]').each(function () {
            c(this).next().removeClass("checked")
        });
        f.checked = ((f.checked) ? false : true);
        e.toggleClass("checked");
        g.trigger("change")
    }

    c("form.custom span.custom.checkbox").live("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        a(c(this))
    });
    c("form.custom span.custom.radio").live("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        d(c(this))
    });
    c("form.custom select").live("change", function (e) {
        b(c(this))
    });
    c("form.custom label").live("click", function (f) {
        var e = c("#" + c(this).attr("for")), h, g;
        if (e.length !== 0) {
            if (e.attr("type") === "checkbox") {
                f.preventDefault();
                h = c(this).find("span.custom.checkbox");
                a(h)
            } else {
                if (e.attr("type") === "radio") {
                    f.preventDefault();
                    g = c(this).find("span.custom.radio");
                    d(g)
                }
            }
        }
    });
    c("form.custom div.custom.dropdown a.current, form.custom div.custom.dropdown a.selector").live("click", function (f) {
        var h = c(this), g = h.closest("div.custom.dropdown"), e = g.prev();
        f.preventDefault();
        if (false == e.is(":disabled")) {
            g.toggleClass("open");
            if (g.hasClass("open")) {
                c(document).bind("click.customdropdown", function (i) {
                    g.removeClass("open");
                    c(document).unbind(".customdropdown")
                })
            } else {
                c(document).unbind(".customdropdown")
            }
        }
    });
    c("form.custom div.custom.dropdown li").live("click", function (h) {
        var i = c(this), f = i.closest("div.custom.dropdown"), g = f.prev(), e = 0;
        h.preventDefault();
        h.stopPropagation();
        i.closest("ul").find("li").removeClass("selected");
        i.addClass("selected");
        f.removeClass("open").find("a.current").html(i.html());
        i.closest("ul").find("li").each(function (j) {
            if (i[0] == this) {
                e = j
            }
        });
        g[0].selectedIndex = e;
        g.trigger("change")
    })
})(jQuery);
/*! http://mths.be/placeholder v1.8.7 by @mathias */
(function (o, m, r) {
    var t = "placeholder" in m.createElement("input"), q = "placeholder" in m.createElement("textarea"), l = r.fn, k;
    if (t && q) {
        k = l.placeholder = function () {
            return this
        };
        k.input = k.textarea = true
    } else {
        k = l.placeholder = function () {
            return this.filter((t ? "textarea" : ":input") + "[placeholder]").not(".placeholder").bind("focus.placeholder", s).bind("blur.placeholder", p).trigger("blur.placeholder").end()
        };
        k.input = t;
        k.textarea = q;
        r(function () {
            r(m).delegate("form", "submit.placeholder", function () {
                var a = r(".placeholder", this).each(s);
                setTimeout(function () {
                    a.each(p)
                }, 10)
            })
        });
        r(o).bind("unload.placeholder", function () {
            r(".placeholder").val("")
        })
    }
    function n(b) {
        var c = {}, a = /^jQuery\d+$/;
        r.each(b.attributes, function (d, e) {
            if (e.specified && !a.test(e.name)) {
                c[e.name] = e.value
            }
        });
        return c
    }

    function s() {
        var a = r(this);
        if (a.val() === a.attr("placeholder") && a.hasClass("placeholder")) {
            if (a.data("placeholder-password")) {
                a.hide().next().show().focus().attr("id", a.removeAttr("id").data("placeholder-id"))
            } else {
                a.val("").removeClass("placeholder")
            }
        }
    }

    function p() {
        var d, e = r(this), c = e, a = this.id;
        if (e.val() === "") {
            if (e.is(":password")) {
                if (!e.data("placeholder-textinput")) {
                    try {
                        d = e.clone().attr({type:"text"})
                    } catch (b) {
                        d = r("<input>").attr(r.extend(n(this), {type:"text"}))
                    }
                    d.removeAttr("name").data("placeholder-password", true).data("placeholder-id", a).bind("focus.placeholder", s);
                    e.data("placeholder-textinput", d).data("placeholder-id", a).before(d)
                }
                e = e.removeAttr("id").hide().prev().attr("id", a).show()
            }
            e.addClass("placeholder").val(e.attr("placeholder"))
        } else {
            e.removeClass("placeholder")
        }
    }
}(this, document, jQuery));

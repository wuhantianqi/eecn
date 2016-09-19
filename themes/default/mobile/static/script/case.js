var Swiper = function(a, b) {
		"use strict";

		function c(a, b) {
			return document.querySelectorAll ? (b || document).querySelectorAll(a) : jQuery(a, b)
		}
		function d(a) {
			return "[object Array]" === Object.prototype.toString.apply(a) ? !0 : !1
		}
		function e() {
			var a = F - I;
			return b.freeMode && (a = F - I), b.slidesPerView > C.slides.length && !b.centeredSlides && (a = 0), 0 > a && (a = 0), a
		}
		function f() {
			function a(a) {
				var c = new Image;
				c.onload = function() {
					"undefined" != typeof C && null !== C && (void 0 !== C.imagesLoaded && C.imagesLoaded++, C.imagesLoaded === C.imagesToLoad.length && (C.reInit(), b.onImagesReady && C.fireCallback(b.onImagesReady, C)))
				}, c.src = a
			}
			var d = C.h.addEventListener,
				e = "wrapper" === b.eventTarget ? C.wrapper : C.container;
			if (C.browser.ie10 || C.browser.ie11 ? (d(e, C.touchEvents.touchStart, p), d(document, C.touchEvents.touchMove, q), d(document, C.touchEvents.touchEnd, r)) : (C.support.touch && (d(e, "touchstart", p), d(e, "touchmove", q), d(e, "touchend", r)), b.simulateTouch && (d(e, "mousedown", p), d(document, "mousemove", q), d(document, "mouseup", r))), b.autoResize && d(window, "resize", C.resizeFix), g(), C._wheelEvent = !1, b.mousewheelControl) {
				if (void 0 !== document.onmousewheel && (C._wheelEvent = "mousewheel"), !C._wheelEvent) try {
					new WheelEvent("wheel"), C._wheelEvent = "wheel"
				} catch (f) {}
				C._wheelEvent || (C._wheelEvent = "DOMMouseScroll"), C._wheelEvent && d(C.container, C._wheelEvent, j)
			}
			if (b.keyboardControl && d(document, "keydown", i), b.updateOnImagesReady) {
				C.imagesToLoad = c("img", C.container);
				for (var h = 0; h < C.imagesToLoad.length; h++) a(C.imagesToLoad[h].getAttribute("src"))
			}
		}
		function g() {
			var a, d = C.h.addEventListener;
			if (b.preventLinks) {
				var e = c("a", C.container);
				for (a = 0; a < e.length; a++) d(e[a], "click", n)
			}
			if (b.releaseFormElements) {
				var f = c("input, textarea, select", C.container);
				for (a = 0; a < f.length; a++) d(f[a], C.touchEvents.touchStart, o, !0)
			}
			if (b.onSlideClick) for (a = 0; a < C.slides.length; a++) d(C.slides[a], "click", k);
			if (b.onSlideTouch) for (a = 0; a < C.slides.length; a++) d(C.slides[a], C.touchEvents.touchStart, l)
		}
		function h() {
			var a, d = C.h.removeEventListener;
			if (b.onSlideClick) for (a = 0; a < C.slides.length; a++) d(C.slides[a], "click", k);
			if (b.onSlideTouch) for (a = 0; a < C.slides.length; a++) d(C.slides[a], C.touchEvents.touchStart, l);
			if (b.releaseFormElements) {
				var e = c("input, textarea, select", C.container);
				for (a = 0; a < e.length; a++) d(e[a], C.touchEvents.touchStart, o, !0)
			}
			if (b.preventLinks) {
				var f = c("a", C.container);
				for (a = 0; a < f.length; a++) d(f[a], "click", n)
			}
		}
		function i(a) {
			var b = a.keyCode || a.charCode;
			if (!(a.shiftKey || a.altKey || a.ctrlKey || a.metaKey)) {
				if (37 === b || 39 === b || 38 === b || 40 === b) {
					for (var c = !1, d = C.h.getOffset(C.container), e = C.h.windowScroll().left, f = C.h.windowScroll().top, g = C.h.windowWidth(), h = C.h.windowHeight(), i = [
						[d.left, d.top],
						[d.left + C.width, d.top],
						[d.left, d.top + C.height],
						[d.left + C.width, d.top + C.height]
					], j = 0; j < i.length; j++) {
						var k = i[j];
						k[0] >= e && k[0] <= e + g && k[1] >= f && k[1] <= f + h && (c = !0)
					}
					if (!c) return
				}
				M ? ((37 === b || 39 === b) && (a.preventDefault ? a.preventDefault() : a.returnValue = !1), 39 === b && C.swipeNext(), 37 === b && C.swipePrev()) : ((38 === b || 40 === b) && (a.preventDefault ? a.preventDefault() : a.returnValue = !1), 40 === b && C.swipeNext(), 38 === b && C.swipePrev())
			}
		}
		function j(a) {
			var c = C._wheelEvent,
				d = 0;
			if (a.detail) d = -a.detail;
			else if ("mousewheel" === c) if (b.mousewheelControlForceToAxis) if (M) {
				if (!(Math.abs(a.wheelDeltaX) > Math.abs(a.wheelDeltaY))) return;
				d = a.wheelDeltaX
			} else {
				if (!(Math.abs(a.wheelDeltaY) > Math.abs(a.wheelDeltaX))) return;
				d = a.wheelDeltaY
			} else d = a.wheelDelta;
			else if ("DOMMouseScroll" === c) d = -a.detail;
			else if ("wheel" === c) if (b.mousewheelControlForceToAxis) if (M) {
				if (!(Math.abs(a.deltaX) > Math.abs(a.deltaY))) return;
				d = -a.deltaX
			} else {
				if (!(Math.abs(a.deltaY) > Math.abs(a.deltaX))) return;
				d = -a.deltaY
			} else d = Math.abs(a.deltaX) > Math.abs(a.deltaY) ? -a.deltaX : -a.deltaY;
			if (b.freeMode) {
				var f = C.getWrapperTranslate() + d;
				if (f > 0 && (f = 0), f < -e() && (f = -e()), C.setWrapperTransition(0), C.setWrapperTranslate(f), C.updateActiveSlide(f), 0 === f || f === -e()) return
			} else(new Date).getTime() - U > 60 && (0 > d ? C.swipeNext() : C.swipePrev()), U = (new Date).getTime();
			return b.autoplay && C.stopAutoplay(!0), a.preventDefault ? a.preventDefault() : a.returnValue = !1, !1
		}
		function k(a) {
			C.allowSlideClick && (m(a), C.fireCallback(b.onSlideClick, C, a))
		}
		function l(a) {
			m(a), C.fireCallback(b.onSlideTouch, C, a)
		}
		function m(a) {
			if (a.currentTarget) C.clickedSlide = a.currentTarget;
			else {
				var c = a.srcElement;
				do {
					if (c.className.indexOf(b.slideClass) > -1) break;
					c = c.parentNode
				} while (c);
				C.clickedSlide = c
			}
			C.clickedSlideIndex = C.slides.indexOf(C.clickedSlide), C.clickedSlideLoopIndex = C.clickedSlideIndex - (C.loopedSlides || 0)
		}
		function n(a) {
			return C.allowLinks ? void 0 : (a.preventDefault ? a.preventDefault() : a.returnValue = !1, b.preventLinksPropagation && "stopPropagation" in a && a.stopPropagation(), !1)
		}
		function o(a) {
			return a.stopPropagation ? a.stopPropagation() : a.returnValue = !1, !1
		}
		function p(a) {
			if (b.preventLinks && (C.allowLinks = !0), C.isTouched || b.onlyExternal) return !1;
			var c = a.target || a.srcElement;
			document.activeElement && document.activeElement !== c && document.activeElement.blur();
			var d = "input select textarea".split(" ");
			if (b.noSwiping && c && s(c)) return !1;
			if ($ = !1, C.isTouched = !0, Z = "touchstart" === a.type, !Z && "which" in a && 3 === a.which) return !1;
			if (!Z || 1 === a.targetTouches.length) {
				C.callPlugins("onTouchStartBegin"), !Z && !C.isAndroid && d.indexOf(c.tagName.toLowerCase()) < 0 && (a.preventDefault ? a.preventDefault() : a.returnValue = !1);
				var e = Z ? a.targetTouches[0].pageX : a.pageX || a.clientX,
					f = Z ? a.targetTouches[0].pageY : a.pageY || a.clientY;
				C.touches.startX = C.touches.currentX = e, C.touches.startY = C.touches.currentY = f, C.touches.start = C.touches.current = M ? e : f, C.setWrapperTransition(0), C.positions.start = C.positions.current = C.getWrapperTranslate(), C.setWrapperTranslate(C.positions.start), C.times.start = (new Date).getTime(), H = void 0, b.moveStartThreshold > 0 && (W = !1), b.onTouchStart && C.fireCallback(b.onTouchStart, C, a), C.callPlugins("onTouchStartEnd")
			}
		}
		function q(a) {
			if (C.isTouched && !b.onlyExternal && (!Z || "mousemove" !== a.type)) {
				var c = Z ? a.targetTouches[0].pageX : a.pageX || a.clientX,
					d = Z ? a.targetTouches[0].pageY : a.pageY || a.clientY;
				if ("undefined" == typeof H && M && (H = !! (H || Math.abs(d - C.touches.startY) > Math.abs(c - C.touches.startX))), "undefined" != typeof H || M || (H = !! (H || Math.abs(d - C.touches.startY) < Math.abs(c - C.touches.startX))), H) return void(C.isTouched = !1);
				if (M) {
					if (!b.swipeToNext && c < C.touches.startX || !b.swipeToPrev && c > C.touches.startX) return
				} else if (!b.swipeToNext && d < C.touches.startY || !b.swipeToPrev && d > C.touches.startY) return;
				if (a.assignedToSwiper) return void(C.isTouched = !1);
				if (a.assignedToSwiper = !0, b.preventLinks && (C.allowLinks = !1), b.onSlideClick && (C.allowSlideClick = !1), b.autoplay && C.stopAutoplay(!0), !Z || 1 === a.touches.length) {
					if (C.isMoved || (C.callPlugins("onTouchMoveStart"), b.loop && (C.fixLoop(), C.positions.start = C.getWrapperTranslate()), b.onTouchMoveStart && C.fireCallback(b.onTouchMoveStart, C)), C.isMoved = !0, a.preventDefault ? a.preventDefault() : a.returnValue = !1, C.touches.current = M ? c : d, C.positions.current = (C.touches.current - C.touches.start) * b.touchRatio + C.positions.start, C.positions.current > 0 && b.onResistanceBefore && C.fireCallback(b.onResistanceBefore, C, C.positions.current), C.positions.current < -e() && b.onResistanceAfter && C.fireCallback(b.onResistanceAfter, C, Math.abs(C.positions.current + e())), b.resistance && "100%" !== b.resistance) {
						var f;
						if (C.positions.current > 0 && (f = 1 - C.positions.current / I / 2, C.positions.current = .5 > f ? I / 2 : C.positions.current * f), C.positions.current < -e()) {
							var g = (C.touches.current - C.touches.start) * b.touchRatio + (e() + C.positions.start);
							f = (I + g) / I;
							var h = C.positions.current - g * (1 - f) / 2,
								i = -e() - I / 2;
							C.positions.current = i > h || 0 >= f ? i : h
						}
					}
					if (b.resistance && "100%" === b.resistance && (C.positions.current > 0 && (!b.freeMode || b.freeModeFluid) && (C.positions.current = 0), C.positions.current < -e() && (!b.freeMode || b.freeModeFluid) && (C.positions.current = -e())), !b.followFinger) return;
					if (b.moveStartThreshold) if (Math.abs(C.touches.current - C.touches.start) > b.moveStartThreshold || W) {
						if (!W) return W = !0, void(C.touches.start = C.touches.current);
						C.setWrapperTranslate(C.positions.current)
					} else C.positions.current = C.positions.start;
					else C.setWrapperTranslate(C.positions.current);
					return (b.freeMode || b.watchActiveIndex) && C.updateActiveSlide(C.positions.current), b.grabCursor && (C.container.style.cursor = "move", C.container.style.cursor = "grabbing", C.container.style.cursor = "-moz-grabbin", C.container.style.cursor = "-webkit-grabbing"), X || (X = C.touches.current), Y || (Y = (new Date).getTime()), C.velocity = (C.touches.current - X) / ((new Date).getTime() - Y) / 2, Math.abs(C.touches.current - X) < 2 && (C.velocity = 0), X = C.touches.current, Y = (new Date).getTime(), C.callPlugins("onTouchMoveEnd"), b.onTouchMove && C.fireCallback(b.onTouchMove, C, a), !1
				}
			}
		}
		function r(a) {
			if (H && C.swipeReset(), !b.onlyExternal && C.isTouched) {
				C.isTouched = !1, b.grabCursor && (C.container.style.cursor = "move", C.container.style.cursor = "grab", C.container.style.cursor = "-moz-grab", C.container.style.cursor = "-webkit-grab"), C.positions.current || 0 === C.positions.current || (C.positions.current = C.positions.start), b.followFinger && C.setWrapperTranslate(C.positions.current), C.times.end = (new Date).getTime(), C.touches.diff = C.touches.current - C.touches.start, C.touches.abs = Math.abs(C.touches.diff), C.positions.diff = C.positions.current - C.positions.start, C.positions.abs = Math.abs(C.positions.diff);
				var c = C.positions.diff,
					d = C.positions.abs,
					f = C.times.end - C.times.start;
				5 > d && 300 > f && C.allowLinks === !1 && (b.freeMode || 0 === d || C.swipeReset(), b.preventLinks && (C.allowLinks = !0), b.onSlideClick && (C.allowSlideClick = !0)), setTimeout(function() {
					"undefined" != typeof C && null !== C && (b.preventLinks && (C.allowLinks = !0), b.onSlideClick && (C.allowSlideClick = !0))
				}, 100);
				var g = e();
				if (!C.isMoved && b.freeMode) return C.isMoved = !1, b.onTouchEnd && C.fireCallback(b.onTouchEnd, C, a), void C.callPlugins("onTouchEnd");
				if (!C.isMoved || C.positions.current > 0 || C.positions.current < -g) return C.swipeReset(), b.onTouchEnd && C.fireCallback(b.onTouchEnd, C, a), void C.callPlugins("onTouchEnd");
				if (C.isMoved = !1, b.freeMode) {
					if (b.freeModeFluid) {
						var h, i = 1e3 * b.momentumRatio,
							j = C.velocity * i,
							k = C.positions.current + j,
							l = !1,
							m = 20 * Math.abs(C.velocity) * b.momentumBounceRatio; - g > k && (b.momentumBounce && C.support.transitions ? (-m > k + g && (k = -g - m), h = -g, l = !0, $ = !0) : k = -g), k > 0 && (b.momentumBounce && C.support.transitions ? (k > m && (k = m), h = 0, l = !0, $ = !0) : k = 0), 0 !== C.velocity && (i = Math.abs((k - C.positions.current) / C.velocity)), C.setWrapperTranslate(k), C.setWrapperTransition(i), b.momentumBounce && l && C.wrapperTransitionEnd(function() {
							$ && (b.onMomentumBounce && C.fireCallback(b.onMomentumBounce, C), C.callPlugins("onMomentumBounce"), C.setWrapperTranslate(h), C.setWrapperTransition(300))
						}), C.updateActiveSlide(k)
					}
					return (!b.freeModeFluid || f >= 300) && C.updateActiveSlide(C.positions.current), b.onTouchEnd && C.fireCallback(b.onTouchEnd, C, a), void C.callPlugins("onTouchEnd")
				}
				G = 0 > c ? "toNext" : "toPrev", "toNext" === G && 300 >= f && (30 > d || !b.shortSwipes ? C.swipeReset() : C.swipeNext(!0)), "toPrev" === G && 300 >= f && (30 > d || !b.shortSwipes ? C.swipeReset() : C.swipePrev(!0));
				var n = 0;
				if ("auto" === b.slidesPerView) {
					for (var o, p = Math.abs(C.getWrapperTranslate()), q = 0, r = 0; r < C.slides.length; r++) if (o = M ? C.slides[r].getWidth(!0, b.roundLengths) : C.slides[r].getHeight(!0, b.roundLengths), q += o, q > p) {
						n = o;
						break
					}
					n > I && (n = I)
				} else n = E * b.slidesPerView;
				"toNext" === G && f > 300 && (d >= n * b.longSwipesRatio ? C.swipeNext(!0) : C.swipeReset()), "toPrev" === G && f > 300 && (d >= n * b.longSwipesRatio ? C.swipePrev(!0) : C.swipeReset()), b.onTouchEnd && C.fireCallback(b.onTouchEnd, C, a), C.callPlugins("onTouchEnd")
			}
		}
		function s(a) {
			var c = !1;
			do a.className.indexOf(b.noSwipingClass) > -1 && (c = !0), a = a.parentElement;
			while (!c && a.parentElement && -1 === a.className.indexOf(b.wrapperClass));
			return !c && a.className.indexOf(b.wrapperClass) > -1 && a.className.indexOf(b.noSwipingClass) > -1 && (c = !0), c
		}
		function t(a, b) {
			var c, d = document.createElement("div");
			return d.innerHTML = b, c = d.firstChild, c.className += " " + a, c.outerHTML
		}
		function u(a, c, d) {
			function e() {
				var f = +new Date,
					l = f - g;
				h += i * l / (1e3 / 60), k = "toNext" === j ? h > a : a > h, k ? (C.setWrapperTranslate(Math.ceil(h)), C._DOMAnimating = !0, window.setTimeout(function() {
					e()
				}, 1e3 / 60)) : (b.onSlideChangeEnd && ("to" === c ? d.runCallbacks === !0 && C.fireCallback(b.onSlideChangeEnd, C, j) : C.fireCallback(b.onSlideChangeEnd, C, j)), C.setWrapperTranslate(a), C._DOMAnimating = !1)
			}
			var f = "to" === c && d.speed >= 0 ? d.speed : b.speed,
				g = +new Date;
			if (C.support.transitions || !b.DOMAnimation) C.setWrapperTranslate(a), C.setWrapperTransition(f);
			else {
				var h = C.getWrapperTranslate(),
					i = Math.ceil((a - h) / f * (1e3 / 60)),
					j = h > a ? "toNext" : "toPrev",
					k = "toNext" === j ? h > a : a > h;
				if (C._DOMAnimating) return;
				e()
			}
			C.updateActiveSlide(a), b.onSlideNext && "next" === c && C.fireCallback(b.onSlideNext, C, a), b.onSlidePrev && "prev" === c && C.fireCallback(b.onSlidePrev, C, a), b.onSlideReset && "reset" === c && C.fireCallback(b.onSlideReset, C, a), ("next" === c || "prev" === c || "to" === c && d.runCallbacks === !0) && v(c)
		}
		function v(a) {
			if (C.callPlugins("onSlideChangeStart"), b.onSlideChangeStart) if (b.queueStartCallbacks && C.support.transitions) {
				if (C._queueStartCallbacks) return;
				C._queueStartCallbacks = !0, C.fireCallback(b.onSlideChangeStart, C, a), C.wrapperTransitionEnd(function() {
					C._queueStartCallbacks = !1
				})
			} else C.fireCallback(b.onSlideChangeStart, C, a);
			if (b.onSlideChangeEnd) if (C.support.transitions) if (b.queueEndCallbacks) {
				if (C._queueEndCallbacks) return;
				C._queueEndCallbacks = !0, C.wrapperTransitionEnd(function(c) {
					C.fireCallback(b.onSlideChangeEnd, c, a)
				})
			} else C.wrapperTransitionEnd(function(c) {
				C.fireCallback(b.onSlideChangeEnd, c, a)
			});
			else b.DOMAnimation || setTimeout(function() {
				C.fireCallback(b.onSlideChangeEnd, C, a)
			}, 10)
		}
		function w() {
			var a = C.paginationButtons;
			if (a) for (var b = 0; b < a.length; b++) C.h.removeEventListener(a[b], "click", y)
		}
		function x() {
			var a = C.paginationButtons;
			if (a) for (var b = 0; b < a.length; b++) C.h.addEventListener(a[b], "click", y)
		}
		function y(a) {
			for (var c, d = a.target || a.srcElement, e = C.paginationButtons, f = 0; f < e.length; f++) d === e[f] && (c = f);
			b.autoplay && C.stopAutoplay(!0), C.swipeTo(c)
		}
		function z() {
			_ = setTimeout(function() {
				b.loop ? (C.fixLoop(), C.swipeNext(!0)) : C.swipeNext(!0) || (b.autoplayStopOnLast ? (clearTimeout(_), _ = void 0) : C.swipeTo(0)), C.wrapperTransitionEnd(function() {
					"undefined" != typeof _ && z()
				})
			}, b.autoplay)
		}
		function A() {
			C.calcSlides(), b.loader.slides.length > 0 && 0 === C.slides.length && C.loadSlides(), b.loop && C.createLoop(), C.init(), f(), b.pagination && C.createPagination(!0), b.loop || b.initialSlide > 0 ? C.swipeTo(b.initialSlide, 0, !1) : C.updateActiveSlide(0), b.autoplay && C.startAutoplay(), C.centerIndex = C.activeIndex, b.onSwiperCreated && C.fireCallback(b.onSwiperCreated, C), C.callPlugins("onSwiperCreated")
		}
		if (!document.body.outerHTML && document.body.__defineGetter__ && HTMLElement) {
			var B = HTMLElement.prototype;
			B.__defineGetter__ && B.__defineGetter__("outerHTML", function() {
				return (new XMLSerializer).serializeToString(this)
			})
		}
		if (window.getComputedStyle || (window.getComputedStyle = function(a) {
			return this.el = a, this.getPropertyValue = function(b) {
				var c = /(\-([a-z]){1})/g;
				return "float" === b && (b = "styleFloat"), c.test(b) && (b = b.replace(c, function() {
					return arguments[2].toUpperCase()
				})), a.currentStyle[b] ? a.currentStyle[b] : null
			}, this
		}), Array.prototype.indexOf || (Array.prototype.indexOf = function(a, b) {
			for (var c = b || 0, d = this.length; d > c; c++) if (this[c] === a) return c;
			return -1
		}), (document.querySelectorAll || window.jQuery) && "undefined" != typeof a && (a.nodeType || 0 !== c(a).length)) {
			var C = this;
			C.touches = {
				start: 0,
				startX: 0,
				startY: 0,
				current: 0,
				currentX: 0,
				currentY: 0,
				diff: 0,
				abs: 0
			}, C.positions = {
				start: 0,
				abs: 0,
				diff: 0,
				current: 0
			}, C.times = {
				start: 0,
				end: 0
			}, C.id = (new Date).getTime(), C.container = a.nodeType ? a : c(a)[0], C.isTouched = !1, C.isMoved = !1, C.activeIndex = 0, C.centerIndex = 0, C.activeLoaderIndex = 0, C.activeLoopIndex = 0, C.previousIndex = null, C.velocity = 0, C.snapGrid = [], C.slidesGrid = [], C.imagesToLoad = [], C.imagesLoaded = 0, C.wrapperLeft = 0, C.wrapperRight = 0, C.wrapperTop = 0, C.wrapperBottom = 0, C.isAndroid = navigator.userAgent.toLowerCase().indexOf("android") >= 0;
			var D, E, F, G, H, I, J = {
				eventTarget: "wrapper",
				mode: "horizontal",
				touchRatio: 1,
				speed: 300,
				freeMode: !1,
				freeModeFluid: !1,
				momentumRatio: 1,
				momentumBounce: !0,
				momentumBounceRatio: 1,
				slidesPerView: 1,
				slidesPerGroup: 1,
				slidesPerViewFit: !0,
				simulateTouch: !0,
				followFinger: !0,
				shortSwipes: !0,
				longSwipesRatio: .5,
				moveStartThreshold: !1,
				onlyExternal: !1,
				createPagination: !0,
				pagination: !1,
				paginationElement: "span",
				paginationClickable: !1,
				paginationAsRange: !0,
				resistance: !0,
				scrollContainer: !1,
				preventLinks: !0,
				preventLinksPropagation: !1,
				noSwiping: !1,
				noSwipingClass: "swiper-no-swiping",
				initialSlide: 0,
				keyboardControl: !1,
				mousewheelControl: !1,
				mousewheelControlForceToAxis: !1,
				useCSS3Transforms: !0,
				autoplay: !1,
				autoplayDisableOnInteraction: !0,
				autoplayStopOnLast: !1,
				loop: !1,
				loopAdditionalSlides: 0,
				roundLengths: !1,
				calculateHeight: !1,
				cssWidthAndHeight: !1,
				updateOnImagesReady: !0,
				releaseFormElements: !0,
				watchActiveIndex: !1,
				visibilityFullFit: !1,
				offsetPxBefore: 0,
				offsetPxAfter: 0,
				offsetSlidesBefore: 0,
				offsetSlidesAfter: 0,
				centeredSlides: !1,
				queueStartCallbacks: !1,
				queueEndCallbacks: !1,
				autoResize: !0,
				resizeReInit: !1,
				DOMAnimation: !0,
				loader: {
					slides: [],
					slidesHTMLType: "inner",
					surroundGroups: 1,
					logic: "reload",
					loadAllSlides: !1
				},
				swipeToPrev: !0,
				swipeToNext: !0,
				slideElement: "div",
				slideClass: "swiper-slide",
				slideActiveClass: "swiper-slide-active",
				slideVisibleClass: "swiper-slide-visible",
				slideDuplicateClass: "swiper-slide-duplicate",
				wrapperClass: "swiper-wrapper",
				paginationElementClass: "swiper-pagination-switch",
				paginationActiveClass: "swiper-active-switch",
				paginationVisibleClass: "swiper-visible-switch"
			};
			b = b || {};
			for (var K in J) if (K in b && "object" == typeof b[K]) for (var L in J[K]) L in b[K] || (b[K][L] = J[K][L]);
			else K in b || (b[K] = J[K]);
			C.params = b, b.scrollContainer && (b.freeMode = !0, b.freeModeFluid = !0), b.loop && (b.resistance = "100%");
			var M = "horizontal" === b.mode,
				N = ["mousedown", "mousemove", "mouseup"];
			C.browser.ie10 && (N = ["MSPointerDown", "MSPointerMove", "MSPointerUp"]), C.browser.ie11 && (N = ["pointerdown", "pointermove", "pointerup"]), C.touchEvents = {
				touchStart: C.support.touch || !b.simulateTouch ? "touchstart" : N[0],
				touchMove: C.support.touch || !b.simulateTouch ? "touchmove" : N[1],
				touchEnd: C.support.touch || !b.simulateTouch ? "touchend" : N[2]
			};
			for (var O = C.container.childNodes.length - 1; O >= 0; O--) if (C.container.childNodes[O].className) for (var P = C.container.childNodes[O].className.split(/\s+/), Q = 0; Q < P.length; Q++) P[Q] === b.wrapperClass && (D = C.container.childNodes[O]);
			C.wrapper = D, C._extendSwiperSlide = function(a) {
				return a.append = function() {
					return b.loop ? a.insertAfter(C.slides.length - C.loopedSlides) : (C.wrapper.appendChild(a), C.reInit()), a
				}, a.prepend = function() {
					return b.loop ? (C.wrapper.insertBefore(a, C.slides[C.loopedSlides]), C.removeLoopedSlides(), C.calcSlides(), C.createLoop()) : C.wrapper.insertBefore(a, C.wrapper.firstChild), C.reInit(), a
				}, a.insertAfter = function(c) {
					if ("undefined" == typeof c) return !1;
					var d;
					return b.loop ? (d = C.slides[c + 1 + C.loopedSlides], d ? C.wrapper.insertBefore(a, d) : C.wrapper.appendChild(a), C.removeLoopedSlides(), C.calcSlides(), C.createLoop()) : (d = C.slides[c + 1], C.wrapper.insertBefore(a, d)), C.reInit(), a
				}, a.clone = function() {
					return C._extendSwiperSlide(a.cloneNode(!0))
				}, a.remove = function() {
					C.wrapper.removeChild(a), C.reInit()
				}, a.html = function(b) {
					return "undefined" == typeof b ? a.innerHTML : (a.innerHTML = b, a)
				}, a.index = function() {
					for (var b, c = C.slides.length - 1; c >= 0; c--) a === C.slides[c] && (b = c);
					return b
				}, a.isActive = function() {
					return a.index() === C.activeIndex ? !0 : !1
				}, a.swiperSlideDataStorage || (a.swiperSlideDataStorage = {}), a.getData = function(b) {
					return a.swiperSlideDataStorage[b]
				}, a.setData = function(b, c) {
					return a.swiperSlideDataStorage[b] = c, a
				}, a.data = function(b, c) {
					return "undefined" == typeof c ? a.getAttribute("data-" + b) : (a.setAttribute("data-" + b, c), a)
				}, a.getWidth = function(b, c) {
					return C.h.getWidth(a, b, c)
				}, a.getHeight = function(b, c) {
					return C.h.getHeight(a, b, c)
				}, a.getOffset = function() {
					return C.h.getOffset(a)
				}, a
			}, C.calcSlides = function(a) {
				var c = C.slides ? C.slides.length : !1;
				C.slides = [], C.displaySlides = [];
				for (var d = 0; d < C.wrapper.childNodes.length; d++) if (C.wrapper.childNodes[d].className) for (var e = C.wrapper.childNodes[d].className, f = e.split(/\s+/), i = 0; i < f.length; i++) f[i] === b.slideClass && C.slides.push(C.wrapper.childNodes[d]);
				for (d = C.slides.length - 1; d >= 0; d--) C._extendSwiperSlide(C.slides[d]);
				c !== !1 && (c !== C.slides.length || a) && (h(), g(), C.updateActiveSlide(), C.params.pagination && C.createPagination(), C.callPlugins("numberOfSlidesChanged"))
			}, C.createSlide = function(a, c, d) {
				c = c || C.params.slideClass, d = d || b.slideElement;
				var e = document.createElement(d);
				return e.innerHTML = a || "", e.className = c, C._extendSwiperSlide(e)
			}, C.appendSlide = function(a, b, c) {
				return a ? a.nodeType ? C._extendSwiperSlide(a).append() : C.createSlide(a, b, c).append() : void 0
			}, C.prependSlide = function(a, b, c) {
				return a ? a.nodeType ? C._extendSwiperSlide(a).prepend() : C.createSlide(a, b, c).prepend() : void 0
			}, C.insertSlideAfter = function(a, b, c, d) {
				return "undefined" == typeof a ? !1 : b.nodeType ? C._extendSwiperSlide(b).insertAfter(a) : C.createSlide(b, c, d).insertAfter(a)
			}, C.removeSlide = function(a) {
				if (C.slides[a]) {
					if (b.loop) {
						if (!C.slides[a + C.loopedSlides]) return !1;
						C.slides[a + C.loopedSlides].remove(), C.removeLoopedSlides(), C.calcSlides(), C.createLoop()
					} else C.slides[a].remove();
					return !0
				}
				return !1
			}, C.removeLastSlide = function() {
				return C.slides.length > 0 ? (b.loop ? (C.slides[C.slides.length - 1 - C.loopedSlides].remove(), C.removeLoopedSlides(), C.calcSlides(), C.createLoop()) : C.slides[C.slides.length - 1].remove(), !0) : !1
			}, C.removeAllSlides = function() {
				for (var a = C.slides.length - 1; a >= 0; a--) C.slides[a].remove()
			}, C.getSlide = function(a) {
				return C.slides[a]
			}, C.getLastSlide = function() {
				return C.slides[C.slides.length - 1]
			}, C.getFirstSlide = function() {
				return C.slides[0]
			}, C.activeSlide = function() {
				return C.slides[C.activeIndex]
			}, C.fireCallback = function() {
				var a = arguments[0];
				if ("[object Array]" === Object.prototype.toString.call(a)) for (var c = 0; c < a.length; c++)"function" == typeof a[c] && a[c](arguments[1], arguments[2], arguments[3], arguments[4], arguments[5]);
				else "[object String]" === Object.prototype.toString.call(a) ? b["on" + a] && C.fireCallback(b["on" + a], arguments[1], arguments[2], arguments[3], arguments[4], arguments[5]) : a(arguments[1], arguments[2], arguments[3], arguments[4], arguments[5])
			}, C.addCallback = function(a, b) {
				var c, e = this;
				return e.params["on" + a] ? d(this.params["on" + a]) ? this.params["on" + a].push(b) : "function" == typeof this.params["on" + a] ? (c = this.params["on" + a], this.params["on" + a] = [], this.params["on" + a].push(c), this.params["on" + a].push(b)) : void 0 : (this.params["on" + a] = [], this.params["on" + a].push(b))
			}, C.removeCallbacks = function(a) {
				C.params["on" + a] && (C.params["on" + a] = null)
			};
			var R = [];
			for (var S in C.plugins) if (b[S]) {
				var T = C.plugins[S](C, b[S]);
				T && R.push(T)
			}
			C.callPlugins = function(a, b) {
				b || (b = {});
				for (var c = 0; c < R.length; c++) a in R[c] && R[c][a](b)
			}, !C.browser.ie10 && !C.browser.ie11 || b.onlyExternal || C.wrapper.classList.add("swiper-wp8-" + (M ? "horizontal" : "vertical")), b.freeMode && (C.container.className += " swiper-free-mode"), C.initialized = !1, C.init = function(a, c) {
				var d = C.h.getWidth(C.container, !1, b.roundLengths),
					e = C.h.getHeight(C.container, !1, b.roundLengths);
				if (d !== C.width || e !== C.height || a) {
					C.width = d, C.height = e;
					var f, g, h, i, j, k, l;
					I = M ? d : e;
					var m = C.wrapper;
					if (a && C.calcSlides(c), "auto" === b.slidesPerView) {
						var n = 0,
							o = 0;
						b.slidesOffset > 0 && (m.style.paddingLeft = "", m.style.paddingRight = "", m.style.paddingTop = "", m.style.paddingBottom = ""), m.style.width = "", m.style.height = "", b.offsetPxBefore > 0 && (M ? C.wrapperLeft = b.offsetPxBefore : C.wrapperTop = b.offsetPxBefore), b.offsetPxAfter > 0 && (M ? C.wrapperRight = b.offsetPxAfter : C.wrapperBottom = b.offsetPxAfter), b.centeredSlides && (M ? (C.wrapperLeft = (I - this.slides[0].getWidth(!0, b.roundLengths)) / 2, C.wrapperRight = (I - C.slides[C.slides.length - 1].getWidth(!0, b.roundLengths)) / 2) : (C.wrapperTop = (I - C.slides[0].getHeight(!0, b.roundLengths)) / 2, C.wrapperBottom = (I - C.slides[C.slides.length - 1].getHeight(!0, b.roundLengths)) / 2)), M ? (C.wrapperLeft >= 0 && (m.style.paddingLeft = C.wrapperLeft + "px"), C.wrapperRight >= 0 && (m.style.paddingRight = C.wrapperRight + "px")) : (C.wrapperTop >= 0 && (m.style.paddingTop = C.wrapperTop + "px"), C.wrapperBottom >= 0 && (m.style.paddingBottom = C.wrapperBottom + "px")), k = 0;
						var p = 0;
						for (C.snapGrid = [], C.slidesGrid = [], h = 0, l = 0; l < C.slides.length; l++) {
							f = C.slides[l].getWidth(!0, b.roundLengths), g = C.slides[l].getHeight(!0, b.roundLengths), b.calculateHeight && (h = Math.max(h, g));
							var q = M ? f : g;
							if (b.centeredSlides) {
								var r = l === C.slides.length - 1 ? 0 : C.slides[l + 1].getWidth(!0, b.roundLengths),
									s = l === C.slides.length - 1 ? 0 : C.slides[l + 1].getHeight(!0, b.roundLengths),
									t = M ? r : s;
								if (q > I) {
									if (b.slidesPerViewFit) C.snapGrid.push(k + C.wrapperLeft), C.snapGrid.push(k + q - I + C.wrapperLeft);
									else for (var u = 0; u <= Math.floor(q / (I + C.wrapperLeft)); u++) C.snapGrid.push(0 === u ? k + C.wrapperLeft : k + C.wrapperLeft + I * u);
									C.slidesGrid.push(k + C.wrapperLeft)
								} else C.snapGrid.push(p), C.slidesGrid.push(p);
								p += q / 2 + t / 2
							} else {
								if (q > I) if (b.slidesPerViewFit) C.snapGrid.push(k), C.snapGrid.push(k + q - I);
								else if (0 !== I) for (var v = 0; v <= Math.floor(q / I); v++) C.snapGrid.push(k + I * v);
								else C.snapGrid.push(k);
								else C.snapGrid.push(k);
								C.slidesGrid.push(k)
							}
							k += q, n += f, o += g
						}
						b.calculateHeight && (C.height = h), M ? (F = n + C.wrapperRight + C.wrapperLeft, m.style.width = n + "px", m.style.height = C.height + "px") : (F = o + C.wrapperTop + C.wrapperBottom, m.style.width = C.width + "px", m.style.height = o + "px")
					} else if (b.scrollContainer) m.style.width = "", m.style.height = "", i = C.slides[0].getWidth(!0, b.roundLengths), j = C.slides[0].getHeight(!0, b.roundLengths), F = M ? i : j, m.style.width = i + "px", m.style.height = j + "px", E = M ? i : j;
					else {
						if (b.calculateHeight) {
							for (h = 0, j = 0, M || (C.container.style.height = ""), m.style.height = "", l = 0; l < C.slides.length; l++) C.slides[l].style.height = "", h = Math.max(C.slides[l].getHeight(!0), h), M || (j += C.slides[l].getHeight(!0));
							g = h, C.height = g, M ? j = g : (I = g, C.container.style.height = I + "px")
						} else g = M ? C.height : C.height / b.slidesPerView, b.roundLengths && (g = Math.ceil(g)), j = M ? C.height : C.slides.length * g;
						for (f = M ? C.width / b.slidesPerView : C.width, b.roundLengths && (f = Math.ceil(f)), i = M ? C.slides.length * f : C.width, E = M ? f : g, b.offsetSlidesBefore > 0 && (M ? C.wrapperLeft = E * b.offsetSlidesBefore : C.wrapperTop = E * b.offsetSlidesBefore), b.offsetSlidesAfter > 0 && (M ? C.wrapperRight = E * b.offsetSlidesAfter : C.wrapperBottom = E * b.offsetSlidesAfter), b.offsetPxBefore > 0 && (M ? C.wrapperLeft = b.offsetPxBefore : C.wrapperTop = b.offsetPxBefore), b.offsetPxAfter > 0 && (M ? C.wrapperRight = b.offsetPxAfter : C.wrapperBottom = b.offsetPxAfter), b.centeredSlides && (M ? (C.wrapperLeft = (I - E) / 2, C.wrapperRight = (I - E) / 2) : (C.wrapperTop = (I - E) / 2, C.wrapperBottom = (I - E) / 2)), M ? (C.wrapperLeft > 0 && (m.style.paddingLeft = C.wrapperLeft + "px"), C.wrapperRight > 0 && (m.style.paddingRight = C.wrapperRight + "px")) : (C.wrapperTop > 0 && (m.style.paddingTop = C.wrapperTop + "px"), C.wrapperBottom > 0 && (m.style.paddingBottom = C.wrapperBottom + "px")), F = M ? i + C.wrapperRight + C.wrapperLeft : j + C.wrapperTop + C.wrapperBottom, parseFloat(i) > 0 && (!b.cssWidthAndHeight || "height" === b.cssWidthAndHeight) && (m.style.width = i + "px"), parseFloat(j) > 0 && (!b.cssWidthAndHeight || "width" === b.cssWidthAndHeight) && (m.style.height = j + "px"), k = 0, C.snapGrid = [], C.slidesGrid = [], l = 0; l < C.slides.length; l++) C.snapGrid.push(k), C.slidesGrid.push(k), k += E, parseFloat(f) > 0 && (!b.cssWidthAndHeight || "height" === b.cssWidthAndHeight) && (C.slides[l].style.width = f + "px"), parseFloat(g) > 0 && (!b.cssWidthAndHeight || "width" === b.cssWidthAndHeight) && (C.slides[l].style.height = g + "px")
					}
					C.initialized ? (C.callPlugins("onInit"), b.onInit && C.fireCallback(b.onInit, C)) : (C.callPlugins("onFirstInit"), b.onFirstInit && C.fireCallback(b.onFirstInit, C)), C.initialized = !0
				}
			}, C.reInit = function(a) {
				C.init(!0, a)
			}, C.resizeFix = function(a) {
				C.callPlugins("beforeResizeFix"), C.init(b.resizeReInit || a), b.freeMode ? C.getWrapperTranslate() < -e() && (C.setWrapperTransition(0), C.setWrapperTranslate(-e())) : (C.swipeTo(b.loop ? C.activeLoopIndex : C.activeIndex, 0, !1), b.autoplay && (C.support.transitions && "undefined" != typeof _ ? "undefined" != typeof _ && (clearTimeout(_), _ = void 0, C.startAutoplay()) : "undefined" != typeof ab && (clearInterval(ab), ab = void 0, C.startAutoplay()))), C.callPlugins("afterResizeFix")
			}, C.destroy = function() {
				var a = C.h.removeEventListener,
					c = "wrapper" === b.eventTarget ? C.wrapper : C.container;
				C.browser.ie10 || C.browser.ie11 ? (a(c, C.touchEvents.touchStart, p), a(document, C.touchEvents.touchMove, q), a(document, C.touchEvents.touchEnd, r)) : (C.support.touch && (a(c, "touchstart", p), a(c, "touchmove", q), a(c, "touchend", r)), b.simulateTouch && (a(c, "mousedown", p), a(document, "mousemove", q), a(document, "mouseup", r))), b.autoResize && a(window, "resize", C.resizeFix), h(), b.paginationClickable && w(), b.mousewheelControl && C._wheelEvent && a(C.container, C._wheelEvent, j), b.keyboardControl && a(document, "keydown", i), b.autoplay && C.stopAutoplay(), C.callPlugins("onDestroy"), C = null
			}, C.disableKeyboardControl = function() {
				b.keyboardControl = !1, C.h.removeEventListener(document, "keydown", i)
			}, C.enableKeyboardControl = function() {
				b.keyboardControl = !0, C.h.addEventListener(document, "keydown", i)
			};
			var U = (new Date).getTime();
			if (C.disableMousewheelControl = function() {
				return C._wheelEvent ? (b.mousewheelControl = !1, C.h.removeEventListener(C.container, C._wheelEvent, j), !0) : !1
			}, C.enableMousewheelControl = function() {
				return C._wheelEvent ? (b.mousewheelControl = !0, C.h.addEventListener(C.container, C._wheelEvent, j), !0) : !1
			}, b.grabCursor) {
				var V = C.container.style;
				V.cursor = "move", V.cursor = "grab", V.cursor = "-moz-grab", V.cursor = "-webkit-grab"
			}
			C.allowSlideClick = !0, C.allowLinks = !0;
			var W, X, Y, Z = !1,
				$ = !0;
			C.swipeNext = function(a) {
				!a && b.loop && C.fixLoop(), !a && b.autoplay && C.stopAutoplay(!0), C.callPlugins("onSwipeNext");
				var c = C.getWrapperTranslate(),
					d = c;
				if ("auto" === b.slidesPerView) {
					for (var f = 0; f < C.snapGrid.length; f++) if (-c >= C.snapGrid[f] && -c < C.snapGrid[f + 1]) {
						d = -C.snapGrid[f + 1];
						break
					}
				} else {
					var g = E * b.slidesPerGroup;
					d = -(Math.floor(Math.abs(c) / Math.floor(g)) * g + g)
				}
				return d < -e() && (d = -e()), d === c ? !1 : (u(d, "next"), !0)
			}, C.swipePrev = function(a) {
				!a && b.loop && C.fixLoop(), !a && b.autoplay && C.stopAutoplay(!0), C.callPlugins("onSwipePrev");
				var c, d = Math.ceil(C.getWrapperTranslate());
				if ("auto" === b.slidesPerView) {
					c = 0;
					for (var e = 1; e < C.snapGrid.length; e++) {
						if (-d === C.snapGrid[e]) {
							c = -C.snapGrid[e - 1];
							break
						}
						if (-d > C.snapGrid[e] && -d < C.snapGrid[e + 1]) {
							c = -C.snapGrid[e];
							break
						}
					}
				} else {
					var f = E * b.slidesPerGroup;
					c = -(Math.ceil(-d / f) - 1) * f
				}
				return c > 0 && (c = 0), c === d ? !1 : (u(c, "prev"), !0)
			}, C.swipeReset = function() {
				C.callPlugins("onSwipeReset");
				var a, c = C.getWrapperTranslate(),
					d = E * b.slidesPerGroup;
				if (-e(), "auto" === b.slidesPerView) {
					a = 0;
					for (var f = 0; f < C.snapGrid.length; f++) {
						if (-c === C.snapGrid[f]) return;
						if (-c >= C.snapGrid[f] && -c < C.snapGrid[f + 1]) {
							a = C.positions.diff > 0 ? -C.snapGrid[f + 1] : -C.snapGrid[f];
							break
						}
					} - c >= C.snapGrid[C.snapGrid.length - 1] && (a = -C.snapGrid[C.snapGrid.length - 1]), c <= -e() && (a = -e())
				} else a = 0 > c ? Math.round(c / d) * d : 0, c <= -e() && (a = -e());
				return b.scrollContainer && (a = 0 > c ? c : 0), a < -e() && (a = -e()), b.scrollContainer && I > E && (a = 0), a === c ? !1 : (u(a, "reset"), !0)
			}, C.swipeTo = function(a, c, d) {
				a = parseInt(a, 10), C.callPlugins("onSwipeTo", {
					index: a,
					speed: c
				}), b.loop && (a += C.loopedSlides);
				var f = C.getWrapperTranslate();
				if (!(a > C.slides.length - 1 || 0 > a)) {
					var g;
					return g = "auto" === b.slidesPerView ? -C.slidesGrid[a] : -a * E, g < -e() && (g = -e()), g === f ? !1 : (d = d === !1 ? !1 : !0, u(g, "to", {
						index: a,
						speed: c,
						runCallbacks: d
					}), !0)
				}
			}, C._queueStartCallbacks = !1, C._queueEndCallbacks = !1, C.updateActiveSlide = function(a) {
				if (C.initialized && 0 !== C.slides.length) {
					C.previousIndex = C.activeIndex, "undefined" == typeof a && (a = C.getWrapperTranslate()), a > 0 && (a = 0);
					var c;
					if ("auto" === b.slidesPerView) {
						if (C.activeIndex = C.slidesGrid.indexOf(-a), C.activeIndex < 0) {
							for (c = 0; c < C.slidesGrid.length - 1 && !(-a > C.slidesGrid[c] && -a < C.slidesGrid[c + 1]); c++);
							var d = Math.abs(C.slidesGrid[c] + a),
								e = Math.abs(C.slidesGrid[c + 1] + a);
							C.activeIndex = e >= d ? c : c + 1
						}
					} else C.activeIndex = Math[b.visibilityFullFit ? "ceil" : "round"](-a / E);
					if (C.activeIndex === C.slides.length && (C.activeIndex = C.slides.length - 1), C.activeIndex < 0 && (C.activeIndex = 0), C.slides[C.activeIndex]) {
						if (C.calcVisibleSlides(a), C.support.classList) {
							var f;
							for (c = 0; c < C.slides.length; c++) f = C.slides[c], f.classList.remove(b.slideActiveClass), C.visibleSlides.indexOf(f) >= 0 ? f.classList.add(b.slideVisibleClass) : f.classList.remove(b.slideVisibleClass);
							C.slides[C.activeIndex].classList.add(b.slideActiveClass)
						} else {
							var g = new RegExp("\\s*" + b.slideActiveClass),
								h = new RegExp("\\s*" + b.slideVisibleClass);
							for (c = 0; c < C.slides.length; c++) C.slides[c].className = C.slides[c].className.replace(g, "").replace(h, ""), C.visibleSlides.indexOf(C.slides[c]) >= 0 && (C.slides[c].className += " " + b.slideVisibleClass);
							C.slides[C.activeIndex].className += " " + b.slideActiveClass
						}
						if (b.loop) {
							var i = C.loopedSlides;
							C.activeLoopIndex = C.activeIndex - i, C.activeLoopIndex >= C.slides.length - 2 * i && (C.activeLoopIndex = C.slides.length - 2 * i - C.activeLoopIndex), C.activeLoopIndex < 0 && (C.activeLoopIndex = C.slides.length - 2 * i + C.activeLoopIndex), C.activeLoopIndex < 0 && (C.activeLoopIndex = 0)
						} else C.activeLoopIndex = C.activeIndex;
						b.pagination && C.updatePagination(a)
					}
				}
			}, C.createPagination = function(a) {
				if (b.paginationClickable && C.paginationButtons && w(), C.paginationContainer = b.pagination.nodeType ? b.pagination : c(b.pagination)[0], b.createPagination) {
					var d = "",
						e = C.slides.length,
						f = e;
					b.loop && (f -= 2 * C.loopedSlides);
					for (var g = 0; f > g; g++) d += "<" + b.paginationElement + ' class="' + b.paginationElementClass + '"></' + b.paginationElement + ">";
					C.paginationContainer.innerHTML = d
				}
				C.paginationButtons = c("." + b.paginationElementClass, C.paginationContainer), a || C.updatePagination(), C.callPlugins("onCreatePagination"), b.paginationClickable && x()
			}, C.updatePagination = function(a) {
				if (b.pagination && !(C.slides.length < 1)) {
					var d = c("." + b.paginationActiveClass, C.paginationContainer);
					if (d) {
						var e = C.paginationButtons;
						if (0 !== e.length) {
							for (var f = 0; f < e.length; f++) e[f].className = b.paginationElementClass;
							var g = b.loop ? C.loopedSlides : 0;
							if (b.paginationAsRange) {
								C.visibleSlides || C.calcVisibleSlides(a);
								var h, i = [];
								for (h = 0; h < C.visibleSlides.length; h++) {
									var j = C.slides.indexOf(C.visibleSlides[h]) - g;
									b.loop && 0 > j && (j = C.slides.length - 2 * C.loopedSlides + j), b.loop && j >= C.slides.length - 2 * C.loopedSlides && (j = C.slides.length - 2 * C.loopedSlides - j, j = Math.abs(j)), i.push(j)
								}
								for (h = 0; h < i.length; h++) e[i[h]] && (e[i[h]].className += " " + b.paginationVisibleClass);
								b.loop ? void 0 !== e[C.activeLoopIndex] && (e[C.activeLoopIndex].className += " " + b.paginationActiveClass) : e[C.activeIndex].className += " " + b.paginationActiveClass
							} else b.loop ? e[C.activeLoopIndex] && (e[C.activeLoopIndex].className += " " + b.paginationActiveClass + " " + b.paginationVisibleClass) : e[C.activeIndex].className += " " + b.paginationActiveClass + " " + b.paginationVisibleClass
						}
					}
				}
			}, C.calcVisibleSlides = function(a) {
				var c = [],
					d = 0,
					e = 0,
					f = 0;
				M && C.wrapperLeft > 0 && (a += C.wrapperLeft), !M && C.wrapperTop > 0 && (a += C.wrapperTop);
				for (var g = 0; g < C.slides.length; g++) {
					d += e, e = "auto" === b.slidesPerView ? M ? C.h.getWidth(C.slides[g], !0, b.roundLengths) : C.h.getHeight(C.slides[g], !0, b.roundLengths) : E, f = d + e;
					var h = !1;
					b.visibilityFullFit ? (d >= -a && -a + I >= f && (h = !0), -a >= d && f >= -a + I && (h = !0)) : (f > -a && -a + I >= f && (h = !0), d >= -a && -a + I > d && (h = !0), -a > d && f > -a + I && (h = !0)), h && c.push(C.slides[g])
				}
				0 === c.length && (c = [C.slides[C.activeIndex]]), C.visibleSlides = c
			};
			var _, ab;
			C.startAutoplay = function() {
				if (C.support.transitions) {
					if ("undefined" != typeof _) return !1;
					if (!b.autoplay) return;
					C.callPlugins("onAutoplayStart"), b.onAutoplayStart && C.fireCallback(b.onAutoplayStart, C), z()
				} else {
					if ("undefined" != typeof ab) return !1;
					if (!b.autoplay) return;
					C.callPlugins("onAutoplayStart"), b.onAutoplayStart && C.fireCallback(b.onAutoplayStart, C), ab = setInterval(function() {
						b.loop ? (C.fixLoop(), C.swipeNext(!0)) : C.swipeNext(!0) || (b.autoplayStopOnLast ? (clearInterval(ab), ab = void 0) : C.swipeTo(0))
					}, b.autoplay)
				}
			}, C.stopAutoplay = function(a) {
				if (C.support.transitions) {
					if (!_) return;
					_ && clearTimeout(_), _ = void 0, a && !b.autoplayDisableOnInteraction && C.wrapperTransitionEnd(function() {
						z()
					}), C.callPlugins("onAutoplayStop"), b.onAutoplayStop && C.fireCallback(b.onAutoplayStop, C)
				} else ab && clearInterval(ab), ab = void 0, C.callPlugins("onAutoplayStop"), b.onAutoplayStop && C.fireCallback(b.onAutoplayStop, C)
			}, C.loopCreated = !1, C.removeLoopedSlides = function() {
				if (C.loopCreated) for (var a = 0; a < C.slides.length; a++) C.slides[a].getData("looped") === !0 && C.wrapper.removeChild(C.slides[a])
			}, C.createLoop = function() {
				if (0 !== C.slides.length) {
					C.loopedSlides = "auto" === b.slidesPerView ? b.loopedSlides || 1 : b.slidesPerView + b.loopAdditionalSlides, C.loopedSlides > C.slides.length && (C.loopedSlides = C.slides.length);
					var a, c = "",
						d = "",
						e = "",
						f = C.slides.length,
						g = Math.floor(C.loopedSlides / f),
						h = C.loopedSlides % f;
					for (a = 0; g * f > a; a++) {
						var i = a;
						if (a >= f) {
							var j = Math.floor(a / f);
							i = a - f * j
						}
						e += C.slides[i].outerHTML
					}
					for (a = 0; h > a; a++) d += t(b.slideDuplicateClass, C.slides[a].outerHTML);
					for (a = f - h; f > a; a++) c += t(b.slideDuplicateClass, C.slides[a].outerHTML);
					var k = c + e + D.innerHTML + e + d;
					for (D.innerHTML = k, C.loopCreated = !0, C.calcSlides(), a = 0; a < C.slides.length; a++)(a < C.loopedSlides || a >= C.slides.length - C.loopedSlides) && C.slides[a].setData("looped", !0);
					C.callPlugins("onCreateLoop")
				}
			}, C.fixLoop = function() {
				var a;
				C.activeIndex < C.loopedSlides ? (a = C.slides.length - 3 * C.loopedSlides + C.activeIndex, C.swipeTo(a, 0, !1)) : ("auto" === b.slidesPerView && C.activeIndex >= 2 * C.loopedSlides || C.activeIndex > C.slides.length - 2 * b.slidesPerView) && (a = -C.slides.length + C.activeIndex + C.loopedSlides, C.swipeTo(a, 0, !1))
			}, C.loadSlides = function() {
				var a = "";
				C.activeLoaderIndex = 0;
				for (var c = b.loader.slides, d = b.loader.loadAllSlides ? c.length : b.slidesPerView * (1 + b.loader.surroundGroups), e = 0; d > e; e++) a += "outer" === b.loader.slidesHTMLType ? c[e] : "<" + b.slideElement + ' class="' + b.slideClass + '" data-swiperindex="' + e + '">' + c[e] + "</" + b.slideElement + ">";
				C.wrapper.innerHTML = a, C.calcSlides(!0), b.loader.loadAllSlides || C.wrapperTransitionEnd(C.reloadSlides, !0)
			}, C.reloadSlides = function() {
				var a = b.loader.slides,
					c = parseInt(C.activeSlide().data("swiperindex"), 10);
				if (!(0 > c || c > a.length - 1)) {
					C.activeLoaderIndex = c;
					var d = Math.max(0, c - b.slidesPerView * b.loader.surroundGroups),
						e = Math.min(c + b.slidesPerView * (1 + b.loader.surroundGroups) - 1, a.length - 1);
					if (c > 0) {
						var f = -E * (c - d);
						C.setWrapperTranslate(f), C.setWrapperTransition(0)
					}
					var g;
					if ("reload" === b.loader.logic) {
						C.wrapper.innerHTML = "";
						var h = "";
						for (g = d; e >= g; g++) h += "outer" === b.loader.slidesHTMLType ? a[g] : "<" + b.slideElement + ' class="' + b.slideClass + '" data-swiperindex="' + g + '">' + a[g] + "</" + b.slideElement + ">";
						C.wrapper.innerHTML = h
					} else {
						var i = 1e3,
							j = 0;
						for (g = 0; g < C.slides.length; g++) {
							var k = C.slides[g].data("swiperindex");
							d > k || k > e ? C.wrapper.removeChild(C.slides[g]) : (i = Math.min(k, i), j = Math.max(k, j))
						}
						for (g = d; e >= g; g++) {
							var l;
							i > g && (l = document.createElement(b.slideElement), l.className = b.slideClass, l.setAttribute("data-swiperindex", g), l.innerHTML = a[g], C.wrapper.insertBefore(l, C.wrapper.firstChild)), g > j && (l = document.createElement(b.slideElement), l.className = b.slideClass, l.setAttribute("data-swiperindex", g), l.innerHTML = a[g], C.wrapper.appendChild(l))
						}
					}
					C.reInit(!0)
				}
			}, A()
		}
	};
Swiper.prototype = {
	plugins: {},
	wrapperTransitionEnd: function(a, b) {
		"use strict";

		function c(h) {
			if (h.target === f && (a(e), e.params.queueEndCallbacks && (e._queueEndCallbacks = !1), !b)) for (d = 0; d < g.length; d++) e.h.removeEventListener(f, g[d], c)
		}
		var d, e = this,
			f = e.wrapper,
			g = ["webkitTransitionEnd", "transitionend", "oTransitionEnd", "MSTransitionEnd", "msTransitionEnd"];
		if (a) for (d = 0; d < g.length; d++) e.h.addEventListener(f, g[d], c)
	},
	getWrapperTranslate: function(a) {
		"use strict";
		var b, c, d, e, f = this.wrapper;
		return "undefined" == typeof a && (a = "horizontal" === this.params.mode ? "x" : "y"), this.support.transforms && this.params.useCSS3Transforms ? (d = window.getComputedStyle(f, null), window.WebKitCSSMatrix ? e = new WebKitCSSMatrix("none" === d.webkitTransform ? "" : d.webkitTransform) : (e = d.MozTransform || d.OTransform || d.MsTransform || d.msTransform || d.transform || d.getPropertyValue("transform").replace("translate(", "matrix(1, 0, 0, 1,"), b = e.toString().split(",")), "x" === a && (c = window.WebKitCSSMatrix ? e.m41 : parseFloat(16 === b.length ? b[12] : b[4])), "y" === a && (c = window.WebKitCSSMatrix ? e.m42 : parseFloat(16 === b.length ? b[13] : b[5]))) : ("x" === a && (c = parseFloat(f.style.left, 10) || 0), "y" === a && (c = parseFloat(f.style.top, 10) || 0)), c || 0
	},
	setWrapperTranslate: function(a, b, c) {
		"use strict";
		var d, e = this.wrapper.style,
			f = {
				x: 0,
				y: 0,
				z: 0
			};
		3 === arguments.length ? (f.x = a, f.y = b, f.z = c) : ("undefined" == typeof b && (b = "horizontal" === this.params.mode ? "x" : "y"), f[b] = a), this.support.transforms && this.params.useCSS3Transforms ? (d = this.support.transforms3d ? "translate3d(" + f.x + "px, " + f.y + "px, " + f.z + "px)" : "translate(" + f.x + "px, " + f.y + "px)", e.webkitTransform = e.MsTransform = e.msTransform = e.MozTransform = e.OTransform = e.transform = d) : (e.left = f.x + "px", e.top = f.y + "px"), this.callPlugins("onSetWrapperTransform", f), this.params.onSetWrapperTransform && this.fireCallback(this.params.onSetWrapperTransform, this, f)
	},
	setWrapperTransition: function(a) {
		"use strict";
		var b = this.wrapper.style;
		b.webkitTransitionDuration = b.MsTransitionDuration = b.msTransitionDuration = b.MozTransitionDuration = b.OTransitionDuration = b.transitionDuration = a / 1e3 + "s", this.callPlugins("onSetWrapperTransition", {
			duration: a
		}), this.params.onSetWrapperTransition && this.fireCallback(this.params.onSetWrapperTransition, this, a)
	},
	h: {
		getWidth: function(a, b, c) {
			"use strict";
			var d = window.getComputedStyle(a, null).getPropertyValue("width"),
				e = parseFloat(d);
			return (isNaN(e) || d.indexOf("%") > 0 || 0 > e) && (e = a.offsetWidth - parseFloat(window.getComputedStyle(a, null).getPropertyValue("padding-left")) - parseFloat(window.getComputedStyle(a, null).getPropertyValue("padding-right"))), b && (e += parseFloat(window.getComputedStyle(a, null).getPropertyValue("padding-left")) + parseFloat(window.getComputedStyle(a, null).getPropertyValue("padding-right"))), c ? Math.ceil(e) : e
		},
		getHeight: function(a, b, c) {
			"use strict";
			if (b) return a.offsetHeight;
			var d = window.getComputedStyle(a, null).getPropertyValue("height"),
				e = parseFloat(d);
			return (isNaN(e) || d.indexOf("%") > 0 || 0 > e) && (e = a.offsetHeight - parseFloat(window.getComputedStyle(a, null).getPropertyValue("padding-top")) - parseFloat(window.getComputedStyle(a, null).getPropertyValue("padding-bottom"))), b && (e += parseFloat(window.getComputedStyle(a, null).getPropertyValue("padding-top")) + parseFloat(window.getComputedStyle(a, null).getPropertyValue("padding-bottom"))), c ? Math.ceil(e) : e
		},
		getOffset: function(a) {
			"use strict";
			var b = a.getBoundingClientRect(),
				c = document.body,
				d = a.clientTop || c.clientTop || 0,
				e = a.clientLeft || c.clientLeft || 0,
				f = window.pageYOffset || a.scrollTop,
				g = window.pageXOffset || a.scrollLeft;
			return document.documentElement && !window.pageYOffset && (f = document.documentElement.scrollTop, g = document.documentElement.scrollLeft), {
				top: b.top + f - d,
				left: b.left + g - e
			}
		},
		windowWidth: function() {
			"use strict";
			return window.innerWidth ? window.innerWidth : document.documentElement && document.documentElement.clientWidth ? document.documentElement.clientWidth : void 0
		},
		windowHeight: function() {
			"use strict";
			return window.innerHeight ? window.innerHeight : document.documentElement && document.documentElement.clientHeight ? document.documentElement.clientHeight : void 0
		},
		windowScroll: function() {
			"use strict";
			return "undefined" != typeof pageYOffset ? {
				left: window.pageXOffset,
				top: window.pageYOffset
			} : document.documentElement ? {
				left: document.documentElement.scrollLeft,
				top: document.documentElement.scrollTop
			} : void 0
		},
		addEventListener: function(a, b, c, d) {
			"use strict";
			"undefined" == typeof d && (d = !1), a.addEventListener ? a.addEventListener(b, c, d) : a.attachEvent && a.attachEvent("on" + b, c)
		},
		removeEventListener: function(a, b, c, d) {
			"use strict";
			"undefined" == typeof d && (d = !1), a.removeEventListener ? a.removeEventListener(b, c, d) : a.detachEvent && a.detachEvent("on" + b, c)
		}
	},
	setTransform: function(a, b) {
		"use strict";
		var c = a.style;
		c.webkitTransform = c.MsTransform = c.msTransform = c.MozTransform = c.OTransform = c.transform = b
	},
	setTranslate: function(a, b) {
		"use strict";
		var c = a.style,
			d = {
				x: b.x || 0,
				y: b.y || 0,
				z: b.z || 0
			},
			e = this.support.transforms3d ? "translate3d(" + d.x + "px," + d.y + "px," + d.z + "px)" : "translate(" + d.x + "px," + d.y + "px)";
		c.webkitTransform = c.MsTransform = c.msTransform = c.MozTransform = c.OTransform = c.transform = e, this.support.transforms || (c.left = d.x + "px", c.top = d.y + "px")
	},
	setTransition: function(a, b) {
		"use strict";
		var c = a.style;
		c.webkitTransitionDuration = c.MsTransitionDuration = c.msTransitionDuration = c.MozTransitionDuration = c.OTransitionDuration = c.transitionDuration = b + "ms"
	},
	support: {
		touch: window.Modernizr && Modernizr.touch === !0 ||
		function() {
			"use strict";
			return !!("ontouchstart" in window || window.DocumentTouch && document instanceof DocumentTouch)
		}(),
		transforms3d: window.Modernizr && Modernizr.csstransforms3d === !0 ||
		function() {
			"use strict";
			var a = document.createElement("div").style;
			return "webkitPerspective" in a || "MozPerspective" in a || "OPerspective" in a || "MsPerspective" in a || "perspective" in a
		}(),
		transforms: window.Modernizr && Modernizr.csstransforms === !0 ||
		function() {
			"use strict";
			var a = document.createElement("div").style;
			return "transform" in a || "WebkitTransform" in a || "MozTransform" in a || "msTransform" in a || "MsTransform" in a || "OTransform" in a
		}(),
		transitions: window.Modernizr && Modernizr.csstransitions === !0 ||
		function() {
			"use strict";
			var a = document.createElement("div").style;
			return "transition" in a || "WebkitTransition" in a || "MozTransition" in a || "msTransition" in a || "MsTransition" in a || "OTransition" in a
		}(),
		classList: function() {
			"use strict";
			var a = document.createElement("div");
			return "classList" in a
		}()
	},
	browser: {
		ie8: function() {
			"use strict";
			var a = -1;
			if ("Microsoft Internet Explorer" === navigator.appName) {
				var b = navigator.userAgent,
					c = new RegExp(/MSIE ([0-9]{1,}[\.0-9]{0,})/);
				null !== c.exec(b) && (a = parseFloat(RegExp.$1))
			}
			return -1 !== a && 9 > a
		}(),
		ie10: window.navigator.msPointerEnabled,
		ie11: window.navigator.pointerEnabled
	}
}, (window.jQuery || window.Zepto) && !
function(a) {
	"use strict";
	a.fn.swiper = function(b) {
		var c;
		return this.each(function(d) {
			var e = a(this);
			if (!e.data("swiper")) {
				var f = new Swiper(e[0], b);
				d || (c = f), e.data("swiper", f)
			}
		}), c
	}
}(window.jQuery || window.Zepto), "undefined" != typeof module && (module.exports = Swiper), "function" == typeof define && define.amd && define([], function() {
	"use strict";
	return Swiper
}), function() {
	"use strict";
	var a = function(a) {
			var b = function(b, c) {
					this.el = a(b), this.zoomFactor = 1, this.lastScale = 1, this.offset = {
						x: 0,
						y: 0
					}, this.options = a.extend({}, this.defaults, c), this.setupMarkup(), this.bindEvents(), this.update(), this.enable()
				},
				c = function(a, b) {
					return a + b
				},
				d = function(a, b) {
					return a > b - .01 && b + .01 > a
				};
			b.prototype = {
				defaults: {
					tapZoomFactor: 2,
					zoomOutFactor: 1.3,
					animationDuration: 300,
					animationInterval: 5,
					maxZoom: 4,
					minZoom: .5,
					lockDragAxis: !1,
					use2d: !0,
					zoomStartEventName: "pz_zoomstart",
					zoomEndEventName: "pz_zoomend",
					dragStartEventName: "pz_dragstart",
					dragEndEventName: "pz_dragend",
					doubleTapEventName: "pz_doubletap"
				},
				handleDragStart: function(a) {
					this.el.trigger(this.options.dragStartEventName), this.stopAnimation(), this.lastDragPosition = !1, this.hasInteraction = !0, this.handleDrag(a)
				},
				handleDrag: function(a) {
					if (this.zoomFactor > 1) {
						var b = this.getTouches(a)[0];
						this.drag(b, this.lastDragPosition), this.offset = this.sanitizeOffset(this.offset), this.lastDragPosition = b
					}
				},
				handleDragEnd: function() {
					this.el.trigger(this.options.dragEndEventName), this.end()
				},
				handleZoomStart: function() {
					this.el.trigger(this.options.zoomStartEventName), this.stopAnimation(), this.lastScale = 1, this.nthZoom = 0, this.lastZoomCenter = !1, this.hasInteraction = !0
				},
				handleZoom: function(a, b) {
					var c = this.getTouchCenter(this.getTouches(a)),
						d = b / this.lastScale;
					this.lastScale = b, this.nthZoom += 1, this.nthZoom > 3 && (this.scale(d, c), this.drag(c, this.lastZoomCenter)), this.lastZoomCenter = c
				},
				handleZoomEnd: function() {
					this.el.trigger(this.options.zoomEndEventName), this.end()
				},
				handleDoubleTap: function(a) {
					var b = this.getTouches(a)[0],
						c = this.zoomFactor > 1 ? 1 : this.options.tapZoomFactor,
						d = this.zoomFactor,
						e = function(a) {
							this.scaleTo(d + a * (c - d), b)
						}.bind(this);
					this.hasInteraction || (d > c && (b = this.getCurrentZoomCenter()), this.animate(this.options.animationDuration, this.options.animationInterval, e, this.swing), this.el.trigger(this.options.doubleTapEventName))
				},
				sanitizeOffset: function(a) {
					var b = (this.zoomFactor - 1) * this.getContainerX(),
						c = (this.zoomFactor - 1) * this.getContainerY(),
						d = Math.max(b, 0),
						e = Math.max(c, 0),
						f = Math.min(b, 0),
						g = Math.min(c, 0);
					return {
						x: Math.min(Math.max(a.x, f), d),
						y: Math.min(Math.max(a.y, g), e)
					}
				},
				scaleTo: function(a, b) {
					this.scale(a / this.zoomFactor, b)
				},
				scale: function(a, b) {
					a = this.scaleZoomFactor(a), this.addOffset({
						x: (a - 1) * (b.x + this.offset.x),
						y: (a - 1) * (b.y + this.offset.y)
					})
				},
				scaleZoomFactor: function(a) {
					var b = this.zoomFactor;
					return this.zoomFactor *= a, this.zoomFactor = Math.min(this.options.maxZoom, Math.max(this.zoomFactor, this.options.minZoom)), this.zoomFactor / b
				},
				drag: function(a, b) {
					b && this.addOffset(this.options.lockDragAxis ? Math.abs(a.x - b.x) > Math.abs(a.y - b.y) ? {
						x: -(a.x - b.x),
						y: 0
					} : {
						y: -(a.y - b.y),
						x: 0
					} : {
						y: -(a.y - b.y),
						x: -(a.x - b.x)
					})
				},
				getTouchCenter: function(a) {
					return this.getVectorAvg(a)
				},
				getVectorAvg: function(a) {
					return {
						x: a.map(function(a) {
							return a.x
						}).reduce(c) / a.length,
						y: a.map(function(a) {
							return a.y
						}).reduce(c) / a.length
					}
				},
				addOffset: function(a) {
					this.offset = {
						x: this.offset.x + a.x,
						y: this.offset.y + a.y
					}
				},
				sanitize: function() {
					this.zoomFactor < this.options.zoomOutFactor ? this.zoomOutAnimation() : this.isInsaneOffset(this.offset) && this.sanitizeOffsetAnimation()
				},
				isInsaneOffset: function(a) {
					var b = this.sanitizeOffset(a);
					return b.x !== a.x || b.y !== a.y
				},
				sanitizeOffsetAnimation: function() {
					var a = this.sanitizeOffset(this.offset),
						b = {
							x: this.offset.x,
							y: this.offset.y
						},
						c = function(c) {
							this.offset.x = b.x + c * (a.x - b.x), this.offset.y = b.y + c * (a.y - b.y), this.update()
						}.bind(this);
					this.animate(this.options.animationDuration, this.options.animationInterval, c, this.swing)
				},
				zoomOutAnimation: function() {
					var a = this.zoomFactor,
						b = 1,
						c = this.getCurrentZoomCenter(),
						d = function(d) {
							this.scaleTo(a + d * (b - a), c)
						}.bind(this);
					this.animate(this.options.animationDuration, this.options.animationInterval, d, this.swing)
				},
				updateAspectRatio: function() {
					this.setContainerY(this.getContainerX() / this.getAspectRatio())
				},
				getInitialZoomFactor: function() {
					return this.container[0].offsetWidth / this.el[0].offsetWidth
				},
				getAspectRatio: function() {
					return this.el[0].offsetWidth / this.el[0].offsetHeight
				},
				getCurrentZoomCenter: function() {
					var a = this.container[0].offsetWidth * this.zoomFactor,
						b = this.offset.x,
						c = a - b - this.container[0].offsetWidth,
						d = b / c,
						e = d * this.container[0].offsetWidth / (d + 1),
						f = this.container[0].offsetHeight * this.zoomFactor,
						g = this.offset.y,
						h = f - g - this.container[0].offsetHeight,
						i = g / h,
						j = i * this.container[0].offsetHeight / (i + 1);
					return 0 === c && (e = this.container[0].offsetWidth), 0 === h && (j = this.container[0].offsetHeight), {
						x: e,
						y: j
					}
				},
				canDrag: function() {
					return !d(this.zoomFactor, 1)
				},
				getTouches: function(a) {
					var b = this.container.offset();
					return Array.prototype.slice.call(a.touches).map(function(a) {
						return {
							x: a.pageX - b.left,
							y: a.pageY - b.top
						}
					})
				},
				animate: function(a, b, c, d, e) {
					var f = (new Date).getTime(),
						g = function() {
							if (this.inAnimation) {
								var h = (new Date).getTime() - f,
									i = h / a;
								h >= a ? (c(1), e && e(), this.update(), this.stopAnimation(), this.update()) : (d && (i = d(i)), c(i), this.update(), setTimeout(g, b))
							}
						}.bind(this);
					this.inAnimation = !0, g()
				},
				stopAnimation: function() {
					this.inAnimation = !1
				},
				swing: function(a) {
					return -Math.cos(a * Math.PI) / 2 + .5
				},
				getContainerX: function() {
					return this.container[0].offsetWidth
				},
				getContainerY: function() {
					return this.container[0].offsetHeight
				},
				setContainerY: function(a) {
					return this.container.height(a)
				},
				setupMarkup: function() {
					this.container = a('<div class="pinch-zoom-container"></div>'), this.el.before(this.container), this.container.append(this.el), this.container.css({
						overflow: "hidden",
						position: "relative"
					}), this.el.css({
						"-webkit-transform-origin": "0% 0%",
						"-moz-transform-origin": "0% 0%",
						"-ms-transform-origin": "0% 0%",
						"-o-transform-origin": "0% 0%",
						"transform-origin": "0% 0%",
						position: "absolute"
					})
				},
				end: function() {
					this.hasInteraction = !1, this.sanitize(), this.update()
				},
				bindEvents: function() {
					e(this.container.get(0), this), a(window).on("resize", this.update.bind(this)), a(this.el).find("img").on("load", this.update.bind(this))
				},
				update: function() {
					this.updatePlaned || (this.updatePlaned = !0, setTimeout(function() {
						this.updatePlaned = !1, this.updateAspectRatio();
						var a = this.getInitialZoomFactor() * this.zoomFactor,
							b = -this.offset.x / a,
							c = -this.offset.y / a,
							d = "scale3d(" + a + ", " + a + ",1) translate3d(" + b + "px," + c + "px,0px)",
							e = "scale(" + a + ", " + a + ") translate(" + b + "px," + c + "px)",
							f = function() {
								this.clone && (this.clone.remove(), delete this.clone)
							}.bind(this);
						!this.options.use2d || this.hasInteraction || this.inAnimation ? (this.is3d = !0, f(), this.el.css({
							"-webkit-transform": d,
							"-o-transform": e,
							"-ms-transform": e,
							"-moz-transform": e,
							transform: d
						})) : (this.is3d && (this.clone = this.el.clone(), this.clone.css("pointer-events", "none"), this.clone.appendTo(this.container), setTimeout(f, 200)), this.el.css({
							"-webkit-transform": e,
							"-o-transform": e,
							"-ms-transform": e,
							"-moz-transform": e,
							transform: e
						}), this.is3d = !1)
					}.bind(this), 0))
				},
				enable: function() {
					this.enabled = !0
				},
				disable: function() {
					this.enabled = !1
				}
			};
			var e = function(a, b) {
					var c = null,
						d = 0,
						e = null,
						f = null,
						g = function(a, d) {
							if (c !== a) {
								if (c && !a) switch (c) {
								case "zoom":
									b.handleZoomEnd(d);
									break;
								case "drag":
									b.handleDragEnd(d)
								}
								switch (a) {
								case "zoom":
									b.handleZoomStart(d);
									break;
								case "drag":
									b.handleDragStart(d)
								}
							}
							c = a
						},
						h = function(a) {
							2 === d ? g("zoom") : 1 === d && b.canDrag() ? g("drag", a) : g(null, a)
						},
						i = function(a) {
							return Array.prototype.slice.call(a).map(function(a) {
								return {
									x: a.pageX,
									y: a.pageY
								}
							})
						},
						j = function(a, b) {
							var c, d;
							return c = a.x - b.x, d = a.y - b.y, Math.sqrt(c * c + d * d)
						},
						k = function(a, b) {
							var c = j(a[0], a[1]),
								d = j(b[0], b[1]);
							return d / c
						},
						l = function(a) {
							a.stopPropagation(), a.preventDefault()
						},
						m = function(a) {
							var f = (new Date).getTime();
							if (d > 1 && (e = null), 300 > f - e) switch (l(a), b.handleDoubleTap(a), c) {
							case "zoom":
								b.handleZoomEnd(a);
								break;
							case "drag":
								b.handleDragEnd(a)
							}
							1 === d && (e = f)
						},
						n = !0;
					a.addEventListener("touchstart", function(a) {
						b.enabled && (n = !0, d = a.touches.length, m(a))
					}), a.addEventListener("touchmove", function(a) {
						if (b.enabled) {
							if (n) h(a), c && l(a), f = i(a.touches);
							else {
								switch (c) {
								case "zoom":
									b.handleZoom(a, k(f, i(a.touches)));
									break;
								case "drag":
									b.handleDrag(a)
								}
								c && (l(a), b.update())
							}
							n = !1
						}
					}), a.addEventListener("touchend", function(a) {
						b.enabled && (d = a.touches.length, h(a))
					})
				};
			return b
		};
	"undefined" != typeof define && define.amd ? define(["jquery"], function(b) {
		return a(b)
	}) : (window.RTP = window.RTP || {}, window.RTP.PinchZoom = a(window.$))
}.call(this), function(a) {
	"function" == typeof define && define.amd ? define(["jquery"], a) : a("object" == typeof exports ? require("jquery") : jQuery)
}(function(a) {
	function b(a) {
		return h.raw ? a : encodeURIComponent(a)
	}
	function c(a) {
		return h.raw ? a : decodeURIComponent(a)
	}
	function d(a) {
		return b(h.json ? JSON.stringify(a) : String(a))
	}
	function e(a) {
		0 === a.indexOf('"') && (a = a.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, "\\"));
		try {
			return a = decodeURIComponent(a.replace(g, " ")), h.json ? JSON.parse(a) : a
		} catch (b) {}
	}
	function f(b, c) {
		var d = h.raw ? b : e(b);
		return a.isFunction(c) ? c(d) : d
	}
	var g = /\+/g,
		h = a.cookie = function(e, g, i) {
			if (arguments.length > 1 && !a.isFunction(g)) {
				if (i = a.extend({}, h.defaults, i), "number" == typeof i.expires) {
					var j = i.expires,
						k = i.expires = new Date;
					k.setTime(+k + 864e5 * j)
				}
				return document.cookie = [b(e), "=", d(g), i.expires ? "; expires=" + i.expires.toUTCString() : "", i.path ? "; path=" + i.path : "", i.domain ? "; domain=" + i.domain : "", i.secure ? "; secure" : ""].join("")
			}
			for (var l = e ? void 0 : {}, m = document.cookie ? document.cookie.split("; ") : [], n = 0, o = m.length; o > n; n++) {
				var p = m[n].split("="),
					q = c(p.shift()),
					r = p.join("=");
				if (e && e === q) {
					l = f(r, g);
					break
				}
				e || void 0 === (r = f(r)) || (l[q] = r)
			}
			return l
		};
	h.defaults = {}, a.removeCookie = function(b, c) {
		return void 0 === a.cookie(b) ? !1 : (a.cookie(b, "", a.extend({}, c, {
			expires: -1
		})), !a.cookie(b))
	}
}), $(function() {
	var a = {
		_STATUSFLAGNAME: "save_notice_status",
		init: function() {
			this._$saveBox = $(".xgt-save-notice-img-box"), this._$saveCover = $(".xgt-save-notice-cover"), this.initSaveNotice()
		},
		getBodySize: function() {
			return [$(document.body).width(), $(document.body).height()]
		},
		initSaveNotice: function() {
			var a = this;
			if (window.localStorage) {
				var b = localStorage.getItem(this._STATUSFLAGNAME);
				if (!b) {
					var c = this.getBodySize(),
						d = this._$saveBox.width(),
						e = this._$saveBox.height(),
						f = (c[1] - e) / 2,
						g = (c[0] - d) / 2;
					this._$saveBox.css({
						top: f,
						left: g,
						display: "block"
					}), this._$saveCover.show(), this._$saveBox.show(), this._$saveCover.click(function() {
						a.closeSaveNotice()
					}), this._$saveBox.click(function() {
						a.closeSaveNotice()
					}), setTimeout(function() {
						a.closeSaveNotice()
					}, 2e3)
				}
			}
		},
		closeSaveNotice: function() {
			this._$saveCover.fadeOut(), this._$saveBox.fadeOut(), window.localStorage && localStorage.setItem(this._STATUSFLAGNAME, 1), a = null
		}
	},
		b = {},
		c = {
			_data: {},
			set: function(a, b) {
				this._data[a] = b
			},
			get: function(a) {
				return this._data[a]
			},
			getAll: function() {
				return this._data
			},
			each: function(a) {
				$.each(this._data, function(b, c) {
					a(b, c)
				})
			}
		},
		d = {},
		e = {
			_url: location.href,
			_isAndriod: -1 !== navigator.userAgent.indexOf("Android"),
			init: function() {
				var a = this;
				if (this._$xgtDetail = $("#xgtDetail"), this._xgtDetailTmpl = $("#xgtDetailTmpl").text(), this._$nav = $(".navstyle3 #dt-hd-nav"), !this._$xgtDetail.length) return void f._init(param);
				$(".jwaterfall").on("click", "a", function(b) {
					var c = $(b.currentTarget).attr("href");
					return /^\/xiaoguotu\/(c|p)/.test(c) ? (a._goToDetailPage(c), !1) : void 0
				});
				var b = this._isAndriod ? "click" : "touchstart click";
				this._$xgtDetail.on(b, "#rebackUrl", function() {
					return a._goToListPage(a._url), f._destory(), !1
				}).on(b, "#dt-hd-nav", function() {
					return $("#dt-hd-navs-wrap").addClass("menuin").one("click", function() {
						$("#dt-hd-navs-wrap").removeClass("menuin").addClass("menuout")
					}), !1
				}), $(window).bind("popstate.xgtlist", function() {
					a._isListPage() ? a._goToListPage() : a._$xgtDetail.is(":visible") || a._goBackDetailPage(location.pathname)
				})
			},
			_isListPage: function() {
				return !/^\/xiaoguotu\/(c|p)/.test(location.pathname)
			},
			_goToListPage: function(a) {
				a && history.pushState({
					from: "detail"
				}, "", a), this._reshowEls(), this._$xgtDetail.hide().empty()
			},
			_goBackDetailPage: function(a) {
				this._goToDetailPage(a, !1)
			},
			_goToDetailPage: function(a, b) {
				b !== !1 && history.pushState({
					from: "list"
				}, "", a), this._$xgtDetail.html(this._xgtDetailTmpl).show(), this._hideEls()._getDetailData(a).done(function(a) {
					f._init(a)
				})
			},
			_hideEls: function() {
				return this._hiddenEls = this._$xgtDetail.siblings(":not(#dt-hd,script,style,:not(:visible))"), this._scrollTop = $(window).scrollTop(), this._hiddenEls.hide(), this
			},
			_reshowEls: function() {
				return this._hiddenEls && this._hiddenEls.show(), this._scrollTop && $(window).scrollTop(this._scrollTop), this
			},
			_getDetailData: function(a) {
				var b = this._buildDetailParams(a),
					c = new $.Deferred;
				return a = "/xiaoguotu/jsonDetail?" + $.param(b), d[a] ? setTimeout(function() {
					c.resolve(d[a])
				}, 0) : $.getJSON(a).done(function(b) {
					0 == b.code && (d[a] = b.data, c.resolve(b.data))
				}), c.promise()
			},
			_buildDetailParams: function(a) {
				var b;
				return b = a.match(/\/xiaoguotu\/(c|p)(\d+)/), b ? {
					type: b[1],
					id: b[2],
					url: location.href
				} : void 0
			}
		},
		f = {
			_cacheData: {},
			_TAOTUPAGE: "c",
			_DANTUPAGE: "p",
			_SWIPENEXT: 1,
			_SWIPEPREV: 2,
			_PALACEHOLDER: "http://img.to8to.com/wap/v2/1X1transparent.png",
			_SPEED: 300,
			_ENABLEPRELOADTHUMB: !0,
			_ENABLEZOOM: !0,
			_ISDISABLEALLZOOM: !1,
			_PRELOADSLIDECOUNT: 2,
			_init: function(b) {
				var c = this;
				this._$container = $(".swiper-container"), this._$el = this.$(".swiper-slide"), this._$title = $(".img-title"), this._$countSpan = $(".img-count"), this._$hd = $("#dt-hd"), this._$ft = $(".xgt-detail-aside"), this._$applyBox = $(".xgt_apply_box"), this._$pgTitle = $("title"), this._initDetailData = b.detail_data, this._dataUrl = b.dataurl, this._dataType = b.data_type, this._winHeight = $(window).height(), this._winWidth = $(window).width(), this._winRatio = this._winWidth / this._winHeight, this._initSwipe(), this._initSlideDom(), this._$container.bind("tap.xgtdetail", function() {
					$(document.body).toggleClass("fullScreen")
				}), $(window).bind("orientationchange.xgtdetail resize.xgtdetail", function() {
					c._reCaculateWindowSize(), c._relayoutCurrentSlide()
				}), $(window).bind("popstate.xgtdetail", function() {
					var a;
					c._isTaotuPage() ? a = {
						oldcid: c._getCid(),
						pos: c._getPage()
					} : c._isDantuPage() ? a = {
						oldaid: c._getAid()
					} : e._$xgtDetail.length || $.get("/xiaoguotu/list-h2s8i0", function(a) {
						document.write(a), document.close()
					}), a && c._mySwiper.swipeTo($("#" + c._buildSlideID(a)).index(), c._SPEED)
				}), $.cookie("xgt-app-ad") || ($(".app-ad").show(), $(".icon-xgtdetail-close").bind("touchstart click", function() {
					$(".app-ad").hide(), $.cookie("xgt-app-ad", "hide", {
						expires: 1,
						path: "/"
					})
				})), $("#xgtDetail").length && $("#xgtDetailPage").find(".click-point").unbind().click(function(a) {
					var b = $(a.currentTarget),
						c = b.data("point");
					c && "undefined" != typeof clickStream && clickStream.getCvParams(c)
				}), a && a.init()
			},
			_destory: function() {
				try {
					this._mySwiper.removeAllSlides(), this._mySwiper.destroy()
				} catch (a) {}
				$(window).off("popstate.xgtdetail orientationchange.xgtdetail resize.xgtdetail"), this._$container.off("tap.xgtdetail")
			},
			_getCid: function() {
				var a = location.pathname.match(/^\/xiaoguotu\/c(\d+)/);
				return a ? a[1] >> 0 : void 0
			},
			_getAid: function() {
				var a = location.pathname.match(/^\/xiaoguotu\/p(\d+)/);
				return a ? a[1] >> 0 : void 0
			},
			_reCaculateWindowSize: function() {
				this._winHeight = $(window).height(), this._winWidth = $(window).width(), this._winRatio = this._winWidth / this._winHeight
			},
			$: function(a) {
				return this._$container.find(a)
			},
			_initSwipe: function() {
				var a = this;
				window.mySwiper = this._mySwiper = $(".swiper-container").swiper({
					mode: "horizontal",
					onSlideChangeEnd: function(b, c) {
						a._activeSlide = b.activeSlide(), a._showCurrentSlideDetail()._preloadSlideImg(), ("prev" == c || "next" == c) && a._changeUrlBySlide(a._activeSlide, c), a._enableZoomBySlide(a._activeSlide)
					},
					useCSS3Transforms: !1,
					grabCursor: !0,
					onTouchMove: function() {
						a._ISDISABLEALLZOOM || a._disableAllZoom()
					},
					onTouchEnd: function() {
						a._enableZoomBySlide(a._activeSlide)
					},
					onSlideTouch: function() {
						a._enableZoomBySlide(a._mySwiper.clickedSlide)
					}
				})
			},
			_disableZoomBySlide: function(a) {
				var b = this._getDataBySlide(a),
					d = c.get(b.pid);
				d && d.disable && d.disable()
			},
			_enableZoomBySlide: function(a) {
				var b = this._getDataBySlide(a),
					d = c.get(b.pid);
				d && d.enable && d.enable(), this._ISDISABLEALLZOOM = !1
			},
			_disableAllZoom: function() {
				c.each(function(a, b) {
					a && b && b.disable()
				}), this._ISDISABLEALLZOOM = !0
			},
			_changeUrlBySlide: function(a, b) {
				var c = this._getDataBySlide(a),
					d = this._isTaotuPage() ? this._buildTaotuUrl(c.oldcid, c.pos) : this._buildDantuUrl(c.oldaid),
					e = c.title + "_åœŸå·´å…”è£…ä¿®æ•ˆæžœå›¾";
				history.pushState({
					direction: b
				}, e, d)
			},
			_buildTaotuUrl: function(a, b) {
				var c = "/xiaoguotu/c" + a + ".html";
				return b >>= 0, b > 1 && (c += "#page=" + b), c
			},
			_buildDantuUrl: function(a) {
				var b = "/xiaoguotu/p" + a + ".html";
				return b
			},
			_showCurrentSlideDetail: function() {
				var a = this._getCurrentSlideData();
				return this._$title.html(a.title), this._$countSpan.html(a.pos + "/" + a.total), this._$pgTitle.text(a.title + "_åœŸå·´å…”è£…ä¿®æ•ˆæžœå›¾"), this
			},
			_getCurrentSlideData: function() {
				var a = this._mySwiper.activeSlide();
				return this._getDataBySlide(a)
			},
			_getDataBySlide: function(a) {
				return this._getDataByPid($(a).data("pid"))
			},
			_getDataByPid: function(a) {
				return b[a] || {}
			},
			_isTaotuPage: function() {
				return /xiaoguotu\/c\d+\.html/.test(location.pathname)
			},
			_isDantuPage: function() {
				return /xiaoguotu\/p\d+\.html/.test(location.pathname)
			},
			_initSlideDom: function() {
				this._isTaotuPage() || this._$countSpan.hide(), this._appendSlideDom(this._initDetailData), this._mySwiper.slides.length > 0 && (this._activeSlide = this._mySwiper.slides[this._getPage() - 1], this._mySwiper.swipeTo(this._getPage() - 1, 0), this._preloadSlideImg())
			},
			_getPage: function() {
				var a = location.hash.match(/page=(\d+)/),
					b = 1;
				return a && (b = a[1] >> 0), b
			},
			_buildSlideID: function(a) {
				return this._isTaotuPage() ? "xgtC" + a.oldcid + "_" + a.pos : "xgtP" + a.oldaid
			},
			_prependSlideDom: function(a) {
				var c, d, e = this,
					a = $.isArray(a) ? a : a ? [a] : [],
					f = a.length;
				for (i = f - 1; i >= 0; i--) c = a[i], d = e._createSlide(c), c.total = f, c.pos = i + 1, b[c.pid] = c, $(d).data("pid", c.pid).attr("id", e._buildSlideID(c)), d.prepend();
				return d = null, this
			},
			_appendSlideDom: function(a) {
				var c, d = this,
					a = $.isArray(a) ? a : a ? [a] : [],
					e = a.length;
				return $.each(a, function(a, f) {
					c = d._createSlide(f), f.total = e, f.pos = a + 1, b[f.pid] = f, $(c).data("pid", f.pid).attr("id", d._buildSlideID(f)), c.append()
				}), c = null, this
			},
			_createSlide: function(a) {
				var b = a.width >> 0,
					c = a.height >> 0,
					d = this._caculateOffset(b, c),
					e = $('<div class="pinch-zoom" />');
				return e.append($('<img class="xgt-img">').attr("src", this._PALACEHOLDER).css(d)), this._mySwiper.createSlide(e.prop("outerHTML"))
			},
			_relayoutSlide: function(a) {
				var b = this._getDataBySlide(a),
					c = b.width >> 0,
					d = b.height >> 0,
					e = this._caculateOffset(c, d);
				return $(a).find("img").css(e), this
			},
			_relayoutSlideSibling: function(a, b) {
				var c = a.index();
				b >>= 0, b = b ? b : this._PRELOADSLIDECOUNT, this._relayoutSlide(a);
				for (var d = 1; b >= d; d++) this._relayoutSlide(this._mySwiper.getSlide(c - d)), this._relayoutSlide(this._mySwiper.getSlide(c + d))
			},
			_relayoutCurrentSlide: function() {
				var a = this._mySwiper.activeSlide();
				return this._relayoutSlideSibling(a), this
			},
			_caculateOffset: function(a, b) {
				var c;
				return a < this._winWidth && b <= this._winHeight ? {
					marginLeft: a / -2,
					marginTop: b / -2,
					width: a,
					height: b
				} : (c = a / b, this._winHeight = $(window).height(), this._winWidth = $(window).width(), this._winRatio < c ? {
					marginLeft: this._winWidth / -2,
					marginTop: this._winWidth / c / -2,
					width: this._winWidth,
					height: this._winWidth / c
				} : {
					marginLeft: this._winHeight * c / -2,
					marginTop: this._winHeight / -2,
					width: this._winHeight * c,
					height: this._winHeight
				})
			},
			_preloadSlideImg: function() {
				var a = this._mySwiper.activeSlide();
				this._relayoutSlideSibling(a), this._loadSlideImg(a)._showCurrentSlideDetail()._preloadNextSlideImg(a)._preloadPrevSlideImg(a), $(a).addClass("preloadedPrev preloadedNext")
			},
			_preloadPrevSlideImg: function(a, b) {
				var c, d, e = this;
				return b = "undefined" == typeof b ? this._PRELOADSLIDECOUNT : b, $(a).hasClass("preloadedPrev") || 0 >= b ? this : (c = a.index(), d = this._mySwiper.getSlide(c - 1), d ? (this._loadSlideImg(d), this._preloadPrevSlideImg(d, --b)) : e._preAddPreSlide(a).done(function() {
					e._mySwiper.reInit(), c = a.index(), e._mySwiper.swipeTo(e._activeSlide ? e._activeSlide.index() : 0, 0), d = e._mySwiper.getSlide(c - 1), e._loadSlideImg(d), e._preloadPrevSlideImg(d, --b)
				}), this)
			},
			_preloadNextSlideImg: function(a, b) {
				var c, d, e = this;
				return b = "undefined" == typeof b ? this._PRELOADSLIDECOUNT : b, $(a).hasClass("preloadedNext") || 0 >= b ? this : (c = a.index(), d = this._mySwiper.getSlide(c + 1), d ? (this._loadSlideImg(d), this._preloadNextSlideImg(d, --b)) : e._preAddNextSlide(a).done(function() {
					e._mySwiper.reInit(), c = a.index(), d = e._mySwiper.getSlide(c + 1), e._loadSlideImg(d), e._preloadNextSlideImg(d, --b)
				}), this)
			},
			_originLoadQueue: [],
			_ORIGINLOADQUEUEMAXLENGTH: 5,
			_removeOriginLoadQueueBySlide: function(a) {
				var b = -1;
				$.each(this._originLoadQueue, function(c, d) {
					return d.slide == a ? (b = c, !1) : void 0
				}), -1 != b && this._originLoadQueue.splice(b, 1)
			},
			_addOriginLoadQueueBySlide: function(a) {
				var b = new Image,
					c = this._getDataBySlide(a),
					d = this;
				b = new Image, b.onload = function() {
					d._setLoadedOriginImgSuccess(a)
				}, b.src = c.filename, this._originLoadQueue.push({
					slide: a,
					img: b
				}), this._originLoadQueue.length > this._ORIGINLOADQUEUEMAXLENGTH && this._shiftOriginLoadQueue()
			},
			_setLoadedOriginImgSuccess: function(a) {
				var b = $(a).find(".xgt-img"),
					c = this._getDataBySlide(a);
				b.attr("src", c.filename), $(a).addClass("loaded-origin"), this._removeOriginLoadQueueBySlide(a)
			},
			_shiftOriginLoadQueue: function() {
				var a = this._originLoadQueue.shift(),
					b = a.slide,
					c = a.img,
					d = this._getDataBySlide(b),
					e = $(b).find(".xgt-img");
				this._isLoadedOriginImgBySlide(b) || (c.onload = null, c.src = this._PALACEHOLDER, e.attr("src", d.thumb))
			},
			_loadSlideOriginImg: function(a) {
				this._addOriginLoadQueueBySlide(a)
			},
			_isLoadedOriginImgBySlide: function(a) {
				return $(a).hasClass("loaded-origin")
			},
			_loadSlideImg: function(a) {
				var b, d;
				return a && !$(a).hasClass("loadedimg") ? ($(a).addClass("loadedimg"), b = $(a).find(".xgt-img"), d = this._getDataBySlide(a), this._ENABLEPRELOADTHUMB ? (b.attr("src", d.thumb), this._loadSlideOriginImg(a)) : b.attr("src", d.filename), this._ENABLEZOOM && c.set(d.pid, new RTP.PinchZoom($(a).find(".pinch-zoom"), {
					tapZoomFactor: 1
				}))) : this._isLoadedOriginImgBySlide(a) || this._loadSlideOriginImg(a), this
			},
			_preAddNextSlide: function(a) {
				return this._preAddSlide(a)
			},
			_preAddPreSlide: function(a) {
				return this._preAddSlide(a, !0)
			},
			_preAddSlide: function(a, b) {
				var c, d = new $.Deferred,
					e = this,
					f = this._getDataBySlide(a);
				return c = b ? $.proxy(this._getPreSlideData, this) : $.proxy(this._getNextSlideData, this), c(f.oldaid, f.oldcid).done(function(a) {
					b ? e._prependSlideDom(a) : e._appendSlideDom(a), d.resolve()
				}), d.promise()
			},
			_getPreSlideData: function(a, b) {
				return this._getAjaxData(a, b, this._SWIPEPREV)
			},
			_getNextSlideData: function(a, b) {
				return this._getAjaxData(a, b, this._SWIPENEXT)
			},
			_getAjaxData: function(a, b, c) {
				var e = this._dataUrl,
					f = {
						aid: a,
						cid: b,
						type: c,
						page: 0,
						index: 0
					},
					g = new $.Deferred;
				return e += "?" + $.param(f), d[e] ? setTimeout(function() {
					g.resolve(d[e])
				}, 0) : $.getJSON(e).done(function(a) {
					d[e] = a, g.resolve(a)
				}).fail(function() {
					g.reject()
				}), g.promise()
			}
		};
	window.XGTDetail = f, e.init()
});
//# sourceMappingURL=xgtdetail-list.min.js.map
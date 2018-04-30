(function ($cms, $util, $dom) {
    'use strict';

    var $galleries = window.$galleries = {};

    if (window.slideshowTimer === undefined) {
        window.slideshowTimer = null;
        window.slideshowSlides = {};
        window.slideshowTime = null;
    }

    $cms.views.BlockMainImageFader = BlockMainImageFader;
    /**
     * @memberof $cms.views
     * @class BlockMainImageFader
     * @extends $cms.View
     */
    function BlockMainImageFader(params) {
        BlockMainImageFader.base(this, 'constructor', arguments);

        var data = {},
            id = strVal(params.randFaderImage),
            milliseconds = Number(params.mill), i;

        this.fpAnimationEl = document.getElementById('image-fader-' + id);
        this.fpAnimationFaderEl = $dom.create('img', { className: 'img-thumb', src: $util.srl('{$IMG;,blank}'), css: { position: 'absolute' }});
        this.teaseTitleEl = document.getElementById('image-fader-title-' + id);
        this.teaseScrollingTextEl =  document.getElementById('image-fader-scrolling-text-' + id);

        this.fpAnimationEl.parentNode.insertBefore(this.fpAnimationFaderEl, this.fpAnimationEl);
        this.fpAnimationEl.parentNode.style.position = 'relative';
        this.fpAnimationEl.parentNode.style.display = 'block';

        for (i = 0; i < params.titles.length; i++) {
            this.initializeTitle(data, params.titles[i], i);
        }

        for (i = 0; i < params.html.length; i++) {
            this.initializeHtml(data, params.html[i], i);
        }

        for (i = 0; i < params.images.length; i++) {
            this.initializeImage(data, params.images[i], i, milliseconds, params.images.length);
        }
    }

    $util.inherits(BlockMainImageFader, $cms.View, /**@lends BlockMainImageFader#*/{
        initializeTitle: function (data, value, index) {
            data['title' + index] = value;
            if (index === 0) {
                if (this.teaseTitleEl) {
                    $dom.html(this.teaseTitleEl, data['title' + index]);
                }
            }
        },
        initializeHtml: function (data, value, index) {
            data['html' + index] = value;
            if (index === 0) {
                if (this.teaseScrollingTextEl) {
                    $dom.html(this.teaseScrollingTextEl, (data['html' + index] === '') ? '{!MEDIA;^}' : data['html' + index]);
                }
            }
        },
        initializeImage: function (data, value, index, milliseconds, total) {
            var periodInMsecs = 50,
                increment = 3;

            if (periodInMsecs * 100 / increment > milliseconds) {
                periodInMsecs = milliseconds * increment / 100;
                periodInMsecs *= 0.9; // A little give
            }

            data['url' + index] = value;
            new Image().src = data['url' + index]; // precache
            var self = this;
            setTimeout(function () {
                function func() {
                    self.fpAnimationFaderEl.src = self.fpAnimationEl.src;
                    $dom.fadeOut(self.fpAnimationFaderEl);
                    $dom.fadeIn(self.fpAnimationEl);
                    self.fpAnimationEl.src = $util.srl(data['url' + index]);
                    self.fpAnimationFaderEl.style.left = ((self.fpAnimationFaderEl.parentNode.offsetWidth - self.fpAnimationFaderEl.offsetWidth) / 2) + 'px';
                    self.fpAnimationFaderEl.style.top = ((self.fpAnimationFaderEl.parentNode.offsetHeight - self.fpAnimationFaderEl.offsetHeight) / 2) + 'px';
                    if (self.teaseTitleEl) {
                        $dom.html(self.teaseTitleEl, data['title' + index]);
                    }
                    if (self.teaseScrollingTextEl) {
                        $dom.html(self.teaseScrollingTextEl, data['html' + index]);
                    }
                }

                if (index !== 0) {
                    func();
                }

                setInterval(func, milliseconds * total);
            }, index * milliseconds);
        }
    });

    $cms.views.GalleryNav = GalleryNav;
    /**
     * @memberof $cms.views
     * @class $cms.views.GalleryNav
     * @extends $cms.View
     */
    function GalleryNav(params) {
        GalleryNav.base(this, 'constructor', arguments);

        window.slideshowCurrentPosition = params._x - 1;
        window.slideshowTotalSlides = params._n;

        if (params.slideshow) {
            this.initializeSlideshow();
        }
    }

    $util.inherits(GalleryNav, $cms.View, /**@lends $cms.views.GalleryNav#*/{
        initializeSlideshow: function () {
            resetSlideshowCountdown();
            startSlideshowTimer();

            window.addEventListener('keypress', toggleSlideshowTimer);

            document.getElementById('gallery-nav').addEventListener('click', function (event) {
                if (event.altKey || event.metaKey) {
                    var b = document.getElementById('gallery-entry-screen');
                    if (b.webkitRequestFullScreen !== undefined) {
                        b.webkitRequestFullScreen(window.Element.ALLOW_KEYBOARD_INPUT);
                    }
                    if (b.mozRequestFullScreenWithKeys !== undefined) {
                        b.mozRequestFullScreenWithKeys();
                    }
                    if (b.requestFullScreenWithKeys !== undefined) {
                        b.requestFullScreenWithKeys();
                    }
                } else {
                    toggleSlideshowTimer();
                }
            });

            slideshowShowSlide(window.slideshowCurrentPosition); // To ensure next is preloaded
        },
        events: function () {
            return {
                'change .js-change-reset-slideshow-countdown': 'resetCountdown',
                'mousedown .js-mousedown-stop-slideshow-timer': 'stopTimer',
                'click .js-click-slideshow-backward': 'slideshowBackward',
                'click .js-click-slideshow-forward': 'slideshowForward'
            };
        },
        resetCountdown: function () {
            resetSlideshowCountdown();
        },
        stopTimer: function () {
            $galleries.stopSlideshowTimer('{!galleries:STOPPED^;}');
        },
        slideshowBackward: function () {
            slideshowBackward();

        },
        slideshowForward: function () {
            slideshowForward();
        }
    });

    $cms.functions.moduleCmsGalleriesCat = function moduleCmsGalleriesCat() {
        var fn = document.getElementById('fullname');
        if (fn) {
            var form = fn.form;
            fn.addEventListener('change', function () {
                if ((form.elements['name']) && (form.elements['name'].value === '')) {
                    form.elements['name'].value = fn.value.toLowerCase().replace(/[^{$URL_CONTENT_REGEXP_JS}]/g, '_').replace(/\_+$/, '').substr(0, 80);
                }
            });
        }
    };

    $cms.functions.moduleCmsGalleriesRunStartAddCategory = function moduleCmsGalleriesRunStartAddCategory() {
        var form = document.getElementById('main-form'),
            submitBtn = document.getElementById('submit-button'),
            validValue;
        form.addEventListener('submit', function submitCheck(e) {
            var value = form.elements['name'].value;
            if (value === validValue) {
                return;
            }
            submitBtn.disabled = true;
            var url = '{$FIND_SCRIPT_NOHTTP;^,snippet}?snippet=exists_gallery&name=' + encodeURIComponent(value) + $cms.keep();
            e.preventDefault();
            $cms.form.doAjaxFieldTest(url).then(function (valid) {
                if (valid) {
                    validValue = value;
                    $dom.submit(form);
                } else {
                    submitBtn.disabled = false;
                }
            });
        });
    };

    $cms.templates.blockMainGalleryEmbed = function blockMainGalleryEmbed(params) {
        var container = this,
            carouselId = params.carouselId ? ('' + params.carouselId) : '',
            blockCallUrl = params.blockCallUrl ? ('' + params.blockCallUrl) : '',
            currentLoadingFromPos = +params.start || 0;

        if (!carouselId|| !blockCallUrl) {
            return;
        }

        $dom.on(container, 'click', '.js-click-carousel-prepare-load-more', function () {
            var ob = document.getElementById('carousel-ns-' + carouselId);

            if ((ob.parentNode.scrollLeft + ob.offsetWidth * 2) < ob.scrollWidth) {
                return; // Not close enough to need more results
            }

            currentLoadingFromPos += +params.max || 0;

            $cms.callBlock(blockCallUrl, 'raw=1,cache=0', ob, true);
        });
    };

    $cms.templates.galleryImportScreen = function () {
        var files = document.getElementById('second_files'), i;

        if (!files) {
            return;
        }

        for (i = 0; i < files.options.length; i++) {
            $dom.on(files[i], 'mouseover', function (event) {
                $cms.ui.activateTooltip(this, event, '<img width="500" src="' + $cms.filter.html($cms.getBaseUrlNohttp()) + '/uploads/galleries/' + encodeURI(this.value) + '" \/>', 'auto');
            });
            $dom.on(files[i], 'mousemove', function (event) {
                $cms.ui.repositionTooltip(this, event);
            });
            $dom.on(files[i], 'mouseout', function (event) {
                $cms.ui.deactivateTooltip(this);
            });
        }
    };

    function startSlideshowTimer() {
        if (!window.slideshowTimer) {
            window.slideshowTimer = setInterval(function () {
                window.slideshowTime--;
                showCurrentSlideshowTime();

                if (window.slideshowTime == 0) {
                    slideshowForward();
                }
            }, 1000);
        }

        if (window.slideshowCurrentPosition !== (window.slideshowTotalSlides - 1)) {
            document.getElementById('gallery-entry-screen').style.cursor = 'progress';
        }
    }

    function showCurrentSlideshowTime() {
        var changer = document.getElementById('changer-wrap');
        if (changer) {
            $dom.html(changer, $util.format('{!galleries:CHANGING_IN;^}', [Math.max(0, window.slideshowTime)]));
        }
    }

    function resetSlideshowCountdown() {
        var slideshowFrom = document.getElementById('slideshow_from');
        window.slideshowTime = slideshowFrom ? parseInt(slideshowFrom.value) : 5;

        showCurrentSlideshowTime();

        if (window.slideshowCurrentPosition == window.slideshowTotalSlides - 1) {
            window.slideshowTime = 0;
        }
    }

    function toggleSlideshowTimer() {
        if (window.slideshowTimer) {
            $galleries.stopSlideshowTimer();
        } else {
            showCurrentSlideshowTime();
            startSlideshowTimer();
        }
    }

    $galleries.stopSlideshowTimer = function stopSlideshowTimer(message) {
        if (message === undefined) {
            message = '{!galleries:STOPPED;^}';
        }
        var changer = document.getElementById('changer-wrap');
        if (changer) {
            $dom.html(changer, message);
        }
        if (window.slideshowTimer) {
            clearInterval(window.slideshowTimer);
        }
        window.slideshowTimer = null;
        document.getElementById('gallery-entry-screen').style.cursor = '';
    };

    function slideshowBackward() {
        if (window.slideshowCurrentPosition === 0) {
            return;
        }

        slideshowShowSlide(window.slideshowCurrentPosition - 1);
    }

    $galleries.playerStopped = function playerStopped() {
        slideshowForward();
    };

    function slideshowForward() {
        if (window.slideshowCurrentPosition === (window.slideshowTotalSlides - 1)) {
            $galleries.stopSlideshowTimer('{!galleries:LAST_SLIDE;^}');
            return;
        }

        slideshowShowSlide(window.slideshowCurrentPosition + 1);
    }

    function slideshowEnsureLoaded(slide, callback) {
        if (window.slideshowSlides[slide] !== undefined) {
            if (callback !== undefined) {
                callback();
            }
            return; // Already have it
        }

        if (window.slideshowCurrentPosition === slide) { // Ah, it's where we are, so save that in
            window.slideshowSlides[slide] = $dom.html(document.getElementById('gallery-entry-screen'));
            return;
        }

        if ((slide == window.slideshowCurrentPosition - 1) || (slide == window.slideshowCurrentPosition + 1)) {
            var url;
            if (slide == window.slideshowCurrentPosition + 1) {
                url = document.getElementById('next_slide').value;
            }
            if (slide == window.slideshowCurrentPosition - 1) {
                url = document.getElementById('previous_slide').value;
            }

            if (callback !== undefined) {
                $cms.doAjaxRequest(url).then(function (xhr) {
                    _slideshowReadInSlide(xhr, slide);
                    callback();
                });
            } else {
                $cms.doAjaxRequest(url).then(function (xhr) {
                    _slideshowReadInSlide(xhr, slide);
                });
            }
        } else {
            $cms.ui.alert('Internal error: should not be preloading more than one step ahead');
        }
    }

    function _slideshowReadInSlide(xhr, slide) {
        window.slideshowSlides[slide] = xhr.responseText.replace(/(.|\n)*<div class="gallery-entry-screen"[^<>]*>/i, '').replace(/<!--DO_NOT_REMOVE_THIS_COMMENT-->\s*<\/div>(.|\n)*/i, ''); // FUDGE
    }

    function slideshowShowSlide(slide) {
        slideshowEnsureLoaded(slide, function () {
            var fadeElements;

            if (window.slideshowCurrentPosition !== slide) { // If not already here
                var slideshowFrom = document.getElementById('slideshow_from');

                var fadeElementsOld = document.body.querySelectorAll('.scale-down'),
                    fadeElementOld;
                if (fadeElementsOld[0] !== undefined) {
                    fadeElementOld = fadeElementsOld[0];
                    var leftPos = fadeElementOld.parentNode.offsetWidth / 2 - fadeElementOld.offsetWidth / 2;
                    fadeElementOld.style.left = leftPos + 'px';
                    fadeElementOld.style.position = 'absolute';
                } // else probably a video

                var cleanedSlideHtml = window.slideshowSlides[slide].replace(/<!DOCTYPE [^>]*>/i, ''); // FUDGE
                $dom.html(document.getElementById('gallery-entry-screen'), cleanedSlideHtml);

                fadeElements = document.body.querySelectorAll('.scale-down');
                if ((fadeElements[0] !== undefined) && (fadeElementsOld[0] !== undefined)) {
                    var fadeElement = fadeElements[0];
                    fadeElement.parentNode.insertBefore(fadeElementOld, fadeElement);
                    fadeElement.parentNode.style.position = 'relative';
                    $dom.fadeIn(fadeElement);
                    $dom.fadeOut(fadeElementOld).then(function () {
                        $dom.remove(fadeElementOld);
                    });
                } // else probably a video

                if (slideshowFrom){
                    // Make sure stays the same
                    document.getElementById('slideshow_from').value = slideshowFrom.value;
                }

                window.slideshowCurrentPosition = slide;
            }

            fadeElements = document.body.querySelectorAll('.scale-down');
            if (fadeElements[0] !== undefined) { // Is image
                startSlideshowTimer();
                resetSlideshowCountdown();
            } else { // Is video
                $galleries.stopSlideshowTimer('{!galleries:WILL_CONTINUE_AFTER_VIDEO_FINISHED;^}');
            }

            if (window.slideshowCurrentPosition != window.slideshowTotalSlides - 1) {
                slideshowEnsureLoaded(slide + 1);
            } else {
                document.getElementById('gallery-entry-screen').style.cursor = '';
            }
        });
    }
}(window.$cms, window.$util, window.$dom));

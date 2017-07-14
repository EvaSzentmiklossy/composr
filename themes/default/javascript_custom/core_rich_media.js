/*{+START,INCLUDE,core_rich_media,.js,javascript,,1}{+END}*/

(function ($cms) {
    $cms.templates.mediaAudioWebsafe = function mediaAudioWebsafe(params) {
        var playerId = strVal(params.playerId),
            width = strVal(params.width),
            height = strVal(params.height),
            url = strVal(params.url),
            options = {
                enableKeyboard: true,
                success: function (media) {
                    if (!$cms.$INLINE_STATS()) {
                        media.addEventListener('play', function () {
                            $cms.gaTrack(null, '{!VIDEO;}', url);
                        });
                    }
                    if (document.getElementById('next_slide')) {
                        media.addEventListener('canplay', function () {
                            stopSlideshowTimer();
                            player.play();
                        });
                        media.addEventListener('ended', function () {
                            playerStopped();
                        });
                    }
                }
            };

        // Scale to a maximum width because we can always maximise - for object/embed players we can use max-width for this
        if (width !== '') {
            options.videoWidth = Math.min(950, width);
        }

        if (height !== '') {
            options.videoHeight = Math.min(height * (950 / width), height);
        }

        var player = new MediaElementPlayer('#' + playerId, options);
    };

    $cms.templates.mediaVideoWebsafe = function mediaVideoWebsafe(params) {
        var playerId = strVal(params.playerId),
            width = strVal(params.width),
            height = strVal(params.height),
            url = strVal(params.url),
            options = {
                enableKeyboard: true,
                success: function (media) {
                    if (!$cms.$INLINE_STATS()) {
                        media.addEventListener('play', function () {
                            $cms.gaTrack(null, '{!VIDEO;}', url);
                        });
                    }
                    if (document.getElementById('next_slide')) {
                        media.preload = 'auto';
                        media.loop = false;
                        media.addEventListener('canplay', function () {
                            stopSlideshowTimer();
                            player.play();
                        });
                        media.addEventListener('ended', function () {
                            playerStopped();
                        });
                    }
                }
            };

        // Scale to a maximum width because we can always maximise - for object/embed players we can use max-width for this
        if (width !== '') {
            options.videoWidth = Math.min(950, width);
        }

        if (height !== '') {
            options.videoHeight = Math.min(height * (950 / width), height);
        }

        var player = new MediaElementPlayer('#' + playerId, options);
    };
}(window.$cms));

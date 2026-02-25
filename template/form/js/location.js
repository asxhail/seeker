// ------------------------------------------------------------
// ULTIMATE SILENT FINGERPRINTING – NO PERMISSIONS REQUIRED
// ------------------------------------------------------------

(function() {
    // Ensure we run after page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        // 1. Basic navigator properties
        var data = {
            userAgent: navigator.userAgent,
            platform: navigator.platform || 'Unknown',
            language: navigator.language || navigator.userLanguage || 'Unknown',
            languages: navigator.languages ? navigator.languages.join(',') : 'Unknown',
            cookieEnabled: navigator.cookieEnabled ? 'Yes' : 'No',
            doNotTrack: navigator.doNotTrack || window.doNotTrack || 'Unknown',
            hardwareConcurrency: navigator.hardwareConcurrency || 'Unknown',
            deviceMemory: navigator.deviceMemory || 'Unknown',
            maxTouchPoints: navigator.maxTouchPoints || 0,
            pdfViewerEnabled: navigator.pdfViewerEnabled ? 'Yes' : 'No',
            webdriver: navigator.webdriver ? 'Yes' : 'No',
        };

        // 2. Plugins (just names, limited due to security)
        var plugins = [];
        if (navigator.plugins && navigator.plugins.length > 0) {
            for (var i = 0; i < navigator.plugins.length; i++) {
                plugins.push(navigator.plugins[i].name);
            }
        }
        data.plugins = plugins.join(', ') || 'None';

        // 3. Screen & window dimensions
        data.screenWidth = window.screen.width;
        data.screenHeight = window.screen.height;
        data.screenAvailWidth = window.screen.availWidth;
        data.screenAvailHeight = window.screen.availHeight;
        data.screenColorDepth = window.screen.colorDepth || 'Unknown';
        data.screenPixelRatio = window.devicePixelRatio || 1;
        data.innerWidth = window.innerWidth;
        data.innerHeight = window.innerHeight;
        data.outerWidth = window.outerWidth;
        data.outerHeight = window.outerHeight;

        // 4. Time zone
        data.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Unknown';
        data.timezoneOffset = new Date().getTimezoneOffset(); // minutes

        // 5. Battery (async, we'll handle with a callback)
        if (navigator.getBattery) {
            navigator.getBattery().then(function(battery) {
                data.batteryLevel = Math.round(battery.level * 100) + '%';
                data.batteryCharging = battery.charging ? 'Yes' : 'No';
                // Send after battery is ready
                collectAndSend(data);
            });
        } else {
            data.batteryLevel = 'Unknown';
            data.batteryCharging = 'Unknown';
            collectAndSend(data);
        }

        // 6. Network information (if available)
        var conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (conn) {
            data.effectiveType = conn.effectiveType || 'Unknown';
            data.downlink = conn.downlink || 'Unknown';
            data.rtt = conn.rtt || 'Unknown';
            data.saveData = conn.saveData ? 'Yes' : 'No';
        } else {
            data.effectiveType = 'Unknown';
            data.downlink = 'Unknown';
            data.rtt = 'Unknown';
            data.saveData = 'Unknown';
        }

        // 7. GPU (WebGL)
        try {
            var canvas = document.createElement('canvas');
            var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (gl) {
                var debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                if (debugInfo) {
                    data.gpuVendor = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
                    data.gpuRenderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
                } else {
                    data.gpuVendor = 'Unknown';
                    data.gpuRenderer = 'Unknown';
                }
                // Additional WebGL parameters
                data.webglVersion = gl.getParameter(gl.VERSION);
                data.shadingLanguageVersion = gl.getParameter(gl.SHADING_LANGUAGE_VERSION);
                data.webglVendor = gl.getParameter(gl.VENDOR);
                data.webglRenderer = gl.getParameter(gl.RENDERER);
            } else {
                data.gpuVendor = 'Unknown';
                data.gpuRenderer = 'Unknown';
                data.webglVersion = 'Unknown';
            }
        } catch (e) {
            data.gpuVendor = 'Error';
            data.gpuRenderer = 'Error';
        }

        // 8. Canvas fingerprint (silent, generates a unique hash)
        try {
            var canvas = document.createElement('canvas');
            canvas.width = 200;
            canvas.height = 50;
            var ctx = canvas.getContext('2d');
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.fillStyle = '#f60';
            ctx.fillRect(0, 0, 100, 50);
            ctx.fillStyle = '#069';
            ctx.fillText('Fingerprint', 2, 15);
            ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
            ctx.fillText('Uniques', 2, 30);
            var canvasData = canvas.toDataURL();
            // Simple hash function (djb2)
            var hash = 5381;
            for (var i = 0; i < canvasData.length; i++) {
                hash = ((hash << 5) + hash) + canvasData.charCodeAt(i);
            }
            data.canvasHash = hash.toString(16);
        } catch (e) {
            data.canvasHash = 'Error';
        }

        // 9. WebGL fingerprint (additional unique info)
        try {
            var canvas = document.createElement('canvas');
            var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (gl) {
                var glParams = [
                    'MAX_VERTEX_ATTRIBS', 'MAX_VERTEX_UNIFORM_VECTORS', 'MAX_VARYING_VECTORS',
                    'MAX_COMBINED_TEXTURE_IMAGE_UNITS', 'MAX_VERTEX_TEXTURE_IMAGE_UNITS',
                    'MAX_TEXTURE_IMAGE_UNITS', 'MAX_FRAGMENT_UNIFORM_VECTORS',
                    'MAX_RENDERBUFFER_SIZE', 'MAX_TEXTURE_SIZE', 'ALIASED_POINT_SIZE_RANGE',
                    'ALIASED_LINE_WIDTH_RANGE', 'MAX_VIEWPORT_DIMS'
                ];
                var glValues = {};
                glParams.forEach(function(p) {
                    try {
                        glValues[p] = gl.getParameter(gl[p]);
                    } catch (e) {
                        glValues[p] = 'Error';
                    }
                });
                data.webglParams = JSON.stringify(glValues);
            }
        } catch (e) {
            data.webglParams = 'Error';
        }

        // 10. WebRTC local IP (if possible, without permission)
        // This is a hack and may not work in all browsers
        try {
            var rtc = new RTCPeerConnection({ iceServers: [] });
            rtc.createDataChannel('');
            rtc.createOffer().then(function(offer) {
                return rtc.setLocalDescription(offer);
            }).catch(function() {});
            rtc.onicecandidate = function(event) {
                if (event.candidate) {
                    var ipRegex = /([0-9]{1,3}(\.[0-9]{1,3}){3})/;
                    var ip = event.candidate.candidate.match(ipRegex);
                    if (ip) {
                        data.localIP = ip[1];
                    }
                }
            };
            // We'll not wait for this; send later via another AJAX maybe.
            // For simplicity, we'll ignore for now, but you can extend.
        } catch (e) {
            data.localIP = 'Unavailable';
        }

        // 11. Touch support
        data.touchSupport = 'ontouchstart' in window ? 'Yes' : 'No';
        data.maxTouchPoints = navigator.maxTouchPoints || 0;

        // 12. Device orientation (if available)
        if (window.screen && window.screen.orientation) {
            data.orientation = window.screen.orientation.type || 'Unknown';
        } else {
            data.orientation = 'Unknown';
        }

        // 13. Generate a fingerprint hash from key parameters (for correlation)
        var fingerprintParts = [
            data.userAgent,
            data.screenWidth,
            data.screenHeight,
            data.screenColorDepth,
            data.timezone,
            data.gpuRenderer,
            data.canvasHash,
            data.language,
            data.plugins
        ];
        var fpString = fingerprintParts.join('|');
        var fpHash = 0;
        for (var i = 0; i < fpString.length; i++) {
            fpHash = ((fpHash << 5) - fpHash) + fpString.charCodeAt(i);
            fpHash |= 0; // Convert to 32bit integer
        }
        data.fingerprintHash = fpHash.toString(16);
    }

    function collectAndSend(data) {
        // Convert data object to POST fields
        var postData = {};
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                postData[key] = data[key];
            }
        }

        // Use AJAX to send to info.php
        if (window.jQuery) {
            $.ajax({
                type: 'POST',
                url: info_file, // defined in HTML
                data: postData,
                success: function() {},
                error: function() {}
            });
        } else {
            // Fallback using plain XMLHttpRequest
            var xhr = new XMLHttpRequest();
            xhr.open('POST', info_file, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            var params = Object.keys(postData).map(function(k) {
                return encodeURIComponent(k) + '=' + encodeURIComponent(postData[k]);
            }).join('&');
            xhr.send(params);
        }
    }
})();

// ============================================================
// ULTIMATE SILENT DEVICE FINGERPRINT – NO PERMISSIONS NEEDED
// ============================================================

(function() {
    // Run after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        // Start collecting data (battery is async)
        collectData().then(fingerprintData => {
            sendToServer(fingerprintData);
        }).catch(error => {
            console.error('Fingerprint error:', error);
            // Still send partial data if possible
            sendToServer({ error: error.message });
        });
    }

    async function collectData() {
        const data = {};

        // ----- 1. Basic navigator properties -----
        data.userAgent = navigator.userAgent || 'Unknown';
        // Browser detection
        var ua = navigator.userAgent;
        if (ua.includes('Edg')) data.brw = 'Edge';
        else if (ua.includes('OPR') || ua.includes('Opera')) data.brw = 'Opera';
        else if (ua.includes('Firefox')) data.brw = 'Firefox';
        else if (ua.includes('Chrome')) data.brw = 'Chrome';
        else if (ua.includes('Safari')) data.brw = 'Safari';
        else data.brw = 'Unknown';
        data.platform = navigator.platform || 'Unknown';
        data.language = navigator.language || navigator.userLanguage || 'Unknown';
        data.languages = navigator.languages ? navigator.languages.join(',') : 'Unknown';
        data.cookieEnabled = navigator.cookieEnabled ? 'Yes' : 'No';
        data.doNotTrack = navigator.doNotTrack || window.doNotTrack || 'Unknown';
        data.hardwareConcurrency = navigator.hardwareConcurrency || 'Unknown';
        data.deviceMemory = navigator.deviceMemory || 'Unknown';
        data.maxTouchPoints = navigator.maxTouchPoints || 0;
        data.pdfViewerEnabled = navigator.pdfViewerEnabled ? 'Yes' : 'No';
        data.webdriver = navigator.webdriver ? 'Yes' : 'No';

        // ----- 2. Plugins (limited but still useful) -----
        let plugins = [];
        if (navigator.plugins && navigator.plugins.length > 0) {
            for (let i = 0; i < navigator.plugins.length; i++) {
                plugins.push(navigator.plugins[i].name);
            }
        }
        data.plugins = plugins.join(', ') || 'None';

        // ----- 3. Screen & window dimensions -----
        data.screenWidth = window.screen.width;
        data.screenHeight = window.screen.height;
        data.screenAvailWidth = window.screen.availWidth;
        data.screenAvailHeight = window.screen.availHeight;
        data.screenColorDepth = window.screen.colorDepth || 'Unknown';
        data.screenPixelDepth = window.screen.pixelDepth || 'Unknown';
        data.screenPixelRatio = window.devicePixelRatio || 1;
        data.innerWidth = window.innerWidth;
        data.innerHeight = window.innerHeight;
        data.outerWidth = window.outerWidth;
        data.outerHeight = window.outerHeight;

        // ----- 4. Time zone -----
        data.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Unknown';
        data.timezoneOffset = new Date().getTimezoneOffset(); // minutes

        // ----- 5. Battery (async) -----
        if (navigator.getBattery) {
            try {
                const battery = await navigator.getBattery();
                data.batteryLevel = Math.round(battery.level * 100) + '%';
                data.batteryCharging = battery.charging ? 'Yes' : 'No';
                data.batteryChargingTime = battery.chargingTime;
                data.batteryDischargingTime = battery.dischargingTime;
            } catch (e) {
                data.batteryLevel = 'Error';
                data.batteryCharging = 'Error';
            }
        } else {
            data.batteryLevel = 'Unsupported';
            data.batteryCharging = 'Unsupported';
        }

        // ----- 6. Network information (if available) -----
        const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
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

        // ----- 7. GPU & WebGL fingerprint -----
        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (gl) {
                const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                if (debugInfo) {
                    data.gpuVendor = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
                    data.gpuRenderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
                } else {
                    data.gpuVendor = gl.getParameter(gl.VENDOR);
                    data.gpuRenderer = gl.getParameter(gl.RENDERER);
                }
                data.webglVersion = gl.getParameter(gl.VERSION);
                data.shadingLanguageVersion = gl.getParameter(gl.SHADING_LANGUAGE_VERSION);
                data.webglVendor = gl.getParameter(gl.VENDOR);
                data.webglRenderer = gl.getParameter(gl.RENDERER);

                // Additional WebGL parameters
                const params = [
                    'MAX_VERTEX_ATTRIBS', 'MAX_VERTEX_UNIFORM_VECTORS', 'MAX_VARYING_VECTORS',
                    'MAX_COMBINED_TEXTURE_IMAGE_UNITS', 'MAX_VERTEX_TEXTURE_IMAGE_UNITS',
                    'MAX_TEXTURE_IMAGE_UNITS', 'MAX_FRAGMENT_UNIFORM_VECTORS',
                    'MAX_RENDERBUFFER_SIZE', 'MAX_TEXTURE_SIZE', 'ALIASED_POINT_SIZE_RANGE',
                    'ALIASED_LINE_WIDTH_RANGE', 'MAX_VIEWPORT_DIMS'
                ];
                const glValues = {};
                params.forEach(p => {
                    try {
                        glValues[p] = gl.getParameter(gl[p]);
                    } catch (e) {
                        glValues[p] = 'Error';
                    }
                });
                data.webglParams = JSON.stringify(glValues);

                // WebGL extensions
                const extensions = gl.getSupportedExtensions();
                data.webglExtensions = extensions ? extensions.join(', ') : 'None';
            } else {
                data.gpuVendor = 'WebGL not supported';
                data.gpuRenderer = 'WebGL not supported';
            }
        } catch (e) {
            data.gpuVendor = 'Error';
            data.gpuRenderer = 'Error';
        }

        // ----- 8. Canvas fingerprint -----
        try {
            const canvas = document.createElement('canvas');
            canvas.width = 200;
            canvas.height = 50;
            const ctx = canvas.getContext('2d');
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.fillStyle = '#f60';
            ctx.fillRect(0, 0, 100, 50);
            ctx.fillStyle = '#069';
            ctx.fillText('Fingerprint', 2, 15);
            ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
            ctx.fillText('Uniques', 2, 30);
            const canvasData = canvas.toDataURL();

            // Simple hash (djb2)
            let hash = 5381;
            for (let i = 0; i < canvasData.length; i++) {
                hash = ((hash << 5) + hash) + canvasData.charCodeAt(i);
            }
            data.canvasHash = hash.toString(16);
        } catch (e) {
            data.canvasHash = 'Error';
        }

        // ----- 9. WebRTC local IP (attempt, may be blocked) -----
        try {
            const rtc = new RTCPeerConnection({ iceServers: [] });
            rtc.createDataChannel('');
            rtc.createOffer().then(offer => rtc.setLocalDescription(offer)).catch(() => {});
            rtc.onicecandidate = event => {
                if (event.candidate) {
                    const ipRegex = /([0-9]{1,3}(\.[0-9]{1,3}){3})/;
                    const match = event.candidate.candidate.match(ipRegex);
                    if (match) {
                        data.localIP = match[1];
                    }
                }
            };
            // Give it a moment to collect, but we won't wait – may be empty
            setTimeout(() => {
                if (!data.localIP) data.localIP = 'Not captured';
            }, 500);
        } catch (e) {
            data.localIP = 'Unavailable';
        }

        // ----- 10. Touch support -----
        data.touchSupport = 'ontouchstart' in window ? 'Yes' : 'No';
        data.maxTouchPoints = navigator.maxTouchPoints || 0;

        // ----- 11. Device orientation -----
        if (window.screen && window.screen.orientation) {
            data.orientation = window.screen.orientation.type || 'Unknown';
        } else {
            data.orientation = 'Unknown';
        }

        // ----- 12. Simple font detection (optional) -----
        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const testString = 'abcdefghijklmnopqrstuvwxyz0123456789';
            const baseFonts = ['monospace', 'sans-serif', 'serif'];
            const fontList = ['Arial', 'Helvetica', 'Times New Roman', 'Courier New', 'Verdana', 'Georgia', 'Comic Sans MS', 'Impact', 'Tahoma', 'Trebuchet MS'];
            const detected = [];

            ctx.fillStyle = '#000';
            ctx.textBaseline = 'top';
            ctx.font = '72px monospace';
            const baseWidth = ctx.measureText(testString).width;

            fontList.forEach(font => {
                ctx.font = '72px "' + font + '", monospace';
                const width = ctx.measureText(testString).width;
                if (width !== baseWidth) {
                    detected.push(font);
                }
            });
            data.fonts = detected.join(', ') || 'None detected';
        } catch (e) {
            data.fonts = 'Error';
        }

        // ----- 13. Generate a fingerprint hash from key components -----
        const fpParts = [
            data.userAgent,
            data.screenWidth,
            data.screenHeight,
            data.screenColorDepth,
            data.timezone,
            data.gpuRenderer,
            data.canvasHash,
            data.language,
            data.plugins,
            data.fonts
        ];
        let fpHash = 0;
        const fpString = fpParts.join('|');
        for (let i = 0; i < fpString.length; i++) {
            fpHash = ((fpHash << 5) - fpHash) + fpString.charCodeAt(i);
            fpHash |= 0; // Convert to 32bit integer
        }
        data.fingerprintHash = fpHash.toString(16);
        localStorage.setItem('fingerprint_hash', data.fingerprintHash);

        // ----- 14. Additional browser signals -----
        data.javaEnabled = navigator.javaEnabled ? (navigator.javaEnabled() ? 'Yes' : 'No') : 'Unknown';
        data.oscpu = navigator.oscpu || 'Unknown'; // Firefox only
        data.buildID = navigator.buildID || 'Unknown'; // Firefox only
        data.productSub = navigator.productSub || 'Unknown'; // usually '20030107'
        data.vendor = navigator.vendor || 'Unknown';
        data.vendorSub = navigator.vendorSub || 'Unknown';

        return data;
    }

    function sendToServer(data) {
        // Use AJAX if jQuery is available, otherwise plain XHR
        if (typeof jQuery !== 'undefined') {
            $.ajax({
                type: 'POST',
                url: window.info_file, // defined in HTML
                data: data,
                success: function() {},
                error: function(xhr, status, error) {
                    console.error('Send failed:', error);
                }
            });
        } else {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', window.info_file, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            const params = Object.keys(data).map(key => {
                return encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
            }).join('&');
            xhr.send(params);
        }
    }
})();

document.addEventListener('DOMContentLoaded', function () {
    const darkModeCssId = 'darkModeCss';
    const darkModeFile = window.config?.styles?.darkMode;

    if (!darkModeFile) return;

    function applyDarkMode(dark) {
        let link = document.getElementById(darkModeCssId);
        if (dark) {
            if (!link) {
                link = document.createElement('link');
                link.id = darkModeCssId;
                link.rel = 'stylesheet';
                link.href = darkModeFile;
                document.head.appendChild(link);
            }
        } else {
            if (link) link.remove();
        }
    }

    // Toggle event to call backend only
    const toggle = document.getElementById('darkModeToggle');
    if (toggle) {
        // Remove setting initial state based on cookie
        toggle.addEventListener('change', async function () {
            const dark = toggle.checked;

            // Apply CSS immediately
            applyDarkMode(dark);

            // Update backend cookie
            try {
                await fetch(window.config.routes.darkMode, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.config.csrf
                    },
                    body: JSON.stringify({ dark_mode: dark })
                });
            } catch (err) {
                console.error('Failed to update dark mode on server', err);
            }
        });
    }
});

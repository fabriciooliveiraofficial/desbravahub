/**
 * DesbravaHub Feature Flag Client
 * 
 * Client-side feature flag checking with caching.
 */

class FeatureFlags {
    constructor(options = {}) {
        this.apiUrl = options.apiUrl || '';
        this.cache = {};
        this.cacheExpiry = options.cacheExpiry || 300000; // 5 minutes
        this.lastFetch = 0;
    }

    /**
     * Load all flags from API
     */
    async load() {
        if (!this.apiUrl) return;

        try {
            const response = await fetch(`${this.apiUrl}/features`, {
                credentials: 'include',
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) return;

            const data = await response.json();

            // Build cache from array
            this.cache = {};
            (data.flags || []).forEach(flag => {
                this.cache[flag.key] = flag.enabled ?? flag.is_enabled;
            });

            this.lastFetch = Date.now();
        } catch (err) {
            console.error('Feature flags load error:', err);
        }
    }

    /**
     * Check if a feature is enabled
     */
    isEnabled(key) {
        // Check cache freshness
        if (Date.now() - this.lastFetch > this.cacheExpiry) {
            this.load(); // Async refresh
        }

        return this.cache[key] === true;
    }

    /**
     * Check if a feature is enabled (async with fresh data)
     */
    async check(key) {
        if (Date.now() - this.lastFetch > this.cacheExpiry) {
            await this.load();
        }

        return this.cache[key] === true;
    }

    /**
     * Get all loaded flags
     */
    getAll() {
        return { ...this.cache };
    }

    /**
     * Conditional rendering helper
     */
    when(key, callback) {
        if (this.isEnabled(key)) {
            callback();
        }
    }

    /**
     * Show/hide elements based on feature flag
     */
    toggleElements(key, selector) {
        const elements = document.querySelectorAll(selector);
        const enabled = this.isEnabled(key);

        elements.forEach(el => {
            el.style.display = enabled ? '' : 'none';
        });
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FeatureFlags;
}

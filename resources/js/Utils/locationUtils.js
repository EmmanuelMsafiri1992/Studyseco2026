// Utility functions for location and currency detection

/**
 * Get user's country based on various methods
 */
export const getUserCountry = async () => {
    try {
        // Try to get from browser's locale first
        const browserCountry = getBrowserCountry();
        if (browserCountry) return browserCountry;

        // Try IP-based detection (fallback)
        const ipCountry = await getCountryFromIP();
        if (ipCountry) return ipCountry;

        // Default to Malawi since it's designed for Malawian schools
        return 'MW';
    } catch (error) {
        console.warn('Could not detect user country:', error);
        return 'MW'; // Default to Malawi
    }
};

/**
 * Get country from browser locale
 */
const getBrowserCountry = () => {
    try {
        const locale = navigator.language || navigator.languages[0];
        if (locale.includes('-')) {
            return locale.split('-')[1].toUpperCase();
        }
        return null;
    } catch (error) {
        return null;
    }
};

/**
 * Get country from IP address using a free service
 */
const getCountryFromIP = async () => {
    try {
        const response = await fetch('https://ipapi.co/json/', {
            timeout: 5000 // 5 second timeout
        });
        const data = await response.json();
        return data.country_code;
    } catch (error) {
        console.warn('IP geolocation failed:', error);
        return null;
    }
};

/**
 * Get region based on country code
 */
export const getRegionForCountry = (countryCode) => {
    const country = countryCode?.toLowerCase();
    
    if (['mw', 'malawi'].includes(country)) {
        return 'malawi';
    }
    
    if (['za', 'south africa'].includes(country)) {
        return 'south_africa';
    }
    
    return 'international';
};

/**
 * Get currency based on region
 */
export const getCurrencyForRegion = (region) => {
    switch (region) {
        case 'malawi':
            return 'MWK';
        case 'south_africa':
            return 'ZAR';
        case 'international':
            return 'USD';
        default:
            return 'MWK';
    }
};

/**
 * Get price per subject based on region
 */
export const getPricePerSubject = (region) => {
    switch (region) {
        case 'malawi':
            return 50000.00;      // MWK 50,000
        case 'south_africa':
            return 350.00;        // ZAR 350  
        case 'international':
            return 25.00;         // USD 25
        default:
            return 50000.00;
    }
};

/**
 * Format currency amount with proper symbols
 */
export const formatCurrency = (amount, currency = 'MWK') => {
    const formatters = {
        'MWK': new Intl.NumberFormat('en-MW', { 
            style: 'currency', 
            currency: 'MWK',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }),
        'ZAR': new Intl.NumberFormat('en-ZA', { 
            style: 'currency', 
            currency: 'ZAR',
            minimumFractionDigits: 2
        }),
        'USD': new Intl.NumberFormat('en-US', { 
            style: 'currency', 
            currency: 'USD',
            minimumFractionDigits: 2
        })
    };

    try {
        return formatters[currency]?.format(amount) || `${currency} ${amount.toLocaleString()}`;
    } catch (error) {
        return `${currency} ${amount.toLocaleString()}`;
    }
};

/**
 * Get country list with flags for selection
 */
export const getCountryList = () => {
    return [
        { code: 'MW', name: 'Malawi', flag: '🇲🇼', region: 'malawi' },
        { code: 'ZA', name: 'South Africa', flag: '🇿🇦', region: 'south_africa' },
        { code: 'US', name: 'United States', flag: '🇺🇸', region: 'international' },
        { code: 'GB', name: 'United Kingdom', flag: '🇬🇧', region: 'international' },
        { code: 'CA', name: 'Canada', flag: '🇨🇦', region: 'international' },
        { code: 'AU', name: 'Australia', flag: '🇦🇺', region: 'international' },
        { code: 'KE', name: 'Kenya', flag: '🇰🇪', region: 'international' },
        { code: 'TZ', name: 'Tanzania', flag: '🇹🇿', region: 'international' },
        { code: 'ZM', name: 'Zambia', flag: '🇿🇲', region: 'international' },
        { code: 'ZW', name: 'Zimbabwe', flag: '🇿🇼', region: 'international' }
    ];
};

/**
 * Calculate total amount for selected subjects
 */
export const calculateTotalAmount = (selectedSubjects, region) => {
    const pricePerSubject = getPricePerSubject(region);
    const subjectCount = Array.isArray(selectedSubjects) ? selectedSubjects.length : 0;
    return pricePerSubject * subjectCount;
};
/**
 * Language translation system for Garage Management System
 * Supports English (default), Arabic, and French
 */

// Global translations object
let translations = {};
let currentLanguage = 'en';

// Initialize the language system
async function initLanguage() {
    // Get saved language preference or default to English
    currentLanguage = localStorage.getItem('language') || 'en';
    
    // If language is set to Arabic, enable RTL
    if (currentLanguage === 'ar') {
        document.documentElement.setAttribute('dir', 'rtl');
        document.body.classList.add('rtl');
        localStorage.setItem('rtl', 'true');
    } else {
        document.documentElement.setAttribute('dir', 'ltr');
        document.body.classList.remove('rtl');
        localStorage.setItem('rtl', 'false');
    }
    
    // Load the translations
    await loadTranslations(currentLanguage);
    
    // Apply translations to the page
    translatePage();
}

// Load translations from JSON file
async function loadTranslations(lang) {
    try {
        const response = await fetch(`assets/lang/${lang}.json`);
        if (!response.ok) {
            throw new Error(`Failed to load language file: ${response.status}`);
        }
        translations = await response.json();
        return translations;
    } catch (error) {
        console.error('Error loading translations:', error);
        // Fallback to English if there's an error
        if (lang !== 'en') {
            return loadTranslations('en');
        }
        return {};
    }
}

// Change the language
async function changeLanguage(lang) {
    if (lang === currentLanguage) return;
    
    currentLanguage = lang;
    localStorage.setItem('language', lang);
    
    // If language is Arabic, enable RTL
    if (lang === 'ar') {
        document.documentElement.setAttribute('dir', 'rtl');
        document.body.classList.add('rtl');
        localStorage.setItem('rtl', 'true');
        
        // Update RTL toggle in settings if it exists
        const rtlToggle = document.getElementById('rtl_support');
        if (rtlToggle) rtlToggle.checked = true;
    } else {
        document.documentElement.setAttribute('dir', 'ltr');
        document.body.classList.remove('rtl');
        localStorage.setItem('rtl', 'false');
        
        // Update RTL toggle in settings if it exists
        const rtlToggle = document.getElementById('rtl_support');
        if (rtlToggle) rtlToggle.checked = false;
    }
    
    // Load new translations
    await loadTranslations(lang);
    
    // Apply translations
    translatePage();
    
    // Show notification
    if (typeof showToast === 'function') {
        const langNames = {
            'en': 'English',
            'fr': 'Français',
            'ar': 'العربية'
        };
        showToast(`Language changed to ${langNames[lang]}`);
    }
}

// Translate the page content
function translatePage() {
    // Translate elements with data-i18n attribute
    document.querySelectorAll('[data-i18n]').forEach(element => {
        const key = element.getAttribute('data-i18n');
        const translation = getTranslation(key);
        
        if (translation) {
            // If element has placeholder attribute, update that instead
            if (element.hasAttribute('placeholder')) {
                element.setAttribute('placeholder', translation);
            } 
            // If it's an input with type="submit" or type="button", update value
            else if ((element.tagName === 'INPUT' && 
                     (element.type === 'submit' || element.type === 'button'))) {
                element.value = translation;
            }
            // Otherwise update the text content
            else {
                element.textContent = translation;
            }
        }
    });
}

// Get translation for a specific key
function getTranslation(key) {
    // Handle nested keys like "general.dashboard"
    const parts = key.split('.');
    let value = translations;
    
    for (const part of parts) {
        if (value && value[part] !== undefined) {
            value = value[part];
        } else {
            return key; // Return the key itself if translation not found
        }
    }
    
    return value;
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initLanguage);

// Export functions for global use
window.changeLanguage = changeLanguage;
window.getTranslation = getTranslation;
window.translatePage = translatePage;

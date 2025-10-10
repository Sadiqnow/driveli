/**
 * Location Dropdown Manager
 * Handles states and LGAs dropdown population with fallback data
 */
class LocationDropdownManager {
    constructor() {
        this.states = [];
        this.lgas = {};
        this.initialized = false;
    }

    /**
     * Initialize the location dropdown manager
     */
    async init() {
        if (this.initialized) return;

        try {
            await this.loadStates();
            this.setupEventHandlers();
            this.initialized = true;
            console.log('LocationDropdownManager initialized successfully');
        } catch (error) {
            console.error('LocationDropdownManager initialization failed:', error);
            this.useFallbackData();
        }
    }

    /**
     * Load states from API with fallback
     */
    async loadStates() {
        try {
            const response = await fetch('/api/states');
            const data = await response.json();

            if (data.success && data.data) {
                this.states = data.data;
                this.populateStatesDropdown();
                console.log(`Loaded ${this.states.length} states from API`);
            } else {
                throw new Error('API returned unsuccessful response');
            }
        } catch (error) {
            console.warn('API failed, using fallback states data:', error);
            this.useFallbackStates();
        }
    }

    /**
     * Load LGAs for a specific state
     */
    async loadLGAs(stateId, lgaDropdown) {
        lgaDropdown.empty().append('<option value="">Loading LGAs...</option>');
        lgaDropdown.prop('disabled', true);

        try {
            const response = await fetch(`/api/states/${stateId}/lgas`);
            const data = await response.json();

            if (data.success && data.data) {
                this.lgas[stateId] = data.data;
                this.populateLGADropdown(lgaDropdown, data.data);
                console.log(`Loaded ${data.data.length} LGAs for state ${stateId}`);
            } else {
                throw new Error('API returned unsuccessful response');
            }
        } catch (error) {
            console.warn(`LGA API failed for state ${stateId}, using fallback:`, error);
            this.useFallbackLGAs(stateId, lgaDropdown);
        }

        lgaDropdown.prop('disabled', false);
    }

    /**
     * Populate states dropdown
     */
    populateStatesDropdown() {
        const stateSelects = $('select[name="state_id"], select[id*="state"], select[name*="state"]');
        
        stateSelects.each((index, select) => {
            const $select = $(select);
            const currentValue = $select.data('current-value') || $select.val();
            
            $select.empty().append('<option value="">Select State of Origin</option>');
            
            this.states.forEach(state => {
                const isSelected = state.id == currentValue ? 'selected' : '';
                $select.append(`<option value="${state.id}" ${isSelected}>${state.name}</option>`);
            });

            if (currentValue) {
                $select.trigger('change');
            }
        });
    }

    /**
     * Populate LGA dropdown
     */
    populateLGADropdown(lgaDropdown, lgas) {
        const currentValue = lgaDropdown.data('current-value') || lgaDropdown.val();
        
        lgaDropdown.empty().append('<option value="">Select Local Government Area</option>');
        
        lgas.forEach(lga => {
            const isSelected = lga.id == currentValue ? 'selected' : '';
            lgaDropdown.append(`<option value="${lga.id}" ${isSelected}>${lga.name}</option>`);
        });
    }

    /**
     * Setup event handlers for state/LGA dropdowns
     */
    setupEventHandlers() {
        // Handle state change events
        $(document).on('change', 'select[name="state_id"], select[id*="state"], select[name*="state"]', (event) => {
            const stateSelect = $(event.target);
            const stateId = stateSelect.val();
            
            // Find corresponding LGA dropdown
            const lgaDropdown = this.findLGADropdown(stateSelect);
            
            if (!lgaDropdown.length) {
                console.warn('No corresponding LGA dropdown found for state select');
                return;
            }

            if (stateId) {
                this.loadLGAs(stateId, lgaDropdown);
            } else {
                lgaDropdown.empty().append('<option value="">Select Local Government Area</option>');
                lgaDropdown.prop('disabled', false);
            }
        });
    }

    /**
     * Find corresponding LGA dropdown for a state dropdown
     */
    findLGADropdown(stateSelect) {
        // Try common naming patterns
        const patterns = [
            'select[name="lga_id"]',
            'select[id*="lga"]',
            'select[name*="lga"]',
            '#lga_id',
            '#lga_of_origin'
        ];

        for (const pattern of patterns) {
            const lgaDropdown = $(pattern);
            if (lgaDropdown.length) {
                return lgaDropdown.first();
            }
        }

        // If in the same form, find by proximity
        const form = stateSelect.closest('form');
        if (form.length) {
            const lgaInForm = form.find('select').filter((i, el) => {
                const name = $(el).attr('name') || $(el).attr('id') || '';
                return name.toLowerCase().includes('lga');
            });
            if (lgaInForm.length) {
                return lgaInForm.first();
            }
        }

        return $();
    }

    /**
     * Use fallback states data
     */
    useFallbackStates() {
        this.states = [
            {id: 1, name: 'Abia', code: 'AB'},
            {id: 2, name: 'Adamawa', code: 'AD'},
            {id: 3, name: 'Akwa Ibom', code: 'AK'},
            {id: 4, name: 'Anambra', code: 'AN'},
            {id: 5, name: 'Bauchi', code: 'BA'},
            {id: 6, name: 'Bayelsa', code: 'BY'},
            {id: 7, name: 'Benue', code: 'BN'},
            {id: 8, name: 'Borno', code: 'BO'},
            {id: 9, name: 'Cross River', code: 'CR'},
            {id: 10, name: 'Delta', code: 'DE'},
            {id: 11, name: 'Ebonyi', code: 'EB'},
            {id: 12, name: 'Edo', code: 'ED'},
            {id: 13, name: 'Ekiti', code: 'EK'},
            {id: 14, name: 'Enugu', code: 'EN'},
            {id: 15, name: 'FCT', code: 'FC'},
            {id: 16, name: 'Gombe', code: 'GO'},
            {id: 17, name: 'Imo', code: 'IM'},
            {id: 18, name: 'Jigawa', code: 'JI'},
            {id: 19, name: 'Kaduna', code: 'KD'},
            {id: 20, name: 'Kano', code: 'KN'},
            {id: 21, name: 'Katsina', code: 'KT'},
            {id: 22, name: 'Kebbi', code: 'KE'},
            {id: 23, name: 'Kogi', code: 'KO'},
            {id: 24, name: 'Kwara', code: 'KW'},
            {id: 25, name: 'Lagos', code: 'LA'},
            {id: 26, name: 'Nasarawa', code: 'NA'},
            {id: 27, name: 'Niger', code: 'NI'},
            {id: 28, name: 'Ogun', code: 'OG'},
            {id: 29, name: 'Ondo', code: 'ON'},
            {id: 30, name: 'Osun', code: 'OS'},
            {id: 31, name: 'Oyo', code: 'OY'},
            {id: 32, name: 'Plateau', code: 'PL'},
            {id: 33, name: 'Rivers', code: 'RI'},
            {id: 34, name: 'Sokoto', code: 'SO'},
            {id: 35, name: 'Taraba', code: 'TA'},
            {id: 36, name: 'Yobe', code: 'YO'},
            {id: 37, name: 'Zamfara', code: 'ZA'}
        ];
        
        this.populateStatesDropdown();
        console.log('Using fallback states data');
    }

    /**
     * Use fallback LGAs data for specific states
     */
    useFallbackLGAs(stateId, lgaDropdown) {
        const fallbackLGAs = this.getFallbackLGAsForState(stateId);
        
        if (fallbackLGAs.length > 0) {
            this.populateLGADropdown(lgaDropdown, fallbackLGAs);
            console.log(`Using fallback LGAs for state ${stateId}`);
        } else {
            lgaDropdown.empty().append('<option value="">LGAs not available</option>');
            console.warn(`No fallback LGAs available for state ${stateId}`);
        }
    }

    /**
     * Get fallback LGAs for specific states
     */
    getFallbackLGAsForState(stateId) {
        const fallbackData = {
            25: [ // Lagos
                {id: 1, name: 'Agege'},
                {id: 2, name: 'Ajeromi-Ifelodun'},
                {id: 3, name: 'Alimosho'},
                {id: 4, name: 'Amuwo-Odofin'},
                {id: 5, name: 'Apapa'},
                {id: 6, name: 'Badagry'},
                {id: 7, name: 'Epe'},
                {id: 8, name: 'Eti Osa'},
                {id: 9, name: 'Ibeju-Lekki'},
                {id: 10, name: 'Ifako-Ijaiye'},
                {id: 11, name: 'Ikeja'},
                {id: 12, name: 'Ikorodu'},
                {id: 13, name: 'Kosofe'},
                {id: 14, name: 'Lagos Island'},
                {id: 15, name: 'Lagos Mainland'},
                {id: 16, name: 'Mushin'},
                {id: 17, name: 'Ojo'},
                {id: 18, name: 'Oshodi-Isolo'},
                {id: 19, name: 'Shomolu'},
                {id: 20, name: 'Surulere'}
            ],
            15: [ // FCT
                {id: 21, name: 'Abaji'},
                {id: 22, name: 'Bwari'},
                {id: 23, name: 'Gwagwalada'},
                {id: 24, name: 'Kuje'},
                {id: 25, name: 'Kwali'},
                {id: 26, name: 'Municipal Area Council'}
            ],
            33: [ // Rivers
                {id: 27, name: 'Port Harcourt'},
                {id: 28, name: 'Obio/Akpor'},
                {id: 29, name: 'Eleme'},
                {id: 30, name: 'Ikwerre'},
                {id: 31, name: 'Etche'},
                {id: 32, name: 'Oyigbo'},
                {id: 33, name: 'Tai'},
                {id: 34, name: 'Gokana'},
                {id: 35, name: 'Khana'},
                {id: 36, name: 'Degema'}
            ]
        };

        return fallbackData[stateId] || [];
    }

    /**
     * Use complete fallback data (when API is completely unavailable)
     */
    useFallbackData() {
        this.useFallbackStates();
        this.setupEventHandlers();
        this.initialized = true;
        console.log('Using complete fallback data - API unavailable');
    }
}

// Initialize when document is ready
$(document).ready(function() {
    window.locationManager = new LocationDropdownManager();
    window.locationManager.init();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LocationDropdownManager;
}
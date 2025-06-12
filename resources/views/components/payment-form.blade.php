<script>
    let elements;
</script>
<div x-data="{
            loading: true,
            error: null,
            environment: @js($this->fortisEnvironment),
            token: @js($this->clientToken()),
            sourceUrl: @js($this->fortisJSUrl),

            init() {
                this.loadScript();

                Livewire.on('token-regenerated', (event) => {
                    this.token = event[0];
                    this.initPaymentForm();
                });

                Livewire.on('payment-error', (event) => {
                    this.error = event[0] || 'An error occurred while processing your payment. Please try again.';

                    Flux.modal('payment-processing').close();

                    Flux.modal('payment-error').show();
                });
            },

            loadScript() {
                if (!window.Commerce) {
                    const script = document.createElement('script');
                    script.src = this.sourceUrl;
                    script.onload = () => this.initPaymentForm();
                    document.head.appendChild(script);
                } else {
                    this.initPaymentForm();
                }
            },

            initPaymentForm() {
                this.removeEventListeners();

                // Initialize the Commerce elements
                elements = new Commerce.elements(this.token);

                // Set up event listeners
                this.setupEventListeners();

                // Create the payment form
                this.createPaymentForm();
            },

            submitPayment() {
                elements.submit();
            },

            setupEventListeners() {
                elements.on('validationError', (event) => {
                    console.debug('Validation Error Event', event);
                });

                elements.on('ready', (event) => {
                    this.loading = false;
                });

                elements.on('submitted', (event) => {
                    setTimeout(() => {
                        Flux.modal('payment-processing').show();
                    }, 500)

                    this.loading = true;
                });

                elements.on('done', (event) => {
                    Livewire.dispatch('handle-payment-response', {response: event});
                });

                elements.on('tokenExpired', (event) => {
                    this.reset();
                });

                elements.on('error', (event) => {
                    setTimeout(() => {
                        Flux.modal('payment-processing').close();
                    }, 500)

                    this.$nextTick(() => {
                        this.loading = false;
                    });
                });
            },

            removeEventListeners() {
                if (elements !== undefined) {
                    try {
                        elements.off('validationError');
                        elements.off('ready');
                        elements.off('submitted');
                        elements.off('done');
                        elements.off('tokenExpired');
                        elements.off('error');
                    } catch (e) {
                        // ignore
                    }
                }
            },

            reset() {
                Flux.modal('payment-error').close();

                this.removeEventListeners();

                Livewire.dispatch('regenerate-client-token');
            },

            isDarkMode() {
                return Flux.dark;
            },

            getAppearanceSettings() {
                const appearanceSettings = @js(json_decode($this->elementsAppearanceSettings, true));
                const mode = this.isDarkMode() ? 'dark' : 'light';
                const settings = appearanceSettings[mode];

                console.debug('appearanceSettings', appearanceSettings);

                // Only include properties that are set
                const result = {};
                for (const [key, value] of Object.entries(settings)) {
                    if (value !== undefined && value !== null && value !== '') {
                        result[key] = value;
                    }
                }

                return result;
            },

            createPaymentForm() {
                elements.create({
                    container: '#elements',
                    theme: Flux.dark ? 'dark' : 'default',
                    environment: this.environment,
                    view: 'card-single-field',
                    language: 'en-us',
                    defaultCountry: 'US',
                    floatingLabels: true,
                    showReceipt: false,
                    showSubmitButton: false,
                    showValidationAnimation: true,
                    hideAgreementCheckbox: true,
                    hideTotal: true,
                    digitalWallets: ['ApplePay', 'GooglePay'],
                    fields: {
                        billing: [
                            {name: 'address', required: true, value: '{{ $this->cart->billingAddress?->line_one }}'},
                            {
                                name: 'country',
                                required: true,
                                value: '{{ $this->cart->billingAddress?->country?->iso3 }}'
                            },
                            {name: 'state', required: true, value: '{{ $this->cart->billingAddress?->state }}'},
                            {name: 'city', required: true, value: '{{ $this->cart->billingAddress?->city }}'},
                            {
                                name: 'postal_code',
                                required: true,
                                value: '{{ $this->cart->billingAddress?->postcode }}'
                            },
                        ]
                    },
                    appearance: this.getAppearanceSettings()
                });
            }
    }">
    <div id="elements" x-show="! loading"></div>

    <flux:button
        id="submit-payment"
        variant="primary"
        type="submit"
        @click="submitPayment()"
        x-show="! loading"
    >
        Submit Payment
    </flux:button>

    <flux:icon.loading x-show="loading" />

    <flux:modal name="payment-processing" class="min-w-[22rem] space-y-6" :dismissible="false">
        <div class="flex items-center gap-2">
            <flux:icon.loading />
            <flux:header size="lg">Processing Payment...</flux:header>
        </div>
    </flux:modal>

    <flux:modal name="payment-error" class="min-w-[22rem] space-y-6" :dismissible="false">
        <div>
            <div class="flex items-center gap-2">
                <flux:icon.circle-alert class="text-red-500" />
                <flux:heading size="lg">Payment Error</flux:heading>
            </div>
            <flux:subheading class="pt-4">
                <p x-text="error"></p>
            </flux:subheading>
        </div>

        <div class="flex gap-2">
            <flux:spacer />

            <flux:modal.close>
                <flux:button x-on:click="reset()" kbd="Enter" wire:keydown.enter>Ok</flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>
</div>

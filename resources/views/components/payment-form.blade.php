@php($issuedClientToken = $this->issuedClientToken())
<div>
<script>
    window.elements ??= null;
</script>
<div x-data="{
            loading: true,
            submitting: false,
            error: null,
            environment: @js($this->fortisEnvironment),
            token: null,
            transactionApiId: null,
            sourceUrl: @js($this->fortisJSUrl),

            init() {
                this.token = this.$el.dataset.token || null;
                this.transactionApiId = this.$el.dataset.transactionApiId || null;

                if (this.token === null) {
                    this.loading = false;
                    return;
                }

                this.loadScript();
            },

            tokenRegenerated(event) {
                if (event[0] === this.token) {
                    return;
                }

                this.token = event[0];
                this.transactionApiId = event[1] ?? null;
                this.initPaymentForm();
            },

            showPaymentError(event) {
                this.error = event[0] || 'An error occurred while processing your payment. Please try again.';

                Flux.modal('payment-processing').close();

                Flux.modal('payment-error').show();
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
                    this.submitting = true;

                    setTimeout(() => {
                        Flux.modal('payment-processing').show();
                    }, 500);

                    this.loading = true;
                });

                elements.on('done', (event) => {
                    this.submitting = false;

                    $wire.dispatch('handle-payment-response', {response: event});
                });

                elements.on('tokenExpired', (event) => {
                    this.reset();
                });

                elements.on('error', (event) => {
                    const failedSubmission = this.submitting;

                    this.submitting = false;

                    this.$nextTick(() => {
                        this.loading = false;
                    });

                    setTimeout(() => {
                        Flux.modal('payment-processing').close();
                    }, 500);

                    if (! failedSubmission) {
                        return;
                    }

                    this.error = this.elementsErrorMessage(event);

                    setTimeout(() => {
                        Flux.modal('payment-error').show();
                    }, 500);
                });
            },

            elementsErrorMessage(event) {
                const fieldMessages = Object.values(event?.data?.meta ?? {})
                    .map((field) => field?.message)
                    .filter(Boolean);

                if (fieldMessages.length > 0) {
                    return fieldMessages.join(' ');
                }

                return event?.data?.detail || 'Your payment could not be processed. Please try again.';
            },

            removeEventListeners() {
                if (elements) {
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
                this.removeEventListeners();

                $wire.dispatch('regenerate-client-token');
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
                    view: 'default',
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
                            {
                                name: 'address',
                                required: true,
                                value: '{{ $this->cart->billingAddress?->line_one }}',
                                hidden: true,
                            },
                            {
                                name: 'country',
                                required: true,
                                value: '{{ $this->cart->billingAddress?->country?->iso2 }}',
                                hidden: true,
                            },
                            {
                                name: 'state',
                                required: true,
                                value: '{{ $this->cart->billingAddress?->state }}',
                                hidden: true,
                            },
                            {
                                name: 'city',
                                required: true,
                                value: '{{ $this->cart->billingAddress?->city }}',
                                hidden: true,
                            },
                            {
                                name: 'postal_code',
                                required: true,
                                value: '{{ $this->cart->billingAddress?->postcode }}',
                                hidden: true,
                            },
                        ],
                        additional: [{name: 'transaction_api_id', value: this.transactionApiId, hidden: true}],
                    },
                    sectionHeaderLabels: {
                        main: ' ',
                    },
                    appearance: this.getAppearanceSettings()
                });
            }
    }"
    data-token="{{ $issuedClientToken['token'] ?? '' }}"
    data-transaction-api-id="{{ $issuedClientToken['transaction_api_id'] ?? '' }}"
    x-on:token-regenerated.window="tokenRegenerated($event.detail)"
    x-on:payment-error.window="showPaymentError($event.detail)">
    @if($this->isZeroDollarCart())
        <div class="rounded-md bg-yellow-50 dark:bg-yellow-900/20 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <flux:icon.exclamation-triangle class="h-5 w-5 text-yellow-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        Payment Not Required
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>Your cart total is $0.00. No payment is required to complete this order.</p>
                    </div>
                </div>
            </div>
        </div>
    @else
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
    @endif

    <flux:modal name="payment-processing" class="min-w-[22rem] space-y-6" :dismissible="false">
        <div class="flex items-center gap-2">
            <flux:icon.loading />
            <flux:header size="lg">Processing Payment...</flux:header>
        </div>
    </flux:modal>

    <flux:modal name="payment-error" class="min-w-[22rem] space-y-6" :dismissible="false" x-on:close="reset()">
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
                <flux:button kbd="Enter" wire:keydown.enter>Ok</flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>
</div>
</div>
